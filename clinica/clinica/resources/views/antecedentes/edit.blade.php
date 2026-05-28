@php
    use App\Models\AntecedenteClinico;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="font-semibold text-2xl text-brand-primary leading-tight">Ficha clinica</h2>
            <p class="text-base text-brand-muted">
                {{ $paciente->nombre_completo }}
                @if ($paciente->edad !== null)
                    &middot; {{ $paciente->edad }} anios
                @endif
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
            @endif

            @if ($errors->any())
                <x-alert type="error" class="mb-6">
                    <ul class="space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-alert>
            @endif

            <form
                method="POST"
                action="{{ route('pacientes.antecedentes.update', $paciente) }}"
                class="space-y-6"
                x-data="{
                    en_tratamiento_medico: {{ old('en_tratamiento_medico', $antecedente->en_tratamiento_medico) ? 'true' : 'false' }},
                    toma_medicamento: {{ old('toma_medicamento', $antecedente->toma_medicamento) ? 'true' : 'false' }},
                    alergico_medicamento: {{ old('alergico_medicamento', $antecedente->alergico_medicamento) ? 'true' : 'false' }},
                }"
            >
                @csrf
                @method('PUT')

                {{-- Anamnesis --}}
                <x-card class="p-6">
                    <h3 class="text-lg font-semibold text-brand-primary">Anamnesis</h3>
                    <p class="mt-1 text-sm text-brand-muted">Antecedentes de visitas y tratamiento actual.</p>

                    <div class="mt-4 grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="ultima_visita_dental" class="block text-sm font-medium text-brand-muted">
                                {{ AntecedenteClinico::CAMPOS_ANAMNESIS_TEXTO['ultima_visita_dental'] }}
                            </label>
                            <input
                                id="ultima_visita_dental"
                                type="date"
                                name="ultima_visita_dental"
                                value="{{ old('ultima_visita_dental', optional($antecedente->ultima_visita_dental)->format('Y-m-d')) }}"
                                class="mt-1 block w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                            >
                        </div>

                        <div>
                            <label for="ultima_visita_motivo" class="block text-sm font-medium text-brand-muted">
                                {{ AntecedenteClinico::CAMPOS_ANAMNESIS_TEXTO['ultima_visita_motivo'] }}
                            </label>
                            <input
                                id="ultima_visita_motivo"
                                type="text"
                                name="ultima_visita_motivo"
                                value="{{ old('ultima_visita_motivo', $antecedente->ultima_visita_motivo) }}"
                                class="mt-1 block w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                            >
                        </div>
                    </div>

                    <div class="mt-5 space-y-4">
                        {{-- presento_complicacion (sin campo condicional) --}}
                        <label class="inline-flex items-center gap-3 text-sm text-brand-primary">
                            <input type="checkbox" name="presento_complicacion" value="1"
                                @checked(old('presento_complicacion', $antecedente->presento_complicacion))
                                class="rounded border-brand-border text-brand-primary shadow-sm focus:ring-brand-primary">
                            {{ AntecedenteClinico::CAMPOS_ANAMNESIS_BOOL['presento_complicacion'] }}
                        </label>

                        {{-- en_tratamiento_medico -> tratamiento_enfermedad --}}
                        <div>
                            <label class="inline-flex items-center gap-3 text-sm text-brand-primary">
                                <input type="checkbox" name="en_tratamiento_medico" value="1"
                                    x-model="en_tratamiento_medico"
                                    class="rounded border-brand-border text-brand-primary shadow-sm focus:ring-brand-primary">
                                {{ AntecedenteClinico::CAMPOS_ANAMNESIS_BOOL['en_tratamiento_medico'] }}
                            </label>
                            <div x-show="en_tratamiento_medico" x-cloak class="mt-2">
                                <input type="text" name="tratamiento_enfermedad"
                                    value="{{ old('tratamiento_enfermedad', $antecedente->tratamiento_enfermedad) }}"
                                    placeholder="{{ AntecedenteClinico::CAMPOS_ANAMNESIS_TEXTO['tratamiento_enfermedad'] }}"
                                    class="block w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary">
                            </div>
                        </div>

                        {{-- toma_medicamento -> cual_medicamento --}}
                        <div>
                            <label class="inline-flex items-center gap-3 text-sm text-brand-primary">
                                <input type="checkbox" name="toma_medicamento" value="1"
                                    x-model="toma_medicamento"
                                    class="rounded border-brand-border text-brand-primary shadow-sm focus:ring-brand-primary">
                                {{ AntecedenteClinico::CAMPOS_ANAMNESIS_BOOL['toma_medicamento'] }}
                            </label>
                            <div x-show="toma_medicamento" x-cloak class="mt-2">
                                <input type="text" name="cual_medicamento"
                                    value="{{ old('cual_medicamento', $antecedente->cual_medicamento) }}"
                                    placeholder="{{ AntecedenteClinico::CAMPOS_ANAMNESIS_TEXTO['cual_medicamento'] }}"
                                    class="block w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary">
                            </div>
                        </div>

                        {{-- alergico_medicamento -> cuales_medicamentos --}}
                        <div>
                            <label class="inline-flex items-center gap-3 text-sm text-brand-primary">
                                <input type="checkbox" name="alergico_medicamento" value="1"
                                    x-model="alergico_medicamento"
                                    class="rounded border-brand-border text-brand-primary shadow-sm focus:ring-brand-primary">
                                {{ AntecedenteClinico::CAMPOS_ANAMNESIS_BOOL['alergico_medicamento'] }}
                            </label>
                            <div x-show="alergico_medicamento" x-cloak class="mt-2">
                                <input type="text" name="cuales_medicamentos"
                                    value="{{ old('cuales_medicamentos', $antecedente->cuales_medicamentos) }}"
                                    placeholder="{{ AntecedenteClinico::CAMPOS_ANAMNESIS_TEXTO['cuales_medicamentos'] }}"
                                    class="block w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary">
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="otro_antecedente" class="block text-sm font-medium text-brand-muted">
                                {{ AntecedenteClinico::CAMPOS_ANAMNESIS_TEXTO['otro_antecedente'] }}
                            </label>
                            <input id="otro_antecedente" type="text" name="otro_antecedente"
                                value="{{ old('otro_antecedente', $antecedente->otro_antecedente) }}"
                                class="mt-1 block w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary">
                        </div>
                    </div>

                    <div class="mt-5">
                        <label for="descripcion_enfermedades" class="block text-sm font-medium text-brand-muted">
                            {{ AntecedenteClinico::CAMPOS_ANAMNESIS_TEXTO['descripcion_enfermedades'] }}
                        </label>
                        <textarea id="descripcion_enfermedades" name="descripcion_enfermedades" rows="3" maxlength="4000"
                            class="mt-1 block w-full rounded-md border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary">{{ old('descripcion_enfermedades', $antecedente->descripcion_enfermedades) }}</textarea>
                    </div>
                </x-card>

                {{-- Antecedentes medicos --}}
                <x-card class="p-6">
                    <h3 class="text-lg font-semibold text-brand-primary">Antecedentes medicos</h3>
                    <p class="mt-1 text-sm text-brand-muted">Marca las condiciones que el paciente presenta.</p>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach (AntecedenteClinico::CAMPOS_MEDICOS as $campo => $etiqueta)
                            <label class="inline-flex items-center gap-3 rounded-md border border-brand-border px-3 py-2 text-sm text-brand-primary">
                                <input type="checkbox" name="{{ $campo }}" value="1"
                                    @checked(old($campo, $antecedente->$campo))
                                    class="rounded border-brand-border text-brand-primary shadow-sm focus:ring-brand-primary">
                                {{ $etiqueta }}
                            </label>
                        @endforeach
                    </div>
                </x-card>

                {{-- Antecedentes odontologicos --}}
                <x-card class="p-6">
                    <h3 class="text-lg font-semibold text-brand-primary">Antecedentes odontologicos</h3>
                    <p class="mt-1 text-sm text-brand-muted">Marca los antecedentes odontologicos del paciente.</p>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach (AntecedenteClinico::CAMPOS_ODONTOLOGICOS as $campo => $etiqueta)
                            <label class="inline-flex items-center gap-3 rounded-md border border-brand-border px-3 py-2 text-sm text-brand-primary">
                                <input type="checkbox" name="{{ $campo }}" value="1"
                                    @checked(old($campo, $antecedente->$campo))
                                    class="rounded border-brand-border text-brand-primary shadow-sm focus:ring-brand-primary">
                                {{ $etiqueta }}
                            </label>
                        @endforeach
                    </div>
                </x-card>

                <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                    <x-link-button href="{{ route('pacientes.index') }}">Volver</x-link-button>
                    <x-button type="submit" class="btn btn-primary">Guardar ficha clinica</x-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
