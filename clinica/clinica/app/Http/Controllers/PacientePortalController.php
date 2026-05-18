<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\NotificacionLog;
use App\Models\Servicio;
use App\Models\User;
use App\Services\AppointmentAvailabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PacientePortalController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $user->load('paciente');

        $paciente = $user->paciente;
        $citas = collect();
        $consultasRecientes = collect();

        if ($paciente) {
            $citas = $paciente->citas()
                ->upcoming()
                ->orderBy('fecha')
                ->orderBy('hora')
                ->get();

            $consultasRecientes = $paciente->consultas()
                ->with('user')
                ->orderByDesc('fecha')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();
        }

        return view('paciente.portal', [
            'user' => $user,
            'paciente' => $paciente,
            'citas' => $citas,
            'consultasRecientes' => $consultasRecientes,
        ]);
    }

    public function cancel(Request $request, Cita $cita): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $paciente = $user->paciente;

        if (! $paciente) {
            return redirect()->route('portal')
                ->with('error', 'Tu usuario todavia no tiene un expediente de paciente asociado.');
        }

        $cita = $paciente->citas()
            ->upcoming()
            ->findOrFail($cita->getKey());

        if ($cita->estado === Cita::ESTADO_CANCELADA) {
            return redirect()->route('portal')
                ->with('error', 'La cita seleccionada ya estaba cancelada.');
        }

        $cita->update([
            'estado' => Cita::ESTADO_CANCELADA,
        ]);

        return redirect()->route('portal')
            ->with('success', 'La cita fue cancelada correctamente.');
    }

    public function reschedule(
        Request $request,
        Cita $cita,
        AppointmentAvailabilityService $availability
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();
        $paciente = $user->paciente;

        if (! $paciente) {
            return redirect()->route('portal')
                ->with('error', 'Tu usuario todavia no tiene un expediente de paciente asociado.');
        }

        $cita = $paciente->citas()
            ->whereIn('estado', [Cita::ESTADO_PENDIENTE, Cita::ESTADO_CONFIRMADA])
            ->findOrFail($cita->getKey());

        if (! $cita->isFuture()) {
            return redirect()->route('portal')
                ->with('error', 'No puedes reagendar una cita pasada.');
        }

        $validated = $request->validate([
            'servicio_id' => ['nullable', Rule::exists('servicios', 'id')->where('activo', true)],
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'hora' => ['required', 'date_format:H:i'],
        ]);

        $servicio = isset($validated['servicio_id'])
            ? Servicio::find($validated['servicio_id'])
            : $cita->servicio;

        $horaFin = $servicio
            ? $availability->endTimeFor($validated['hora'], $servicio)
            : now()->setTimeFromTimeString($validated['hora'])->addMinutes(30)->format('H:i');

        if (! $availability->isAvailable($validated['fecha'], $validated['hora'], $horaFin, $cita->id)) {
            return redirect()->route('portal')
                ->with('error', 'El nuevo horario seleccionado no esta disponible.');
        }

        $cita->update([
            'servicio_id' => $servicio?->id,
            'fecha' => $validated['fecha'],
            'hora' => $validated['hora'],
            'hora_fin' => $horaFin,
            'estado' => Cita::ESTADO_PENDIENTE,
        ]);

        NotificacionLog::create([
            'cita_id' => $cita->id,
            'canal' => 'email',
            'tipo' => 'reagendamiento_paciente',
            'destinatario' => $paciente->correo,
            'estado' => 'registrado',
            'payload' => [
                'fecha' => $cita->fecha?->toDateString(),
                'hora' => substr((string) $cita->hora, 0, 5),
                'hora_fin' => substr((string) $cita->hora_fin, 0, 5),
            ],
            'enviado_en' => now(),
        ]);

        return redirect()->route('portal')
            ->with('success', 'Tu cita fue reagendada correctamente.');
    }
}
