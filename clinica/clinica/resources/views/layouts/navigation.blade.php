@php
    $user = Auth::user();
    $homeRoute = route($user->homeRoute(), absolute: false);
    $canAccessBackoffice = $user->canAccessBackoffice();
@endphp

<nav x-data="{ open: false }" class="bg-brand-surface border-b border-brand-border shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            <!-- Logo -->
            <div class="flex items-center gap-6">
                <a href="{{ $homeRoute }}" class="flex flex-col leading-tight">
                    <span class="text-lg font-semibold text-brand-primary">DENS32</span>
                    <span class="text-xs uppercase tracking-wider text-brand-muted">Clínica Dental</span>
                </a>

                <!-- Links -->
                <div class="hidden sm:flex gap-4">
                    @if ($canAccessBackoffice)
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            Dashboard
                        </x-nav-link>
                        <x-nav-link :href="route('pacientes.index')" :active="request()->routeIs('pacientes.*')">
                            Pacientes
                        </x-nav-link>
                        <x-nav-link :href="route('citas.index')" :active="request()->routeIs('citas.*')">
                            Citas
                        </x-nav-link>
                    @else
                        <x-nav-link :href="route('portal')" :active="request()->routeIs('portal')">
                            Portal
                        </x-nav-link>
                        <x-nav-link :href="route('portal.consultas.index')" :active="request()->routeIs('portal.consultas.*')">
                            Historial
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- User -->
            <div class="hidden sm:flex sm:items-center">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-2 text-sm text-brand-muted hover:text-brand-primary">
                            <span>{{ $user->name }}</span>
                            <svg class="w-4 h-4" fill="currentColor">
                                <path d="M5.293 7.293L10 12l4.707-4.707" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            Profile
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                Log Out
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Mobile button -->
            <div class="flex items-center sm:hidden">
                <button @click="open = ! open" class="p-2 text-gray-500 hover:text-gray-700">
                    ☰
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile menu -->
    <div x-show="open" class="sm:hidden px-4 pb-4 space-y-2">
        @if ($canAccessBackoffice)
            <x-responsive-nav-link :href="route('dashboard')">Dashboard</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('pacientes.index')">Pacientes</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('citas.index')">Citas</x-responsive-nav-link>
        @else
            <x-responsive-nav-link :href="route('portal')">Portal</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('portal.consultas.index')">Historial</x-responsive-nav-link>
        @endif
    </div>
</nav>