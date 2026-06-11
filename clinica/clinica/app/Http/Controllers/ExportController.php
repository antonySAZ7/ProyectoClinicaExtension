<?php

namespace App\Http\Controllers;

use App\Models\Consulta;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Exportación de datos a CSV (Persona 3 — "el círculo completo").
 *
 * Genera archivos que la doctora puede abrir directo en Excel. Se usa CSV
 * nativo (sin dependencias externas): delimitador ';' + BOM UTF-8 para que
 * Excel en español respete acentos y separe columnas al doble clic.
 *
 * Los reportes con dinero/clínicos requieren validación humana (no son
 * dictámenes automáticos); este export solo refleja lo ya registrado.
 */
class ExportController extends Controller
{
    /**
     * Listado de pacientes con datos generales y resumen financiero.
     */
    public function pacientes(): StreamedResponse
    {
        $encabezados = [
            'ID', 'Nombre completo', 'DPI', 'Fecha nacimiento', 'Edad',
            'Sexo', 'Estado civil', 'Ocupación', 'Teléfono', 'Correo', 'Dirección',
            'Presupuesto total', 'Total pagado', 'Saldo pendiente', 'Registrado',
        ];

        $generador = function () {
            foreach (
                Paciente::query()
                    ->with(['consultas.presupuestoItems', 'pagos'])
                    ->orderBy('nombre_completo')
                    ->cursor() as $p
            ) {
                yield [
                    $p->id,
                    $p->nombre_completo,
                    $p->dpi,
                    optional($p->fecha_nacimiento)->format('d/m/Y'),
                    $p->edad,
                    $p->sexo,
                    $p->estado_civil,
                    $p->ocupacion,
                    $p->telefono,
                    $p->correo,
                    $p->direccion,
                    number_format((float) $p->presupuesto_total, 2, ',', '.'),
                    number_format((float) $p->total_pagado, 2, ',', '.'),
                    number_format((float) $p->saldo_pendiente, 2, ',', '.'),
                    optional($p->created_at)->format('d/m/Y H:i'),
                ];
            }
        };

        return $this->stream('pacientes', $encabezados, $generador());
    }

    /**
     * Consultas con su presupuesto, opcionalmente acotadas por rango de fechas.
     */
    public function consultas(Request $request): StreamedResponse
    {
        [$desde, $hasta] = $this->rango($request);

        $encabezados = [
            'Consulta ID', 'Fecha', 'Paciente', 'DPI', 'Motivo', 'Diagnóstico',
            'Líneas presupuesto', 'Presupuesto total', 'Presupuesto aceptado', 'Estado cita',
        ];

        $generador = function () use ($desde, $hasta) {
            $query = Consulta::query()
                ->with(['paciente', 'presupuestoItems', 'cita'])
                ->orderByDesc('fecha');

            if ($desde && $hasta) {
                $query->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()]);
            }

            foreach ($query->cursor() as $consulta) {
                yield [
                    $consulta->id,
                    optional($consulta->fecha)->format('d/m/Y'),
                    $consulta->paciente?->nombre_completo,
                    $consulta->paciente?->dpi,
                    $consulta->motivo,
                    $consulta->diagnostico,
                    $consulta->presupuestoItems->count(),
                    number_format((float) $consulta->presupuesto_total, 2, ',', '.'),
                    $consulta->presupuesto_aceptado_en ? 'Sí' : 'No',
                    $consulta->cita?->estado ?? 'Sin cita',
                ];
            }
        };

        $sufijo = $desde && $hasta
            ? '_'.$desde->format('Ymd').'-'.$hasta->format('Ymd')
            : '';

        return $this->stream('consultas'.$sufijo, $encabezados, $generador());
    }

    /**
     * Estado de cuenta general: presupuesto, pagado y saldo por paciente.
     */
    public function estadoCuenta(): StreamedResponse
    {
        $encabezados = [
            'Paciente', 'DPI', 'Teléfono', 'Presupuesto total', 'Total pagado', 'Saldo pendiente',
        ];

        $generador = function () {
            foreach (
                Paciente::query()
                    ->with(['consultas.presupuestoItems', 'pagos'])
                    ->orderBy('nombre_completo')
                    ->cursor() as $p
            ) {
                yield [
                    $p->nombre_completo,
                    $p->dpi,
                    $p->telefono,
                    number_format((float) $p->presupuesto_total, 2, ',', '.'),
                    number_format((float) $p->total_pagado, 2, ',', '.'),
                    number_format((float) $p->saldo_pendiente, 2, ',', '.'),
                ];
            }
        };

        return $this->stream('estado_cuenta', $encabezados, $generador());
    }

    /**
     * Resolver rango de fechas opcional desde la query (?desde&hasta, Y-m-d).
     *
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    private function rango(Request $request): array
    {
        $desde = $this->parseFecha($request->query('desde'));
        $hasta = $this->parseFecha($request->query('hasta'));

        if ($desde && $hasta && $desde->greaterThan($hasta)) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        return [$desde, $hasta];
    }

    private function parseFecha(?string $valor): ?Carbon
    {
        if (! $valor) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $valor)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Construir la respuesta CSV en streaming.
     *
     * @param  array<int, string>  $encabezados
     * @param  iterable<int, array<int, mixed>>  $filas
     */
    private function stream(string $base, array $encabezados, iterable $filas): StreamedResponse
    {
        $nombre = sprintf('%s_%s.csv', $base, now()->format('Ymd_His'));

        return response()->streamDownload(function () use ($encabezados, $filas) {
            $salida = fopen('php://output', 'w');

            // BOM UTF-8: Excel lo necesita para mostrar tildes y eñes.
            fwrite($salida, "\xEF\xBB\xBF");

            fputcsv($salida, $encabezados, ';');
            foreach ($filas as $fila) {
                fputcsv($salida, $fila, ';');
            }

            fclose($salida);
        }, $nombre, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
