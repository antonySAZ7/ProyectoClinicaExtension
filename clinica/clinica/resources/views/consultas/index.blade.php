<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-brand-primary leading-tight">
                    {{ $isPortal ? 'Mi historial clinico' : 'Historial clinico del paciente' }}
                </h2>
                <p class="mt-1 text-base text-brand-muted">
                    {{ $paciente->nombre_completo }}{{ $paciente->user?->email ? ' - '.$paciente->user->email : '' }}
                </p>
            </div>

            @if (! $isPortal)
                <x-link-button href="{{ route('pacientes.consultas.create', $paciente) }}" variant="primary">
                    Nueva consulta
                </x-link-button>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <x-alert type="success">
                    {{ session('success') }}
                </x-alert>
            @endif

            @if (session('error'))
                <x-alert type="error">
                    {{ session('error') }}
                </x-alert>
            @endif

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
<x-card class="p-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Paciente</p>
                        <p class="mt-2 text-base font-semibold text-brand-primary">{{ $paciente->nombre_completo }}</p>
                    </x-card>

<x-card class="p-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-brand-muted">DPI</p>
                        <p class="mt-2 text-base text-brand-primary">{{ $paciente->dpi }}</p>
                    </x-card>

<x-card class="p-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Telefono</p>
                        <p class="mt-2 text-base text-brand-primary">{{ $paciente->telefono }}</p>
                    </x-card>

<x-card class="p-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Consultas registradas</p>
                        <p class="mt-2 text-base text-brand-primary">{{ $consultas->count() }}</p>
                    </x-card>
            </div>

            <x-card class="overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-brand-border">
                        <thead class="bg-brand-soft">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-brand-muted">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-brand-muted">Motivo</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-brand-muted">Diagnostico</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-brand-muted">Registrado por</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-brand-muted">Adjuntos</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-brand-muted">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border bg-brand-contrast">
                            @forelse ($consultas as $consulta)
                                <tr class="align-top">
                                    <td class="px-6 py-4 text-sm text-brand-primary">
                                        {{ $consulta->fecha->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-brand-primary">
                                        {{ $consulta->motivo }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-brand-primary">
                                        {{ \Illuminate\Support\Str::limit($consulta->diagnostico, 100) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-brand-primary">
                                        {{ $consulta->user?->name ?? 'Personal clinico' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-brand-primary">
                                        {{ $consulta->archivos->count() }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4 text-right text-sm">
                                        <x-link-button href="{{ route($isPortal ? 'portal.consultas.show' : 'consultas.show', $consulta) }}">
                                            Ver detalle
                                        </x-link-button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-sm text-brand-muted">
                                        No hay consultas registradas para este paciente.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
