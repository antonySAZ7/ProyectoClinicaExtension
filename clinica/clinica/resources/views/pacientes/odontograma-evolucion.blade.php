@php
    $estadosMeta = [
        'sana' => ['label' => 'Sana', 'color' => 'bg-emerald-100 text-emerald-700 border-emerald-200'],
        'caries' => ['label' => 'Caries', 'color' => 'bg-amber-100 text-amber-700 border-amber-200'],
        'obturada' => ['label' => 'Obturada', 'color' => 'bg-sky-100 text-sky-700 border-sky-200'],
        'ausente' => ['label' => 'Ausente', 'color' => 'bg-gray-100 text-gray-600 border-gray-200'],
        'extraccion' => ['label' => 'Extracción', 'color' => 'bg-rose-100 text-rose-700 border-rose-200'],
        'corona' => ['label' => 'Corona', 'color' => 'bg-violet-100 text-violet-700 border-violet-200'],
        'endodoncia' => ['label' => 'Endodoncia', 'color' => 'bg-indigo-100 text-indigo-700 border-indigo-200'],
    ];

    $cuadranteLabel = function ($cuadrante) {
        return match ($cuadrante) {
            1 => 'Superior derecho',
            2 => 'Superior izquierdo',
            3 => 'Inferior izquierdo',
            4 => 'Inferior derecho',
            default => 'Cuadrante '.$cuadrante,
        };
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-brand-primary leading-tight">
                    Evolución del odontograma
                </h2>
                <p class="mt-1 text-sm text-brand-muted">
                    {{ $paciente->nombre_completo }} · {{ count($evoluciones) }} pieza(s) registrada(s) en {{ $totalConsultas }} consulta(s).
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <x-link-button href="{{ route('pacientes.show', $paciente) }}">
                    Volver al perfil
                </x-link-button>
                <x-link-button href="{{ route('pacientes.consultas.index', $paciente) }}">
                    Historial clínico
                </x-link-button>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">

            {{-- Leyenda de estados --}}
            <div class="rounded-xl border border-brand-border bg-white p-4 shadow-sm">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-brand-muted">Leyenda de estados</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($estadosMeta as $key => $meta)
                        <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium {{ $meta['color'] }}">
                            {{ $meta['label'] }}
                        </span>
                    @endforeach
                </div>
            </div>

            @if (count($evoluciones) === 0)
                <x-card class="p-12 text-center">
                    <x-lucide-square-dashed class="mx-auto h-10 w-10 text-brand-muted" />
                    <p class="mt-4 text-sm text-brand-muted">
                        Este paciente todavía no tiene piezas registradas en ninguna consulta.
                    </p>
                </x-card>
            @else
                <div class="space-y-4">
                    @foreach ($evoluciones as $evolucion)
                        @php $estadoActual = $estadosMeta[$evolucion['estado_actual']] ?? $estadosMeta['sana']; @endphp
                        <x-card class="overflow-hidden">
                            <div class="flex flex-col gap-2 border-b border-brand-border bg-brand-soft px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-md bg-white text-base font-bold text-brand-primary shadow-sm">
                                        {{ $evolucion['numero'] }}
                                    </span>
                                    <div>
                                        <p class="text-sm font-semibold text-brand-primary">
                                            {{ $evolucion['nombre'] }}
                                        </p>
                                        <p class="text-xs text-brand-muted">
                                            {{ $cuadranteLabel($evolucion['cuadrante']) }} ·
                                            {{ count($evolucion['cambios']) }} registro(s)
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-brand-muted">Estado más reciente</span>
                                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $estadoActual['color'] }}">
                                        {{ $estadoActual['label'] }}
                                    </span>
                                </div>
                            </div>

                            <ol class="relative divide-y divide-brand-border">
                                @foreach ($evolucion['cambios'] as $i => $cambio)
                                    @php
                                        $estadoMeta = $estadosMeta[$cambio['estado']] ?? $estadosMeta['sana'];
                                        $esActual = $i === 0;
                                    @endphp
                                    <li class="flex flex-col gap-2 px-5 py-4 sm:flex-row sm:items-start sm:gap-4">
                                        <div class="flex shrink-0 items-center gap-2 sm:w-32">
                                            <span
                                                class="h-2.5 w-2.5 shrink-0 rounded-full {{ $esActual ? 'bg-brand-primary ring-4 ring-brand-soft' : 'bg-brand-border' }}"
                                                aria-hidden="true"
                                            ></span>
                                            <span class="text-sm font-medium text-brand-primary">
                                                {{ $cambio['fecha']?->format('d/m/Y') ?? '—' }}
                                            </span>
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $estadoMeta['color'] }}">
                                                    {{ $estadoMeta['label'] }}
                                                </span>
                                                @if ($esActual)
                                                    <span class="text-xs font-medium text-brand-muted">actual</span>
                                                @endif
                                            </div>

                                            @if (! empty($cambio['observaciones']))
                                                <p class="mt-2 text-sm text-brand-primary [overflow-wrap:anywhere]">
                                                    {{ $cambio['observaciones'] }}
                                                </p>
                                            @endif

                                            <p class="mt-1 text-xs text-brand-muted">
                                                Motivo de la consulta: {{ $cambio['motivo'] ?: '—' }}
                                            </p>
                                        </div>

                                        <div class="shrink-0">
                                            <a
                                                href="{{ route('consultas.show', $cambio['consulta_id']) }}"
                                                class="inline-flex items-center justify-center gap-1 rounded-md border border-brand-border bg-white px-3 py-1.5 text-xs font-semibold text-brand-primary transition hover:bg-brand-soft"
                                            >
                                                Ver consulta
                                                <x-lucide-arrow-right class="h-3.5 w-3.5" />
                                            </a>
                                        </div>
                                    </li>
                                @endforeach
                            </ol>
                        </x-card>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
