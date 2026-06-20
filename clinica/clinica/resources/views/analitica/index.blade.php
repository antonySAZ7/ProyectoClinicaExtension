<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Análisis</h2>
                <p class="mt-1 text-sm text-gray-500">Indicadores, exportaciones y gráficos de la clínica.</p>
            </div>

            <div x-data="{ abierto: false }" class="relative">
                <button
                    type="button"
                    @click="abierto = !abierto"
                    @click.outside="abierto = false"
                    class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
                >
                    <x-lucide-download class="h-4 w-4" />
                    Exportar datos
                    <x-lucide-chevron-down class="h-4 w-4" />
                </button>

                <div
                    x-show="abierto"
                    x-cloak
                    x-transition
                    class="absolute right-0 z-10 mt-2 w-72 rounded-lg border border-gray-200 bg-white p-1 shadow-lg"
                >
                    <a href="{{ route('exportar.pacientes') }}" class="flex items-start gap-3 rounded-md px-3 py-2 text-sm transition hover:bg-gray-50">
                        <x-lucide-users class="mt-0.5 h-4 w-4 shrink-0 text-gray-400" />
                        <span>
                            <span class="block font-medium text-gray-900">Pacientes</span>
                            <span class="block text-xs text-gray-500">Listado con saldos y datos generales.</span>
                        </span>
                    </a>
                    <a href="{{ route('exportar.consultas', ['desde' => $desde, 'hasta' => $hasta]) }}" class="flex items-start gap-3 rounded-md px-3 py-2 text-sm transition hover:bg-gray-50">
                        <x-lucide-clipboard-list class="mt-0.5 h-4 w-4 shrink-0 text-gray-400" />
                        <span>
                            <span class="block font-medium text-gray-900">Consultas con presupuesto</span>
                            <span class="block text-xs text-gray-500">Del periodo seleccionado.</span>
                        </span>
                    </a>
                    <a href="{{ route('exportar.estado-cuenta') }}" class="flex items-start gap-3 rounded-md px-3 py-2 text-sm transition hover:bg-gray-50">
                        <x-lucide-circle-dollar-sign class="mt-0.5 h-4 w-4 shrink-0 text-gray-400" />
                        <span>
                            <span class="block font-medium text-gray-900">Estado de cuenta general</span>
                            <span class="block text-xs text-gray-500">Presupuesto, pagado y saldo por paciente.</span>
                        </span>
                    </a>
                    <div class="my-1 border-t border-gray-100"></div>
                    <a href="{{ route('exportar.excel-historico') }}" class="flex items-start gap-3 rounded-md px-3 py-2 text-sm transition hover:bg-teal-50">
                        <x-lucide-file-spreadsheet class="mt-0.5 h-4 w-4 shrink-0 text-teal-600" />
                        <span>
                            <span class="block font-medium text-gray-900">Excel completo</span>
                            <span class="block text-xs text-gray-500">Fichas en la estructura del Excel original.</span>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </x-slot>

    @php
        $etiquetasPreset = [
            'hoy' => 'Hoy',
            '7dias' => 'Últimos 7 días',
            'mes' => 'Este mes',
            'mes_anterior' => 'Mes anterior',
            'personalizado' => 'Rango personalizado',
        ];
        $etiquetasExtra = [
            'tasa' => 'Tasas de confirmación / cancelación',
            'citas_dia' => 'Citas por día',
            'pacientes_nuevos' => 'Pacientes nuevos del periodo',
        ];
        $graficos = [
            'ingresos_por_mes' => $analitica['ingresos_por_mes'],
            'pacientes_por_mes' => $analitica['pacientes_por_mes'],
            'distribucion_estados' => $analitica['distribucion_estados'],
            'ocupacion' => $analitica['ocupacion'],
            'tratamientos' => $analitica['tratamientos'],
        ];
    @endphp

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
                <x-metric-card label="Pacientes" :value="$metricas['pacientes_total']" accent="text-gray-900" />
                <x-card class="p-5">
                    <p class="text-sm font-medium text-emerald-700">Ingresos del mes</p>
                    <p class="mt-2 text-right text-2xl font-bold text-emerald-800">Q{{ number_format($analitica['kpis']['ingresos_mes'], 2) }}</p>
                </x-card>
                <x-card class="p-5">
                    <p class="text-sm font-medium text-rose-700">Saldo total por cobrar</p>
                    <p class="mt-2 text-right text-2xl font-bold text-rose-800">Q{{ number_format($analitica['kpis']['saldo_total'], 2) }}</p>
                </x-card>
                <x-metric-card label="Citas esta semana" :value="$analitica['kpis']['citas_semana']" accent="text-indigo-700" />
                <x-metric-card label="Consultas atendidas (mes)" :value="$analitica['kpis']['consultas_atendidas_mes']" accent="text-sky-700" />
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-card class="p-5">
                    <p class="text-sm font-medium text-gray-500">Tasa de conversión cita a consulta</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ $analitica['conversion']['tasa'] }}%</p>
                    <p class="mt-1 text-xs text-gray-400">
                        {{ $analitica['conversion']['atendidas'] }} atendidas de
                        {{ $analitica['conversion']['atendidas'] + $analitica['conversion']['canceladas'] + $analitica['conversion']['no_show'] }}
                        citas cerradas en el periodo.
                    </p>
                </x-card>
                <x-card class="p-5">
                    <p class="text-sm font-medium text-gray-500">Ingreso promedio por consulta atendida</p>
                    <p class="mt-2 text-right text-2xl font-bold text-gray-900">Q{{ number_format($analitica['kpis']['ingreso_promedio_consulta'], 2) }}</p>
                    <p class="mt-1 text-xs text-gray-400">Total cobrado entre consultas vinculadas a una cita.</p>
                </x-card>
            </div>

            <form method="GET" action="{{ route('analitica.index') }}" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <input type="hidden" name="filtros" value="1">

                <div>
                    <p class="text-sm font-semibold text-gray-700">Periodo</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach (['hoy', '7dias', 'mes', 'mes_anterior'] as $p)
                            <button
                                type="submit"
                                name="preset"
                                value="{{ $p }}"
                                @class([
                                    'rounded-md px-3 py-2 text-sm font-medium transition',
                                    'bg-gray-900 text-white' => $preset === $p,
                                    'border border-gray-300 text-gray-700 hover:bg-gray-50' => $preset !== $p,
                                ])
                            >
                                {{ $etiquetasPreset[$p] }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
                    <div>
                        <label for="desde" class="mb-1 block text-xs font-medium text-gray-600">Desde</label>
                        <input id="desde" type="date" name="desde" value="{{ $desde }}" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-400 focus:ring-gray-400">
                    </div>
                    <div>
                        <label for="hasta" class="mb-1 block text-xs font-medium text-gray-600">Hasta</label>
                        <input id="hasta" type="date" name="hasta" value="{{ $hasta }}" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-400 focus:ring-gray-400">
                    </div>
                    <button
                        type="submit"
                        name="preset"
                        value="personalizado"
                        @class([
                            'rounded-md px-4 py-2 text-sm font-semibold transition',
                            'bg-gray-900 text-white' => $preset === 'personalizado',
                            'border border-gray-300 text-gray-700 hover:bg-gray-50' => $preset !== 'personalizado',
                        ])
                    >
                        Aplicar rango
                    </button>
                </div>

                <div class="mt-5 border-t border-gray-100 pt-4">
                    <p class="text-sm font-semibold text-gray-700">Información adicional a mostrar</p>
                    <div class="mt-2 flex flex-wrap gap-x-6 gap-y-2">
                        @foreach ($extrasDisponibles as $extra)
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input
                                    type="checkbox"
                                    name="extras[]"
                                    value="{{ $extra }}"
                                    @checked(in_array($extra, $extras, true))
                                    class="rounded border-gray-300 text-gray-900 shadow-sm focus:ring-gray-400"
                                >
                                {{ $etiquetasExtra[$extra] }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </form>

            <p class="text-sm text-gray-600">
                Mostrando <span class="font-semibold text-gray-900">{{ $etiquetasPreset[$preset] ?? 'periodo' }}</span>:
                del {{ \Illuminate\Support\Carbon::parse($desde)->format('d/m/Y') }}
                al {{ \Illuminate\Support\Carbon::parse($hasta)->format('d/m/Y') }}.
            </p>

            <section>
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Resumen general</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <x-metric-card label="Pacientes registrados" :value="$metricas['pacientes_total']" accent="text-gray-900" />
                    <x-metric-card label="Citas de hoy" :value="$metricas['citas_hoy']" accent="text-indigo-700" />
                    <x-metric-card label="Próximas citas" :value="$metricas['citas_proximas']" accent="text-indigo-700" />
                </div>
            </section>

            <section>
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Citas del periodo</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <x-metric-card label="Total" :value="$metricas['periodo_total']" accent="text-gray-900" />
                    <x-metric-card label="Pendientes" :value="$metricas['periodo_pendientes']" accent="text-amber-600" />
                    <x-metric-card label="Confirmadas" :value="$metricas['periodo_confirmadas']" accent="text-green-700" />
                    <x-metric-card label="Canceladas" :value="$metricas['periodo_canceladas']" accent="text-red-700" />
                </div>
            </section>

            @if (in_array('tasa', $extras, true))
                <section>
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Tasas del periodo</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-metric-card label="Tasa de confirmación" :value="$metricas['tasa_confirmacion'] . '%'" accent="text-green-700" />
                        <x-metric-card label="Tasa de cancelación" :value="$metricas['tasa_cancelacion'] . '%'" accent="text-red-700" />
                    </div>
                </section>
            @endif

            @if (in_array('pacientes_nuevos', $extras, true))
                <section>
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Pacientes</h3>
                    <x-metric-card label="Pacientes nuevos del periodo" :value="$metricas['pacientes_nuevos']" accent="text-sky-700" />
                </section>
            @endif

            @if (in_array('citas_dia', $extras, true))
                <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Citas por día</h3>
                    @if (empty($metricas['citas_por_dia']))
                        <p class="mt-4 text-sm text-gray-500">No hay citas en el periodo seleccionado.</p>
                    @else
                        @php $maxDia = collect($metricas['citas_por_dia'])->max('total') ?: 1; @endphp
                        <div class="mt-4 space-y-2">
                            @foreach ($metricas['citas_por_dia'] as $dia)
                                <div class="flex items-center gap-3">
                                    <span class="w-24 shrink-0 text-sm text-gray-600">{{ $dia['fecha'] }}</span>
                                    <div class="h-5 flex-1 rounded bg-gray-100">
                                        <div class="h-5 rounded bg-indigo-500" style="width: {{ max(4, round($dia['total'] / $maxDia * 100)) }}%"></div>
                                    </div>
                                    <span class="w-8 shrink-0 text-right text-sm font-semibold text-gray-900">{{ $dia['total'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endif

            <section class="space-y-6">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Gráficos</h3>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <x-card class="p-6">
                        <h4 class="text-base font-semibold text-gray-900">Ingresos por mes</h4>
                        <p class="mb-4 text-xs text-gray-500">Abonos cobrados en los últimos 12 meses.</p>
                        <div class="relative h-64">
                            <canvas id="grafico-ingresos"></canvas>
                            <p data-grafico-vacio class="hidden absolute inset-0 items-center justify-center text-sm text-gray-400">Sin ingresos registrados.</p>
                        </div>
                    </x-card>

                    <x-card class="p-6">
                        <h4 class="text-base font-semibold text-gray-900">Ocupación de agenda por estado</h4>
                        <p class="mb-4 text-xs text-gray-500">Distribución de citas del periodo seleccionado.</p>
                        <div class="relative h-64">
                            <canvas id="grafico-estados"></canvas>
                            <p data-grafico-vacio class="hidden absolute inset-0 items-center justify-center text-sm text-gray-400">Sin citas en el periodo.</p>
                        </div>
                    </x-card>

                    <x-card class="p-6">
                        <h4 class="text-base font-semibold text-gray-900">Tratamientos más frecuentes</h4>
                        <p class="mb-4 text-xs text-gray-500">Según las líneas de presupuesto registradas.</p>
                        <div class="relative h-64">
                            <canvas id="grafico-tratamientos"></canvas>
                            <p data-grafico-vacio class="hidden absolute inset-0 items-center justify-center text-sm text-gray-400">Aún no hay tratamientos registrados.</p>
                        </div>
                    </x-card>

                    <x-card class="p-6">
                        <h4 class="text-base font-semibold text-gray-900">Citas por día</h4>
                        <p class="mb-4 text-xs text-gray-500">Volumen de agenda en el periodo seleccionado.</p>
                        <div class="relative h-64">
                            <canvas id="grafico-ocupacion"></canvas>
                            <p data-grafico-vacio class="hidden absolute inset-0 items-center justify-center text-sm text-gray-400">Sin citas en el periodo.</p>
                        </div>
                    </x-card>

                    <x-card class="p-6 lg:col-span-2">
                        <h4 class="text-base font-semibold text-gray-900">Pacientes nuevos por mes</h4>
                        <p class="mb-4 text-xs text-gray-500">Tendencia de captación en los últimos 12 meses.</p>
                        <div class="relative h-64">
                            <canvas id="grafico-pacientes"></canvas>
                            <p data-grafico-vacio class="hidden absolute inset-0 items-center justify-center text-sm text-gray-400">Sin pacientes nuevos registrados.</p>
                        </div>
                    </x-card>
                </div>
            </section>

            <script type="application/json" id="analitica-data">@json($graficos)</script>

            @if (! empty($metricas['generado_en']))
                <p class="text-right text-xs text-gray-400">
                    Datos actualizados {{ \Illuminate\Support\Carbon::parse($metricas['generado_en'])->diffForHumans() }}
                    (se refrescan cada minuto).
                </p>
            @endif
        </div>
    </div>

    @push('scripts')
        @vite('resources/js/dashboard.js')
    @endpush
</x-app-layout>
