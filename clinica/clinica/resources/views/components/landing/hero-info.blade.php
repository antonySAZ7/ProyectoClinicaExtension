@props([
    'icon',
    'label',
    'value',
    'valueClass' => '',
])

<div {{ $attributes->merge(['class' => 'min-w-0 rounded-[24px] border border-white/70 bg-white/90 px-5 py-5 shadow-[0_18px_40px_rgba(36,35,31,0.08)] backdrop-blur-md']) }}>
    <div class="flex min-w-0 items-start gap-4">
        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[var(--brand-soft)] text-[var(--brand-primary)] shadow-inner">
            <x-dynamic-component :component="'lucide-' . $icon" class="h-[18px] w-[18px]" />
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-[var(--brand-muted)]">
                {{ $label }}
            </p>
            <p class="mt-2 text-xl font-medium leading-snug text-[var(--brand-primary)] [overflow-wrap:anywhere] {{ $valueClass }}">
                {{ $value }}
            </p>
        </div>
    </div>
</div>
