@php
    $brand = config('site.brand');
@endphp

<footer class="border-t border-[var(--brand-border)] bg-[var(--brand-surface)] py-12">
    <div class="mx-auto flex max-w-7xl flex-col gap-8 px-6 md:flex-row md:items-center md:justify-between md:px-12">
        <div>
            <p class="font-display text-4xl leading-none text-[var(--brand-primary)]">
                {{ $brand['name'] }}
            </p>
            <p class="mt-2 text-xs uppercase tracking-[0.28em] text-[var(--brand-muted)]">
                {{ $brand['subtitle'] }}
            </p>
            <p class="mt-4 text-sm text-[var(--brand-muted)]">
                © {{ $brand['year'] }} {{ $brand['legal_name'] }}. Todos los derechos reservados.
            </p>
        </div>

        <div class="flex flex-col gap-3 text-sm text-[var(--brand-muted)]">
            <a
                href="{{ $brand['phone_href'] }}"
                class="flex items-center gap-2 transition-colors hover:text-[var(--brand-primary)]"
            >
                <x-lucide-phone class="h-4 w-4" />
                {{ $brand['phone_display'] }}
            </a>
            <a
                href="{{ $brand['instagram_href'] }}"
                target="_blank"
                rel="noreferrer"
                class="flex items-center gap-2 transition-colors hover:text-[var(--brand-primary)]"
            >
                <x-lucide-instagram class="h-4 w-4" />
                {{ $brand['instagram_display'] }}
            </a>
            <a
                href="{{ $brand['email_href'] }}"
                class="flex items-center gap-2 transition-colors hover:text-[var(--brand-primary)]"
            >
                <x-lucide-mail class="h-4 w-4" />
                {{ $brand['email_display'] }}
            </a>
        </div>
    </div>
</footer>
