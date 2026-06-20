@props([
    'label',
    'value',
    'accent' => 'text-gray-900',
])

@php
    $displayValue = is_numeric($value) ? number_format((float) $value, ((float) $value == (int) $value) ? 0 : 1) : $value;
@endphp

<div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
    <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
    <p class="mt-2 text-3xl font-bold {{ $accent }}">{{ $displayValue }}</p>
</div>
