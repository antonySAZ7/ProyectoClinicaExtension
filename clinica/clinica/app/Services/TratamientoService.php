<?php

namespace App\Services;

use App\Models\FaseTratamiento;
use App\Models\Paciente;
use App\Models\Tratamiento;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TratamientoService
{
    public function createTratamiento(Paciente $paciente, User $user, array $data): Tratamiento
    {
        return $paciente->tratamientos()->create([
            'pieza_id' => $data['pieza_id'] ?? null,
            'user_id' => $user->id,
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'estado' => $data['estado'] ?? Tratamiento::ESTADO_EN_PROGRESO,
            'fecha_inicio' => $data['fecha_inicio'],
        ]);
    }

    public function updateTratamiento(Tratamiento $tratamiento, array $data): Tratamiento
    {
        $tratamiento->update([
            'pieza_id' => $data['pieza_id'] ?? null,
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'estado' => $data['estado'],
            'fecha_inicio' => $data['fecha_inicio'],
        ]);

        return $tratamiento->refresh();
    }

    public function finalizar(Tratamiento $tratamiento): Tratamiento
    {
        $tratamiento->update(['estado' => Tratamiento::ESTADO_FINALIZADO]);

        return $tratamiento->refresh();
    }

    public function deleteTratamiento(Tratamiento $tratamiento): void
    {
        $tratamiento->delete();
    }

    public function createFase(Tratamiento $tratamiento, User $user, array $data): FaseTratamiento
    {
        $this->ensureConsultaBelongsToPaciente($tratamiento, $data['consulta_id'] ?? null);

        return DB::transaction(function () use ($tratamiento, $user, $data) {
            $orden = $data['orden'] ?? $this->nextOrden($tratamiento);

            return $tratamiento->fases()->create([
                'consulta_id' => $data['consulta_id'] ?? null,
                'user_id' => $user->id,
                'descripcion' => $data['descripcion'],
                'fecha' => $data['fecha'],
                'completada' => (bool) ($data['completada'] ?? true),
                'orden' => $orden,
            ]);
        });
    }

    public function updateFase(FaseTratamiento $fase, array $data): FaseTratamiento
    {
        $fase->loadMissing('tratamiento');
        $this->ensureConsultaBelongsToPaciente($fase->tratamiento, $data['consulta_id'] ?? null);

        $fase->update([
            'consulta_id' => $data['consulta_id'] ?? null,
            'descripcion' => $data['descripcion'],
            'fecha' => $data['fecha'],
            'completada' => (bool) ($data['completada'] ?? false),
            'orden' => $data['orden'] ?? $fase->orden,
        ]);

        return $fase->refresh();
    }

    public function deleteFase(FaseTratamiento $fase): void
    {
        $fase->delete();
    }

    protected function nextOrden(Tratamiento $tratamiento): int
    {
        return ((int) $tratamiento->fases()->max('orden')) + 1;
    }

    protected function ensureConsultaBelongsToPaciente(Tratamiento $tratamiento, mixed $consultaId): void
    {
        if (! $consultaId) {
            return;
        }

        $exists = $tratamiento->paciente
            ->consultas()
            ->whereKey($consultaId)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'consulta_id' => 'La consulta seleccionada no pertenece al paciente del tratamiento.',
            ]);
        }
    }
}
