<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Portal del paciente
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-xl border border-sky-100 bg-white shadow-sm">
                <div class="p-6">
                    <p class="text-sm font-medium uppercase tracking-wide text-sky-700">Cuenta</p>
                    <h1 class="mt-2 text-2xl font-semibold text-gray-900">{{ $user->name }}</h1>
                    <p class="mt-1 text-sm text-gray-600">{{ $user->email }}</p>
                </div>
            </div>

            @if (! $paciente)
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-amber-900">Expediente pendiente de vincular</h3>
                    <p class="mt-2 text-sm text-amber-800">
                        Tu cuenta ya tiene acceso como paciente, pero todavia no esta vinculada a un expediente en la clinica.
                        Un administrador puede asignarte desde el modulo de pacientes.
                    </p>
                </div>
            @else
                <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)]">
                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                        <div class="border-b border-gray-100 px-6 py-4">
                            <h3 class="text-lg font-semibold text-gray-900">Datos del expediente</h3>
                        </div>
                        <dl class="grid gap-4 px-6 py-5 sm:grid-cols-2">
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Nombre</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $paciente->nombre_completo }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">DPI</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $paciente->dpi }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Telefono</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $paciente->telefono }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Correo</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $paciente->correo }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Direccion</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $paciente->direccion }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                        <div class="border-b border-gray-100 px-6 py-4">
                            <h3 class="text-lg font-semibold text-gray-900">Proximas citas</h3>
                            <p class="mt-1 text-sm text-gray-600">Agenda visible solo para el paciente autenticado.</p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Fecha</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Hora</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Motivo</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @forelse ($paciente->citas as $cita)
                                        <tr>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ $cita->fecha?->format('d/m/Y') }}</td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ \Illuminate\Support\Str::of((string) $cita->hora)->substr(0, 5) }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-700">{{ $cita->motivo }}</td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                                @php
                                                    $badgeEstilo = match ($cita->estado) {
                                                        'confirmada' => 'background:#dcfce7; color:#15803d;',
                                                        'cancelada'  => 'background:#fee2e2; color:#b91c1c;',
                                                        default      => 'background:#fef3c7; color:#b45309;',
                                                    };
                                                @endphp
                                                <span style="display:inline-flex; border-radius:9999px; padding:2px 10px; font-size:12px; font-weight:700; {{ $badgeEstilo }}">
                                                    {{ ucfirst($cita->estado) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500">
                                                No tienes citas futuras registradas.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
