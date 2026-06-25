<div class="p-6">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-brand-primary">{{ $title }}</h2>
            <p class="mt-1 text-sm text-brand-muted">Registre lo realizado o planificado en esta etapa.</p>
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

        <div class="grid gap-4 sm:grid-cols-[1fr_120px]">
            <div>
                <label class="block text-sm font-medium text-brand-muted" for="fase-fecha-{{ $fase?->id ?? 'nueva' }}">
                    Fecha <span class="text-red-500">*</span>
                </label>
                <input
                    id="fase-fecha-{{ $fase?->id ?? 'nueva' }}"
                    name="fecha"
                    type="date"
                    required
                    value="{{ old('fecha', $fase?->fecha?->format('Y-m-d') ?? today()->toDateString()) }}"
                    class="mt-1 block w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-brand-muted" for="fase-orden-{{ $fase?->id ?? 'nueva' }}">
                    Orden
                </label>
                <input
                    id="fase-orden-{{ $fase?->id ?? 'nueva' }}"
                    name="orden"
                    type="number"
                    min="0"
                    max="999"
                    value="{{ old('orden', $fase?->orden) }}"
                    class="mt-1 block w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                >
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-brand-muted" for="fase-consulta-{{ $fase?->id ?? 'nueva' }}">
                Consulta vinculada
            </label>
            <select
                id="fase-consulta-{{ $fase?->id ?? 'nueva' }}"
                name="consulta_id"
                class="mt-1 block w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
            >
                <option value="">Sin consulta vinculada</option>
                @foreach ($consultasParaTratamiento as $consulta)
                    <option value="{{ $consulta['id'] }}" @selected((int) old('consulta_id', $fase?->consulta_id) === $consulta['id'])>
                        {{ $consulta['label'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-brand-muted" for="fase-descripcion-{{ $fase?->id ?? 'nueva' }}">
                Descripción de la fase <span class="text-red-500">*</span>
            </label>
            <textarea
                id="fase-descripcion-{{ $fase?->id ?? 'nueva' }}"
                name="descripcion"
                rows="4"
                maxlength="4000"
                required
                class="mt-1 block w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                placeholder="Ej. Apertura cameral, conductometría, medicación intraconducto."
            >{{ old('descripcion', $fase?->descripcion) }}</textarea>
        </div>

        <input type="hidden" name="completada" value="0">
        <label class="inline-flex items-center gap-3 text-sm text-brand-primary">
            <input
                type="checkbox"
                name="completada"
                value="1"
                @checked(old('completada', $fase?->completada ?? true))
                class="rounded border-brand-border text-brand-primary shadow-sm focus:ring-brand-primary"
            >
            Fase completada
        </label>

        <div class="flex flex-col gap-2 border-t border-brand-border pt-4 sm:flex-row sm:justify-end">
            <button
                type="button"
                class="inline-flex items-center justify-center rounded-md border border-brand-border bg-white px-4 py-2 text-sm font-semibold text-brand-primary transition hover:bg-brand-soft"
                @click="$dispatch('close')"
            >
                Cancelar
            </button>
            <button type="submit" class="inline-flex items-center justify-center rounded-md bg-brand-primary px-4 py-2 text-sm font-semibold text-brand-contrast transition hover:bg-brand-primary-strong">
                Guardar fase
            </button>
        </div>
    </form>
</div>
