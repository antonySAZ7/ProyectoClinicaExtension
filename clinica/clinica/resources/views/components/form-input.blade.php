@props(['label' => null, 'name' => null, 'value' => null])

<div>
    @if($label && $name)
        <label for="{{ $name }}" class="block text-base font-medium text-brand-muted mb-1">
            {{ $label }}
        </label>
    @endif

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        {{ $attributes->merge([
            'class' => 'block w-full rounded-md border-brand-border shadow-sm focus:border-brand-primary focus:ring-brand-primary'
        ]) }}
    >

    @error($name)
        <p class="mt-1 text-sm text-brand-error">{{ $message }}</p>
    @enderror
</div>