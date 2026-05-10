@props([
    'eyebrow',
    'title',
    'description',
    'image',
    'imageAlt' => '',
    'objectPosition' => null,
    'reverse' => false,
])

<div class="overflow-hidden rounded-2xl border border-[var(--brand-border)] bg-white shadow-sm">
    <div class="grid gap-0 lg:grid-cols-2 {{ $reverse ? 'lg:[&>*:first-child]:order-2' : '' }}">
        <div class="flex items-center px-8 py-10 md:px-12">
            <div>
                <p class="font-script text-4xl text-[var(--brand-muted)]">{{ $eyebrow }}</p>
                <h3 class="font-display text-5xl leading-none text-[var(--brand-primary)] md:text-6xl">
                    {{ $title }}
                </h3>
                <div class="mt-5 h-px w-32 bg-[var(--brand-primary)]/70"></div>
                <p class="mt-8 text-lg leading-relaxed text-[var(--brand-muted)]">
                    {{ $description }}
                </p>
            </div>
        </div>
        <div class="min-h-[360px]">
            <img
                src="{{ asset($image) }}"
                alt="{{ $imageAlt }}"
                loading="lazy"
                class="h-full w-full object-cover"
                @if ($objectPosition) style="object-position: {{ $objectPosition }};" @endif
            />
        </div>
    </div>
</div>
