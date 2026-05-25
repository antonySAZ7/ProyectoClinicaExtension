@php
    $brand = config('site.brand');
    $about = config('site.about');
    $photos = config('site.photos');
    $framing = config('site.framing');
@endphp

<x-layouts.public title="Nosotros - DENS32">
    <div class="pt-20 text-[var(--brand-primary)]">
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
    </div>
</x-layouts.public>
