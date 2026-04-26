@props(['href' => '#', 'variant' => 'outline'])

<a href="{{ $href }}" {{ $attributes->merge([
    'class' => $variant === 'primary'
        ? 'inline-flex items-center px-4 py-2 bg-brand-primary text-brand-contrast rounded-md hover:bg-brand-primary-strong'
        : 'inline-flex items-center px-4 py-2 border border-brand-border text-brand-primary rounded-md hover:bg-brand-soft'
]) }}>
    {{ $slot }}
</a>