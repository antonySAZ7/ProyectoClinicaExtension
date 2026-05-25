<?php

namespace App\Http\Controllers;

use App\Mail\CitaConfirmationMail;
use App\Models\Cita;
use App\Models\NotificacionLog;
use App\Models\Paciente;
use App\Models\Servicio;
use App\Models\User;
use App\Services\AppointmentAvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PublicCitaController extends Controller
{
    public function availability(Request $request, AppointmentAvailabilityService $availability): JsonResponse
    {
        $validated = $request->validate([
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'servicio_id' => ['required', Rule::exists('servicios', 'id')->where('activo', true)],
        ]);

        $servicio = Servicio::find($validated['servicio_id']);

        return response()->json([
            'fecha' => $validated['fecha'],
            'servicio' => [
                'id' => $servicio->id,
                'nombre' => $servicio->nombre,
                'duracion_minutos' => $servicio->duracion_minutos,
            ],
            'bloques' => $availability->timelineSlots($validated['fecha'], $servicio),
        ]);
    }

    public function create(AppointmentAvailabilityService $availability): View
    {
        $user = Auth::user();
        $user?->loadMissing('paciente');

        $servicios = Servicio::where('activo', true)->orderBy('nombre')->get();

        return view('citas.public-create', [
            'servicios' => $servicios,
            'user' => $user,
            'needsPacienteData' => ! $user || ! $user->isPaciente() || ($user->isPaciente() && ! $user->paciente),
            'fechaSugerida' => $this->nextAvailableDate($availability, $servicios->first()),
        ]);
    }

    protected function nextAvailableDate(AppointmentAvailabilityService $availability, ?Servicio $servicio): string
    {
        $fecha = now()->startOfDay();

        for ($i = 0; $i < 14; $i++) {
            if (! empty($availability->availableSlots($fecha->toDateString(), $servicio))) {
                return $fecha->toDateString();
            }
            $fecha->addDay();
        }

        return now()->addDay()->toDateString();
    }

    public function store(
        Request $request,
        AppointmentAvailabilityService $availability
    ): RedirectResponse {
        $user = $request->user();
        $user?->loadMissing('paciente');
        $needsPacienteData = ! $user || ! $user->isPaciente() || ($user->isPaciente() && ! $user->paciente);

        $rules = [
            'servicio_id' => ['required', Rule::exists('servicios', 'id')->where('activo', true)],
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'hora' => ['required', 'date_format:H:i'],
            'motivo' => ['nullable', 'string', 'max:255'],
            'nombre_completo' => [$needsPacienteData ? 'required' : 'nullable', 'string', 'max:255'],
            'correo' => [$needsPacienteData ? 'required' : 'nullable', 'email', 'max:255'],
            'dpi' => [$needsPacienteData ? 'required' : 'nullable', 'string', 'max:20'],
            'fecha_nacimiento' => [$needsPacienteData ? 'required' : 'nullable', 'date'],
            'telefono' => [$needsPacienteData ? 'required' : 'nullable', 'string', 'max:20'],
            'direccion' => [$needsPacienteData ? 'required' : 'nullable', 'string', 'max:255'],
        ];

        if ($needsPacienteData) {
            $rules['correo'][] = Rule::unique('pacientes', 'correo');
            $rules['dpi'][] = Rule::unique('pacientes', 'dpi');
            $rules['password'] = ['required', 'confirmed', Password::defaults()];
        }

        if (! $user || ! $user->isPaciente()) {
            $rules['correo'][] = Rule::unique('users', 'email');
        }

        $validated = $request->validate($rules);
        $servicio = Servicio::findOrFail($validated['servicio_id']);
        $horaFin = $availability->endTimeFor($validated['hora'], $servicio);

        if (! $availability->isAvailable($validated['fecha'], $validated['hora'], $horaFin)) {
            return back()
                ->withErrors(['hora' => 'El horario seleccionado no esta disponible.'])
                ->withInput();
        }

        $wasGuest = ! $user;

        [$cita, $pacienteUser] = DB::transaction(function () use ($validated, $user, $servicio, $horaFin) {
            $paciente = $this->resolvePaciente($validated, $user);
            $paciente->loadMissing('user');

            $cita = Cita::create([
                'paciente_id' => $paciente->id,
                'servicio_id' => $servicio->id,
                'fecha' => $validated['fecha'],
                'hora' => $validated['hora'],
                'hora_fin' => $horaFin,
                'motivo' => ($validated['motivo'] ?? null) ?: $servicio->nombre,
                'estado' => Cita::ESTADO_PENDIENTE,
            ]);

            return [$cita, $paciente->user];
        });

        $cita->load(['paciente', 'servicio']);
        Mail::to($cita->paciente->correo)->send(new CitaConfirmationMail($cita));
        $this->logEmail($cita, 'confirmacion_agendamiento', $cita->paciente->correo, [
            'fecha' => $cita->fecha?->toDateString(),
            'hora' => substr((string) $cita->hora, 0, 5),
            'servicio' => $cita->servicio?->nombre,
        ]);

        if ($wasGuest && $pacienteUser) {
            Auth::login($pacienteUser);
            $request->session()->regenerate();

            return redirect()->route('portal')
                ->with('success', 'Tu cita fue registrada y tu cuenta quedó activa. Aquí puedes ver tus citas.');
        }

        return redirect()->route('public.citas.create')
            ->with('success', 'Tu solicitud de cita fue registrada correctamente.');
    }

    protected function resolvePaciente(array $validated, ?User $user): Paciente
    {
        if ($user?->isPaciente()) {
            $user->loadMissing('paciente');

            if ($user->paciente) {
                return $user->paciente;
            }
        }

        if (! $user || ! $user->isPaciente()) {
            $user = User::create([
                'name' => $validated['nombre_completo'],
                'email' => $validated['correo'],
                'role' => User::ROLE_PACIENTE,
                'password' => Hash::make($validated['password']),
            ]);
        }

        return Paciente::create([
            'user_id' => $user->id,
            'nombre_completo' => $validated['nombre_completo'] ?? $user->name,
            'dpi' => $validated['dpi'],
            'fecha_nacimiento' => $validated['fecha_nacimiento'],
            'telefono' => $validated['telefono'],
            'correo' => $validated['correo'] ?? $user->email,
            'direccion' => $validated['direccion'],
        ]);
    }

    protected function logEmail(Cita $cita, string $tipo, string $to, array $payload): void
    {
        NotificacionLog::create([
            'cita_id' => $cita->id,
            'canal' => 'email',
            'tipo' => $tipo,
            'destinatario' => $to,
            'estado' => 'enviado',
            'payload' => $payload,
            'enviado_en' => now(),
        ]);
    }
}
