<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Paciente;
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

        return view('dashboard', [
            'metricas' => $metricas,
            'extras' => $extras,
            'extrasDisponibles' => self::EXTRAS_DISPONIBLES,
            'preset' => $preset,
            'desde' => $desde->toDateString(),
            'hasta' => $hasta->toDateString(),
        ]);
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
