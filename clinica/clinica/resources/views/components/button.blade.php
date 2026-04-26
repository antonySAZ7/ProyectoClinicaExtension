@props(['variant' => 'primary', 'type' => 'button'])

@php
$base = 'inline-flex items-center px-4 py-2 rounded-md text-base font-semibold transition focus:outline-none focus:ring-2 focus:ring-offset-2';
$variants = [
    'primary' => 'bg-brand-primary text-brand-contrast hover:bg-brand-primary-strong focus:ring-brand-primary',
    'outline' => 'border border-brand-border text-brand-primary hover:bg-brand-soft focus:ring-brand-primary',
];
@endphp

<button {{ $attributes->merge([
    'type' => $type,
    'class' => $base . ' ' . ($variants[$variant] ?? $variants['primary'])
]) }}>
    {{ $slot }}
</button>