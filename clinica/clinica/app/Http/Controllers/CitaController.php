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
            ->whereDate('fecha', '>=', today())
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
        $estados = ['pendiente', 'confirmada', 'cancelada'];

        return view('citas.create', compact('pacientes', 'estados'));
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
            'motivo' => ['required', 'string', 'max:255'],
            'estado' => ['nullable', Rule::in(['pendiente', 'confirmada', 'cancelada'])],
            'observaciones' => ['nullable', 'string'],
        ]);

        $validated['estado'] = $validated['estado'] ?? 'pendiente';

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
        $estados = ['pendiente', 'confirmada', 'cancelada'];

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
            'motivo' => ['required', 'string', 'max:255'],
            'estado' => ['required', Rule::in(['pendiente', 'confirmada', 'cancelada'])],
            'observaciones' => ['nullable', 'string'],
        ]);

        $cita->update($validated);

        return redirect()->route('citas.index')
            ->with('success', 'Cita actualizada correctamente.');
    }

    /**
     * Cancel the specified appointment instead of deleting it.
     */
    public function destroy(Cita $cita)
    {
        $cita->update([
            'estado' => 'cancelada',
        ]);

        return redirect()->route('citas.index')
            ->with('success', 'Cita cancelada correctamente.');
    }
}
