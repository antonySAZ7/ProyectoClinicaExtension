<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="nombre_completo" value="Nombre completo" />
            <x-text-input id="nombre_completo" class="block mt-1 w-full" type="text" name="nombre_completo" :value="old('nombre_completo')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('nombre_completo')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="correo" value="Correo" />
            <x-text-input id="correo" class="block mt-1 w-full" type="email" name="correo" :value="old('correo')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('correo')" class="mt-2" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="dpi" value="DPI" />
                <x-text-input id="dpi" class="block mt-1 w-full" type="text" name="dpi" :value="old('dpi')" required maxlength="20" />
                <x-input-error :messages="$errors->get('dpi')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="fecha_nacimiento" value="Fecha de nacimiento" />
                <x-text-input id="fecha_nacimiento" class="block mt-1 w-full" type="date" name="fecha_nacimiento" :value="old('fecha_nacimiento')" required />
                <x-input-error :messages="$errors->get('fecha_nacimiento')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="telefono" value="Telefono" />
                <x-text-input id="telefono" class="block mt-1 w-full" type="text" name="telefono" :value="old('telefono')" required maxlength="20" autocomplete="tel" />
                <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="sexo" value="Sexo (opcional)" />
                <select id="sexo" name="sexo" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">Sin especificar</option>
                    <option value="femenino" @selected(old('sexo') === 'femenino')>Femenino</option>
                    <option value="masculino" @selected(old('sexo') === 'masculino')>Masculino</option>
                    <option value="otro" @selected(old('sexo') === 'otro')>Otro</option>
                </select>
                <x-input-error :messages="$errors->get('sexo')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="estado_civil" value="Estado civil (opcional)" />
                <x-text-input id="estado_civil" class="block mt-1 w-full" type="text" name="estado_civil" :value="old('estado_civil')" maxlength="100" />
                <x-input-error :messages="$errors->get('estado_civil')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="ocupacion" value="Ocupacion (opcional)" />
                <x-text-input id="ocupacion" class="block mt-1 w-full" type="text" name="ocupacion" :value="old('ocupacion')" maxlength="255" />
                <x-input-error :messages="$errors->get('ocupacion')" class="mt-2" />
            </div>
        </div>

        <div>
            <x-input-label for="direccion" value="Direccion" />
            <x-text-input id="direccion" class="block mt-1 w-full" type="text" name="direccion" :value="old('direccion')" required maxlength="255" autocomplete="street-address" />
            <x-input-error :messages="$errors->get('direccion')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
