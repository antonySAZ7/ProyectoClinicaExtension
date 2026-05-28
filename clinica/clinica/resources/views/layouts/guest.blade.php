<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        @php $brand = config('site.brand'); @endphp
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-brand-surface">
            <a
                href="{{ route('landing') }}"
                class="absolute left-4 top-4 inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium text-brand-muted transition hover:bg-brand-soft hover:text-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary focus:ring-offset-2"
            >
                <x-lucide-arrow-left class="h-4 w-4" />
                Volver al inicio
            </a>

            <div>
                <a href="/" class="block text-center">
                    <p class="font-display text-5xl leading-none text-brand-primary">
                        {{ $brand['name'] }}
                    </p>
                    <p class="mt-1 text-xs uppercase tracking-[0.28em] text-brand-muted">
                        {{ $brand['subtitle'] }}
                    </p>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
