<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold text-gray-900">Bienvenido al sistema</h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Desde aqui puedes acceder rapidamente al modulo de pacientes.
                    </p>

                    <div class="mt-6">
                        <a
                            href="{{ route('pacientes.index') }}"
                            class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            Ir a pacientes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
