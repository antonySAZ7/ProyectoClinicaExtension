<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePacienteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'paciente')),
                Rule::unique('pacientes', 'user_id')->ignore($this->route('paciente')),
            ],
            'nombre_completo' => 'required|string|max:255',
            'dpi' => [
                'required',
                'string',
                'max:20',
                Rule::unique('pacientes', 'dpi')->ignore($this->route('paciente')),
            ],
            'fecha_nacimiento' => 'required|date',
            'telefono' => 'required|string|max:20',
            'correo' => [
                'required',
                'email',
                'max:255',
                Rule::unique('pacientes', 'correo')->ignore($this->route('paciente')),
            ],
            'direccion' => 'required|string|max:255',
            'sexo' => 'nullable|string|max:50',
            'estado_civil' => 'nullable|string|max:100',
            'ocupacion' => 'nullable|string|max:255',
        ];
    }
}
