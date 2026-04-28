<x-guest-layout>
    <!-- Estado de sesion -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Correo electronico -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Contrasena -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Recordarme y olvido -->
        <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-brand-border text-brand-primary shadow-sm focus:ring-brand-primary" name="remember">
                <span class="ms-2 text-sm text-brand-muted">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-brand-muted underline hover:text-brand-primary rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-primary" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
        </div>

        <div class="mt-6">
            <x-primary-button class="w-full justify-center">
                {{ __('Log in') }}
            </x-primary-button>
        </div>

        @if (Route::has('register'))
            <p class="mt-6 text-center text-sm text-brand-muted">
                {{ __("Don't have an account?") }}
                <a class="ms-1 font-semibold text-brand-primary underline hover:text-brand-primary-strong rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-primary" href="{{ route('register') }}">
                    {{ __('Register') }}
                </a>
            </p>
        @endif
    </form>
</x-guest-layout>
