<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\Consulta;
use App\Models\Paciente;
use App\Models\Pago;
use Illuminate\Validation\ValidationException;

class PagoService
{
    public function registrarAbono(Paciente $paciente, array $data): Pago
    {
        $monto = round((float) $data['monto'], 2);

        if ($monto <= 0) {
            throw ValidationException::withMessages([
                'monto' => 'El monto del abono debe ser mayor a 0.',
            ]);
        }

        if ($monto > $this->saldoPendiente($paciente)) {
            throw ValidationException::withMessages([
                'monto' => 'El abono no puede exceder el saldo pendiente del paciente.',
            ]);
        }

        if (! empty($data['consulta_id'])) {
            $belongsToPaciente = Consulta::query()
                ->whereKey($data['consulta_id'])
                ->where('paciente_id', $paciente->id)
                ->exists();

            if (! $belongsToPaciente) {
                throw ValidationException::withMessages([
                    'consulta_id' => 'La consulta seleccionada no pertenece a este paciente.',
                ]);
            }
        }

        if (! empty($data['cita_id'])) {
            $belongsToPaciente = Cita::query()
                ->whereKey($data['cita_id'])
                ->where('paciente_id', $paciente->id)
                ->exists();

            if (! $belongsToPaciente) {
                throw ValidationException::withMessages([
                    'cita_id' => 'La cita seleccionada no pertenece a este paciente.',
                ]);
            }
        }

        return $paciente->pagos()->create([
            'cita_id' => $data['cita_id'] ?? null,
            'consulta_id' => $data['consulta_id'] ?? null,
            'monto' => $monto,
            'metodo_pago' => $data['metodo_pago'],
            'estado' => $data['estado'] ?? Pago::ESTADO_COMPLETADO,
            'fecha_pago' => $data['fecha_pago'] ?? today()->toDateString(),
            'notas' => $data['notas'] ?? null,
        ]);
    }

    public function saldoPendiente(Paciente $paciente): float
    {
        $paciente->loadMissing(['consultas.presupuestoItems', 'pagos']);

        return round((float) $paciente->saldo_pendiente, 2);
    }
}
