<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Panel
        </h2>
    </x-slot>

    @php
        $etiquetasPreset = [
            'hoy' => 'Hoy',
            '7dias' => 'Ultimos 7 dias',
            'mes' => 'Este mes',
            'mes_anterior' => 'Mes anterior',
            'personalizado' => 'Rango personalizado',
        ];
        $etiquetasExtra = [
            'tasa' => 'Tasas de confirmacion / cancelacion',
            'citas_dia' => 'Citas por dia',
            'pacientes_nuevos' => 'Pacientes nuevos del periodo',
        ];
    @endphp

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">

            {{-- Barra de filtros --}}
            <form method="GET" action="{{ route('dashboard') }}" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <input type="hidden" name="filtros" value="1">

                {{-- Presets de periodo --}}
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

                {{-- Rango personalizado --}}
                <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
                    <div>
                        <label for="desde" class="mb-1 block text-xs font-medium text-gray-600">Desde</label>
                        <input
                            id="desde"
                            type="date"
                            name="desde"
                            value="{{ $desde }}"
                            class="rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-400 focus:ring-gray-400"
                        >
                    </div>
                    <div>
                        <label for="hasta" class="mb-1 block text-xs font-medium text-gray-600">Hasta</label>
                        <input
                            id="hasta"
                            type="date"
                            name="hasta"
                            value="{{ $hasta }}"
                            class="rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-400 focus:ring-gray-400"
                        >
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

                {{-- Filtros de informacion adicional --}}
                <div class="mt-5 border-t border-gray-100 pt-4">
                    <p class="text-sm font-semibold text-gray-700">Informacion adicional a mostrar</p>
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
                    <p class="mt-2 text-xs text-gray-400">
                        Marca o desmarca y vuelve a aplicar un periodo para actualizar las secciones.
                    </p>
                </div>
            </form>

            {{-- Resumen del periodo activo --}}
            <p class="text-sm text-gray-600">
                Mostrando <span class="font-semibold text-gray-900">{{ $etiquetasPreset[$preset] ?? 'periodo' }}</span>:
                del {{ \Illuminate\Support\Carbon::parse($desde)->format('d/m/Y') }}
                al {{ \Illuminate\Support\Carbon::parse($hasta)->format('d/m/Y') }}.
            </p>

            {{-- Foto actual (global) --}}
            <section>
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Resumen general</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <x-metric-card label="Pacientes registrados" :value="$metricas['pacientes_total']" accent="text-gray-900" />
                    <x-metric-card label="Citas de hoy" :value="$metricas['citas_hoy']" accent="text-indigo-700" />
                    <x-metric-card label="Proximas citas" :value="$metricas['citas_proximas']" accent="text-indigo-700" />
                </div>
            </section>

            {{-- Citas del periodo --}}
            <section>
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Citas del periodo</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <x-metric-card label="Total" :value="$metricas['periodo_total']" accent="text-gray-900" />
                    <x-metric-card label="Pendientes" :value="$metricas['periodo_pendientes']" accent="text-amber-600" />
                    <x-metric-card label="Confirmadas" :value="$metricas['periodo_confirmadas']" accent="text-green-700" />
                    <x-metric-card label="Canceladas" :value="$metricas['periodo_canceladas']" accent="text-red-700" />
                </div>
            </section>

            {{-- EXTRA: tasas --}}
            @if (in_array('tasa', $extras, true))
                <section>
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Tasas del periodo</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-metric-card label="Tasa de confirmacion" :value="$metricas['tasa_confirmacion'] . '%'" accent="text-green-700" />
                        <x-metric-card label="Tasa de cancelacion" :value="$metricas['tasa_cancelacion'] . '%'" accent="text-red-700" />
                    </div>
                </section>
            @endif

            {{-- EXTRA: pacientes nuevos --}}
            @if (in_array('pacientes_nuevos', $extras, true))
                <section>
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Pacientes</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <x-metric-card label="Pacientes nuevos del periodo" :value="$metricas['pacientes_nuevos']" accent="text-sky-700" />
                    </div>
                </section>
            @endif

            {{-- EXTRA: citas por dia --}}
            @if (in_array('citas_dia', $extras, true))
                <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Citas por dia</h3>

                    @if (empty($metricas['citas_por_dia']))
                        <p class="mt-4 text-sm text-gray-500">No hay citas en el periodo seleccionado.</p>
                    @else
                        @php $maxDia = collect($metricas['citas_por_dia'])->max('total') ?: 1; @endphp
                        <div class="mt-4 space-y-2">
                            @foreach ($metricas['citas_por_dia'] as $dia)
                                <div class="flex items-center gap-3">
                                    <span class="w-24 shrink-0 text-sm text-gray-600">{{ $dia['fecha'] }}</span>
                                    <div class="h-5 flex-1 rounded bg-gray-100">
                                        <div
                                            class="h-5 rounded bg-indigo-500"
                                            style="width: {{ max(4, round($dia['total'] / $maxDia * 100)) }}%"
                                        ></div>
                                    </div>
                                    <span class="w-8 shrink-0 text-right text-sm font-semibold text-gray-900">{{ $dia['total'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endif

            @if (! empty($metricas['generado_en']))
                <p class="text-right text-xs text-gray-400">
                    Datos actualizados {{ \Illuminate\Support\Carbon::parse($metricas['generado_en'])->diffForHumans() }}
                    (se refrescan cada minuto).
                </p>
            @endif
        </div>
    </div>
</x-app-layout>
