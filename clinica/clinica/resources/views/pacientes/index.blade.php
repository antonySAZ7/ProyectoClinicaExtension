<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pacientes</h2>
                <p class="mt-1 text-sm text-gray-500">Administra expedientes y vincula usuarios con rol paciente.</p>
            </div>

            <div class="flex items-center gap-2">
                <a
                    href="{{ route('exportar.pacientes') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    <x-lucide-download class="h-4 w-4" />
                    Exportar
                </a>
                <a
                    href="{{ route('pacientes.create') }}"
                    class="inline-flex items-center justify-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-800"
                >
                    Nuevo paciente
                </a>
            </div>
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
                <form
                    id="form-buscar-pacientes"
                    method="GET"
                    action="{{ route('pacientes.index') }}"
                    class="flex flex-col gap-3 sm:flex-row"
                >
                    <input
                        type="text"
                        name="buscar"
                        value="{{ request('buscar') }}"
                        class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-400 focus:ring-gray-400"
                        placeholder="Buscar por nombre o DPI"
                        autocomplete="off"
                    >
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                    >
                        Buscar
                    </button>
                </form>
            </div>

            @once
                <script>
                    (function () {
                        const form = document.getElementById('form-buscar-pacientes');
                        if (!form) return;
                        const input = form.querySelector('input[name="buscar"]');
                        if (!input) return;
                        let timer = null;
                        input.addEventListener('input', function () {
                            clearTimeout(timer);
                            timer = setTimeout(function () {
                                form.submit();
                            }, 350);
                        });

                        if (input.value !== '') {
                            input.focus();
                            const v = input.value;
                            input.setSelectionRange(v.length, v.length);
                        }
                    })();
                </script>
            @endonce

            @if ($pacientes->total() > 0)
                <p class="text-sm text-gray-500">
                    Mostrando {{ $pacientes->firstItem() }}–{{ $pacientes->lastItem() }} de {{ $pacientes->total() }} pacientes.
                </p>
            @endif

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Nombre</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Usuario</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">DPI</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Teléfono</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Correo</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($pacientes as $paciente)
                                <tr>
                                    <td class="px-4 py-4 text-sm font-medium text-gray-900">
                                        <a
                                            href="{{ route('pacientes.show', $paciente) }}"
                                            class="text-brand-primary hover:underline"
                                        >
                                            {{ $paciente->nombre_completo }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-700">{{ $paciente->user?->email ?? 'Sin usuario' }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-700">{{ $paciente->dpi }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-700">{{ $paciente->telefono }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-700">{{ $paciente->correo }}</td>
                                    <td class="px-4 py-4 text-sm">
                                        <div class="flex flex-col gap-2 sm:flex-row">
                                            <a
                                                href="{{ route('pacientes.show', $paciente) }}"
                                                class="inline-flex items-center justify-center rounded-md border border-brand-border px-3 py-2 font-medium text-brand-primary transition hover:bg-brand-soft"
                                            >
                                                Perfil
                                            </a>

                                            <a
                                                href="{{ route('pacientes.consultas.index', $paciente) }}"
                                                class="inline-flex items-center justify-center rounded-md border border-sky-300 px-3 py-2 font-medium text-sky-700 transition hover:bg-sky-50"
                                            >
                                                Historial
                                            </a>

                                            <a
                                                href="{{ route('pacientes.antecedentes.edit', $paciente) }}"
                                                class="inline-flex items-center justify-center rounded-md border border-emerald-300 px-3 py-2 font-medium text-emerald-700 transition hover:bg-emerald-50"
                                            >
                                                Ficha clínica
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
                                                    type="button"
                                                    class="inline-flex items-center justify-center rounded-md border border-red-300 px-3 py-2 font-medium text-red-700 transition hover:bg-red-50"
                                                    onclick="window.confirmAndSubmit(this.closest('form'), {
                                                        title: '¿Eliminar este paciente?',
                                                        message: 'Se borrará el expediente del paciente, junto con todas sus consultas, observaciones, archivos y pagos asociados. Esta acción no se puede deshacer.',
                                                        confirmText: 'Eliminar paciente',
                                                        variant: 'danger',
                                                    })"
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

            @if ($pacientes->hasPages())
                <div class="mt-2">
                    {{ $pacientes->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
