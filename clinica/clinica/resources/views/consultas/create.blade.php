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

                <form method="POST" action="{{ route('pacientes.consultas.store', $paciente) }}" enctype="multipart/form-data" class="space-y-6">
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
                        <label for="diagnostico" class="mb-2 block text-sm font-medium text-brand-muted">Diagnostico</label>
                        <x-textarea 
                            id="diagnostico"
                            name="diagnostico" 
                            rows="5" 
                            placeholder="Describe el diagnostico principal de la consulta" 
                            required>{{ old('diagnostico') }}</x-textarea>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-base font-semibold uppercase tracking-wide text-brand-muted">Observaciones</h3>
                                <p class="mt-1 text-base text-brand-muted">Puedes agregar varias observaciones para la misma consulta.</p>
                            </div>

<button
                                type="button"
                                id="agregar-observacion"
                                class="btn btn-outline"
                            >
                                Agregar observacion
                            </button>
                        </div>

                        <div id="lista-observaciones" class="space-y-3">
                            @foreach ($observaciones as $observacion)
                                <x-textarea
                                    name="observaciones[]"
                                    rows="3"
                                    placeholder="Escribe una observacion relevante de la consulta"
                                >{{ $observacion }}</x-textarea>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label for="archivos" class="mb-2 block text-sm font-medium text-brand-muted">Archivos adjuntos</label>
                        <input
                            id="archivos"
                            type="file"
                            name="archivos[]"
                            multiple
                            accept=".pdf,image/png,image/jpeg,image/jpg,image/webp"
                            class="block w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                        >
                        <p class="mt-2 text-xs text-brand-muted">Formatos permitidos: PDF, JPG, JPEG, PNG y WEBP. Maximo 5 MB por archivo.</p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <x-button type="submit" class="btn btn-primary">
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
            const lista = document.getElementById('lista-observaciones');
            const boton = document.getElementById('agregar-observacion');

            boton.addEventListener('click', function () {
                const existing = lista.querySelector('textarea');
                const clone = existing.cloneNode(true);
                clone.value = '';
                lista.appendChild(clone);
                clone.focus();

                lista.appendChild(textarea);
                textarea.focus();
            });
        });
    </script>
</x-app-layout>
