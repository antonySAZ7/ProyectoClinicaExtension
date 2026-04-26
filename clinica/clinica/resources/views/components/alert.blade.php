@props(['type' => 'info'])

@php
$map = [
    'success' => 'bg-brand-success-light text-brand-success border border-brand-success-light',
    'error'   => 'bg-brand-error-light text-brand-error border border-brand-error-light',
    'warning' => 'bg-brand-warning-light text-brand-warning border border-brand-warning-light',
    'info'    => 'bg-brand-info-light text-brand-info border border-brand-info-light',
];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-md px-4 py-3 text-base ' . ($map[$type] ?? $map['info'])]) }}>
    {{ $slot }}
</div>