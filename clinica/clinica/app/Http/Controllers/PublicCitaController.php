<?php

namespace App\Http\Controllers;

use App\Mail\CitaConfirmationMail;
use App\Models\Cita;
use App\Models\NotificacionLog;
use App\Models\Paciente;
use App\Models\Servicio;
use App\Models\User;
use App\Services\AppointmentAvailabilityService;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PublicCitaController extends Controller
{
    public function create(): View
    {
        $user = Auth::user();
        $user?->loadMissing('paciente');

        return view('citas.public-create', [
            'servicios' => Servicio::where('activo', true)->orderBy('nombre')->get(),
            'user' => $user,
            'needsPacienteData' => ! $user || ! $user->isPaciente() || ($user->isPaciente() && ! $user->paciente),
        ]);
    }

    public function store(
        Request $request,
        AppointmentAvailabilityService $availability,
        WhatsAppService $whatsApp
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

        $cita = DB::transaction(function () use ($validated, $user, $servicio, $horaFin) {
            $paciente = $this->resolvePaciente($validated, $user);

            return Cita::create([
                'paciente_id' => $paciente->id,
                'servicio_id' => $servicio->id,
                'fecha' => $validated['fecha'],
                'hora' => $validated['hora'],
                'hora_fin' => $horaFin,
                'motivo' => ($validated['motivo'] ?? null) ?: $servicio->nombre,
                'estado' => Cita::ESTADO_PENDIENTE,
            ]);
        });

        $cita->load(['paciente', 'servicio']);
        Mail::to($cita->paciente->correo)->send(new CitaConfirmationMail($cita));
        $this->notifyAppointmentCreated($cita, $whatsApp);

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
                'password' => Hash::make(Str::password(16)),
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

    protected function notifyAppointmentCreated(Cita $cita, WhatsAppService $whatsApp): void
    {
        $patientResult = $whatsApp->sendTemplate($cita->paciente->telefono, 'confirmacion_agendamiento', [
            $cita->paciente->nombre_completo,
            $cita->fecha?->format('d/m/Y'),
            substr((string) $cita->hora, 0, 5),
            $cita->servicio?->nombre ?? $cita->motivo,
        ]);

        $this->logWhatsApp($cita, 'confirmacion_agendamiento', $cita->paciente->telefono, $patientResult);

        $doctorNumber = config('services.whatsapp.doctor_number');

        if ($doctorNumber) {
            $doctorResult = $whatsApp->sendText(
                $doctorNumber,
                "Nueva cita: {$cita->paciente->nombre_completo}, {$cita->fecha?->format('d/m/Y')} {$cita->hora}, {$cita->motivo}."
            );

            $this->logWhatsApp($cita, 'nueva_cita_doctora', $doctorNumber, $doctorResult);
        }
    }

    protected function logWhatsApp(Cita $cita, string $tipo, string $to, array $result): void
    {
        NotificacionLog::create([
            'cita_id' => $cita->id,
            'canal' => 'whatsapp',
            'tipo' => $tipo,
            'destinatario' => $to,
            'estado' => $result['skipped'] ? 'omitido' : ($result['ok'] ? 'enviado' : 'error'),
            'payload' => $result,
            'enviado_en' => now(),
        ]);
    }
}
