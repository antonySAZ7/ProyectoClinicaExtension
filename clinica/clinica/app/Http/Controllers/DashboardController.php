<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Consulta;
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

        return view('dashboard', [
            'metricas' => $metricas,
            'extras' => $extras,
            'extrasDisponibles' => self::EXTRAS_DISPONIBLES,
            'preset' => $preset,
            'desde' => $desde->toDateString(),
            'hasta' => $hasta->toDateString(),
            'operativo' => $operativo,
        ]);
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
