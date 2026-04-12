<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Portal del paciente
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="overflow-hidden rounded-xl border border-sky-100 bg-white shadow-sm">
                <div class="border-b border-sky-100 px-6 py-4">
                    <p class="text-sm font-semibold uppercase tracking-wide text-sky-700">Informacion personal</p>
                </div>

                <div class="grid gap-4 px-6 py-5 sm:grid-cols-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Nombre</p>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $paciente?->nombre_completo ?? $user->name }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Telefono</p>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $paciente?->telefono ?? 'Pendiente de registrar' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Correo</p>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $paciente?->correo ?? $user->email }}
                        </p>
                    </div>
                </div>
            </div>

            @if (! $paciente)
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-6 py-5 text-sm text-amber-800 shadow-sm">
                    Tu cuenta ya tiene acceso como paciente, pero todavia no esta vinculada a un expediente clinico.
                    Cuando un administrador relacione tu usuario con tu registro, aqui podras ver tus citas.
                </div>
            @endif

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">Mis citas</h3>
                    <p class="mt-1 text-sm text-gray-600">Solo se muestran las citas asociadas a tu expediente.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Hora</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Motivo</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Accion</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($citas as $cita)
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                        {{ $cita->fecha?->format('d/m/Y') }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                        {{ \Illuminate\Support\Str::of((string) $cita->hora)->substr(0, 5) }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                        {{ ucfirst($cita->estado) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{ $cita->motivo }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                        @if ($cita->estado !== 'cancelada')
                                            <form method="POST" action="{{ route('portal.citas.cancelar', $cita) }}">
                                                @csrf
                                                @method('PATCH')
                                                <x-danger-button onclick="return confirm('Deseas cancelar esta cita?');">
                                                    Cancelar mi cita
                                                </x-danger-button>
                                            </form>
                                        @else
                                            <span class="text-xs font-medium text-gray-400">Sin accion</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">
                                        No tienes citas futuras registradas por el momento.
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
