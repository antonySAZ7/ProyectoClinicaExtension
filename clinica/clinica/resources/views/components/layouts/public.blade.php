@props([
    'title' => null,
    'description' => null,
    'ogImage' => null,
])

@php
    $brand = config('site.brand');
    $seo = config('site.seo');
    $pageTitle = $title ?? $seo['title'];
    $pageDescription = $description ?? $seo['description'];
    $pageOgImage = asset($ogImage ?? $seo['og_image']);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">

    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:image" content="{{ $pageOgImage }}">
    <meta property="og:site_name" content="{{ $brand['legal_name'] }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $pageOgImage }}">

    <link rel="icon" href="{{ asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-inter antialiased min-h-screen bg-[var(--brand-soft)] text-[var(--brand-primary)]">
    <x-landing.navbar />

    <main>
        {{ $slot }}
    </main>

    <x-landing.footer />
</body>
</html>
