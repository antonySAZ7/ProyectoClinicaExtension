<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Panel
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            <section class="space-y-6">
                <header>
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Panel del día</h3>
                    <p class="mt-1 text-xs text-gray-400">
                        Datos en tiempo real - {{ \Illuminate\Support\Carbon::parse($operativo['ahora'])->format('d/m/Y H:i') }}
                    </p>
                </header>

                <div class="grid gap-6 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">
                    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900">Citas de hoy</h4>
                                <p class="mt-1 text-xs text-gray-500">{{ $operativo['citas_hoy']->count() }} cita(s) programadas.</p>
                            </div>
                            <a href="{{ route('citas.calendario') }}" class="text-xs font-semibold text-indigo-700 hover:underline">
                                Ver calendario
                            </a>
                        </div>

                        <div class="divide-y divide-gray-100">
                            @forelse ($operativo['citas_hoy'] as $cita)
                                @php
                                    $hora = \Illuminate\Support\Str::of((string) $cita->hora)->substr(0, 5);
                                    $badgeEstilo = match ($cita->estado) {
                                        'confirmada' => 'bg-emerald-100 text-emerald-700',
                                        'atendida' => 'bg-sky-100 text-sky-700',
                                        'cancelada' => 'bg-rose-100 text-rose-700',
                                        'no_show' => 'bg-gray-200 text-gray-600',
                                        default => 'bg-amber-100 text-amber-700',
                                    };
                                @endphp
                                <div class="flex flex-wrap items-center gap-3 px-5 py-3">
                                    <span class="w-14 shrink-0 font-semibold text-gray-900">{{ $hora }}</span>
                                    <div class="min-w-0 flex-1">
                                        <a href="{{ route('pacientes.show', $cita->paciente_id) }}" class="block truncate text-sm font-medium text-gray-900 hover:underline">
                                            {{ $cita->paciente?->nombre_completo ?? 'Paciente no disponible' }}
                                        </a>
                                        <p class="truncate text-xs text-gray-500">{{ $cita->motivo }}</p>
                                    </div>
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $badgeEstilo }}">
                                        {{ ucfirst($cita->estado) }}
                                    </span>

                                    @if ($cita->estado === 'confirmada')
                                        <a
                                            href="{{ route('pacientes.consultas.create', ['paciente' => $cita->paciente_id, 'cita_id' => $cita->id]) }}"
                                            class="inline-flex items-center gap-1 rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-700"
                                        >
                                            <x-lucide-play class="h-3.5 w-3.5" />
                                            Iniciar consulta
                                        </a>
                                    @elseif ($cita->estado === 'atendida' && $cita->consulta)
                                        <a
                                            href="{{ route('consultas.show', $cita->consulta) }}"
                                            class="inline-flex items-center gap-1 rounded-md border border-sky-300 bg-sky-50 px-3 py-1.5 text-xs font-semibold text-sky-700 transition hover:bg-sky-100"
                                        >
                                            Ver consulta
                                        </a>
                                    @endif
                                </div>
                            @empty
                                <div class="px-5 py-8 text-center text-sm text-gray-500">
                                    No hay citas programadas para hoy.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                        <div class="border-b border-gray-100 px-5 py-4">
                            <h4 class="text-lg font-semibold text-gray-900">Próxima cita</h4>
                            <p class="mt-1 text-xs text-gray-500">Tu siguiente confirmada o pendiente.</p>
                        </div>

                        <div class="px-5 py-5">
                            @if ($operativo['proxima_cita'])
                                @php
                                    $px = $operativo['proxima_cita'];
                                    $inicio = $px->startsAt();
                                @endphp
                                <p class="text-2xl font-semibold text-gray-900">{{ $inicio?->format('H:i') }}</p>
                                <p class="text-xs uppercase tracking-wide text-gray-500">
                                    {{ $inicio?->isToday() ? 'Hoy' : $inicio?->translatedFormat('l d/m/Y') }}
                                </p>

                                @if ($inicio)
                                    @php $diffMin = now()->diffInMinutes($inicio, false); @endphp
                                    <p class="mt-2 text-sm font-medium text-indigo-700">
                                        @if ($diffMin <= 0)
                                            En curso ahora
                                        @elseif ($diffMin < 60)
                                            En {{ (int) $diffMin }} minuto(s)
                                        @elseif ($diffMin < 60 * 24)
                                            En {{ (int) floor($diffMin / 60) }} hora(s)
                                        @else
                                            {{ $inicio->diffForHumans(['parts' => 2]) }}
                                        @endif
                                    </p>
                                @endif

                                <div class="mt-4 border-t border-gray-100 pt-4">
                                    <a href="{{ route('pacientes.show', $px->paciente_id) }}" class="text-sm font-medium text-gray-900 hover:underline">
                                        {{ $px->paciente?->nombre_completo ?? '-' }}
                                    </a>
                                    <p class="mt-1 text-xs text-gray-500">{{ $px->motivo }}</p>
                                </div>

                                @if ($px->estado === 'confirmada')
                                    <a
                                        href="{{ route('pacientes.consultas.create', ['paciente' => $px->paciente_id, 'cita_id' => $px->id]) }}"
                                        class="mt-4 inline-flex items-center justify-center gap-1 rounded-md bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700"
                                    >
                                        <x-lucide-play class="h-3.5 w-3.5" />
                                        Iniciar consulta
                                    </a>
                                @endif
                            @else
                                <p class="py-6 text-center text-sm text-gray-500">
                                    No hay citas próximas confirmadas.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="rounded-xl border border-amber-200 bg-amber-50 shadow-sm">
                        <div class="border-b border-amber-100 px-5 py-4">
                            <div class="flex items-center gap-2">
                                <x-lucide-clock class="h-4 w-4 text-amber-700" />
                                <h4 class="text-base font-semibold text-amber-900">Consultas con presupuesto sin cerrar</h4>
                            </div>
                            <p class="mt-1 text-xs text-amber-700">
                                Consultas con ítems de presupuesto pero aún no aceptado. Ciérrelas para empezar a cobrar.
                            </p>
                        </div>

                        <div class="divide-y divide-amber-100">
                            @forelse ($operativo['consultas_sin_cerrar'] as $consulta)
                                <a href="{{ route('consultas.show', $consulta) }}" class="flex items-center justify-between gap-3 px-5 py-3 transition hover:bg-amber-100/50">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-amber-900">{{ $consulta->paciente?->nombre_completo ?? '-' }}</p>
                                        <p class="text-xs text-amber-700">{{ $consulta->fecha?->format('d/m/Y') }} - {{ $consulta->motivo }}</p>
                                    </div>
                                    <x-lucide-arrow-right class="h-4 w-4 shrink-0 text-amber-700" />
                                </a>
                            @empty
                                <p class="px-5 py-6 text-center text-sm text-amber-700">
                                    Todas las consultas con presupuesto están cerradas.
                                </p>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-xl border border-rose-200 bg-rose-50 shadow-sm">
                        <div class="border-b border-rose-100 px-5 py-4">
                            <div class="flex items-center gap-2">
                                <x-lucide-circle-dollar-sign class="h-4 w-4 text-rose-700" />
                                <h4 class="text-base font-semibold text-rose-900">Top saldos por cobrar</h4>
                            </div>
                            <p class="mt-1 text-xs text-rose-700">
                                Los 5 pacientes con mayor saldo pendiente. Click para registrar abono.
                            </p>
                        </div>

                        <div class="divide-y divide-rose-100">
                            @forelse ($operativo['top_saldos'] as $fila)
                                <a href="{{ route('pacientes.show', $fila['id']) }}" class="flex items-center justify-between gap-3 px-5 py-3 transition hover:bg-rose-100/50">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-rose-900">{{ $fila['nombre'] }}</p>
                                        <p class="text-right text-xs text-rose-700 sm:text-left">
                                            Pagado Q{{ number_format($fila['pagado'], 2) }} de Q{{ number_format($fila['presupuesto'], 2) }}
                                        </p>
                                    </div>
                                    <span class="shrink-0 text-right text-base font-semibold text-rose-700">
                                        Q{{ number_format($fila['saldo'], 2) }}
                                    </span>
                                </a>
                            @empty
                                <p class="px-5 py-6 text-center text-sm text-rose-700">
                                    Sin saldos pendientes.
                                </p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
