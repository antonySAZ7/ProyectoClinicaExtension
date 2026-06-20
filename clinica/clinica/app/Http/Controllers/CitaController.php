<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Paciente;
use App\Models\RecordatorioSeguimiento;
use App\Models\Servicio;
use App\Services\CitaService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CitaController extends Controller
{
    /**
     * Display a listing of future appointments.
     */
    public function index()
    {
        $citas = Cita::with(['paciente', 'consulta'])
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
                    Cita::ESTADO_ATENDIDA => ['bg' => '#2563eb', 'border' => '#1d4ed8'],
                    Cita::ESTADO_CANCELADA => ['bg' => '#dc2626', 'border' => '#b91c1c'],
                    Cita::ESTADO_NO_SHOW => ['bg' => '#6b7280', 'border' => '#4b5563'],
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
    public function store(Request $request, CitaService $service)
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

        $service->createBackoffice($validated);

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
        $estados = [
            Cita::ESTADO_PENDIENTE,
            Cita::ESTADO_CONFIRMADA,
            Cita::ESTADO_ATENDIDA,
            Cita::ESTADO_CANCELADA,
            Cita::ESTADO_NO_SHOW,
        ];

        return view('citas.edit', compact('cita', 'pacientes', 'servicios', 'estados'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cita $cita, CitaService $service)
    {
        $validated = $request->validate([
            'paciente_id' => ['required', 'exists:pacientes,id'],
            'servicio_id' => ['nullable', Rule::exists('servicios', 'id')->where('activo', true)],
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'hora' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora'],
            'motivo' => ['required', 'string', 'max:255'],
            'estado' => ['required', Rule::in([
                Cita::ESTADO_PENDIENTE,
                Cita::ESTADO_CONFIRMADA,
                Cita::ESTADO_ATENDIDA,
                Cita::ESTADO_CANCELADA,
                Cita::ESTADO_NO_SHOW,
            ])],
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

        $service->updateBackoffice($cita, $validated);

        return redirect()->route('citas.index')
            ->with('success', 'Cita actualizada correctamente.');
    }

     /**
     * Confirm an upcoming appointment by its owner patient.
     */
    public function confirmar(Request $request, Cita $cita, CitaService $service)
    {
        [$type, $message] = $service->confirmForPatient($request->user(), $cita);

        return redirect()->route('portal')
            ->with($type, $message);
    }

    /**
     * Cancel the specified appointment instead of deleting it.
     */
    public function destroy(Cita $cita, CitaService $service)
    {
        $service->cancelBackoffice($cita);

        return redirect()->route('citas.index')
            ->with('success', 'Cita cancelada correctamente.');
    }
}
