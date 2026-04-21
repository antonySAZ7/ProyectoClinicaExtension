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
            'diagnostico' => ['required', 'string'],
            'observaciones' => ['nullable', 'array'],
            'observaciones.*' => ['nullable', 'string'],
            'archivos' => ['nullable', 'array'],
            'archivos.*' => ['file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $observaciones = collect($this->input('observaciones', []))
            ->map(fn ($observacion) => is_string($observacion) ? trim($observacion) : $observacion)
            ->filter(fn ($observacion) => filled($observacion))
            ->values()
            ->all();

        $this->merge([
            'observaciones' => $observaciones,
        ]);
    }
}
