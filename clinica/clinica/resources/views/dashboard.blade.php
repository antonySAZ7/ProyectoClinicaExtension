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

            {{-- ============================ --}}
            {{-- PANEL OPERATIVO DEL DÍA       --}}
            {{-- ============================ --}}
            <section class="space-y-6">
                <header>
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Panel del día</h3>
                    <p class="mt-1 text-xs text-gray-400">
                        Datos en tiempo real — {{ \Illuminate\Support\Carbon::parse($operativo['ahora'])->format('d/m/Y H:i') }}
                    </p>
                </header>

                <div class="grid gap-6 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">
                    {{-- Citas de hoy --}}
                    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900">Tus citas de hoy</h4>
                                <p class="mt-1 text-xs text-gray-500">{{ $operativo['citas_hoy']->count() }} cita(s) programadas.</p>
                            </div>
                            <a
                                href="{{ route('citas.calendario') }}"
                                class="text-xs font-semibold text-indigo-700 hover:underline"
                            >
                                Ver calendario
                            </a>
                        </div>

                        <div class="divide-y divide-gray-100">
                            @forelse ($operativo['citas_hoy'] as $cita)
                                @php
                                    $hora = \Illuminate\Support\Str::of((string) $cita->hora)->substr(0, 5);
                                    $badgeEstilo = match ($cita->estado) {
                                        'confirmada' => 'bg-emerald-100 text-emerald-700',
                                        'atendida' => 'bg-sky-100 text-sky-700',
                                        'cancelada' => 'bg-rose-100 text-rose-700',
                                        'no_show' => 'bg-gray-200 text-gray-600',
                                        default => 'bg-amber-100 text-amber-700',
                                    };
                                @endphp
                                <div class="flex flex-wrap items-center gap-3 px-5 py-3">
                                    <span class="w-14 shrink-0 font-semibold text-gray-900">{{ $hora }}</span>
                                    <div class="min-w-0 flex-1">
                                        <a
                                            href="{{ route('pacientes.show', $cita->paciente_id) }}"
                                            class="block truncate text-sm font-medium text-gray-900 hover:underline"
                                        >
                                            {{ $cita->paciente?->nombre_completo ?? 'Paciente no disponible' }}
                                        </a>
                                        <p class="truncate text-xs text-gray-500">{{ $cita->motivo }}</p>
                                    </div>
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $badgeEstilo }}">
                                        {{ ucfirst($cita->estado) }}
                                    </span>

                                    @if ($cita->estado === 'confirmada')
                                        <a
                                            href="{{ route('pacientes.consultas.create', ['paciente' => $cita->paciente_id, 'cita_id' => $cita->id]) }}"
                                            class="inline-flex items-center gap-1 rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-700"
                                        >
                                            <x-lucide-play class="h-3.5 w-3.5" />
                                            Iniciar consulta
                                        </a>
                                    @elseif ($cita->estado === 'atendida' && $cita->consulta)
                                        <a
                                            href="{{ route('consultas.show', $cita->consulta) }}"
                                            class="inline-flex items-center gap-1 rounded-md border border-sky-300 bg-sky-50 px-3 py-1.5 text-xs font-semibold text-sky-700 transition hover:bg-sky-100"
                                        >
                                            Ver consulta
                                        </a>
                                    @endif
                                </div>
                            @empty
                                <div class="px-5 py-8 text-center text-sm text-gray-500">
                                    No hay citas programadas para hoy.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Próxima cita --}}
                    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                        <div class="border-b border-gray-100 px-5 py-4">
                            <h4 class="text-lg font-semibold text-gray-900">Próxima cita</h4>
                            <p class="mt-1 text-xs text-gray-500">Tu siguiente confirmada o pendiente.</p>
                        </div>

                        <div class="px-5 py-5">
                            @if ($operativo['proxima_cita'])
                                @php
                                    $px = $operativo['proxima_cita'];
                                    $inicio = $px->startsAt();
                                @endphp
                                <p class="text-2xl font-semibold text-gray-900">
                                    {{ $inicio?->format('H:i') }}
                                </p>
                                <p class="text-xs uppercase tracking-wide text-gray-500">
                                    {{ $inicio?->isToday() ? 'Hoy' : $inicio?->translatedFormat('l d/m/Y') }}
                                </p>

                                @if ($inicio)
                                    @php
                                        $diffMin = now()->diffInMinutes($inicio, false);
                                    @endphp
                                    <p class="mt-2 text-sm font-medium text-indigo-700">
                                        @if ($diffMin <= 0)
                                            En curso ahora
                                        @elseif ($diffMin < 60)
                                            En {{ (int) $diffMin }} minuto(s)
                                        @elseif ($diffMin < 60 * 24)
                                            En {{ (int) floor($diffMin / 60) }} hora(s)
                                        @else
                                            {{ $inicio->diffForHumans(['parts' => 2]) }}
                                        @endif
                                    </p>
                                @endif

                                <div class="mt-4 border-t border-gray-100 pt-4">
                                    <a
                                        href="{{ route('pacientes.show', $px->paciente_id) }}"
                                        class="text-sm font-medium text-gray-900 hover:underline"
                                    >
                                        {{ $px->paciente?->nombre_completo ?? '—' }}
                                    </a>
                                    <p class="mt-1 text-xs text-gray-500">{{ $px->motivo }}</p>
                                </div>

                                @if ($px->estado === 'confirmada')
                                    <a
                                        href="{{ route('pacientes.consultas.create', ['paciente' => $px->paciente_id, 'cita_id' => $px->id]) }}"
                                        class="mt-4 inline-flex items-center justify-center gap-1 rounded-md bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700"
                                    >
                                        <x-lucide-play class="h-3.5 w-3.5" />
                                        Iniciar consulta
                                    </a>
                                @endif
                            @else
                                <p class="py-6 text-center text-sm text-gray-500">
                                    No hay citas próximas confirmadas.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Consultas sin presupuesto cerrado + Top saldos --}}
                <div class="grid gap-6 lg:grid-cols-2">
                    {{-- Pendientes de cerrar --}}
                    <div class="rounded-xl border border-amber-200 bg-amber-50 shadow-sm">
                        <div class="border-b border-amber-100 px-5 py-4">
                            <div class="flex items-center gap-2">
                                <x-lucide-clock class="h-4 w-4 text-amber-700" />
                                <h4 class="text-base font-semibold text-amber-900">Consultas con presupuesto sin cerrar</h4>
                            </div>
                            <p class="mt-1 text-xs text-amber-700">
                                Consultas con ítems de presupuesto pero aún no aceptado. Cierralas para empezar a cobrar.
                            </p>
                        </div>

                        <div class="divide-y divide-amber-100">
                            @forelse ($operativo['consultas_sin_cerrar'] as $consulta)
                                <a
                                    href="{{ route('consultas.show', $consulta) }}"
                                    class="flex items-center justify-between gap-3 px-5 py-3 transition hover:bg-amber-100/50"
                                >
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-amber-900">
                                            {{ $consulta->paciente?->nombre_completo ?? '—' }}
                                        </p>
                                        <p class="text-xs text-amber-700">
                                            {{ $consulta->fecha?->format('d/m/Y') }} · {{ $consulta->motivo }}
                                        </p>
                                    </div>
                                    <x-lucide-arrow-right class="h-4 w-4 shrink-0 text-amber-700" />
                                </a>
                            @empty
                                <p class="px-5 py-6 text-center text-sm text-amber-700">
                                    ✓ Todas las consultas con presupuesto están cerradas.
                                </p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Top saldos pendientes --}}
                    <div class="rounded-xl border border-rose-200 bg-rose-50 shadow-sm">
                        <div class="border-b border-rose-100 px-5 py-4">
                            <div class="flex items-center gap-2">
                                <x-lucide-circle-dollar-sign class="h-4 w-4 text-rose-700" />
                                <h4 class="text-base font-semibold text-rose-900">Top saldos por cobrar</h4>
                            </div>
                            <p class="mt-1 text-xs text-rose-700">
                                Los 5 pacientes con mayor saldo pendiente. Click para registrar abono.
                            </p>
                        </div>

                        <div class="divide-y divide-rose-100">
                            @forelse ($operativo['top_saldos'] as $fila)
                                <a
                                    href="{{ route('pacientes.show', $fila['id']) }}"
                                    class="flex items-center justify-between gap-3 px-5 py-3 transition hover:bg-rose-100/50"
                                >
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-rose-900">{{ $fila['nombre'] }}</p>
                                        <p class="text-xs text-rose-700">
                                            Pagado Q{{ number_format($fila['pagado'], 2) }} de Q{{ number_format($fila['presupuesto'], 2) }}
                                        </p>
                                    </div>
                                    <span class="shrink-0 text-base font-semibold text-rose-700">
                                        Q{{ number_format($fila['saldo'], 2) }}
                                    </span>
                                </a>
                            @empty
                                <p class="px-5 py-6 text-center text-sm text-rose-700">
                                    ✓ Sin saldos pendientes.
                                </p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </section>

            {{-- ============================ --}}
            {{-- PANEL ANALÍTICO (P3)         --}}
            {{-- ============================ --}}
            <div class="border-t border-gray-200 pt-8">
                <h3 class="mb-1 text-sm font-semibold uppercase tracking-wide text-gray-500">Panel analítico</h3>
                <p class="mb-6 text-xs text-gray-400">Métricas por período para análisis.</p>
            </div>

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
