@props([
    'icon',
    'title',
    'description',
])

<div class="rounded-2xl border border-[var(--brand-border)] bg-white p-6 shadow-sm">
    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[var(--brand-soft)] text-[var(--brand-primary)]">
        <x-dynamic-component :component="'lucide-' . $icon" class="h-5 w-5" />
    </div>
    <h3 class="mt-4 font-display text-3xl text-[var(--brand-primary)]">{{ $title }}</h3>
    <p class="mt-3 text-sm leading-relaxed text-[var(--brand-muted)]">{{ $description }}</p>
</div>
