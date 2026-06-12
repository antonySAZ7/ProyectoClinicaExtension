<?php

namespace App\Services;

use App\Models\AntecedenteClinico;
use App\Models\Consulta;
use App\Models\Pago;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Export a Excel (.xlsx) con el formato del archivo histórico DENS_32_OFICIAL.xlsm.
 *
 * La doctora trabajó años con ese Excel; este export le devuelve sus datos en
 * la estructura que ya conoce, ahora generada desde el sistema:
 *
 *  - Hoja "BD": una fila por consulta con el mismo orden de columnas que la
 *    hoja BD original (datos generales, anamnesis, antecedentes médicos y
 *    odontológicos, signos vitales y 15 slots de Pieza/Diagnóstico/
 *    Tratamiento/Precio).
 *  - Hoja "Estatus": el tablero derivado original (piezas/diagnósticos/
 *    tratamientos concatenados con "|", presupuesto, abonos y saldo).
 *
 * Diferencias deliberadas con el original:
 *  - "Presupuesto Total" se calcula siempre desde las líneas reales (el
 *    original lo capturaba a mano y su fórmula de suma estaba rota).
 *  - "Abonos por citas" sale de los pagos cobrados registrados (en el
 *    original era una celda manual).
 *  - Las piezas usan numeración FDI (11-48), que es la que maneja el sistema;
 *    el original usaba numeración universal (1-32).
 */
class ExcelHistoricoService
{
    /** El formato histórico tiene 15 líneas de tratamiento por consulta. */
    private const SLOTS_TRATAMIENTO = 15;

    /**
     * Antecedentes médicos en el orden de columnas de la BD original
     * (intercalado izquierda/derecha del formulario APP).
     *
     * @var array<int, string>
     */
    private const ORDEN_MEDICOS = [
        'ant_cardiovascular', 'ant_convulsiones',
        'ant_diabetes', 'ant_venereas',
        'ant_endocrino', 'ant_fiebre_reumatica',
        'ant_renal', 'ant_tuberculosis',
        'ant_alergias', 'ant_hemorragias',
        'ant_hepatitis', 'ant_discrasias',
        'ant_embarazo', 'ant_otras_medicas',
    ];

    /**
     * Antecedentes odontológicos en el orden de columnas de la BD original.
     *
     * @var array<int, string>
     */
    private const ORDEN_ODONTOLOGICOS = [
        'odo_hemorragia', 'odo_dolor_dentario',
        'odo_infecciones', 'odo_sensibilidad',
        'odo_ulceras', 'odo_otras',
        'odo_reaccion_anestesia',
    ];

    public function generar(): Spreadsheet
    {
        $libro = new Spreadsheet();

        $consultas = Consulta::query()
            ->with([
                'paciente.antecedenteClinico',
                'presupuestoItems.pieza',
                'pagos',
                'observaciones',
            ])
            ->orderBy('fecha')
            ->orderBy('id')
            ->get();

        $this->hojaBd($libro->getActiveSheet(), $consultas);
        $this->hojaEstatus($libro->createSheet(), $consultas);

        $libro->setActiveSheetIndex(0);

        return $libro;
    }

    /**
     * Hoja "BD": réplica de la base de datos plana del Excel original.
     *
     * @param  \Illuminate\Support\Collection<int, Consulta>  $consultas
     */
    private function hojaBd(Worksheet $hoja, $consultas): void
    {
        $hoja->setTitle('BD');

        $encabezados = array_merge(
            [
                'Concatenado', 'Fecha', 'Paciente', 'Estado Civil', 'Fecha de nacimiento',
                'Ocupación', 'Edad', 'Teléfono', 'Sexo', 'Correo Electrónico',
                'Dirección de domicilio', 'Número de Identificación', 'Motivo de la Consulta',
                'Fecha aproximada de su última visita a un consultorio dental',
                'Motivo por el cual visitó el consultorio dental',
                '¿Presentó alguna complicación?',
                '¿Está siendo tratado por un médico actualmente?',
                '¿Para qué enfermedad?',
                '¿Toma algún tipo de medicamento?',
                '¿Cuál medicamento?',
                '¿Es alérgico a algún medicamento?',
                '¿Cuál o cuáles medicamentos?',
                'Otro',
            ],
            $this->etiquetas(self::ORDEN_MEDICOS, AntecedenteClinico::CAMPOS_MEDICOS),
            $this->etiquetas(self::ORDEN_ODONTOLOGICOS, AntecedenteClinico::CAMPOS_ODONTOLOGICOS),
            [
                'Descripción de su enfermedad, enfermedades',
                'Peso', 'Altura', 'Presión Arterial',
                'Frecuencia Cardiaca', 'Frecuencia Respiratoria', 'Otros',
            ],
            $this->encabezadosSlots(),
            ['Presupuesto Total'],
        );

        $this->titulo($hoja, 'BASE DE DATOS DE PACIENTES DE LA CLÍNICA DENS 32', count($encabezados));
        $hoja->fromArray($encabezados, null, 'A2');

        $fila = 3;
        foreach ($consultas as $consulta) {
            // strictNullComparison: sin esto, fromArray omite ceros y cadenas vacías.
            $hoja->fromArray($this->filaBd($consulta), null, 'A'.$fila, true);
            $fila++;
        }

        $this->estilizar($hoja, count($encabezados), $fila - 1);

        // Montos con formato de moneda: los 15 precios + Presupuesto Total.
        $colPrecioBase = count($encabezados) - (self::SLOTS_TRATAMIENTO * 4) - 1;
        for ($slot = 0; $slot < self::SLOTS_TRATAMIENTO; $slot++) {
            $this->formatoMoneda($hoja, $colPrecioBase + ($slot * 4) + 4, $fila - 1);
        }
        $this->formatoMoneda($hoja, count($encabezados), $fila - 1);
    }

    /**
     * @return array<int, mixed>
     */
    private function filaBd(Consulta $consulta): array
    {
        $paciente = $consulta->paciente;
        $antecedente = $paciente?->antecedenteClinico;
        $fecha = $consulta->fecha?->format('d/m/Y');

        $valores = [
            trim($fecha.' '.$paciente?->nombre_completo),
            $fecha,
            $paciente?->nombre_completo,
            $paciente?->estado_civil,
            $paciente?->fecha_nacimiento?->format('d/m/Y'),
            $paciente?->ocupacion,
            $paciente?->edad,
            $paciente?->telefono,
            $paciente?->sexo,
            $paciente?->correo,
            $paciente?->direccion,
            $paciente?->dpi,
            $consulta->motivo,
            $antecedente?->ultima_visita_dental?->format('d/m/Y'),
            $antecedente?->ultima_visita_motivo,
            $this->siNo($antecedente?->presento_complicacion),
            $this->siNo($antecedente?->en_tratamiento_medico),
            $antecedente?->tratamiento_enfermedad,
            $this->siNo($antecedente?->toma_medicamento),
            $antecedente?->cual_medicamento,
            $this->siNo($antecedente?->alergico_medicamento),
            $antecedente?->cuales_medicamentos,
            $antecedente?->otro_antecedente,
        ];

        foreach ([...self::ORDEN_MEDICOS, ...self::ORDEN_ODONTOLOGICOS] as $campo) {
            $valores[] = $this->siNo($antecedente?->{$campo});
        }

        $valores[] = $antecedente?->descripcion_enfermedades;
        $valores[] = $consulta->peso;
        $valores[] = $consulta->altura;
        $valores[] = $consulta->presion_arterial;
        $valores[] = $consulta->frecuencia_cardiaca;
        $valores[] = $consulta->frecuencia_respiratoria;
        $valores[] = $consulta->signos_otros;

        // 15 slots Pieza/Diagnóstico/Tratamiento/Precio, como el formato original.
        // Si la consulta tiene más líneas, el Presupuesto Total igual las incluye.
        $items = $consulta->presupuestoItems->take(self::SLOTS_TRATAMIENTO)->values();
        for ($slot = 0; $slot < self::SLOTS_TRATAMIENTO; $slot++) {
            $item = $items->get($slot);
            $valores[] = $item?->pieza?->numero;
            $valores[] = $item?->diagnostico;
            $valores[] = $item?->tratamiento;
            $valores[] = $item ? (float) $item->subtotal : null;
        }

        $valores[] = (float) $consulta->presupuesto_total;

        return $valores;
    }

    /**
     * Hoja "Estatus": tablero derivado del Excel original, una fila por consulta.
     *
     * @param  \Illuminate\Support\Collection<int, Consulta>  $consultas
     */
    private function hojaEstatus(Worksheet $hoja, $consultas): void
    {
        $hoja->setTitle('Estatus');

        $encabezados = [
            'Concatenado', 'Fecha', 'Paciente', 'Motivo de la Consulta',
            'Piezas', 'Diagnóstico', 'Tratamiento', 'Precio Individual',
            'Presupuesto de tratamiento', 'Abonos por citas', 'Nuevo Saldo',
            'Observaciones',
        ];

        $this->titulo($hoja, 'ESTATUS DE PACIENTES — CLÍNICA DENS 32', count($encabezados));
        $hoja->fromArray($encabezados, null, 'A2');

        $estadosCobrados = [Pago::ESTADO_COMPLETADO, Pago::ESTADO_PAGADO];

        $fila = 3;
        foreach ($consultas as $consulta) {
            $items = $consulta->presupuestoItems;
            $presupuesto = (float) $consulta->presupuesto_total;
            $abonos = round((float) $consulta->pagos
                ->whereIn('estado', $estadosCobrados)
                ->sum('monto'), 2);

            $hoja->fromArray([
                trim(($consulta->fecha?->format('d/m/Y')).' '.$consulta->paciente?->nombre_completo),
                $consulta->fecha?->format('d/m/Y'),
                $consulta->paciente?->nombre_completo,
                $consulta->motivo,
                $items->map(fn ($i) => $i->pieza?->numero ?? '—')->implode('|'),
                $items->pluck('diagnostico')->implode('|'),
                $items->pluck('tratamiento')->implode('|'),
                $items->map(fn ($i) => number_format((float) $i->subtotal, 2))->implode('|'),
                $presupuesto,
                $abonos,
                round($presupuesto - $abonos, 2),
                $consulta->observaciones
                    ->sortBy('created_at')
                    ->pluck('descripcion')
                    ->implode(' | '),
            ], null, 'A'.$fila, true);
            $fila++;
        }

        $this->estilizar($hoja, count($encabezados), $fila - 1);

        foreach ([9, 10, 11] as $columna) {
            $this->formatoMoneda($hoja, $columna, $fila - 1);
        }
    }

    /**
     * Encabezados de los 15 slots: "Pieza 1", "Diagnóstico 1", ... "Precio 15".
     *
     * @return array<int, string>
     */
    private function encabezadosSlots(): array
    {
        $encabezados = [];
        for ($slot = 1; $slot <= self::SLOTS_TRATAMIENTO; $slot++) {
            $encabezados[] = 'Pieza '.$slot;
            $encabezados[] = 'Diagnóstico '.$slot;
            $encabezados[] = 'Tratamiento '.$slot;
            $encabezados[] = 'Precio '.$slot;
        }

        return $encabezados;
    }

    /**
     * Resolver etiquetas legibles para una lista ordenada de campos.
     *
     * @param  array<int, string>  $orden
     * @param  array<string, string>  $catalogo
     * @return array<int, string>
     */
    private function etiquetas(array $orden, array $catalogo): array
    {
        return array_map(fn (string $campo) => $catalogo[$campo] ?? $campo, $orden);
    }

    private function siNo(?bool $valor): string
    {
        if ($valor === null) {
            return '';
        }

        return $valor ? 'Si' : 'No';
    }

    private function titulo(Worksheet $hoja, string $texto, int $columnas): void
    {
        $ultima = Coordinate::stringFromColumnIndex(min($columnas, 12));
        $hoja->mergeCells('A1:'.$ultima.'1');
        $hoja->setCellValue('A1', $texto);
        $hoja->getStyle('A1')->getFont()->setBold(true)->setSize(13);
    }

    private function estilizar(Worksheet $hoja, int $columnas, int $ultimaFila): void
    {
        $ultimaColumna = Coordinate::stringFromColumnIndex($columnas);

        $hoja->getStyle('A2:'.$ultimaColumna.'2')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F766E']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $hoja->getRowDimension(2)->setRowHeight(28);

        // Congelar título + encabezados y las 3 primeras columnas (clave/fecha/paciente).
        $hoja->freezePane('D3');
        $hoja->setAutoFilter('A2:'.$ultimaColumna.max(2, $ultimaFila));

        $hoja->getDefaultColumnDimension()->setWidth(16);
        foreach (['A' => 30, 'B' => 12, 'C' => 28] as $columna => $ancho) {
            $hoja->getColumnDimension($columna)->setWidth($ancho);
        }
    }

    private function formatoMoneda(Worksheet $hoja, int $columna, int $ultimaFila): void
    {
        if ($ultimaFila < 3) {
            return;
        }

        $letra = Coordinate::stringFromColumnIndex($columna);
        $hoja->getStyle($letra.'3:'.$letra.$ultimaFila)
            ->getNumberFormat()
            ->setFormatCode('"Q"#,##0.00');
    }
}
