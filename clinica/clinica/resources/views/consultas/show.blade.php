<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-brand-primary leading-tight">Detalle de consulta</h2>
                <p class="mt-1 break-words text-base text-brand-muted">
                    {{ $consulta->paciente->nombre_completo }} - {{ $consulta->fecha->format('d/m/Y') }}
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                @unless ($isPortal)
                    @php $saldoPaciente = (float) $consulta->paciente->saldo_pendiente; @endphp

                    @if ($saldoPaciente > 0)
                        <button
                            x-data
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700"
                            @click.prevent="$dispatch('open-modal', 'registrar-abono')"
                        >
                            <x-lucide-circle-dollar-sign class="h-4 w-4" />
                            Registrar abono
                        </button>
                    @endif

                    <form method="POST" action="{{ route('consultas.seguimiento.store', $consulta) }}">
                        @csrf
                        <x-button type="submit" variant="outline">
                            Crear seguimiento
                        </x-button>
                    </form>

                    <x-link-button href="{{ route('consultas.edit', $consulta) }}">
                        Editar consulta
                    </x-link-button>

                    <x-link-button variant="primary" href="{{ route('consultas.pdf', $consulta) }}">
                        Exportar PDF
                    </x-link-button>
                @endunless

                <x-link-button href="{{ $isPortal ? route('portal.consultas.index') : route('pacientes.consultas.index', $consulta->paciente) }}">
                    Volver
                </x-link-button>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <x-alert type="success">
                    {{ session('success') }}
                </x-alert>
            @endif

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)]">
                <div class="space-y-6">
                    <x-card class="p-6">
                        <h3 class="text-lg font-semibold text-brand-primary">Resumen</h3>
                        <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Fecha</dt>
                                <dd class="mt-1 text-base text-brand-primary">{{ $consulta->fecha->format('d/m/Y') }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Registrado por</dt>
                                <dd class="mt-1 text-base text-brand-primary">{{ $consulta->user?->name ?? 'Personal clinico' }}</dd>
                            </div>

                            <div class="min-w-0 sm:col-span-2">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Motivo</dt>
                                <dd class="mt-1 text-base text-brand-primary [overflow-wrap:anywhere]">{{ $consulta->motivo }}</dd>
                            </div>

                            <div class="min-w-0 sm:col-span-2">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Diagnostico</dt>
                                <dd class="mt-1 whitespace-pre-line text-base text-brand-primary [overflow-wrap:anywhere]">{{ $consulta->diagnostico }}</dd>
                            </div>
                        </dl>
                    </x-card>

                    @php
                        $tieneSignos = $consulta->peso || $consulta->altura || $consulta->presion_arterial
                            || $consulta->frecuencia_cardiaca || $consulta->frecuencia_respiratoria || $consulta->signos_otros;
                    @endphp
                    @if ($tieneSignos)
                        <x-card class="p-6">
                            <h3 class="text-lg font-semibold text-brand-primary">Signos vitales</h3>
                            <dl class="mt-4 grid gap-4 sm:grid-cols-3">
                                @if ($consulta->peso)
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Peso</dt>
                                        <dd class="mt-1 text-base text-brand-primary">{{ $consulta->peso }} kg</dd>
                                    </div>
                                @endif
                                @if ($consulta->altura)
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Altura</dt>
                                        <dd class="mt-1 text-base text-brand-primary">{{ $consulta->altura }} m</dd>
                                    </div>
                                @endif
                                @if ($consulta->presion_arterial)
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Presion arterial</dt>
                                        <dd class="mt-1 text-base text-brand-primary">{{ $consulta->presion_arterial }}</dd>
                                    </div>
                                @endif
                                @if ($consulta->frecuencia_cardiaca)
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Frec. cardiaca</dt>
                                        <dd class="mt-1 text-base text-brand-primary">{{ $consulta->frecuencia_cardiaca }} lpm</dd>
                                    </div>
                                @endif
                                @if ($consulta->frecuencia_respiratoria)
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Frec. respiratoria</dt>
                                        <dd class="mt-1 text-base text-brand-primary">{{ $consulta->frecuencia_respiratoria }} rpm</dd>
                                    </div>
                                @endif
                                @if ($consulta->signos_otros)
                                    <div class="min-w-0 sm:col-span-3">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Otros</dt>
                                        <dd class="mt-1 text-base text-brand-primary [overflow-wrap:anywhere]">{{ $consulta->signos_otros }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </x-card>
                    @endif

                    <x-card class="p-6">
                        <h3 class="text-lg font-semibold text-brand-primary">Paciente</h3>
                        <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div class="min-w-0">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Nombre</dt>
                                <dd class="mt-1 text-base text-brand-primary [overflow-wrap:anywhere]">{{ $consulta->paciente->nombre_completo }}</dd>
                            </div>

                            <div class="min-w-0">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-brand-muted">DPI</dt>
                                <dd class="mt-1 text-base text-brand-primary [overflow-wrap:anywhere]">{{ $consulta->paciente->dpi }}</dd>
                            </div>

                            <div class="min-w-0">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Telefono</dt>
                                <dd class="mt-1 text-base text-brand-primary [overflow-wrap:anywhere]">{{ $consulta->paciente->telefono }}</dd>
                            </div>

                            <div class="min-w-0">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Correo</dt>
                                <dd class="mt-1 text-base text-brand-primary [overflow-wrap:anywhere]">{{ $consulta->paciente->correo }}</dd>
                            </div>
                        </dl>
                    </x-card>
                </div>

                <div class="space-y-6">
                    <x-card class="p-6">
                        <h3 class="text-lg font-semibold text-brand-primary">Observaciones</h3>

                        <div class="mt-4 space-y-3">
                            @forelse ($consulta->observaciones->sortBy('created_at') as $observacion)
                                <div class="rounded-lg border border-brand-border bg-brand-soft px-4 py-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-brand-muted">
                                            {{ $observacion->created_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}
                                        </p>

                                        @unless ($isPortal)
                                            <form method="POST" action="{{ route('observaciones.destroy', $observacion) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="button"
                                                    class="text-xs font-medium text-rose-600 hover:text-rose-700"
                                                    onclick="window.confirmAndSubmit(this.closest('form'), {
                                                        title: '¿Eliminar esta observación?',
                                                        message: 'La observación se borrará permanentemente del historial de la consulta.',
                                                        confirmText: 'Eliminar',
                                                        variant: 'danger',
                                                    })"
                                                >
                                                    Eliminar
                                                </button>
                                            </form>
                                        @endunless
                                    </div>
                                    <p class="mt-2 whitespace-pre-line text-base text-brand-primary [overflow-wrap:anywhere]">
                                        {{ $observacion->descripcion }}
                                    </p>
                                </div>
                            @empty
                                <p class="text-base text-brand-muted">No se registraron observaciones adicionales para esta consulta.</p>
                            @endforelse
                        </div>

                        @unless ($isPortal)
                            <form method="POST" action="{{ route('consultas.observaciones.store', $consulta) }}" class="mt-5 space-y-3">
                                @csrf
                                <label for="nueva-observacion" class="block text-sm font-medium text-brand-muted">
                                    Agregar nueva observación
                                </label>
                                <x-textarea
                                    id="nueva-observacion"
                                    name="descripcion"
                                    rows="3"
                                    maxlength="4000"
                                    placeholder="Escribe una nota de seguimiento, hallazgo o evolución."
                                    required></x-textarea>
                                <x-button type="submit" class="btn btn-primary">
                                    Agregar observación
                                </x-button>
                            </form>
                        @endunless
                    </x-card>

                    <x-card class="p-6">
                    <h3 class="text-lg font-semibold text-brand-primary">Archivos adjuntos</h3>

                    <div class="mt-4 space-y-3">
                        @forelse ($consulta->archivos as $archivo)
                            <div class="flex flex-col gap-3 rounded-lg border border-brand-border px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0 flex-1">
                                    <p class="text-base font-medium text-brand-primary [overflow-wrap:anywhere]">
                                        {{ $archivo->nombre_original ?? basename($archivo->ruta) }}
                                    </p>
                                    <p class="mt-1 text-xs uppercase tracking-wide text-brand-muted [overflow-wrap:anywhere]">
                                        {{ $archivo->tipo }}
                                    </p>
                                </div>

                                <div class="flex flex-shrink-0 flex-wrap gap-2 sm:flex-nowrap">
                                    <x-link-button
                                        href="{{ route('archivos.ver', $archivo) }}"
                                        target="_blank"
                                        rel="noopener"
                                    >
                                        Ver
                                    </x-link-button>

                                    <x-link-button
                                        variant="primary"
                                        href="{{ route('archivos.descargar', $archivo) }}"
                                    >
                                        Descargar
                                    </x-link-button>

                                    @unless ($isPortal)
                                        <form method="POST" action="{{ route('archivos.destroy', $archivo) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="button"
                                                onclick="window.confirmAndSubmit(this.closest('form'), {
                                                    title: '¿Eliminar este archivo?',
                                                    message: 'El archivo se borrará permanentemente del expediente del paciente.',
                                                    confirmText: 'Eliminar',
                                                    variant: 'danger',
                                                })"
                                                class="inline-flex items-center justify-center rounded-md border border-rose-300 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-100"
                                            >
                                                Eliminar
                                            </button>
                                        </form>
                                    @endunless
                                </div>
                            </div>
                        @empty
                            <p class="text-base text-brand-muted">
                                No hay archivos adjuntos en esta consulta.
                            </p>
                        @endforelse
                    </div>

                    @unless ($isPortal)
                        <form
                            method="POST"
                            action="{{ route('consultas.archivos.store', $consulta) }}"
                            enctype="multipart/form-data"
                            class="mt-5 space-y-3 border-t border-brand-border pt-5"
                        >
                            @csrf
                            <label for="archivos-nuevos" class="block text-sm font-medium text-brand-muted">
                                Agregar archivos
                            </label>

                            <input
                                id="archivos-nuevos"
                                type="file"
                                name="archivos[]"
                                multiple
                                accept=".pdf,image/png,image/jpeg,image/jpg,image/webp"
                                class="block w-full text-sm text-brand-primary file:mr-3 file:rounded-md file:border-0 file:bg-brand-soft file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-primary hover:file:bg-brand-border"
                                required
                            >

                            <p class="text-xs text-brand-muted">PDF, JPG, JPEG, PNG, WEBP — máximo 5 MB por archivo.</p>

                            <x-button type="submit" class="btn btn-primary">
                                Subir archivos
                            </x-button>
                        </form>
                    @endunless
                </x-card>
                </div>
            </div>

            {{-- Odontograma --}}
            <x-card class="p-6">
                <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-brand-primary">Odontograma</h3>
                        <p class="mt-1 text-sm text-brand-muted">
                            @if ($isPortal)
                                Estado dental registrado en esta consulta.
                            @else
                                Registra el estado clínico de cada pieza dental.
                            @endif
                        </p>
                    </div>
                </div>

                <x-odontograma :consulta-id="$consulta->id" :view-only="$isPortal" />
            </x-card>

            {{-- Presupuesto (ancho completo, al final) --}}
            @include('consultas.partials.presupuesto', [
                'consulta' => $consulta,
                'isPortal' => $isPortal,
                'piezasCatalogo' => $piezasCatalogo,
                'tarifasCatalogo' => $tarifasCatalogo,
            ])
        </div>
    </div>

    @unless ($isPortal)
        @if ((float) $consulta->paciente->saldo_pendiente > 0)
            @include('pagos.partials.modal-abono', [
                'paciente' => $consulta->paciente,
                'saldoPendiente' => (float) $consulta->paciente->saldo_pendiente,
                'consultas' => collect([[
                    'id' => $consulta->id,
                    'label' => $consulta->fecha->format('d/m/Y').' — '.($consulta->motivo ?: 'Sin motivo'),
                ]]),
                'consultaPreasignada' => $consulta->id,
            ])
        @endif
    @endunless
</x-app-layout>
