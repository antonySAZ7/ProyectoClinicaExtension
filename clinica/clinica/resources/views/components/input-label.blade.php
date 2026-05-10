@props(['value' => null, 'required' => false])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-gray-700']) }}>
    {{ $value ?? $slot }}
    @if ($required)
        <span class="text-red-500" aria-hidden="true">*</span>
    @endif
</label>
