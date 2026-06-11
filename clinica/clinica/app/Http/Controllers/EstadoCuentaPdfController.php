<?php

namespace App\Http\Controllers;

use App\Models\Consulta;
use App\Models\Paciente;
use App\Models\Pago;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

/**
 * Estado de cuenta imprimible en PDF (Persona 3 — ficha "Individual por fecha").
 *
 * Replica la ficha del Excel: datos del paciente, presupuesto general, abonos,
 * saldo actual, observaciones y espacio para firma física. Reusa dompdf
 * (barryvdh/laravel-dompdf), igual que el PDF de consulta.
 *
 * Dos contextos sobre la misma vista:
 *  - paciente(): estado de cuenta general (todas las consultas del paciente).
 *  - consulta(): ficha anclada a una consulta concreta (su presupuesto y sus
 *    observaciones), mostrando además el saldo global del paciente.
 *
 * Documento financiero: requiere validación humana antes de entregarse; solo
 * refleja lo ya registrado en el sistema.
 */
class EstadoCuentaPdfController extends Controller
{
    /**
     * Estado de cuenta general del paciente.
     */
    public function paciente(Paciente $paciente): Response
    {
        $this->assertDompdf();

        $paciente->load([
            'consultas' => fn ($q) => $q->orderBy('fecha'),
            'consultas.presupuestoItems.pieza',
            'consultas.observaciones',
            'pagos',
        ]);

        $pdf = Pdf::loadView('pdf.estado-cuenta', $this->datos($paciente, null));

        return $pdf->download('estado-cuenta-'.Str::slug($paciente->nombre_completo).'.pdf');
    }

    /**
     * Ficha de estado de cuenta anclada a una consulta.
     */
    public function consulta(Consulta $consulta): Response
    {
        $this->assertDompdf();

        $consulta->load([
            'paciente.pagos',
            'presupuestoItems.pieza',
            'observaciones',
            'cita',
        ]);

        $pdf = Pdf::loadView('pdf.estado-cuenta', $this->datos($consulta->paciente, $consulta));

        return $pdf->download('estado-cuenta-consulta-'.$consulta->id.'.pdf');
    }

    /**
     * Normalizar los datos de la ficha para la vista.
     *
     * @return array<string, mixed>
     */
    private function datos(Paciente $paciente, ?Consulta $consulta): array
    {
        $estadosCobrados = [Pago::ESTADO_COMPLETADO, Pago::ESTADO_PAGADO];

        // Abonos cobrados, en orden cronológico (fecha_pago, o creación si falta).
        $abonos = $paciente->pagos
            ->whereIn('estado', $estadosCobrados)
            ->sortBy(fn (Pago $p) => $p->fecha_pago ?? $p->created_at)
            ->values();

        if ($consulta) {
            $items = $consulta->presupuestoItems;
            $observaciones = $consulta->observaciones
                ->sortBy('created_at')
                ->map(fn ($o) => [
                    'fecha' => $o->created_at,
                    'descripcion' => $o->descripcion,
                    'consulta_fecha' => $consulta->fecha,
                ])
                ->values();
        } else {
            $items = $paciente->consultas->flatMap->presupuestoItems;
            // Observaciones de todas las consultas, ordenadas por su registro.
            $observaciones = $paciente->consultas
                ->flatMap(fn (Consulta $c) => $c->observaciones->map(fn ($o) => [
                    'fecha' => $o->created_at,
                    'descripcion' => $o->descripcion,
                    'consulta_fecha' => $c->fecha,
                ]))
                ->sortBy('fecha')
                ->values();
        }

        return [
            'paciente' => $paciente,
            'consulta' => $consulta,
            'items' => $items,
            'abonos' => $abonos,
            'observaciones' => $observaciones,
            'presupuestoGeneral' => (float) $paciente->presupuesto_total,
            'totalAbonado' => (float) $paciente->total_pagado,
            'saldoActual' => (float) $paciente->saldo_pendiente,
            'generadoEn' => now(),
        ];
    }

    private function assertDompdf(): void
    {
        abort_unless(
            class_exists(Pdf::class),
            500,
            'barryvdh/laravel-dompdf no esta instalado.'
        );
    }
}
