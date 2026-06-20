@php
    /** @var \App\Models\RecordatorioSeguimiento|null $recordatorio */
    $recordatorio = $recordatorio ?? null;
    $activo = old('activar_recordatorio_seguimiento', $recordatorio?->activo ? '1' : null);
    $modo = old('recordatorio_modo', $recordatorio?->modo ?? 'intervalo');
    $titulo = old('recordatorio_titulo', $recordatorio?->titulo);
    $intervalo = old('recordatorio_intervalo_meses', $recordatorio?->intervalo_meses ?? 6);
    $fechaObjetivo = old('recordatorio_fecha_objetivo', $recordatorio?->fecha_objetivo?->format('Y-m-d'));
    $diasAntes = old('recordatorio_dias_antes', $recordatorio?->dias_antes ?? [7, 1, 0]);
    $mensaje = old('recordatorio_mensaje', $recordatorio?->mensaje);
@endphp

<div class="rounded-xl border border-sky-100 bg-sky-50/60 p-5">
    <label class="flex items-start gap-3">
        <input
            id="activar_recordatorio_seguimiento"
            type="checkbox"
            name="activar_recordatorio_seguimiento"
            value="1"
            class="mt-1 rounded border-gray-300 text-sky-700 focus:ring-sky-600"
            @checked($activo)
        >
        <span>
            <span class="block text-sm font-semibold text-gray-900">Activar recordatorio de seguimiento</span>
            <span class="mt-1 block text-sm text-gray-600">
                Para avisarle al paciente que ya le corresponde agendar una nueva cita preventiva.
            </span>
        </span>
    </label>

    <div id="recordatorio_seguimiento_fields" class="mt-5 grid gap-5 md:grid-cols-2">
        <div>
            <label for="recordatorio_modo" class="mb-2 block text-sm font-medium text-gray-700">Tipo de recordatorio</label>
            <select
                id="recordatorio_modo"
                name="recordatorio_modo"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900"
            >
                <option value="intervalo" @selected($modo === 'intervalo')>Cada cierto tiempo</option>
                <option value="personalizado" @selected($modo === 'personalizado')>Fecha personalizada</option>
            </select>
        </div>

        <div class="md:col-span-2">
            <label for="recordatorio_titulo" class="mb-2 block text-sm font-medium text-gray-700">Titulo del recordatorio</label>
            <input
                id="recordatorio_titulo"
                type="text"
                name="recordatorio_titulo"
                value="{{ $titulo }}"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900"
                placeholder="Si se deja vacio, se usa el servicio de la cita, por ejemplo Limpieza"
            >
        </div>

        <div data-recordatorio-intervalo>
            <label for="recordatorio_intervalo_meses" class="mb-2 block text-sm font-medium text-gray-700">Recordar en meses</label>
            <input
                id="recordatorio_intervalo_meses"
                type="number"
                name="recordatorio_intervalo_meses"
                value="{{ $intervalo }}"
                min="1"
                max="60"
                step="1"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900"
                placeholder="Ej. 3, 5, 6, 12"
            >
            <p class="mt-1 text-xs text-gray-500">Ejemplo: 5 significa cinco meses despues de esta cita.</p>
        </div>

        <div data-recordatorio-personalizado>
            <label for="recordatorio_fecha_objetivo" class="mb-2 block text-sm font-medium text-gray-700">Fecha objetivo</label>
            <input
                id="recordatorio_fecha_objetivo"
                type="date"
                name="recordatorio_fecha_objetivo"
                value="{{ $fechaObjetivo }}"
                min="{{ now()->toDateString() }}"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900"
            >
        </div>

        <div>
            <p class="mb-2 block text-sm font-medium text-gray-700">Enviar aviso</p>
            <div class="flex flex-wrap gap-4 rounded-md border border-gray-200 bg-white px-3 py-2">
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input
                        type="checkbox"
                        name="recordatorio_dias_antes[]"
                        value="7"
                        class="rounded border-gray-300 text-sky-700 focus:ring-sky-600"
                        @checked(in_array(7, array_map('intval', $diasAntes), true))
                    >
                    Una semana antes
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input
                        type="checkbox"
                        name="recordatorio_dias_antes[]"
                        value="1"
                        class="rounded border-gray-300 text-sky-700 focus:ring-sky-600"
                        @checked(in_array(1, array_map('intval', $diasAntes), true))
                    >
                    Un día antes
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input
                        type="checkbox"
                        name="recordatorio_dias_antes[]"
                        value="0"
                        class="rounded border-gray-300 text-sky-700 focus:ring-sky-600"
                        @checked(in_array(0, array_map('intval', $diasAntes), true))
                    >
                    El mismo día
                </label>
            </div>
        </div>

        <div class="md:col-span-2">
            <label for="recordatorio_mensaje" class="mb-2 block text-sm font-medium text-gray-700">Mensaje personalizado</label>
            <textarea
                id="recordatorio_mensaje"
                name="recordatorio_mensaje"
                rows="3"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900"
                placeholder="Ej. Ya te corresponde tu limpieza de seguimiento. Escríbenos para agendar."
            >{{ $mensaje }}</textarea>
        </div>
    </div>
</div>

@once
    <script>
        (function () {
            const active = document.getElementById('activar_recordatorio_seguimiento');
            const wrapper = document.getElementById('recordatorio_seguimiento_fields');
            const mode = document.getElementById('recordatorio_modo');
            const intervalBlock = document.querySelector('[data-recordatorio-intervalo]');
            const customBlock = document.querySelector('[data-recordatorio-personalizado]');
            if (!active || !wrapper || !mode || !intervalBlock || !customBlock) return;

            function sync() {
                wrapper.classList.toggle('hidden', !active.checked);
                intervalBlock.classList.toggle('hidden', mode.value !== 'intervalo');
                customBlock.classList.toggle('hidden', mode.value !== 'personalizado');
            }

            active.addEventListener('change', sync);
            mode.addEventListener('change', sync);
            sync();
        })();
    </script>
@endonce
