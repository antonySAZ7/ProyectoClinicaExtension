@php
    $brand = config('site.brand');
    $contact = config('site.contact');
    $photos = config('site.photos');
    $framing = config('site.framing');
@endphp

<x-layouts.public title="Contacto - DENS32">
    <div class="pt-20 text-[var(--brand-primary)]">
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
