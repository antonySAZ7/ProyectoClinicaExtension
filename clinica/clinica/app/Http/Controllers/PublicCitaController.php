<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use App\Services\AppointmentAvailabilityService;
use App\Services\CitaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        CitaService $service
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
        $wasGuest = ! $user;
        [, $pacienteUser] = $service->createPublicAppointment($validated, $user);

        if ($wasGuest && $pacienteUser) {
            Auth::login($pacienteUser);
            $request->session()->regenerate();

            return redirect()->route('portal')
                ->with('success', 'Tu cita fue registrada y tu cuenta quedó activa. Aquí puedes ver tus citas.');
        }

        return redirect()->route('public.citas.create')
            ->with('success', 'Tu solicitud de cita fue registrada correctamente.');
    }
}
