@props([
    'src',
    'alt' => '',
    'objectPosition' => null,
])

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-2xl shadow-sm']) }}>
    <div class="relative w-full aspect-[4/5] md:aspect-[3/4]">
        <img
            src="{{ asset($src) }}"
            alt="{{ $alt }}"
            loading="lazy"
            class="absolute inset-0 w-full h-full object-cover"
            @if ($objectPosition) style="object-position: {{ $objectPosition }};" @endif
        />
        <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(255,255,255,0),rgba(36,35,31,0.22))]"></div>
    </div>
</div>