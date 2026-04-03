@extends('layouts.app')

@section('content')
    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Citas futuras</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Gestiona las citas programadas y su estado actual.
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <a
                        href="{{ route('citas.calendario') }}"
                        class="inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                    >
                        Ver calendario
                    </a>

                    <a
                        href="{{ route('citas.create') }}"
                        class="inline-flex items-center justify-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-800"
                    >
                        Nueva cita
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Paciente</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Fecha</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Hora</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Estado</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($citas as $cita)
                                <tr class="align-top">
                                    <td class="whitespace-nowrap px-4 py-4 text-sm font-medium text-gray-900">
                                        {{ optional($cita->paciente)->nombre_completo ?? 'Paciente no disponible' }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-700">
                                        {{ $cita->fecha?->format('d/m/Y') }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-700">
                                        {{ \Illuminate\Support\Str::of((string) $cita->hora)->substr(0, 5) }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4 text-sm">
                                        @php
                                            $badgeClasses = match ($cita->estado) {
                                                'confirmada' => 'bg-blue-100 text-blue-800',
                                                'cancelada' => 'bg-red-100 text-red-800',
                                                default => 'bg-amber-100 text-amber-800',
                                            };
                                        @endphp

                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $badgeClasses }}">
                                            {{ ucfirst($cita->estado) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-sm">
                                        <div class="flex flex-col gap-2 sm:flex-row">
                                            <a
                                                href="{{ route('citas.edit', $cita) }}"
                                                class="inline-flex items-center justify-center rounded-md border border-amber-300 px-3 py-2 font-medium text-amber-700 transition hover:bg-amber-50"
                                            >
                                                Editar
                                            </a>

                                            @if ($cita->estado !== 'cancelada')
                                                <form method="POST" action="{{ route('citas.destroy', $cita) }}">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button
                                                        type="submit"
                                                        class="inline-flex items-center justify-center rounded-md border border-red-300 px-3 py-2 font-medium text-red-700 transition hover:bg-red-50"
                                                        onclick="return confirm('Deseas cancelar esta cita?')"
                                                    >
                                                        Cancelar
                                                    </button>
                                                </form>
                                            @else
                                                <span class="inline-flex items-center rounded-md bg-gray-100 px-3 py-2 font-medium text-gray-500">
                                                    Cancelada
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">
                                        No hay citas futuras registradas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
