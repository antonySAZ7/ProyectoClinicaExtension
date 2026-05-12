<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Paciente;
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
        $estados = [Cita::ESTADO_PENDIENTE, Cita::ESTADO_CONFIRMADA];

        return view('citas.create', compact('pacientes', 'estados'));
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'paciente_id' => ['required', 'exists:pacientes,id'],
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'hora' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora'],
            'motivo' => ['required', 'string', 'max:255'],
            'estado' => ['nullable', Rule::in([Cita::ESTADO_PENDIENTE, Cita::ESTADO_CONFIRMADA, Cita::ESTADO_CANCELADA])],
            'observaciones' => ['nullable', 'string'],
        ], [
            'hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
        ]);

        $validated['estado'] = $validated['estado'] ?? Cita::ESTADO_PENDIENTE;

        Cita::create($validated);

        return redirect()->route('citas.index')
            ->with('success', 'Cita registrada correctamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cita $cita)
    {
        $pacientes = Paciente::orderBy('nombre_completo')->get();
        $estados = [Cita::ESTADO_PENDIENTE, Cita::ESTADO_CONFIRMADA, Cita::ESTADO_CANCELADA];

        return view('citas.edit', compact('cita', 'pacientes', 'estados'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cita $cita)
    {
        $validated = $request->validate([
            'paciente_id' => ['required', 'exists:pacientes,id'],
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'hora' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora'],
            'motivo' => ['required', 'string', 'max:255'],
            'estado' => ['required', Rule::in([Cita::ESTADO_PENDIENTE, Cita::ESTADO_CONFIRMADA, Cita::ESTADO_CANCELADA])],
            'observaciones' => ['nullable', 'string'],
        ], [
            'hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
        ]);

        $cita->update($validated);

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
}
