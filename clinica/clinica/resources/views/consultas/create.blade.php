<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Registrar consulta medica</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="mb-6">
                    <p class="text-sm font-medium uppercase tracking-wide text-sky-700">Paciente</p>
                    <h1 class="mt-2 text-2xl font-semibold text-gray-900">{{ $paciente->nombre_completo }}</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Completa la consulta, agrega observaciones y adjunta archivos si es necesario.
                    </p>
                </div>

                @if ($errors->any())
                    <div class="mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <ul class="space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('pacientes.consultas.store', $paciente) }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label for="fecha" class="mb-2 block text-sm font-medium text-gray-700">Fecha</label>
                            <input
                                id="fecha"
                                type="date"
                                name="fecha"
                                value="{{ old('fecha', now()->toDateString()) }}"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900"
                                required
                            >
                        </div>

                        <div>
                            <label for="motivo" class="mb-2 block text-sm font-medium text-gray-700">Motivo</label>
                            <input
                                id="motivo"
                                type="text"
                                name="motivo"
                                value="{{ old('motivo') }}"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900"
                                placeholder="Ej. control post operatorio, consulta general"
                                required
                            >
                        </div>
                    </div>

                    <div>
                        <label for="diagnostico" class="mb-2 block text-sm font-medium text-gray-700">Diagnostico</label>
                        <textarea
                            id="diagnostico"
                            name="diagnostico"
                            rows="5"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900"
                            placeholder="Describe el diagnostico principal de la consulta"
                            required
                        >{{ old('diagnostico') }}</textarea>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">Observaciones</h3>
                                <p class="mt-1 text-sm text-gray-500">Puedes agregar varias observaciones para la misma consulta.</p>
                            </div>

                            <button
                                type="button"
                                id="agregar-observacion"
                                class="inline-flex items-center justify-center rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                            >
                                Agregar observacion
                            </button>
                        </div>

                        <div id="lista-observaciones" class="space-y-3">
                            @foreach ($observaciones as $observacion)
                                <textarea
                                    name="observaciones[]"
                                    rows="3"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900"
                                    placeholder="Escribe una observacion relevante de la consulta"
                                >{{ $observacion }}</textarea>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label for="archivos" class="mb-2 block text-sm font-medium text-gray-700">Archivos adjuntos</label>
                        <input
                            id="archivos"
                            type="file"
                            name="archivos[]"
                            multiple
                            accept=".pdf,image/png,image/jpeg,image/jpg,image/webp"
                            class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900"
                        >
                        <p class="mt-2 text-xs text-gray-500">Formatos permitidos: PDF, JPG, JPEG, PNG y WEBP. Maximo 5 MB por archivo.</p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-800"
                        >
                            Guardar consulta
                        </button>

                        <a
                            href="{{ route('pacientes.consultas.index', $paciente) }}"
                            class="inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                        >
                            Volver al historial
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const lista = document.getElementById('lista-observaciones');
            const boton = document.getElementById('agregar-observacion');

            boton.addEventListener('click', function () {
                const textarea = document.createElement('textarea');
                textarea.name = 'observaciones[]';
                textarea.rows = 3;
                textarea.placeholder = 'Escribe una observacion relevante de la consulta';
                textarea.className = 'block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900';

                lista.appendChild(textarea);
                textarea.focus();
            });
        });
    </script>
</x-app-layout>
