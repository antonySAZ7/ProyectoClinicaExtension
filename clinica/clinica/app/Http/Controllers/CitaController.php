<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Paciente;
use App\Models\RecordatorioSeguimiento;
use App\Models\Servicio;
use App\Services\AppointmentAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CitaController extends Controller
{
    /**
     * Display a listing of future appointments.
     */
    public function index()
    {
        $citas = Cita::with('paciente')
            ->upcoming()
            ->orderBy('fecha')
            ->orderBy('hora')
            ->get();

        return view('citas.index', compact('citas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $pacientes = Paciente::orderBy('nombre_completo')->get();
        $servicios = Servicio::where('activo', true)->orderBy('nombre')->get();
        $estados = [Cita::ESTADO_PENDIENTE, Cita::ESTADO_CONFIRMADA];

        return view('citas.create', compact('pacientes', 'servicios', 'estados'));
    }

    /**
     * Display appointments in calendar format.
     */
    public function calendario()
    {
        $eventos = Cita::with('paciente')
            ->orderBy('fecha')
            ->orderBy('hora')
            ->get()
            ->map(function (Cita $cita) {
                $horaCompleta = substr((string) $cita->hora, 0, 8);
                $horaCorta = substr($horaCompleta, 0, 5);
                $horaFinCorta = $cita->hora_fin ? substr((string) $cita->hora_fin, 0, 5) : null;

                $inicio = $cita->fecha?->copy()->setTimeFromTimeString($horaCompleta);
                $fin = $cita->hora_fin
                    ? $cita->fecha?->copy()->setTimeFromTimeString(substr((string) $cita->hora_fin, 0, 8))
                    : $inicio?->copy()->addMinutes(30);
                $finDelDia = $cita->fecha?->copy()->endOfDay();

                if ($fin && $finDelDia && $fin->greaterThan($finDelDia)) {
                    $fin = $finDelDia;
                }

                $estado = $cita->estado ?? 'pendiente';
                $colorEvento = match ($estado) {
                    Cita::ESTADO_CONFIRMADA => ['bg' => '#16a34a', 'border' => '#15803d'],
                    Cita::ESTADO_CANCELADA => ['bg' => '#dc2626', 'border' => '#b91c1c'],
                    default => ['bg' => '#d97706', 'border' => '#b45309'],
                };

                return [
                    'title' => $cita->paciente?->nombre_completo ?? 'Paciente no disponible',
                    'start' => $inicio?->format('Y-m-d\TH:i:s'),
                    'end' => $fin?->format('Y-m-d\TH:i:s'),
                    'allDay' => false,
                    'backgroundColor' => $colorEvento['bg'],
                    'borderColor' => $colorEvento['border'],
                    'extendedProps' => [
                        'fecha' => $cita->fecha?->format('d/m/Y'),
                        'hora' => $horaCorta,
                        'hora_fin' => $horaFinCorta,
                        'estado' => ucfirst($estado),
                        'motivo' => $cita->motivo,
                        'observaciones' => $cita->observaciones ?: 'Sin observaciones',
                    ],
                ];
            })
            ->values();

        return view('citas.calendario', [
            'eventos' => $eventos,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, AppointmentAvailabilityService $availability)
    {
        $validated = $request->validate([
            'paciente_id' => ['required', 'exists:pacientes,id'],
            'servicio_id' => ['nullable', Rule::exists('servicios', 'id')->where('activo', true)],
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'hora' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora'],
            'motivo' => ['required', 'string', 'max:255'],
            'estado' => ['nullable', Rule::in([Cita::ESTADO_PENDIENTE, Cita::ESTADO_CONFIRMADA, Cita::ESTADO_CANCELADA])],
            'observaciones' => ['nullable', 'string'],
            'activar_recordatorio_seguimiento' => ['nullable', 'boolean'],
            'recordatorio_modo' => ['nullable', Rule::in([RecordatorioSeguimiento::MODO_INTERVALO, RecordatorioSeguimiento::MODO_PERSONALIZADO])],
            'recordatorio_titulo' => ['nullable', 'string', 'max:255'],
            'recordatorio_intervalo_meses' => ['nullable', 'integer', 'min:1', 'max:60'],
            'recordatorio_fecha_objetivo' => ['nullable', 'date', 'after_or_equal:today'],
            'recordatorio_dias_antes' => ['nullable', 'array'],
            'recordatorio_dias_antes.*' => ['integer', Rule::in([0, 1, 7])],
            'recordatorio_mensaje' => ['nullable', 'string', 'max:1000'],
        ], [
            'hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
        ]);

        $followUpData = $this->extractFollowUpData($validated);
        $validated['estado'] = $validated['estado'] ?? Cita::ESTADO_PENDIENTE;

        if ($followUpError = $this->validateFollowUpSelection($followUpData)) {
            return $followUpError;
        }

        if (! $availability->isAvailable($validated['fecha'], $validated['hora'], $validated['hora_fin'])) {
            return back()
                ->withErrors(['hora' => 'El horario seleccionado no esta disponible.'])
                ->withInput();
        }

        $cita = Cita::create($validated);
        $this->syncFollowUpReminder($cita, $followUpData);

        return redirect()->route('citas.index')
            ->with('success', 'Cita registrada correctamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cita $cita)
    {
        $cita->load('recordatoriosSeguimiento');
        $pacientes = Paciente::orderBy('nombre_completo')->get();
        $servicios = Servicio::where('activo', true)->orderBy('nombre')->get();
        $estados = [Cita::ESTADO_PENDIENTE, Cita::ESTADO_CONFIRMADA, Cita::ESTADO_CANCELADA];

        return view('citas.edit', compact('cita', 'pacientes', 'servicios', 'estados'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cita $cita, AppointmentAvailabilityService $availability)
    {
        $validated = $request->validate([
            'paciente_id' => ['required', 'exists:pacientes,id'],
            'servicio_id' => ['nullable', Rule::exists('servicios', 'id')->where('activo', true)],
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'hora' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora'],
            'motivo' => ['required', 'string', 'max:255'],
            'estado' => ['required', Rule::in([Cita::ESTADO_PENDIENTE, Cita::ESTADO_CONFIRMADA, Cita::ESTADO_CANCELADA])],
            'observaciones' => ['nullable', 'string'],
            'activar_recordatorio_seguimiento' => ['nullable', 'boolean'],
            'recordatorio_modo' => ['nullable', Rule::in([RecordatorioSeguimiento::MODO_INTERVALO, RecordatorioSeguimiento::MODO_PERSONALIZADO])],
            'recordatorio_titulo' => ['nullable', 'string', 'max:255'],
            'recordatorio_intervalo_meses' => ['nullable', 'integer', 'min:1', 'max:60'],
            'recordatorio_fecha_objetivo' => ['nullable', 'date', 'after_or_equal:today'],
            'recordatorio_dias_antes' => ['nullable', 'array'],
            'recordatorio_dias_antes.*' => ['integer', Rule::in([0, 1, 7])],
            'recordatorio_mensaje' => ['nullable', 'string', 'max:1000'],
        ], [
            'hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
        ]);

        $followUpData = $this->extractFollowUpData($validated);

        if ($followUpError = $this->validateFollowUpSelection($followUpData)) {
            return $followUpError;
        }

        if (
            $validated['estado'] !== Cita::ESTADO_CANCELADA
            && ! $availability->isAvailable($validated['fecha'], $validated['hora'], $validated['hora_fin'], $cita->id)
        ) {
            return back()
                ->withErrors(['hora' => 'El horario seleccionado no esta disponible.'])
                ->withInput();
        }

        $cita->update($validated);
        $this->syncFollowUpReminder($cita, $followUpData);

        return redirect()->route('citas.index')
            ->with('success', 'Cita actualizada correctamente.');
    }

    /**
     * Confirm an upcoming appointment by its owner patient.
     */
    public function confirmar(Request $request, Cita $cita)
    {
        $user = $request->user();
        $user->loadMissing('paciente');

        if (! $user->paciente || $cita->paciente_id !== $user->paciente->id) {
            abort(403);
        }

        if (! $cita->isFuture()) {
            return redirect()->route('portal')
                ->with('error', 'No puedes confirmar una cita pasada.');
        }

        if ($cita->estado === Cita::ESTADO_CANCELADA) {
            return redirect()->route('portal')
                ->with('error', 'No puedes confirmar una cita cancelada.');
        }

        if ($cita->estado === Cita::ESTADO_CONFIRMADA) {
            return redirect()->route('portal')
                ->with('success', 'Tu cita ya estaba confirmada.');
        }

        $cita->update([
            'estado' => Cita::ESTADO_CONFIRMADA,
        ]);

        return redirect()->route('portal')
            ->with('success', 'Tu cita fue confirmada correctamente.');
    }

    /**
     * Cancel the specified appointment instead of deleting it.
     */
    public function destroy(Cita $cita)
    {
        $cita->update([
            'estado' => Cita::ESTADO_CANCELADA,
        ]);

        return redirect()->route('citas.index')
            ->with('success', 'Cita cancelada correctamente.');
    }

    protected function extractFollowUpData(array &$validated): array
    {
        $data = [
            'activo' => (bool) ($validated['activar_recordatorio_seguimiento'] ?? false),
            'modo' => $validated['recordatorio_modo'] ?? RecordatorioSeguimiento::MODO_INTERVALO,
            'titulo' => $validated['recordatorio_titulo'] ?? null,
            'intervalo_meses' => $validated['recordatorio_intervalo_meses'] ?? null,
            'fecha_objetivo' => $validated['recordatorio_fecha_objetivo'] ?? null,
            'dias_antes' => $validated['recordatorio_dias_antes'] ?? [7, 1, 0],
            'mensaje' => $validated['recordatorio_mensaje'] ?? null,
        ];

        unset(
            $validated['activar_recordatorio_seguimiento'],
            $validated['recordatorio_modo'],
            $validated['recordatorio_titulo'],
            $validated['recordatorio_intervalo_meses'],
            $validated['recordatorio_fecha_objetivo'],
            $validated['recordatorio_dias_antes'],
            $validated['recordatorio_mensaje']
        );

        return $data;
    }

    protected function syncFollowUpReminder(Cita $cita, array $data): void
    {
        if (! $data['activo']) {
            $cita->recordatoriosSeguimiento()->delete();

            return;
        }

        $modo = $data['modo'];
        $intervaloMeses = $modo === RecordatorioSeguimiento::MODO_INTERVALO
            ? (int) ($data['intervalo_meses'] ?: 6)
            : null;

        $fechaObjetivo = $modo === RecordatorioSeguimiento::MODO_INTERVALO
            ? $cita->fecha?->copy()->addMonthsNoOverflow($intervaloMeses)
            : $data['fecha_objetivo'];

        if (! $fechaObjetivo) {
            return;
        }

        $cita->recordatoriosSeguimiento()->delete();
        $cita->recordatoriosSeguimiento()->create([
            'paciente_id' => $cita->paciente_id,
            'activo' => true,
            'modo' => $modo,
            'titulo' => $data['titulo'],
            'intervalo_meses' => $intervaloMeses,
            'fecha_objetivo' => $fechaObjetivo,
            'dias_antes' => collect($data['dias_antes'] ?: [7, 1, 0])
                ->map(fn ($day) => (int) $day)
                ->unique()
                ->values()
                ->all(),
            'mensaje' => $data['mensaje'],
            'fechas_enviadas' => [],
        ]);
    }

    protected function validateFollowUpSelection(array $data)
    {
        if (! $data['activo']) {
            return null;
        }

        if ($data['modo'] === RecordatorioSeguimiento::MODO_PERSONALIZADO && empty($data['fecha_objetivo'])) {
            return back()
                ->withErrors(['recordatorio_fecha_objetivo' => 'Selecciona la fecha objetivo del recordatorio.'])
                ->withInput();
        }

        return null;
    }
}
