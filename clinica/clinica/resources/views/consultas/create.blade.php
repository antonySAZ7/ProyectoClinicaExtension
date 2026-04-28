<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-brand-primary leading-tight">Registrar consulta medica</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <x-card>
                <div class="mb-6">
                    <p class="text-base font-medium uppercase tracking-wide text-brand-muted">Paciente</p>
                    <h1 class="mt-2 text-2xl font-semibold text-brand-primary">{{ $paciente->nombre_completo }}</h1>
                    <p class="mt-1 text-base text-brand-muted">
                        Completa la consulta, agrega observaciones y adjunta archivos si es necesario.
                    </p>
                </div>

                @if ($errors->any())
                    <x-alert type="error">
                        <ul class="space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </x-alert>
                @endif

                <form id="form-consulta" method="POST" action="{{ route('pacientes.consultas.store', $paciente) }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <x-form-input 
                                label="Fecha" 
                                name="fecha" 
                                type="date" 
                                value="{{ old('fecha', now()->toDateString()) }}" 
                                required />
                        </div>

                        <div>
                            <x-form-input 
                                label="Motivo" 
                                name="motivo" 
                                type="text" 
                                value="{{ old('motivo') }}" 
                                placeholder="Ej. control post operatorio, consulta general" 
                                required />
                        </div>
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <label for="diagnostico" class="block text-sm font-medium text-brand-muted">Diagnostico</label>
                            <span id="contador-diagnostico" class="text-xs text-brand-muted">0/4000</span>
                        </div>
                        <x-textarea
                            id="diagnostico"
                            name="diagnostico"
                            rows="5"
                            maxlength="4000"
                            placeholder="Describe el diagnostico principal de la consulta"
                            required>{{ old('diagnostico') }}</x-textarea>
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <label for="observaciones" class="block text-sm font-medium text-brand-muted">Observaciones</label>
                            <span id="contador-observaciones" class="text-xs text-brand-muted">0/4000</span>
                        </div>
                        <x-textarea
                            id="observaciones"
                            name="observaciones"
                            rows="5"
                            maxlength="4000"
                            placeholder="Escribe las observaciones relevantes de la consulta"
                        >{{ $observaciones }}</x-textarea>
                    </div>

                    <div>
                        <span class="mb-2 block text-sm font-medium text-brand-muted">Archivos adjuntos</span>

                        <input
                            id="archivos"
                            type="file"
                            name="archivos[]"
                            multiple
                            accept=".pdf,image/png,image/jpeg,image/jpg,image/webp"
                            class="sr-only"
                        >

                        <div class="flex flex-wrap items-center gap-3">
                            <label
                                for="archivos"
                                class="inline-flex cursor-pointer items-center rounded-md border border-brand-border px-4 py-2 text-brand-primary hover:bg-brand-soft"
                            >
                                Elegir archivos
                            </label>

                            <span id="resumen-archivos" class="text-sm text-brand-muted">
                                Ningun archivo seleccionado.
                            </span>
                        </div>

                        <p class="mt-2 text-xs text-brand-muted">Formatos permitidos: PDF, JPG, JPEG, PNG y WEBP. Maximo 5 MB por archivo.</p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <x-button id="submit-consulta" type="submit" class="btn btn-primary">
                            Guardar consulta
                        </x-button>

                        <x-link-button href="{{ route('pacientes.consultas.index', $paciente) }}">
                            Volver al historial
                        </x-link-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const inputArchivos = document.getElementById('archivos');
            const resumenArchivos = document.getElementById('resumen-archivos');
            const formConsulta = document.getElementById('form-consulta');
            const submitBtn = document.getElementById('submit-consulta');

            function bindContador(textareaId, contadorId, max) {
                const textarea = document.getElementById(textareaId);
                const contador = document.getElementById(contadorId);
                if (!textarea || !contador) return;

                const actualizar = function () {
                    const len = textarea.value.length;
                    contador.textContent = len + '/' + max;
                    contador.className = len >= max
                        ? 'text-xs text-brand-error'
                        : 'text-xs text-brand-muted';
                };

                textarea.addEventListener('input', actualizar);
                actualizar();
            }

            bindContador('diagnostico', 'contador-diagnostico', 4000);
            bindContador('observaciones', 'contador-observaciones', 4000);

            if (inputArchivos && resumenArchivos) {
                const textoVacio = 'Ningun archivo seleccionado.';
                const claseMuted = 'text-sm text-brand-muted';
                const claseActiva = 'text-sm text-brand-primary';

                inputArchivos.addEventListener('change', function () {
                    if (!inputArchivos.files || inputArchivos.files.length === 0) {
                        resumenArchivos.textContent = textoVacio;
                        resumenArchivos.className = claseMuted;
                        return;
                    }

                    const lineas = Array.from(inputArchivos.files).map(function (f) {
                        const kb = (f.size / 1024).toFixed(0);
                        return f.name + ' (' + kb + ' KB)';
                    });
                    resumenArchivos.textContent = lineas.join(', ');
                    resumenArchivos.className = claseActiva;
                });
            }

            if (formConsulta && submitBtn) {
                formConsulta.addEventListener('submit', function () {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Guardando...';
                });
            }
        });
    </script>
</x-app-layout>
