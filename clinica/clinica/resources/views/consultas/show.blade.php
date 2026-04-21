<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detalle de consulta</h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $consulta->paciente->nombre_completo }} - {{ $consulta->fecha->format('d/m/Y') }}
                </p>
            </div>

            <a
                href="{{ $isPortal ? route('portal.consultas.index') : route('pacientes.consultas.index', $consulta->paciente) }}"
                class="inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
            >
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)]">
                <div class="space-y-6">
                    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900">Resumen</h3>
                        <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Fecha</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $consulta->fecha->format('d/m/Y') }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Registrado por</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $consulta->user?->name ?? 'Personal clinico' }}</dd>
                            </div>

                            <div class="sm:col-span-2">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Motivo</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $consulta->motivo }}</dd>
                            </div>

                            <div class="sm:col-span-2">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Diagnostico</dt>
                                <dd class="mt-1 whitespace-pre-line text-sm text-gray-900">{{ $consulta->diagnostico }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900">Paciente</h3>
                        <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Nombre</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $consulta->paciente->nombre_completo }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">DPI</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $consulta->paciente->dpi }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Telefono</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $consulta->paciente->telefono }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Correo</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $consulta->paciente->correo }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900">Observaciones</h3>

                        <div class="mt-4 space-y-3">
                            @forelse ($consulta->observaciones as $observacion)
                                <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
                                    {{ $observacion->descripcion }}
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No se registraron observaciones adicionales para esta consulta.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900">Archivos adjuntos</h3>

                        <div class="mt-4 space-y-3">
                            @forelse ($consulta->archivos as $archivo)
                                <div class="flex flex-col gap-3 rounded-lg border border-gray-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $archivo->nombre_original ?? basename($archivo->ruta) }}
                                        </p>
                                        <p class="mt-1 text-xs uppercase tracking-wide text-gray-500">{{ $archivo->tipo }}</p>
                                    </div>

                                    <a
                                        href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($archivo->ruta) }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center justify-center rounded-md border border-sky-300 px-3 py-2 text-sm font-semibold text-sky-700 transition hover:bg-sky-50"
                                    >
                                        Abrir archivo
                                    </a>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No hay archivos adjuntos en esta consulta.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
