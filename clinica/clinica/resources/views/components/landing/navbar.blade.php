@php
    $brand = config('site.brand');
    $navLinks = [
        ['label' => 'Inicio',     'href' => route('landing'),           'route' => 'landing'],
        ['label' => 'Nosotros',   'href' => route('landing.nosotros'),  'route' => 'landing.nosotros'],
        ['label' => 'Objetivos',  'href' => route('landing.objetivos'), 'route' => 'landing.objetivos'],
        ['label' => 'Contacto',   'href' => route('landing.contacto'),  'route' => 'landing.contacto'],
    ];
@endphp

<nav
    x-data="{ scrolled: false, mobileOpen: false }"
    x-init="scrolled = window.scrollY > 10"
    @scroll.window="scrolled = window.scrollY > 10"
    :class="scrolled
        ? 'bg-[var(--brand-surface)]/95 py-4 shadow-[0_14px_34px_rgba(30,30,28,0.08)] backdrop-blur-md'
        : 'bg-transparent py-6'"
    class="fixed left-0 right-0 top-0 z-50 transition-all duration-300"
>
    <div class="mx-auto flex max-w-7xl items-center justify-between px-6 md:px-12">
        <a href="{{ url('/') }}" class="text-left">
            <p class="font-display text-4xl leading-none text-[var(--brand-primary)]">
                {{ $brand['name'] }}
            </p>
            <p class="text-xs uppercase tracking-[0.28em] text-[var(--brand-muted)]">
                {{ $brand['subtitle'] }}
            </p>
        </a>

        {{-- Desktop --}}
        <div class="hidden items-center gap-8 md:flex">
            @guest
                @foreach ($navLinks as $link)
                    @php $active = request()->routeIs($link['route']); @endphp
                    <a
                        href="{{ $link['href'] }}"
                        @class([
                            'font-medium transition-colors',
                            'text-[var(--brand-primary)]' => $active,
                            'text-[var(--brand-muted)] hover:text-[var(--brand-primary)]' => ! $active,
                        ])
                    >
                        {{ $link['label'] }}
                    </a>
                @endforeach

                <a
                    href="{{ route('login') }}"
                    class="inline-flex items-center justify-center rounded-md border border-[var(--brand-border)] bg-[var(--brand-soft)] px-6 py-3 text-sm font-semibold text-[var(--brand-primary)] transition hover:bg-[var(--brand-border)]"
                >
                    Iniciar sesión
                </a>

                <a
                    href="{{ route('public.citas.create') }}"
                    class="inline-flex items-center justify-center rounded-md bg-[var(--brand-primary)] px-6 py-3 text-sm font-semibold text-[var(--brand-contrast)] transition hover:bg-[var(--brand-primary-strong)]"
                >
                    Agenda tu cita
                </a>
            @endguest

            @auth
                @if (auth()->user()->isPaciente())
                    <a
                        href="{{ route('portal') }}"
                        class="inline-flex items-center gap-2 font-medium text-[var(--brand-muted)] transition-colors hover:text-[var(--brand-primary)]"
                    >
                        <x-lucide-calendar class="h-[18px] w-[18px]" />
                        Mis citas
                    </a>
                @elseif (auth()->user()->canAccessBackoffice())
                    <a
                        href="{{ route('dashboard') }}"
                        class="inline-flex items-center gap-2 font-medium text-[var(--brand-muted)] transition-colors hover:text-[var(--brand-primary)]"
                    >
                        <x-lucide-calendar class="h-[18px] w-[18px]" />
                        Dashboard
                    </a>
                @endif

                <div class="flex items-center gap-2 rounded-full border border-[var(--brand-border)] bg-white px-3 py-1.5">
                    <x-lucide-user class="h-4 w-4 text-[var(--brand-primary)]" />
                    <span class="text-sm font-medium text-[var(--brand-primary)]">
                        {{ auth()->user()->name }}
                    </span>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        title="Cerrar sesión"
                        class="p-2 text-[var(--brand-muted)] transition-colors hover:text-red-500"
                    >
                        <x-lucide-log-out class="h-5 w-5" />
                    </button>
                </form>
            @endauth
        </div>

        {{-- Mobile toggle --}}
        <button
            type="button"
            class="p-2 text-[var(--brand-primary)] md:hidden"
            @click="mobileOpen = !mobileOpen"
            :aria-expanded="mobileOpen"
            aria-label="Abrir menú"
        >
            <template x-if="!mobileOpen"><x-lucide-menu class="h-6 w-6" /></template>
            <template x-if="mobileOpen"><x-lucide-x class="h-6 w-6" /></template>
        </button>
    </div>

    {{-- Mobile menu --}}
    <div
        x-show="mobileOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="overflow-hidden border-b border-[var(--brand-border)] bg-[var(--brand-surface)] md:hidden"
        x-cloak
    >
        <div class="flex flex-col gap-6 px-6 py-8">
            @guest
                @foreach ($navLinks as $link)
                    @php $active = request()->routeIs($link['route']); @endphp
                    <a
                        href="{{ $link['href'] }}"
                        @click="mobileOpen = false"
                        @class([
                            'text-left text-lg font-medium',
                            'text-[var(--brand-primary)]' => $active,
                            'text-[var(--brand-muted)]' => ! $active,
                        ])
                    >
                        {{ $link['label'] }}
                    </a>
                @endforeach

                <div class="space-y-3 border-t border-[var(--brand-border)] pt-4">
                    <a
                        href="{{ route('login') }}"
                        @click="mobileOpen = false"
                        class="block w-full rounded-md border border-[var(--brand-border)] bg-white px-6 py-3 text-center text-sm font-semibold text-[var(--brand-primary)] transition hover:bg-[var(--brand-soft)]"
                    >
                        Iniciar sesión
                    </a>
                    <a
                        href="{{ route('public.citas.create') }}"
                        class="block w-full rounded-md bg-[var(--brand-primary)] px-6 py-3 text-center text-sm font-semibold text-[var(--brand-contrast)] transition hover:bg-[var(--brand-primary-strong)]"
                    >
                        Agenda tu cita
                    </a>
                </div>
            @endguest

            @auth
                <div class="space-y-4">
                    <p class="font-semibold text-[var(--brand-primary)]">{{ auth()->user()->name }}</p>

                    @if (auth()->user()->isPaciente())
                        <a
                            href="{{ route('portal') }}"
                            @click="mobileOpen = false"
                            class="block w-full py-2 text-left text-[var(--brand-muted)]"
                        >
                            Mis citas
                        </a>
                    @elseif (auth()->user()->canAccessBackoffice())
                        <a
                            href="{{ route('dashboard') }}"
                            @click="mobileOpen = false"
                            class="block w-full py-2 text-left text-[var(--brand-muted)]"
                        >
                            Dashboard
                        </a>
                    @endif

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            type="submit"
                            class="block w-full py-2 text-left text-red-500"
                        >
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </div>
</nav>
