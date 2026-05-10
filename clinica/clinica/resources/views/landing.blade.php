@php
    $brand = config('site.brand');
    $hero = config('site.hero');
    $about = config('site.about');
    $objectives = config('site.objectives');
    $mission = config('site.mission');
    $vision = config('site.vision');
    $contact = config('site.contact');
    $photos = config('site.photos');
    $framing = config('site.framing');
@endphp

<x-layouts.public>
    <div id="inicio" class="pt-20 text-[var(--brand-primary)]">

        {{-- Hero --}}
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
                            href="{{ route('register') }}"
                            class="inline-flex items-center justify-center rounded-md bg-[var(--brand-primary)] px-8 py-4 text-base font-semibold text-[var(--brand-contrast)] transition hover:bg-[var(--brand-primary-strong)]"
                        >
                            Agenda tu cita
                        </a>
                        <a
                            href="#contacto"
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

        {{-- Sobre nosotros --}}
        <section id="sobre-nosotros" class="bg-[var(--brand-surface)] px-6 py-24 md:px-12">
            <div class="mx-auto grid max-w-7xl gap-16 lg:grid-cols-[1.1fr_0.9fr]">
                <div>
                    <x-landing.section-heading :eyebrow="$about['eyebrow']" :title="$about['title']" />

                    <div class="space-y-6 text-lg leading-relaxed text-[var(--brand-muted)]">
                        @foreach ($about['paragraphs'] as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    </div>

                    <div class="mt-10 grid gap-4 sm:grid-cols-2">
                        @foreach ($about['highlights'] as $highlight)
                            <x-landing.mini-info
                                :icon="$highlight['icon']"
                                :title="$highlight['title']"
                                :description="$highlight['description']"
                            />
                        @endforeach
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="overflow-hidden rounded-2xl border border-[var(--brand-border)] bg-white shadow-sm">
                        <div class="relative aspect-[4/5] sm:aspect-[5/4] lg:aspect-[4/5]">
                            <img
                                src="{{ asset($photos['about_team']) }}"
                                alt="{{ $brand['legal_name'] }} - equipo fundador"
                                loading="lazy"
                                class="h-full w-full object-cover"
                                style="object-position: {{ $framing['about_team'] }};"
                            />
                            <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(36,35,31,0.04),rgba(36,35,31,0.5))]"></div>
                            <div class="absolute bottom-0 left-0 p-6 text-white">
                                <p class="text-xs uppercase tracking-[0.3em] text-white/75">{{ $about['team_caption'] }}</p>
                                <p class="font-display text-4xl leading-none">
                                    {{ implode(' y ', $brand['founders']) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-[var(--brand-border)] bg-white p-6 shadow-sm">
                        <p class="text-xs uppercase tracking-[0.28em] text-[var(--brand-muted)]">{{ $about['side_card']['eyebrow'] }}</p>
                        <p class="mt-3 font-display text-3xl text-[var(--brand-primary)]">
                            {{ $about['side_card']['title'] }}
                        </p>
                        <p class="mt-3 text-sm leading-relaxed text-[var(--brand-muted)]">
                            {{ $about['side_card']['description'] }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Objetivos --}}
        <section id="objetivos" class="bg-[var(--brand-soft)] px-6 py-24 md:px-12">
            <div class="mx-auto max-w-7xl">
                <x-landing.section-heading
                    :eyebrow="$objectives['eyebrow']"
                    :title="$objectives['title']"
                    centered
                />
                <div class="mt-14 grid gap-6 md:grid-cols-2">
                    @foreach ($objectives['items'] as $i => $item)
                        <x-landing.objective-card
                            :index="$i + 1"
                            :title="$item['title']"
                            :description="$item['description']"
                        />
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Misión y visión --}}
        <section id="mision-vision" class="bg-[var(--brand-surface)] px-6 py-24 md:px-12">
            <div class="mx-auto flex max-w-7xl flex-col gap-10">
                <x-landing.info-panel
                    :eyebrow="$mission['eyebrow']"
                    :title="$mission['title']"
                    :description="$mission['description']"
                    :image="$photos['mission']"
                    :object-position="$framing['mission']"
                    image-alt="Misión de DENS32"
                />
                <x-landing.info-panel
                    :eyebrow="$vision['eyebrow']"
                    :title="$vision['title']"
                    :description="$vision['description']"
                    :image="$photos['vision']"
                    :object-position="$framing['vision']"
                    image-alt="Visión de DENS32"
                    reverse
                />
            </div>
        </section>

        {{-- Contacto --}}
        <section id="contacto" class="bg-[var(--brand-soft)] px-6 py-24 md:px-12">
            <div class="mx-auto grid max-w-7xl gap-12 lg:grid-cols-[0.95fr_1.05fr]">
                <div>
                    <x-landing.section-heading :eyebrow="$contact['eyebrow']" :title="$contact['title']" />
                    <p class="max-w-xl text-lg leading-relaxed text-[var(--brand-muted)]">
                        {{ $contact['description'] }}
                    </p>

                    <div class="mt-10 grid gap-4">
                        <x-landing.contact-card
                            icon="phone"
                            label="Teléfono"
                            :value="$brand['phone_display']"
                            :href="$brand['phone_href']"
                        />
                        <x-landing.contact-card
                            icon="instagram"
                            label="Instagram"
                            :value="$brand['instagram_display']"
                            :href="$brand['instagram_href']"
                        />
                        <x-landing.contact-card
                            icon="mail"
                            label="Correo"
                            :value="$brand['email_display']"
                            :href="$brand['email_href']"
                        />
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-[var(--brand-border)] bg-white shadow-sm">
                    <div class="relative h-full min-h-[420px]">
                        <img
                            src="{{ asset($photos['contact']) }}"
                            alt="Contacto {{ $brand['legal_name'] }}"
                            loading="lazy"
                            class="h-full w-full object-cover"
                            style="object-position: {{ $framing['contact'] }};"
                        />
                        <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(36,35,31,0.18),rgba(36,35,31,0.62))]"></div>
                        <div class="absolute inset-x-0 bottom-0 p-8 text-white">
                            <p class="font-script text-4xl">{{ $brand['subtitle'] }}</p>
                            <p class="font-display text-6xl leading-none tracking-[0.08em]">
                                {{ $brand['name'] }}
                            </p>
                            <p class="mt-4 max-w-md text-white/85">
                                {{ $contact['overlay_text'] }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
</x-layouts.public>
