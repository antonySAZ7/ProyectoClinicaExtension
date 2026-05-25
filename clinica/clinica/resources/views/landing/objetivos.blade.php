@php
    $objectives = config('site.objectives');
    $mission = config('site.mission');
    $vision = config('site.vision');
    $photos = config('site.photos');
    $framing = config('site.framing');
@endphp

<x-layouts.public title="Objetivos y Misión - DENS32">
    <div class="pt-20 text-[var(--brand-primary)]">

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

        {{-- Misión y Visión --}}
        <section id="mision-vision" class="bg-[var(--brand-surface)] px-6 py-24 md:px-12">
            <div class="mx-auto max-w-7xl">
                <x-landing.section-heading
                    eyebrow="Nuestro propósito"
                    title="Misión y Visión"
                    centered
                />

                <div class="mt-14 flex flex-col gap-10">
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
            </div>
        </section>

    </div>
</x-layouts.public>
