@php
    use App\Models\Tratamiento;

    $estadoMeta = [
        Tratamiento::ESTADO_EN_PROGRESO => ['label' => 'En progreso', 'class' => 'bg-sky-50 text-sky-700 border-sky-200'],
        Tratamiento::ESTADO_FINALIZADO => ['label' => 'Finalizado', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
        Tratamiento::ESTADO_SUSPENDIDO => ['label' => 'Suspendido', 'class' => 'bg-amber-50 text-amber-700 border-amber-200'],
    ];
@endphp

<x-card class="overflow-hidden">
    <div class="flex flex-col gap-3 border-b border-brand-border px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="text-lg font-semibold text-brand-primary">Tratamientos</h3>
            <p class="mt-1 text-sm text-brand-muted">Seguimiento clínico de tratamientos que requieren varias sesiones.</p>
        </div>

        <button
            x-data
            type="button"
            class="inline-flex items-center justify-center gap-2 rounded-md bg-brand-primary px-4 py-2 text-sm font-semibold text-brand-contrast transition hover:bg-brand-primary-strong"
            @click.prevent="$dispatch('open-modal', 'nuevo-tratamiento')"
        >
            <x-lucide-plus class="h-4 w-4" />
            Nuevo tratamiento
        </button>
    </div>

    <div class="divide-y divide-brand-border">
        @forelse ($paciente->tratamientos as $tratamiento)
            @php
                $meta = $estadoMeta[$tratamiento->estado] ?? $estadoMeta[Tratamiento::ESTADO_EN_PROGRESO];
                $totalFases = $tratamiento->fases->count();
                $fasesCompletadas = $tratamiento->fases->where('completada', true)->count();
                $avance = $totalFases > 0 ? round(($fasesCompletadas / $totalFases) * 100) : 0;
            @endphp

            <section class="px-6 py-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h4 class="text-base font-semibold text-brand-primary">{{ $tratamiento->nombre }}</h4>
                            <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $meta['class'] }}">
                                {{ $meta['label'] }}
                            </span>
                            @if ($tratamiento->pieza)
                                <span class="inline-flex items-center rounded-full border border-brand-border bg-brand-soft px-2.5 py-1 text-xs font-semibold text-brand-muted">
                                    Pieza {{ $tratamiento->pieza->numero }}
                                </span>
                            @endif
                        </div>

                        <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-brand-muted">
                            <span>Inicio: {{ $tratamiento->fecha_inicio?->format('d/m/Y') }}</span>
                            <span>Responsable: {{ $tratamiento->user?->name ?? 'No asignado' }}</span>
                            <span>{{ $fasesCompletadas }}/{{ $totalFases }} fase(s) completada(s)</span>
                        </div>

                        @if ($tratamiento->descripcion)
                            <p class="mt-3 max-w-3xl text-sm text-brand-primary [overflow-wrap:anywhere]">{{ $tratamiento->descripcion }}</p>
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-2 lg:justify-end">
                        <button
                            x-data
                            type="button"
                            class="inline-flex items-center justify-center gap-1 rounded-md border border-brand-border bg-white px-3 py-2 text-xs font-semibold text-brand-primary transition hover:bg-brand-soft"
                            @click.prevent="$dispatch('open-modal', 'nueva-fase-{{ $tratamiento->id }}')"
                        >
                            <x-lucide-list-plus class="h-4 w-4" />
                            Nueva fase
                        </button>

                        <button
                            x-data
                            type="button"
                            class="inline-flex items-center justify-center gap-1 rounded-md border border-brand-border bg-white px-3 py-2 text-xs font-semibold text-brand-primary transition hover:bg-brand-soft"
                            @click.prevent="$dispatch('open-modal', 'editar-tratamiento-{{ $tratamiento->id }}')"
                        >
                            <x-lucide-pencil class="h-4 w-4" />
                            Editar
                        </button>

                        @if ($tratamiento->estado !== Tratamiento::ESTADO_FINALIZADO)
                            <form method="POST" action="{{ route('tratamientos.finalizar', $tratamiento) }}">
                                @csrf
                                @method('PATCH')
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center gap-1 rounded-md border border-emerald-300 bg-white px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50"
                                    onclick="window.confirmAndSubmit(this.closest('form'), {
                                        title: 'Finalizar tratamiento',
                                        message: 'El tratamiento quedará marcado como finalizado. Puede editarlo después si necesita reabrirlo.',
                                        confirmText: 'Finalizar',
                                        variant: 'info',
                                    })"
                                >
                                    <x-lucide-check-circle class="h-4 w-4" />
                                    Finalizar
                                </button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('tratamientos.destroy', $tratamiento) }}">
                            @csrf
                            @method('DELETE')
                            <button
                                type="button"
                                class="inline-flex items-center justify-center gap-1 rounded-md border border-rose-300 bg-white px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-50"
                                onclick="window.confirmAndSubmit(this.closest('form'), {
                                    title: 'Eliminar tratamiento',
                                    message: 'Se eliminarán el tratamiento y todas sus fases. Esta acción no se puede deshacer.',
                                    confirmText: 'Eliminar',
                                    variant: 'danger',
                                })"
                            >
                                <x-lucide-trash-2 class="h-4 w-4" />
                                Eliminar
                            </button>
                        </form>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="flex items-center justify-between text-xs text-brand-muted">
                        <span>Avance del tratamiento</span>
                        <span class="font-semibold">{{ $avance }}%</span>
                    </div>
                    <div class="mt-1 h-2 w-full overflow-hidden rounded-full bg-brand-border">
                        <div class="h-full rounded-full bg-sky-500 transition-all" style="width: {{ $avance }}%;"></div>
                    </div>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse ($tratamiento->fases as $fase)
                        <div class="grid gap-3 rounded-lg border border-brand-border bg-brand-soft/40 p-4 sm:grid-cols-[auto_minmax(0,1fr)_auto] sm:items-start">
                            <div class="mt-0.5">
                                @if ($fase->completada)
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                                        <x-lucide-check class="h-4 w-4" />
                                    </span>
                                @else
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-white text-brand-muted ring-1 ring-brand-border">
                                        <x-lucide-circle class="h-4 w-4" />
                                    </span>
                                @endif
                            </div>

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2 text-xs text-brand-muted">
                                    <span class="font-semibold text-brand-primary">{{ $fase->fecha?->format('d/m/Y') }}</span>
                                    <span>Orden {{ $fase->orden }}</span>
                                    <span>{{ $fase->user?->name ?? 'Sin responsable' }}</span>
                                </div>
                                <p class="mt-1 text-sm text-brand-primary [overflow-wrap:anywhere]">{{ $fase->descripcion }}</p>
                                @if ($fase->consulta)
                                    <a href="{{ route('consultas.show', $fase->consulta) }}" class="mt-2 inline-flex text-xs font-semibold text-sky-700 hover:underline">
                                        Ver consulta vinculada
                                    </a>
                                @endif
                            </div>

                            <div class="flex gap-2 sm:justify-end">
                                <button
                                    x-data
                                    type="button"
                                    class="inline-flex items-center justify-center rounded-md border border-brand-border bg-white p-2 text-brand-primary transition hover:bg-brand-soft"
                                    @click.prevent="$dispatch('open-modal', 'editar-fase-{{ $fase->id }}')"
                                    aria-label="Editar fase"
                                >
                                    <x-lucide-pencil class="h-4 w-4" />
                                </button>

                                <form method="POST" action="{{ route('fases-tratamiento.destroy', $fase) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center rounded-md border border-rose-300 bg-white p-2 text-rose-700 transition hover:bg-rose-50"
                                        onclick="window.confirmAndSubmit(this.closest('form'), {
                                            title: 'Eliminar fase',
                                            message: 'Esta fase se eliminará del tratamiento.',
                                            confirmText: 'Eliminar',
                                            variant: 'danger',
                                        })"
                                        aria-label="Eliminar fase"
                                    >
                                        <x-lucide-trash-2 class="h-4 w-4" />
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-lg border border-dashed border-brand-border bg-brand-soft/50 px-4 py-5 text-center text-sm text-brand-muted">
                            Aún no hay fases registradas para este tratamiento.
                        </div>
                    @endforelse
                </div>
            </section>
        @empty
            <div class="px-6 py-10 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-brand-soft text-brand-primary">
                    <x-lucide-clipboard-list class="h-6 w-6" />
                </div>
                <p class="mt-3 text-sm font-semibold text-brand-primary">Sin tratamientos en seguimiento</p>
                <p class="mt-1 text-sm text-brand-muted">Registre el primer tratamiento para llevar el avance por fases.</p>
            </div>
        @endforelse
    </div>
</x-card>

<x-modal name="nuevo-tratamiento" maxWidth="xl" focusable>
    @include('pacientes.partials.tratamiento-form', [
        'title' => 'Nuevo tratamiento',
        'action' => route('pacientes.tratamientos.store', $paciente),
        'method' => 'POST',
        'tratamiento' => null,
        'piezasDentales' => $piezasDentales,
        'estadoMeta' => $estadoMeta,
    ])
</x-modal>

@foreach ($paciente->tratamientos as $tratamiento)
    <x-modal name="editar-tratamiento-{{ $tratamiento->id }}" maxWidth="xl" focusable>
        @include('pacientes.partials.tratamiento-form', [
            'title' => 'Editar tratamiento',
            'action' => route('tratamientos.update', $tratamiento),
            'method' => 'PUT',
            'tratamiento' => $tratamiento,
            'piezasDentales' => $piezasDentales,
            'estadoMeta' => $estadoMeta,
        ])
    </x-modal>

    <x-modal name="nueva-fase-{{ $tratamiento->id }}" maxWidth="xl" focusable>
        @include('pacientes.partials.fase-tratamiento-form', [
            'title' => 'Nueva fase',
            'action' => route('tratamientos.fases.store', $tratamiento),
            'method' => 'POST',
            'fase' => null,
            'consultasParaTratamiento' => $consultasParaTratamiento,
        ])
    </x-modal>

    @foreach ($tratamiento->fases as $fase)
        <x-modal name="editar-fase-{{ $fase->id }}" maxWidth="xl" focusable>
            @include('pacientes.partials.fase-tratamiento-form', [
                'title' => 'Editar fase',
                'action' => route('fases-tratamiento.update', $fase),
                'method' => 'PUT',
                'fase' => $fase,
                'consultasParaTratamiento' => $consultasParaTratamiento,
            ])
        </x-modal>
    @endforeach
@endforeach
