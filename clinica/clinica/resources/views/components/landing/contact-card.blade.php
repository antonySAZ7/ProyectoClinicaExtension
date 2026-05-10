@props([
    'icon',
    'label',
    'value',
    'href',
])

@php
    $isExternal = str_starts_with($href, 'http');
@endphp

<a
    href="{{ $href }}"
    @if ($isExternal) target="_blank" rel="noreferrer" @endif
    class="flex min-w-0 items-center gap-4 rounded-2xl border border-[var(--brand-border)] bg-white px-5 py-4 transition-colors hover:border-[var(--brand-primary)]"
>
    <div class="flex h-11 w-11 items-center justify-center rounded-full bg-[var(--brand-soft)] text-[var(--brand-primary)]">
        <x-dynamic-component :component="'lucide-' . $icon" class="h-5 w-5" />
    </div>
    <div class="min-w-0 flex-1">
        <p class="text-sm uppercase tracking-[0.2em] text-[var(--brand-muted)]">{{ $label }}</p>
        <p class="text-base leading-snug text-[var(--brand-primary)] [overflow-wrap:anywhere]">{{ $value }}</p>
    </div>
    <x-lucide-arrow-right class="ml-auto h-[18px] w-[18px] shrink-0 text-[var(--brand-muted)]" />
</a>
