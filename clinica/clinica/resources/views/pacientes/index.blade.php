<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pacientes</h2>
                <p class="mt-1 text-sm text-gray-500">Administra expedientes y vincula usuarios con rol paciente.</p>
            </div>

            <a
                href="{{ route('pacientes.create') }}"
                class="inline-flex items-center justify-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-800"
            >
                Nuevo paciente
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <form method="GET" action="{{ route('pacientes.index') }}" class="flex flex-col gap-3 sm:flex-row">
                    <input
                        type="text"
                        name="buscar"
                        value="{{ request('buscar') }}"
                        class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-400 focus:ring-gray-400"
                        placeholder="Buscar por nombre o DPI"
                    >
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                    >
                        Buscar
                    </button>
                </form>
            </div>

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Nombre</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Usuario</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">DPI</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Telefono</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Correo</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($pacientes as $paciente)
                                <tr>
                                    <td class="px-4 py-4 text-sm font-medium text-gray-900">{{ $paciente->nombre_completo }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-700">{{ $paciente->user?->email ?? 'Sin usuario' }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-700">{{ $paciente->dpi }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-700">{{ $paciente->telefono }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-700">{{ $paciente->correo }}</td>
                                    <td class="px-4 py-4 text-sm">
                                        <div class="flex flex-col gap-2 sm:flex-row">
                                            <a
                                                href="{{ route('pacientes.consultas.index', $paciente) }}"
                                                class="inline-flex items-center justify-center rounded-md border border-sky-300 px-3 py-2 font-medium text-sky-700 transition hover:bg-sky-50"
                                            >
                                                Historial
                                            </a>

                                            <a
                                                href="{{ route('pacientes.edit', $paciente) }}"
                                                class="inline-flex items-center justify-center rounded-md border border-amber-300 px-3 py-2 font-medium text-amber-700 transition hover:bg-amber-50"
                                            >
                                                Editar
                                            </a>

                                            <form method="POST" action="{{ route('pacientes.destroy', $paciente) }}">
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center justify-center rounded-md border border-red-300 px-3 py-2 font-medium text-red-700 transition hover:bg-red-50"
                                                    onclick="return confirm('Deseas eliminar este paciente?')"
                                                >
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">
                                        No hay pacientes registrados.
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
