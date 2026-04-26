<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-brand-primary leading-tight">Detalle de consulta</h2>
                <p class="mt-1 text-base text-brand-muted">
                    {{ $consulta->paciente->nombre_completo }} - {{ $consulta->fecha->format('d/m/Y') }}
                </p>
            </div>

            <x-link-button href="{{ $isPortal ? route('portal.consultas.index') : route('pacientes.consultas.index', $consulta->paciente) }}">
                Volver
            </x-link-button>
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

                            <div class="sm:col-span-2">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Motivo</dt>
                                <dd class="mt-1 text-base text-brand-primary">{{ $consulta->motivo }}</dd>
                            </div>

                            <div class="sm:col-span-2">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Diagnostico</dt>
                                <dd class="mt-1 whitespace-pre-line text-base text-brand-primary">{{ $consulta->diagnostico }}</dd>
                            </div>
                        </dl>
                    </x-card>

                    <x-card class="p-6">
                        <h3 class="text-lg font-semibold text-brand-primary">Paciente</h3>
                        <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Nombre</dt>
                                <dd class="mt-1 text-base text-brand-primary">{{ $consulta->paciente->nombre_completo }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-brand-muted">DPI</dt>
                                <dd class="mt-1 text-base text-brand-primary">{{ $consulta->paciente->dpi }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Telefono</dt>
                                <dd class="mt-1 text-base text-brand-primary">{{ $consulta->paciente->telefono }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Correo</dt>
                                <dd class="mt-1 text-base text-brand-primary">{{ $consulta->paciente->correo }}</dd>
                            </div>
                        </dl>
                    </x-card>
                </div>

                <div class="space-y-6">
                    <x-card class="p-6">
                        <h3 class="text-lg font-semibold text-brand-primary">Observaciones</h3>

                        <div class="mt-4 space-y-3">
                            @forelse ($consulta->observaciones as $observacion)
                                <div class="rounded-lg border border-brand-border bg-brand-soft px-4 py-3 text-base text-brand-primary">
                                    {{ $observacion->descripcion }}
                                </div>
                            @empty
                                <p class="text-base text-brand-muted">No se registraron observaciones adicionales para esta consulta.</p>
                            @endforelse
                        </div>
                    </x-card>

                    <x-card class="p-6">
                    <h3 class="text-lg font-semibold text-brand-primary">Archivos adjuntos</h3>

                    <div class="mt-4 space-y-3">
                        @forelse ($consulta->archivos as $archivo)
                            <div class="flex flex-col gap-3 rounded-lg border border-brand-border px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-base font-medium text-brand-primary">
                                        {{ $archivo->nombre_original ?? basename($archivo->ruta) }}
                                    </p>
                                    <p class="mt-1 text-xs uppercase tracking-wide text-brand-muted">
                                        {{ $archivo->tipo }}
                                    </p>
                                </div>

                                <x-link-button 
                                    href="{{ Storage::disk('public')->url($archivo->ruta) }}" 
                                    target="_blank"
                                >
                                    Abrir archivo
                                </x-link-button>
                            </div>
                        @empty
                            <p class="text-base text-brand-muted">
                                No hay archivos adjuntos en esta consulta.
                            </p>
                        @endforelse
                    </div>
                </x-card>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
