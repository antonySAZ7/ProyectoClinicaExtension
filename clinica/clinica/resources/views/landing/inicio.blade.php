@php
    $brand = config('site.brand');
    $hero = config('site.hero');
    $photos = config('site.photos');
    $framing = config('site.framing');
@endphp

<x-layouts.public>
    <div id="inicio" class="pt-20 text-[var(--brand-primary)]">
        <section class="relative flex min-h-[720px] items-center overflow-hidden px-6 py-20 md:px-12">
            <div class="absolute inset-0 bg-[linear-gradient(180deg,var(--brand-surface),var(--brand-soft))]"></div>

            <div class="relative z-10 mx-auto grid w-full max-w-7xl gap-10 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
                <div class="space-y-10">
                    <div class="max-w-3xl">
                        <p class="mb-3 font-script text-4xl text-[var(--brand-muted)] md:text-6xl">
                            {{ $brand['subtitle'] }}
                        </p>
                        <h1 class="font-display text-6xl leading-none tracking-[0.08em] text-[var(--brand-primary)] md:text-8xl">
                            {{ $brand['name'] }}
                        </h1>
                        <p class="mt-6 max-w-2xl text-lg text-[var(--brand-primary)]/90 md:text-2xl">
                            {{ $brand['tagline'] }}
                        </p>
                        <p class="mt-6 max-w-2xl text-base text-[var(--brand-muted)] md:text-lg">
                            {{ $hero['description'] }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-4 sm:flex-row">
                        <a
                            href="{{ route('public.citas.create') }}"
                            class="inline-flex items-center justify-center rounded-md bg-[var(--brand-primary)] px-8 py-4 text-base font-semibold text-[var(--brand-contrast)] transition hover:bg-[var(--brand-primary-strong)]"
                        >
                            Agenda tu cita
                        </a>
                        <a
                            href="{{ route('landing.contacto') }}"
                            class="inline-flex items-center justify-center rounded-md border border-[var(--brand-primary)] bg-transparent px-8 py-4 text-base font-semibold text-[var(--brand-primary)] transition hover:bg-[var(--brand-primary)] hover:text-[var(--brand-contrast)]"
                        >
                            Ver contacto
                        </a>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-landing.hero-info icon="phone" label="Teléfono" :value="$brand['phone_display']" />
                        <x-landing.hero-info icon="instagram" label="Instagram" :value="$brand['instagram_display']" />
                        <x-landing.hero-info
                            icon="mail"
                            label="Correo"
                            :value="$brand['email_display']"
                            class="sm:col-span-2"
                            value-class="text-lg md:text-xl"
                        />
                    </div>
                </div>

                <div class="relative">
                    <div class="absolute -left-6 top-10 hidden h-48 w-48 rounded-full bg-white/60 blur-3xl lg:block"></div>
                    <div class="grid grid-cols-2 items-start gap-4 md:gap-6">
                        <x-landing.hero-photo
                            :src="$photos['hero_primary']"
                            :alt="$brand['legal_name'] . ' - vista principal 1'"
                            :object-position="$framing['hero_primary']"
                            class="mt-0 md:mt-10"
                        />
                        <x-landing.hero-photo
                            :src="$photos['hero_secondary']"
                            :alt="$brand['legal_name'] . ' - vista principal 2'"
                            :object-position="$framing['hero_secondary']"
                            class="mt-12 md:mt-0"
                        />
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-layouts.public>
