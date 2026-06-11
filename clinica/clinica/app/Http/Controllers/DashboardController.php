<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Consulta;
use App\Models\ConsultaPresupuestoItem;
use App\Models\Paciente;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * TTL (segundos) que viven las metricas en cache antes de recalcularse.
     */
    private const CACHE_TTL = 60;

    /**
     * Secciones adicionales disponibles para mostrar/ocultar en el panel.
     */
    public const EXTRAS_DISPONIBLES = ['tasa', 'citas_dia', 'pacientes_nuevos'];

    /**
     * Clave de la "version" del namespace de cache. Cuando algo cambia en la BD
     * (citas, pacientes), incrementamos esta version y todas las claves viejas
     * pasan a ser orphan (expiran solas via TTL).
     */
    private const VERSION_KEY = 'dashboard.metricas.version';

    /**
     * Invalidar todas las metricas cacheadas del dashboard.
     *
     * Llamar desde controllers que modifican citas o pacientes para que el
     * admin vea cambios inmediatos sin esperar al TTL de 60s.
     */
    public static function invalidate(): void
    {
        // microtime garantiza valores estrictamente crecientes incluso cuando
        // varias escrituras ocurren en el mismo segundo (ej. delete justo
        // despues de un create).
        Cache::forever(self::VERSION_KEY, (string) microtime(true));
    }

    /**
     * Mostrar el panel con metricas de citas y pacientes filtradas por periodo.
     */
    public function index(Request $request)
    {
        [$desde, $hasta, $preset] = $this->resolverRango($request);
        $extras = $this->resolverExtras($request);

        $version = Cache::rememberForever(self::VERSION_KEY, fn () => (string) microtime(true));

        $claveCache = sprintf(
            'dashboard.metricas.v%s.%s.%s.%s',
            $version,
            $desde->toDateString(),
            $hasta->toDateString(),
            implode('-', $extras) ?: 'base'
        );

        $metricas = Cache::remember($claveCache, self::CACHE_TTL, function () use ($desde, $hasta, $extras) {
            return $this->calcularMetricas($desde, $hasta, $extras);
        });

        $operativo = $this->datosOperativos();

        $analitica = Cache::remember(
            $claveCache.'.analitica',
            self::CACHE_TTL,
            fn () => $this->calcularAnalitica($desde, $hasta)
        );

        return view('dashboard', [
            'metricas' => $metricas,
            'extras' => $extras,
            'extrasDisponibles' => self::EXTRAS_DISPONIBLES,
            'preset' => $preset,
            'desde' => $desde->toDateString(),
            'hasta' => $hasta->toDateString(),
            'operativo' => $operativo,
            'analitica' => $analitica,
        ]);
    }

    /**
     * Calcular las series y KPIs analíticos para los gráficos del panel.
     *
     * Las series mensuales (ingresos, pacientes nuevos) son una foto de los
     * últimos 12 meses e ignoran el filtro de periodo; las series de
     * distribución, conversión y ocupación sí respetan [desde, hasta].
     *
     * El agrupamiento mensual se hace en PHP (no con funciones de fecha del
     * motor) para no acoplar el cálculo a MySQL — el volumen de una clínica es
     * bajo y todo queda cacheado el TTL del panel.
     *
     * @return array<string, mixed>
     */
    private function calcularAnalitica(Carbon $desde, Carbon $hasta): array
    {
        $estadosCobrados = [Pago::ESTADO_COMPLETADO, Pago::ESTADO_PAGADO];

        $inicioMes = today()->startOfMonth();
        $finMes = today()->copy()->endOfMonth();
        $inicioSemana = today()->startOfWeek();
        $finSemana = today()->copy()->endOfWeek();
        $inicio12Meses = today()->copy()->startOfMonth()->subMonthsNoOverflow(11);

        // --- Series mensuales: ingresos cobrados (últimos 12 meses) ---
        // La "fecha efectiva" de un abono es fecha_pago si existe, si no la de
        // creación (cubre abonos viejos importados sin fecha_pago explícita).
        $pagosCobrados = Pago::query()
            ->whereIn('estado', $estadosCobrados)
            ->where(function ($query) use ($inicio12Meses) {
                $query->where('fecha_pago', '>=', $inicio12Meses->toDateString())
                    ->orWhere(function ($sub) use ($inicio12Meses) {
                        $sub->whereNull('fecha_pago')
                            ->where('created_at', '>=', $inicio12Meses);
                    });
            })
            ->get(['monto', 'fecha_pago', 'created_at']);

        $ingresosPorMes = $this->bucketsMensuales(12);
        foreach ($pagosCobrados as $pago) {
            $fecha = $pago->fecha_pago ?? $pago->created_at;
            $clave = Carbon::parse($fecha)->format('Y-m');
            if (array_key_exists($clave, $ingresosPorMes)) {
                $ingresosPorMes[$clave] += (float) $pago->monto;
            }
        }

        // --- Series mensuales: pacientes nuevos (últimos 12 meses) ---
        $pacientesPorMes = $this->bucketsMensuales(12);
        Paciente::query()
            ->where('created_at', '>=', $inicio12Meses)
            ->get(['created_at'])
            ->each(function (Paciente $paciente) use (&$pacientesPorMes) {
                $clave = Carbon::parse($paciente->created_at)->format('Y-m');
                if (array_key_exists($clave, $pacientesPorMes)) {
                    $pacientesPorMes[$clave]++;
                }
            });

        // --- Distribución de estados de citas en el periodo ---
        $rangoCitas = [$desde->toDateString(), $hasta->toDateString()];
        $citasPeriodo = Cita::whereBetween('fecha', $rangoCitas);

        $porEstado = [
            Cita::ESTADO_PENDIENTE => (clone $citasPeriodo)->where('estado', Cita::ESTADO_PENDIENTE)->count(),
            Cita::ESTADO_CONFIRMADA => (clone $citasPeriodo)->where('estado', Cita::ESTADO_CONFIRMADA)->count(),
            Cita::ESTADO_ATENDIDA => (clone $citasPeriodo)->where('estado', Cita::ESTADO_ATENDIDA)->count(),
            Cita::ESTADO_CANCELADA => (clone $citasPeriodo)->where('estado', Cita::ESTADO_CANCELADA)->count(),
            Cita::ESTADO_NO_SHOW => (clone $citasPeriodo)->where('estado', Cita::ESTADO_NO_SHOW)->count(),
        ];

        // --- Tasa de conversión cita -> consulta (proxy de calidad de agenda) ---
        $atendidas = $porEstado[Cita::ESTADO_ATENDIDA];
        $canceladas = $porEstado[Cita::ESTADO_CANCELADA];
        $noShow = $porEstado[Cita::ESTADO_NO_SHOW];
        $baseConversion = $atendidas + $canceladas + $noShow;
        $tasaConversion = $baseConversion > 0
            ? round($atendidas / $baseConversion * 100, 1)
            : 0.0;

        // --- Ocupación de agenda: citas por día del periodo ---
        $ocupacion = (clone $citasPeriodo)
            ->selectRaw('fecha, COUNT(*) as total')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get()
            ->map(fn ($fila) => [
                'fecha' => Carbon::parse($fila->fecha)->format('d/m'),
                'total' => (int) $fila->total,
            ]);

        // --- Tratamientos más frecuentes (líneas de presupuesto) ---
        $tratamientos = ConsultaPresupuestoItem::query()
            ->selectRaw('tratamiento, COUNT(*) as total, COALESCE(SUM(subtotal), 0) as ingresos')
            ->whereNotNull('tratamiento')
            ->where('tratamiento', '!=', '')
            ->groupBy('tratamiento')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(fn ($fila) => [
                'tratamiento' => $fila->tratamiento,
                'total' => (int) $fila->total,
                'ingresos' => round((float) $fila->ingresos, 2),
            ]);

        // --- Saldos pendientes: total global por cobrar ---
        $presupuestoGlobal = (float) ConsultaPresupuestoItem::sum('subtotal');
        $pagadoGlobal = (float) Pago::whereIn('estado', $estadosCobrados)->sum('monto');
        $saldoTotal = round(max(0, $presupuestoGlobal - $pagadoGlobal), 2);

        // --- Ingreso promedio por consulta atendida (cruce pagos x consultas con cita) ---
        $consultasAtendidas = Consulta::whereNotNull('cita_id')->count();
        $ingresoPromedioConsulta = $consultasAtendidas > 0
            ? round($pagadoGlobal / $consultasAtendidas, 2)
            : 0.0;

        return [
            'kpis' => [
                'ingresos_mes' => round($ingresosPorMes[$inicioMes->format('Y-m')] ?? 0, 2),
                'saldo_total' => $saldoTotal,
                'citas_semana' => Cita::whereBetween('fecha', [$inicioSemana->toDateString(), $finSemana->toDateString()])->count(),
                'consultas_atendidas_mes' => Cita::where('estado', Cita::ESTADO_ATENDIDA)
                    ->whereBetween('fecha', [$inicioMes->toDateString(), $finMes->toDateString()])
                    ->count(),
                'ingreso_promedio_consulta' => $ingresoPromedioConsulta,
            ],
            'ingresos_por_mes' => [
                'labels' => array_map(fn ($clave) => Carbon::createFromFormat('Y-m', $clave)->format('m/Y'), array_keys($ingresosPorMes)),
                'data' => array_map(fn ($v) => round($v, 2), array_values($ingresosPorMes)),
            ],
            'pacientes_por_mes' => [
                'labels' => array_map(fn ($clave) => Carbon::createFromFormat('Y-m', $clave)->format('m/Y'), array_keys($pacientesPorMes)),
                'data' => array_values($pacientesPorMes),
            ],
            'distribucion_estados' => [
                'labels' => ['Pendiente', 'Confirmada', 'Atendida', 'Cancelada', 'No asistió'],
                'data' => array_values($porEstado),
            ],
            'conversion' => [
                'atendidas' => $atendidas,
                'canceladas' => $canceladas,
                'no_show' => $noShow,
                'tasa' => $tasaConversion,
            ],
            'ocupacion' => [
                'labels' => $ocupacion->pluck('fecha')->all(),
                'data' => $ocupacion->pluck('total')->all(),
            ],
            'tratamientos' => [
                'labels' => $tratamientos->pluck('tratamiento')->all(),
                'data' => $tratamientos->pluck('total')->all(),
            ],
        ];
    }

    /**
     * Construir un mapa ordenado ['Y-m' => 0] de los últimos $n meses,
     * incluyendo el mes actual como último elemento.
     *
     * @return array<string, float|int>
     */
    private function bucketsMensuales(int $n): array
    {
        $buckets = [];
        $cursor = today()->copy()->startOfMonth()->subMonthsNoOverflow($n - 1);

        for ($i = 0; $i < $n; $i++) {
            $buckets[$cursor->format('Y-m')] = 0;
            $cursor->addMonthNoOverflow();
        }

        return $buckets;
    }

    /**
     * Datos del día — citas de hoy, próxima cita, consultas pendientes de cerrar
     * presupuesto y top de saldos por cobrar.
     *
     * No se cachea porque cambia constantemente durante el día (cita atendida,
     * abono registrado, etc.) y la doctora abre el panel justamente para ver
     * el estado actual.
     *
     * @return array<string, mixed>
     */
    private function datosOperativos(): array
    {
        $hoy = today();

        $citasHoy = Cita::query()
            ->with(['paciente', 'consulta'])
            ->whereDate('fecha', $hoy)
            ->orderBy('hora')
            ->get();

        $proximaCita = Cita::query()
            ->with('paciente')
            ->whereIn('estado', [Cita::ESTADO_PENDIENTE, Cita::ESTADO_CONFIRMADA])
            ->upcoming()
            ->orderBy('fecha')
            ->orderBy('hora')
            ->first();

        $consultasSinPresupuestoCerrado = Consulta::query()
            ->with('paciente')
            ->whereNull('presupuesto_aceptado_en')
            ->whereHas('presupuestoItems')
            ->orderByDesc('fecha')
            ->limit(5)
            ->get();

        $estadosPagosCompletados = [Pago::ESTADO_COMPLETADO, Pago::ESTADO_PAGADO];

        $topSaldos = Paciente::query()
            ->select('pacientes.*')
            ->selectSub(
                'SELECT COALESCE(SUM(cpi.subtotal), 0)
                 FROM consulta_presupuesto_items cpi
                 INNER JOIN consultas c ON c.id = cpi.consulta_id
                 WHERE c.paciente_id = pacientes.id',
                'presupuesto_total_sql'
            )
            ->selectSub(
                Pago::query()
                    ->selectRaw('COALESCE(SUM(monto), 0)')
                    ->whereColumn('pagos.paciente_id', 'pacientes.id')
                    ->whereIn('estado', $estadosPagosCompletados)
                    ->getQuery(),
                'total_pagado_sql'
            )
            // GROUP BY por PK: vuelve la cláusula HAVING válida en SQLite (motor
            // de los tests) sin romper MySQL, que reconoce la dependencia
            // funcional sobre la clave primaria.
            ->groupBy('pacientes.id')
            ->havingRaw('(presupuesto_total_sql - total_pagado_sql) > 0')
            ->orderByRaw('(presupuesto_total_sql - total_pagado_sql) DESC')
            ->limit(5)
            ->get()
            ->map(fn (Paciente $p) => [
                'id' => $p->id,
                'nombre' => $p->nombre_completo,
                'presupuesto' => (float) $p->presupuesto_total_sql,
                'pagado' => (float) $p->total_pagado_sql,
                'saldo' => round((float) $p->presupuesto_total_sql - (float) $p->total_pagado_sql, 2),
            ]);

        return [
            'citas_hoy' => $citasHoy,
            'proxima_cita' => $proximaCita,
            'consultas_sin_cerrar' => $consultasSinPresupuestoCerrado,
            'top_saldos' => $topSaldos,
            'ahora' => now()->toIso8601String(),
        ];
    }

    /**
     * Resolver el rango de fechas [desde, hasta] segun preset o rango manual.
     *
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    private function resolverRango(Request $request): array
    {
        $preset = $request->query('preset', 'mes');
        $hoy = today();

        // Rango personalizado: solo si ambas fechas son validas.
        if ($preset === 'personalizado') {
            $desde = $this->parseFecha($request->query('desde'));
            $hasta = $this->parseFecha($request->query('hasta'));

            if ($desde && $hasta) {
                if ($desde->greaterThan($hasta)) {
                    [$desde, $hasta] = [$hasta, $desde];
                }

                return [$desde->startOfDay(), $hasta->startOfDay(), 'personalizado'];
            }

            // Fechas invalidas: caer al mes actual.
            $preset = 'mes';
        }

        return match ($preset) {
            'hoy' => [$hoy->copy(), $hoy->copy(), 'hoy'],
            '7dias' => [$hoy->copy()->subDays(6), $hoy->copy(), '7dias'],
            'mes_anterior' => [
                $hoy->copy()->subMonthNoOverflow()->startOfMonth(),
                $hoy->copy()->subMonthNoOverflow()->endOfMonth()->startOfDay(),
                'mes_anterior',
            ],
            default => [$hoy->copy()->startOfMonth(), $hoy->copy()->endOfMonth()->startOfDay(), 'mes'],
        };
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
     * Determinar que secciones extra mostrar.
     *
     * Primera carga (sin filtros aplicados) => todas activas.
     * Si el usuario aplico el formulario => respetar lo que marco (aunque sea vacio).
     *
     * @return array<int, string>
     */
    private function resolverExtras(Request $request): array
    {
        if (! $request->boolean('filtros')) {
            return self::EXTRAS_DISPONIBLES;
        }

        $seleccionados = (array) $request->query('extras', []);

        return array_values(array_intersect(self::EXTRAS_DISPONIBLES, $seleccionados));
    }

    /**
     * Calcular todas las metricas del periodo indicado.
     *
     * @param  array<int, string>  $extras
     * @return array<string, mixed>
     */
    private function calcularMetricas(Carbon $desde, Carbon $hasta, array $extras): array
    {
        $rangoCitas = [$desde->toDateString(), $hasta->toDateString()];
        $rangoRegistro = [$desde->copy()->startOfDay(), $hasta->copy()->endOfDay()];

        // Citas del periodo (contadas por su fecha de cita).
        $citasPeriodo = Cita::whereBetween('fecha', $rangoCitas);

        $totalPeriodo = (clone $citasPeriodo)->count();
        $confirmadas = (clone $citasPeriodo)->where('estado', Cita::ESTADO_CONFIRMADA)->count();
        $canceladas = (clone $citasPeriodo)->where('estado', Cita::ESTADO_CANCELADA)->count();
        $pendientes = (clone $citasPeriodo)->where('estado', Cita::ESTADO_PENDIENTE)->count();

        $metricas = [
            // Globales (no dependen del periodo): foto actual de la clinica.
            'pacientes_total' => Paciente::count(),
            'citas_hoy' => Cita::whereDate('fecha', today()->toDateString())->count(),
            'citas_proximas' => Cita::upcoming()->count(),

            // Del periodo seleccionado.
            'periodo_total' => $totalPeriodo,
            'periodo_pendientes' => $pendientes,
            'periodo_confirmadas' => $confirmadas,
            'periodo_canceladas' => $canceladas,

            'generado_en' => now()->toIso8601String(),
        ];

        if (in_array('tasa', $extras, true)) {
            $metricas['tasa_confirmacion'] = $totalPeriodo > 0
                ? round($confirmadas / $totalPeriodo * 100, 1)
                : 0.0;
            $metricas['tasa_cancelacion'] = $totalPeriodo > 0
                ? round($canceladas / $totalPeriodo * 100, 1)
                : 0.0;
        }

        if (in_array('citas_dia', $extras, true)) {
            $metricas['citas_por_dia'] = (clone $citasPeriodo)
                ->selectRaw('fecha, COUNT(*) as total')
                ->groupBy('fecha')
                ->orderBy('fecha')
                ->get()
                ->map(fn ($fila) => [
                    'fecha' => Carbon::parse($fila->fecha)->format('d/m/Y'),
                    'total' => (int) $fila->total,
                ])
                ->all();
        }

        if (in_array('pacientes_nuevos', $extras, true)) {
            $metricas['pacientes_nuevos'] = Paciente::whereBetween('created_at', $rangoRegistro)->count();
        }

        return $metricas;
    }
}
