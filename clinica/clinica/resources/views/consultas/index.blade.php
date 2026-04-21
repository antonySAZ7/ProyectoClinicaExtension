<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $isPortal ? 'Mi historial clinico' : 'Historial clinico del paciente' }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $paciente->nombre_completo }}{{ $paciente->user?->email ? ' - '.$paciente->user->email : '' }}
                </p>
            </div>

            @if (! $isPortal)
                <a
                    href="{{ route('pacientes.consultas.create', $paciente) }}"
                    class="inline-flex items-center justify-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-800"
                >
                    Nueva consulta
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Paciente</p>
                    <p class="mt-2 text-sm font-semibold text-gray-900">{{ $paciente->nombre_completo }}</p>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">DPI</p>
                    <p class="mt-2 text-sm text-gray-900">{{ $paciente->dpi }}</p>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Telefono</p>
                    <p class="mt-2 text-sm text-gray-900">{{ $paciente->telefono }}</p>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Consultas registradas</p>
                    <p class="mt-2 text-sm text-gray-900">{{ $consultas->count() }}</p>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Fecha</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Motivo</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Diagnostico</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Registrado por</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Adjuntos</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($consultas as $consulta)
                                <tr class="align-top">
                                    <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-700">
                                        {{ $consulta->fecha->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-4 text-sm font-medium text-gray-900">
                                        {{ $consulta->motivo }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-700">
                                        {{ \Illuminate\Support\Str::limit($consulta->diagnostico, 100) }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-700">
                                        {{ $consulta->user?->name ?? 'Personal clinico' }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-700">
                                        {{ $consulta->archivos->count() }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4 text-right text-sm">
                                        <a
                                            href="{{ route($isPortal ? 'portal.consultas.show' : 'consultas.show', $consulta) }}"
                                            class="inline-flex items-center justify-center rounded-md border border-gray-300 px-3 py-2 font-medium text-gray-700 transition hover:bg-gray-50"
                                        >
                                            Ver detalle
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">
                                        No hay consultas registradas para este paciente.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
