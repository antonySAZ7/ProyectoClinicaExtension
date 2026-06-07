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

            @if ($paciente)
                @php
                    $presupuestoTotal = (float) $paciente->presupuesto_total;
                    $totalPagado = (float) $paciente->total_pagado;
                    $saldoPendiente = (float) $paciente->saldo_pendiente;
                    $progresoPago = $presupuestoTotal > 0 ? min(100, round($totalPagado / $presupuestoTotal * 100, 1)) : 0;
                @endphp

                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900">Mi saldo</h3>
                        <p class="mt-1 text-sm text-gray-600">Resumen financiero de tus tratamientos registrados.</p>
                    </div>

                    <div class="grid gap-4 px-6 py-5 sm:grid-cols-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Presupuesto</p>
                            <p class="mt-1 text-lg font-semibold text-gray-900">Q{{ number_format($presupuestoTotal, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Pagado</p>
                            <p class="mt-1 text-lg font-semibold text-emerald-700">Q{{ number_format($totalPagado, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Pendiente</p>
                            <p class="mt-1 text-lg font-semibold text-amber-700">Q{{ number_format($saldoPendiente, 2) }}</p>
                        </div>
                    </div>

                    <div class="px-6 pb-5">
                        <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full bg-emerald-500" style="width: {{ $progresoPago }}%"></div>
                        </div>
                    </div>
                </div>
            @endif

            @if ($paciente?->antecedenteClinico)
                @php
                    $ant = $paciente->antecedenteClinico;
                    $medicosSi = collect(\App\Models\AntecedenteClinico::CAMPOS_MEDICOS)
                        ->filter(fn ($etq, $campo) => $ant->$campo)->values();
                    $odontoSi = collect(\App\Models\AntecedenteClinico::CAMPOS_ODONTOLOGICOS)
                        ->filter(fn ($etq, $campo) => $ant->$campo)->values();
                @endphp
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900">Mis antecedentes clinicos</h3>
                        <p class="mt-1 text-xs text-gray-500">Informacion registrada por la clinica (solo lectura).</p>
                    </div>

                    <div class="grid gap-6 px-6 py-5 sm:grid-cols-2">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Antecedentes medicos</p>
                            @if ($medicosSi->isEmpty())
                                <p class="mt-1 text-sm text-gray-500">Sin antecedentes medicos marcados.</p>
                            @else
                                <ul class="mt-2 flex flex-wrap gap-2">
                                    @foreach ($medicosSi as $etiqueta)
                                        <li class="rounded-full bg-rose-50 px-3 py-1 text-xs font-medium text-rose-700">{{ $etiqueta }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Antecedentes odontologicos</p>
                            @if ($odontoSi->isEmpty())
                                <p class="mt-1 text-sm text-gray-500">Sin antecedentes odontologicos marcados.</p>
                            @else
                                <ul class="mt-2 flex flex-wrap gap-2">
                                    @foreach ($odontoSi as $etiqueta)
                                        <li class="rounded-full bg-sky-50 px-3 py-1 text-xs font-medium text-sky-700">{{ $etiqueta }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        @if ($ant->toma_medicamento && $ant->cual_medicamento)
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Medicamento que toma</p>
                                <p class="mt-1 text-sm text-gray-900">{{ $ant->cual_medicamento }}</p>
                            </div>
                        @endif

                        @if ($ant->alergico_medicamento && $ant->cuales_medicamentos)
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Alergias a medicamentos</p>
                                <p class="mt-1 text-sm text-gray-900">{{ $ant->cuales_medicamentos }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Mis citas</h3>
                            <p class="mt-1 text-sm text-gray-600">Solo se muestran las citas asociadas a tu expediente.</p>
                        </div>

                        <a
                            href="{{ route('public.citas.create') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-md bg-[var(--brand-primary)] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-[var(--brand-primary)] focus:ring-offset-2"
                        >
                            <x-lucide-calendar-plus class="h-4 w-4" />
                            Agendar nueva cita
                        </a>
                    </div>
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
                                        {{ \Illuminate\Support\Str::of((string) $cita->hora)->substr(0, 5) }}@if ($cita->hora_fin) - {{ \Illuminate\Support\Str::of((string) $cita->hora_fin)->substr(0, 5) }}@endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        @php
                                            $badgeEstilo = match ($cita->estado) {
                                                'confirmada' => 'background:#dcfce7; color:#15803d;',
                                                'atendida'   => 'background:#dbeafe; color:#1d4ed8;',
                                                'cancelada'  => 'background:#fee2e2; color:#b91c1c;',
                                                'no_show'    => 'background:#f3f4f6; color:#4b5563;',
                                                default      => 'background:#fef3c7; color:#b45309;',
                                            };
                                        @endphp
                                        <span style="display:inline-flex; border-radius:9999px; padding:2px 10px; font-size:12px; font-weight:700; {{ $badgeEstilo }}">
                                            {{ $cita->estado === 'no_show' ? 'No show' : ucfirst($cita->estado) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{ $cita->motivo }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                        @if (in_array($cita->estado, ['cancelada', 'atendida', 'no_show']))
                                            <span class="text-xs font-medium text-gray-400">Sin accion</span>
                                        @else
                                            <div class="flex flex-col items-end gap-2 sm:flex-row sm:justify-end">
                                                @if ($cita->estado === 'pendiente')
                                                    <form method="POST" action="{{ route('citas.confirmar', $cita) }}">
                                                        @csrf
                                                        <button
                                                            type="button"
                                                            class="inline-flex items-center justify-center rounded-md border border-emerald-300 bg-emerald-50 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-emerald-700 transition hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                                                            onclick="window.confirmAndSubmit(this.closest('form'), {
                                                                title: '¿Confirmar esta cita?',
                                                                message: 'Le avisaremos al consultorio que asistirás.',
                                                                confirmText: 'Confirmar',
                                                                cancelText: 'Volver',
                                                                variant: 'info',
                                                            })"
                                                        >
                                                            Confirmar cita
                                                        </button>
                                                    </form>
                                                @endif

                                                <form method="POST" action="{{ route('portal.citas.cancelar', $cita) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <x-danger-button
                                                        type="button"
                                                        onclick="window.confirmAndSubmit(this.closest('form'), {
                                                            title: '¿Cancelar tu cita?',
                                                            message: 'La cita será cancelada y deberás agendar una nueva si la querés reprogramar.',
                                                            confirmText: 'Cancelar cita',
                                                            cancelText: 'Volver',
                                                            variant: 'warning',
                                                        })"
                                                    >
                                                        Cancelar mi cita
                                                    </x-danger-button>
                                                </form>
                                            </div>
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

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Historial clinico reciente</h3>
                            <p class="mt-1 text-sm text-gray-600">Ultimas consultas registradas en tu expediente.</p>
                        </div>

                        @if ($paciente)
                            <a
                                href="{{ route('portal.consultas.index') }}"
                                class="inline-flex items-center justify-center rounded-md border border-sky-300 px-4 py-2 text-sm font-semibold text-sky-700 transition hover:bg-sky-50"
                            >
                                Ver historial completo
                            </a>
                        @endif
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Motivo</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Registrado por</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Accion</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($consultasRecientes as $consulta)
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                        {{ $consulta->fecha->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{ $consulta->motivo }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{ $consulta->user?->name ?? 'Personal clinico' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                        <a
                                            href="{{ route('portal.consultas.show', $consulta) }}"
                                            class="inline-flex items-center justify-center rounded-md border border-gray-300 px-3 py-2 font-medium text-gray-700 transition hover:bg-gray-50"
                                        >
                                            Ver detalle
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500">
                                        No tienes consultas registradas en tu historial clinico por el momento.
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
