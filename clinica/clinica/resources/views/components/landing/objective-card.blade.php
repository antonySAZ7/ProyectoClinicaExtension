@props([
    'index',
    'title',
    'description',
])

<div class="h-full rounded-2xl border border-[var(--brand-border)] bg-white p-6 shadow-sm">
    <div class="mb-5 flex h-14 w-14 items-center justify-center rounded-full border border-[var(--brand-border)] bg-[var(--brand-soft)] font-display text-4xl text-[var(--brand-primary)]">
        {{ $index }}
    </div>
    <h3 class="font-display text-3xl leading-tight text-[var(--brand-primary)]">
        {{ $title }}
    </h3>
    <p class="mt-4 text-base leading-relaxed text-[var(--brand-muted)]">
        {{ $description }}
    </p>
</div>
