@props([
    'eyebrow',
    'title',
    'centered' => false,
])

<div class="{{ $centered ? 'mb-12 text-center' : 'mb-12' }}">
    <p class="font-script text-4xl text-[var(--brand-muted)] md:text-5xl">{{ $eyebrow }}</p>
    <h2 class="font-display text-5xl leading-none text-[var(--brand-primary)] md:text-7xl">
        {{ $title }}
    </h2>
    <div class="mt-5 h-px bg-[var(--brand-primary)]/70 {{ $centered ? 'mx-auto w-48' : 'w-40 md:w-52' }}"></div>
</div>
