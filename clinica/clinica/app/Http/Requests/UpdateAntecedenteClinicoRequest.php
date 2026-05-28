<?php

namespace App\Http\Requests;

use App\Models\AntecedenteClinico;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAntecedenteClinicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageClinicalHistory() ?? false;
    }

    public function rules(): array
    {
        $reglas = [
            'ultima_visita_dental' => ['nullable', 'date'],
            'ultima_visita_motivo' => ['nullable', 'string', 'max:255'],
            'tratamiento_enfermedad' => ['nullable', 'string', 'max:255'],
            'cual_medicamento' => ['nullable', 'string', 'max:255'],
            'cuales_medicamentos' => ['nullable', 'string', 'max:255'],
            'otro_antecedente' => ['nullable', 'string', 'max:255'],
            'descripcion_enfermedades' => ['nullable', 'string', 'max:4000'],
        ];

        foreach (AntecedenteClinico::camposBooleanos() as $campo) {
            $reglas[$campo] = ['nullable', 'boolean'];
        }

        return $reglas;
    }

    /**
     * Normalizar los checkboxes a boolean (presente = true, ausente = false).
     */
    protected function prepareForValidation(): void
    {
        $normalizados = [];

        foreach (AntecedenteClinico::camposBooleanos() as $campo) {
            $normalizados[$campo] = $this->boolean($campo);
        }

        $this->merge($normalizados);
    }
}
