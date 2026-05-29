<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-brand-primary leading-tight">Editar consulta</h2>
                <p class="mt-1 text-base text-brand-muted">
                    {{ $paciente->nombre_completo }} — {{ $consulta->fecha->format('d/m/Y') }}
                </p>
            </div>

            <x-link-button href="{{ route('consultas.show', $consulta) }}">
                Cancelar
            </x-link-button>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <x-card>
                @if ($errors->any())
                    <x-alert type="error">
                        <ul class="space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </x-alert>
                @endif

                <form method="POST" action="{{ route('consultas.update', $consulta) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <x-form-input
                                label="Fecha"
                                name="fecha"
                                type="date"
                                value="{{ old('fecha', $consulta->fecha->toDateString()) }}"
                                required />
                        </div>

                        <div>
                            <x-form-input
                                label="Motivo"
                                name="motivo"
                                type="text"
                                value="{{ old('motivo', $consulta->motivo) }}"
                                required />
                        </div>
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <label for="diagnostico" class="block text-sm font-medium text-brand-muted">Diagnóstico <span class="text-red-500" aria-hidden="true">*</span></label>
                            <span id="contador-diagnostico" class="text-xs text-brand-muted">0/4000</span>
                        </div>
                        <x-textarea
                            id="diagnostico"
                            name="diagnostico"
                            rows="5"
                            maxlength="4000"
                            required>{{ old('diagnostico', $consulta->diagnostico) }}</x-textarea>
                    </div>

                    <div class="rounded-lg border border-brand-border p-4">
                        <p class="text-sm font-semibold text-brand-primary">Signos vitales</p>
                        <p class="mt-1 text-xs text-brand-muted">Opcionales.</p>

                        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <label for="peso" class="block text-sm font-medium text-brand-muted">Peso (kg)</label>
                                <input id="peso" type="number" step="0.01" min="0" name="peso"
                                    value="{{ old('peso', $consulta->peso) }}"
                                    class="mt-1 block w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary">
                            </div>
                            <div>
                                <label for="altura" class="block text-sm font-medium text-brand-muted">Altura (m)</label>
                                <input id="altura" type="number" step="0.01" min="0" name="altura"
                                    value="{{ old('altura', $consulta->altura) }}"
                                    class="mt-1 block w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary">
                            </div>
                            <div>
                                <label for="presion_arterial" class="block text-sm font-medium text-brand-muted">Presion arterial</label>
                                <input id="presion_arterial" type="text" name="presion_arterial"
                                    value="{{ old('presion_arterial', $consulta->presion_arterial) }}"
                                    placeholder="Ej. 120/80"
                                    class="mt-1 block w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary">
                            </div>
                            <div>
                                <label for="frecuencia_cardiaca" class="block text-sm font-medium text-brand-muted">Frecuencia cardiaca (lpm)</label>
                                <input id="frecuencia_cardiaca" type="number" min="0" name="frecuencia_cardiaca"
                                    value="{{ old('frecuencia_cardiaca', $consulta->frecuencia_cardiaca) }}"
                                    class="mt-1 block w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary">
                            </div>
                            <div>
                                <label for="frecuencia_respiratoria" class="block text-sm font-medium text-brand-muted">Frecuencia respiratoria (rpm)</label>
                                <input id="frecuencia_respiratoria" type="number" min="0" name="frecuencia_respiratoria"
                                    value="{{ old('frecuencia_respiratoria', $consulta->frecuencia_respiratoria) }}"
                                    class="mt-1 block w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary">
                            </div>
                            <div>
                                <label for="signos_otros" class="block text-sm font-medium text-brand-muted">Otros</label>
                                <input id="signos_otros" type="text" name="signos_otros"
                                    value="{{ old('signos_otros', $consulta->signos_otros) }}"
                                    class="mt-1 block w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary">
                            </div>
                        </div>
                    </div>

                    <p class="text-xs text-brand-muted"><span class="text-red-500">*</span> Campos obligatorios</p>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <x-button type="submit" class="btn btn-primary">
                            Guardar cambios
                        </x-button>

                        <x-link-button href="{{ route('consultas.show', $consulta) }}">
                            Cancelar
                        </x-link-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const textarea = document.getElementById('diagnostico');
            const contador = document.getElementById('contador-diagnostico');
            if (!textarea || !contador) return;

            const actualizar = function () {
                const len = textarea.value.length;
                contador.textContent = len + '/4000';
            };

            textarea.addEventListener('input', actualizar);
            actualizar();
        });
    </script>
</x-app-layout>
