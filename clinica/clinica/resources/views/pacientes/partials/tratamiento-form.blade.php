@php
    use App\Models\Tratamiento;

    $selectedEstado = old('estado', $tratamiento?->estado ?? Tratamiento::ESTADO_EN_PROGRESO);
@endphp

<div class="p-6">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-brand-primary">{{ $title }}</h2>
            <p class="mt-1 text-sm text-brand-muted">Plan clínico general y pieza asociada, si aplica.</p>
        </div>

        <button type="button" class="text-brand-muted hover:text-brand-primary" @click="$dispatch('close')">
            <x-lucide-x class="h-5 w-5" />
        </button>
    </div>

    <form method="POST" action="{{ $action }}" class="mt-5 space-y-4">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

        <div>
            <label class="block text-sm font-medium text-brand-muted" for="tratamiento-nombre-{{ $tratamiento?->id ?? 'nuevo' }}">
                Nombre del tratamiento <span class="text-red-500">*</span>
            </label>
            <input
                id="tratamiento-nombre-{{ $tratamiento?->id ?? 'nuevo' }}"
                name="nombre"
                type="text"
                maxlength="255"
                required
                value="{{ old('nombre', $tratamiento?->nombre) }}"
                placeholder="Ej. Endodoncia pieza 26"
                class="mt-1 block w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
            >
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label class="block text-sm font-medium text-brand-muted" for="tratamiento-fecha-{{ $tratamiento?->id ?? 'nuevo' }}">
                    Fecha de inicio <span class="text-red-500">*</span>
                </label>
                <input
                    id="tratamiento-fecha-{{ $tratamiento?->id ?? 'nuevo' }}"
                    name="fecha_inicio"
                    type="date"
                    required
                    value="{{ old('fecha_inicio', $tratamiento?->fecha_inicio?->format('Y-m-d') ?? today()->toDateString()) }}"
                    class="mt-1 block w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-brand-muted" for="tratamiento-pieza-{{ $tratamiento?->id ?? 'nuevo' }}">
                    Pieza
                </label>
                <select
                    id="tratamiento-pieza-{{ $tratamiento?->id ?? 'nuevo' }}"
                    name="pieza_id"
                    class="mt-1 block w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                >
                    <option value="">Sin pieza</option>
                    @foreach ($piezasDentales as $pieza)
                        <option value="{{ $pieza->id }}" @selected((int) old('pieza_id', $tratamiento?->pieza_id) === $pieza->id)>
                            {{ $pieza->numero }} - {{ $pieza->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-brand-muted" for="tratamiento-estado-{{ $tratamiento?->id ?? 'nuevo' }}">
                    Estado
                </label>
                <select
                    id="tratamiento-estado-{{ $tratamiento?->id ?? 'nuevo' }}"
                    name="estado"
                    class="mt-1 block w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                >
                    @foreach ($estadoMeta as $estado => $meta)
                        <option value="{{ $estado }}" @selected($selectedEstado === $estado)>{{ $meta['label'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-brand-muted" for="tratamiento-descripcion-{{ $tratamiento?->id ?? 'nuevo' }}">
                Descripción general
            </label>
            <textarea
                id="tratamiento-descripcion-{{ $tratamiento?->id ?? 'nuevo' }}"
                name="descripcion"
                rows="3"
                maxlength="4000"
                class="mt-1 block w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                placeholder="Notas del plan, objetivos o consideraciones clínicas."
            >{{ old('descripcion', $tratamiento?->descripcion) }}</textarea>
        </div>

        <div class="flex flex-col gap-2 border-t border-brand-border pt-4 sm:flex-row sm:justify-end">
            <button
                type="button"
                class="inline-flex items-center justify-center rounded-md border border-brand-border bg-white px-4 py-2 text-sm font-semibold text-brand-primary transition hover:bg-brand-soft"
                @click="$dispatch('close')"
            >
                Cancelar
            </button>
            <button type="submit" class="inline-flex items-center justify-center rounded-md bg-brand-primary px-4 py-2 text-sm font-semibold text-brand-contrast transition hover:bg-brand-primary-strong">
                Guardar tratamiento
            </button>
        </div>
    </form>
</div>
