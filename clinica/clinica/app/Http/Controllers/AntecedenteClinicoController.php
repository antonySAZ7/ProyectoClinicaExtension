<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAntecedenteClinicoRequest;
use App\Models\AntecedenteClinico;
use App\Models\Paciente;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AntecedenteClinicoController extends Controller
{
    /**
     * Formulario de edicion de la ficha clinica (antecedentes) del paciente.
     */
    public function edit(Paciente $paciente): View
    {
        $antecedente = $paciente->antecedenteClinico ?? new AntecedenteClinico();

        return view('antecedentes.edit', [
            'paciente' => $paciente,
            'antecedente' => $antecedente,
        ]);
    }

    /**
     * Guardar la ficha clinica (crea o actualiza el registro 1:1).
     */
    public function update(UpdateAntecedenteClinicoRequest $request, Paciente $paciente): RedirectResponse
    {
        $paciente->antecedenteClinico()->updateOrCreate(
            ['paciente_id' => $paciente->id],
            $request->validated(),
        );

        return redirect()->route('pacientes.antecedentes.edit', $paciente)
            ->with('success', 'Ficha clinica actualizada correctamente.');
    }
}
