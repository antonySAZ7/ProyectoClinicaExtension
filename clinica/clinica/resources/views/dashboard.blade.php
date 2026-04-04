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
                        Desde aqui puedes acceder rapidamente a los modulos administrativos principales.
                    </p>

                    <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                        <a
                            href="{{ route('pacientes.index') }}"
                            class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            Ir a pacientes
                        </a>

                        <a
                            href="{{ route('citas.index') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            Ir a citas
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
