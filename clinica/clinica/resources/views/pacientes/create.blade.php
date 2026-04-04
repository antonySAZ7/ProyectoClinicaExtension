<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Registrar paciente</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <p class="text-sm text-gray-600">Puedes vincular el expediente con una cuenta que ya tenga rol paciente.</p>
                </div>

                <form method="POST" action="{{ route('pacientes.store') }}" class="space-y-6 px-6 py-6">
                    @csrf

                    @if ($errors->any())
                        <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                            <ul class="space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div>
                        <label for="user_id" class="block text-sm font-medium text-gray-700">Usuario paciente</label>
                        <select
                            id="user_id"
                            name="user_id"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-400 focus:ring-gray-400"
                        >
                            <option value="">Sin vincular</option>
                            @foreach ($usuariosPaciente as $usuarioPaciente)
                                <option value="{{ $usuarioPaciente->id }}" @selected(old('user_id') == $usuarioPaciente->id)>
                                    {{ $usuarioPaciente->name }} ({{ $usuarioPaciente->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="nombre_completo" class="block text-sm font-medium text-gray-700">Nombre completo</label>
                            <input id="nombre_completo" type="text" name="nombre_completo" value="{{ old('nombre_completo') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-400 focus:ring-gray-400">
                        </div>

                        <div>
                            <label for="dpi" class="block text-sm font-medium text-gray-700">DPI</label>
                            <input id="dpi" type="text" name="dpi" value="{{ old('dpi') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-400 focus:ring-gray-400">
                        </div>

                        <div>
                            <label for="fecha_nacimiento" class="block text-sm font-medium text-gray-700">Fecha de nacimiento</label>
                            <input id="fecha_nacimiento" type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-400 focus:ring-gray-400">
                        </div>

                        <div>
                            <label for="telefono" class="block text-sm font-medium text-gray-700">Telefono</label>
                            <input id="telefono" type="text" name="telefono" value="{{ old('telefono') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-400 focus:ring-gray-400">
                        </div>

                        <div>
                            <label for="correo" class="block text-sm font-medium text-gray-700">Correo</label>
                            <input id="correo" type="email" name="correo" value="{{ old('correo') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-400 focus:ring-gray-400">
                        </div>

                        <div class="sm:col-span-2">
                            <label for="direccion" class="block text-sm font-medium text-gray-700">Direccion</label>
                            <input id="direccion" type="text" name="direccion" value="{{ old('direccion') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-400 focus:ring-gray-400">
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                        <a
                            href="{{ route('pacientes.index') }}"
                            class="inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                        >
                            Volver
                        </a>
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-800"
                        >
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
