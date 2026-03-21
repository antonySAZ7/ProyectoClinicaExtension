@extends('layouts.app')

@section('content')
    <div class="py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="mb-6">
                    <h1 class="text-2xl font-semibold text-gray-900">Editar cita</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Actualiza la informacion de la cita seleccionada.
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

                <form method="POST" action="{{ route('citas.update', $cita) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label for="paciente_id" class="mb-2 block text-sm font-medium text-gray-700">Paciente</label>
                            <select
                                id="paciente_id"
                                name="paciente_id"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900"
                                required
                            >
                                <option value="">Selecciona un paciente</option>
                                @foreach ($pacientes as $paciente)
                                    <option
                                        value="{{ $paciente->id }}"
                                        @selected(old('paciente_id', $cita->paciente_id) == $paciente->id)
                                    >
                                        {{ $paciente->nombre_completo }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="estado" class="mb-2 block text-sm font-medium text-gray-700">Estado</label>
                            <select
                                id="estado"
                                name="estado"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900"
                                required
                            >
                                @foreach ($estados as $estado)
                                    <option value="{{ $estado }}" @selected(old('estado', $cita->estado) === $estado)>
                                        {{ ucfirst($estado) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="fecha" class="mb-2 block text-sm font-medium text-gray-700">Fecha</label>
                            <input
                                id="fecha"
                                type="date"
                                name="fecha"
                                value="{{ old('fecha', $cita->fecha?->format('Y-m-d')) }}"
                                min="{{ now()->toDateString() }}"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900"
                                required
                            >
                        </div>

                        <div>
                            <label for="hora" class="mb-2 block text-sm font-medium text-gray-700">Hora</label>
                            <input
                                id="hora"
                                type="time"
                                name="hora"
                                value="{{ old('hora', \Illuminate\Support\Str::of((string) $cita->hora)->substr(0, 5)) }}"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900"
                                required
                            >
                        </div>
                    </div>

                    <div>
                        <label for="motivo" class="mb-2 block text-sm font-medium text-gray-700">Motivo</label>
                        <input
                            id="motivo"
                            type="text"
                            name="motivo"
                            value="{{ old('motivo', $cita->motivo) }}"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900"
                            required
                        >
                    </div>

                    <div>
                        <label for="observaciones" class="mb-2 block text-sm font-medium text-gray-700">Observaciones</label>
                        <textarea
                            id="observaciones"
                            name="observaciones"
                            rows="4"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900"
                        >{{ old('observaciones', $cita->observaciones) }}</textarea>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-800"
                        >
                            Actualizar cita
                        </button>

                        <a
                            href="{{ route('citas.index') }}"
                            class="inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                        >
                            Volver
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
