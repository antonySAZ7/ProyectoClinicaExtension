<?php

namespace App\Http\Controllers;

use App\Models\FaseTratamiento;
use App\Models\Paciente;
use App\Models\Tratamiento;
use App\Services\TratamientoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TratamientoController extends Controller
{
    public function store(Request $request, Paciente $paciente, TratamientoService $service): RedirectResponse
    {
        $validated = $request->validate($this->tratamientoRules());

        $service->createTratamiento($paciente, $request->user(), $validated);

        return redirect()->route('pacientes.show', $paciente)
            ->with('success', 'Tratamiento registrado correctamente.');
    }

    public function update(Request $request, Tratamiento $tratamiento, TratamientoService $service): RedirectResponse
    {
        $validated = $request->validate($this->tratamientoRules(requireEstado: true));

        $service->updateTratamiento($tratamiento, $validated);

        return redirect()->route('pacientes.show', $tratamiento->paciente_id)
            ->with('success', 'Tratamiento actualizado correctamente.');
    }

    public function finalizar(Tratamiento $tratamiento, TratamientoService $service): RedirectResponse
    {
        $service->finalizar($tratamiento);

        return redirect()->route('pacientes.show', $tratamiento->paciente_id)
            ->with('success', 'Tratamiento marcado como finalizado.');
    }

    public function destroy(Tratamiento $tratamiento, TratamientoService $service): RedirectResponse
    {
        $pacienteId = $tratamiento->paciente_id;

        $service->deleteTratamiento($tratamiento);

        return redirect()->route('pacientes.show', $pacienteId)
            ->with('success', 'Tratamiento eliminado correctamente.');
    }

    public function storeFase(Request $request, Tratamiento $tratamiento, TratamientoService $service): RedirectResponse
    {
        $validated = $request->validate($this->faseRules());

        $service->createFase($tratamiento, $request->user(), $validated);

        return redirect()->route('pacientes.show', $tratamiento->paciente_id)
            ->with('success', 'Fase registrada correctamente.');
    }

    public function updateFase(Request $request, FaseTratamiento $fase, TratamientoService $service): RedirectResponse
    {
        $fase->loadMissing('tratamiento');
        $validated = $request->validate($this->faseRules());

        $service->updateFase($fase, $validated);

        return redirect()->route('pacientes.show', $fase->tratamiento->paciente_id)
            ->with('success', 'Fase actualizada correctamente.');
    }

    public function destroyFase(FaseTratamiento $fase, TratamientoService $service): RedirectResponse
    {
        $fase->loadMissing('tratamiento');
        $pacienteId = $fase->tratamiento->paciente_id;

        $service->deleteFase($fase);

        return redirect()->route('pacientes.show', $pacienteId)
            ->with('success', 'Fase eliminada correctamente.');
    }

    protected function tratamientoRules(bool $requireEstado = false): array
    {
        return [
            'pieza_id' => ['nullable', 'exists:piezas_dentales,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:4000'],
            'estado' => [
                $requireEstado ? 'required' : 'nullable',
                Rule::in([
                    Tratamiento::ESTADO_EN_PROGRESO,
                    Tratamiento::ESTADO_FINALIZADO,
                    Tratamiento::ESTADO_SUSPENDIDO,
                ]),
            ],
            'fecha_inicio' => ['required', 'date'],
        ];
    }

    protected function faseRules(): array
    {
        return [
            'consulta_id' => ['nullable', 'exists:consultas,id'],
            'descripcion' => ['required', 'string', 'max:4000'],
            'fecha' => ['required', 'date'],
            'completada' => ['nullable', 'boolean'],
            'orden' => ['nullable', 'integer', 'min:0', 'max:999'],
        ];
    }
}
