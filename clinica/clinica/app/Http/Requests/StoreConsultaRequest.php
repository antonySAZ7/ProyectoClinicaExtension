<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreConsultaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageClinicalHistory() ?? false;
    }

    public function rules(): array
    {
        return [
            'fecha' => ['required', 'date'],
            'motivo' => ['required', 'string', 'max:255'],
            'diagnostico' => ['required', 'string', 'max:4000'],
            'observaciones' => ['nullable', 'string', 'max:4000'],

            // Signos vitales (opcionales)
            'peso' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'altura' => ['nullable', 'numeric', 'min:0', 'max:99.99'],
            'presion_arterial' => ['nullable', 'string', 'max:20'],
            'frecuencia_cardiaca' => ['nullable', 'integer', 'min:0', 'max:400'],
            'frecuencia_respiratoria' => ['nullable', 'integer', 'min:0', 'max:200'],
            'signos_otros' => ['nullable', 'string', 'max:255'],

            'archivos' => ['nullable', 'array'],
            'archivos.*' => ['file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $observaciones = $this->input('observaciones');

        $this->merge([
            'observaciones' => is_string($observaciones) ? trim($observaciones) : null,
        ]);
    }
}
