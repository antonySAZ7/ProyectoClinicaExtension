@php
    $presupuestoTotal = (float) $paciente->presupuesto_total;
    $totalPagado = (float) $paciente->total_pagado;
    $saldoPendiente = (float) $paciente->saldo_pendiente;
    $porcentajePagado = $presupuestoTotal > 0
        ? min(100, round(($totalPagado / $presupuestoTotal) * 100, 1))
        : 0;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-brand-primary leading-tight">
                    {{ $paciente->nombre_completo }}
                </h2>
                <p class="mt-1 text-sm text-brand-muted">
                    Perfil del paciente · DPI {{ $paciente->dpi ?: '—' }}
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <x-link-button href="{{ route('pacientes.consultas.index', $paciente) }}">
                    Historial clínico
                </x-link-button>
                <x-link-button href="{{ route('pacientes.odontograma.evolucion', $paciente) }}">
                    Evolución odontograma
                </x-link-button>
                <x-link-button href="{{ route('pacientes.antecedentes.edit', $paciente) }}">
                    Ficha clínica
                </x-link-button>
                <x-link-button href="{{ route('pacientes.estado-cuenta.pdf', $paciente) }}">
                    Estado de cuenta
                </x-link-button>
                <x-link-button href="{{ route('pacientes.edit', $paciente) }}" variant="primary">
                    Editar
                </x-link-button>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <x-alert type="success">{{ session('success') }}</x-alert>
            @endif

            {{-- Info básica + saldo --}}
            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)]">
                {{-- Info personal --}}
                <x-card class="p-6">
                    <h3 class="text-lg font-semibold text-brand-primary">Información</h3>
                    <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Teléfono</dt>
                            <dd class="mt-1 text-sm text-brand-primary">{{ $paciente->telefono ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Correo</dt>
                            <dd class="mt-1 text-sm text-brand-primary break-words">{{ $paciente->correo ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Fecha de nacimiento</dt>
                            <dd class="mt-1 text-sm text-brand-primary">
                                {{ $paciente->fecha_nacimiento?->format('d/m/Y') ?: '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Sexo</dt>
                            <dd class="mt-1 text-sm text-brand-primary">{{ $paciente->sexo ?: '—' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Dirección</dt>
                            <dd class="mt-1 text-sm text-brand-primary">{{ $paciente->direccion ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Ocupación</dt>
                            <dd class="mt-1 text-sm text-brand-primary">{{ $paciente->ocupacion ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Estado civil</dt>
                            <dd class="mt-1 text-sm text-brand-primary">{{ $paciente->estado_civil ?: '—' }}</dd>
                        </div>
                    </dl>
                </x-card>

                {{-- Tarjeta de saldo --}}
                <x-card class="p-6">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-brand-primary">Saldo</h3>
                            <p class="mt-1 text-sm text-brand-muted">Estado financiero del paciente.</p>
                        </div>

                        @if ($saldoPendiente > 0)
                            <button
                                x-data
                                type="button"
                                class="inline-flex items-center justify-center gap-2 rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700"
                                @click.prevent="$dispatch('open-modal', 'registrar-abono')"
                            >
                                <x-lucide-circle-dollar-sign class="h-4 w-4" />
                                Registrar abono
                            </button>
                        @endif
                    </div>

                    <div class="mt-5 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-lg border border-brand-border bg-brand-soft p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Presupuesto total</p>
                            <p class="mt-2 text-xl font-semibold text-brand-primary">
                                Q{{ number_format($presupuestoTotal, 2) }}
                            </p>
                        </div>
                        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Pagado</p>
                            <p class="mt-2 text-xl font-semibold text-emerald-700">
                                Q{{ number_format($totalPagado, 2) }}
                            </p>
                        </div>
                        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Pendiente</p>
                            <p class="mt-2 text-xl font-semibold text-amber-700">
                                Q{{ number_format($saldoPendiente, 2) }}
                            </p>
                        </div>
                    </div>

                    @if ($presupuestoTotal > 0)
                        <div class="mt-4">
                            <div class="flex items-center justify-between text-xs text-brand-muted">
                                <span>Avance de pago</span>
                                <span class="font-semibold">{{ $porcentajePagado }}%</span>
                            </div>
                            <div class="mt-1 h-2 w-full overflow-hidden rounded-full bg-brand-border">
                                <div
                                    class="h-full rounded-full bg-emerald-500 transition-all"
                                    style="width: {{ $porcentajePagado }}%;"
                                ></div>
                            </div>
                        </div>
                    @endif

                    @if ($saldoPendiente <= 0 && $presupuestoTotal > 0)
                        <div class="mt-4 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                            ✓ Este paciente no tiene saldo pendiente.
                        </div>
                    @elseif ($presupuestoTotal === 0.0)
                        <p class="mt-4 text-sm text-brand-muted">
                            Aún no hay presupuesto registrado en consultas de este paciente.
                        </p>
                    @endif
                </x-card>
            </div>

            {{-- Historial de pagos --}}
            <x-card class="overflow-hidden">
                <div class="flex items-center justify-between border-b border-brand-border px-6 py-4">
                    <div>
                        <h3 class="text-lg font-semibold text-brand-primary">Historial de pagos</h3>
                        <p class="mt-1 text-sm text-brand-muted">{{ $paciente->pagos->count() }} abono(s) registrado(s).</p>
                    </div>

                    @if ($saldoPendiente > 0)
                        <button
                            x-data
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-md border border-brand-border bg-white px-3 py-2 text-xs font-semibold text-brand-primary transition hover:bg-brand-soft"
                            @click.prevent="$dispatch('open-modal', 'registrar-abono')"
                        >
                            <x-lucide-plus class="h-4 w-4" />
                            Nuevo abono
                        </button>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-brand-border text-sm">
                        <thead class="bg-brand-soft">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-brand-muted">Fecha</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-brand-muted">Método</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-brand-muted">Consulta</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-brand-muted">Notas</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-brand-muted">Monto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border">
                            @forelse ($paciente->pagos as $pago)
                                <tr>
                                    <td class="px-4 py-3 text-brand-primary">
                                        {{ optional($pago->fecha_pago)->format('d/m/Y') ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-brand-primary capitalize">{{ $pago->metodo_pago }}</td>
                                    <td class="px-4 py-3 text-brand-primary">
                                        @if ($pago->consulta_id)
                                            <a
                                                href="{{ route('consultas.show', $pago->consulta_id) }}"
                                                class="text-sky-700 hover:underline"
                                            >
                                                Consulta #{{ $pago->consulta_id }}
                                            </a>
                                        @else
                                            <span class="text-brand-muted">Abono general</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-brand-primary [overflow-wrap:anywhere]">
                                        {{ $pago->notas ?: '—' }}
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-brand-primary">
                                        Q{{ number_format((float) $pago->monto, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center text-brand-muted">
                                        Este paciente todavía no tiene abonos registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($paciente->pagos->isNotEmpty())
                            <tfoot>
                                <tr class="bg-brand-soft">
                                    <th colspan="4" class="px-4 py-3 text-right text-sm font-semibold text-brand-primary">Total pagado</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-brand-primary">Q{{ number_format($totalPagado, 2) }}</th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </x-card>

            {{-- Últimas consultas --}}
            <x-card class="overflow-hidden">
                <div class="border-b border-brand-border px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-brand-primary">Últimas consultas</h3>
                        <x-link-button href="{{ route('pacientes.consultas.index', $paciente) }}">
                            Ver todas
                        </x-link-button>
                    </div>
                </div>
                <div class="divide-y divide-brand-border">
                    @forelse ($paciente->consultas as $consulta)
                        <div class="flex items-center justify-between gap-3 px-6 py-3">
                            <div>
                                <p class="text-sm font-medium text-brand-primary">
                                    {{ $consulta->fecha->format('d/m/Y') }} — {{ $consulta->motivo }}
                                </p>
                                @if ($consulta->presupuesto_aceptado_en)
                                    <p class="text-xs text-emerald-700">Presupuesto aceptado</p>
                                @endif
                            </div>
                            <x-link-button href="{{ route('consultas.show', $consulta) }}">
                                Ver
                            </x-link-button>
                        </div>
                    @empty
                        <div class="px-6 py-6 text-center text-sm text-brand-muted">
                            Sin consultas registradas.
                        </div>
                    @endforelse
                </div>
            </x-card>
        </div>
    </div>

    {{-- Modal --}}
    @include('pagos.partials.modal-abono', [
        'paciente' => $paciente,
        'saldoPendiente' => $saldoPendiente,
        'consultas' => $consultasParaAbono,
        'consultaPreasignada' => null,
    ])
</x-app-layout>
