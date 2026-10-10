<x-layouts.app :title="null" description="Iznajmljivanje vozila u Novom Sadu sa jasnom cenom po danu. Izaberite termin, lokaciju i vozilo, a rezervaciju potvrđujemo mejlom. Plaćanje pri preuzimanju.">
    {{-- Hero --}}
    <section class="relative overflow-hidden">
        <div class="wrap grid items-center gap-10 pb-10 pt-10 lg:grid-cols-[1.05fr_1fr] lg:gap-14 lg:pb-16 lg:pt-16">
            <div>
                <p class="eyebrow">{{ $company->name }}</p>
                <h1 class="display mt-4">Vozilo kada vam treba. <span class="text-brand-text">Cena koju vidite je cena koju plaćate.</span></h1>
                <p class="lead mt-6">Izaberite termin, preuzmite vozilo na lokaciji koja vam odgovara i platite pri preuzimanju. Bez skrivenih troškova i bez plaćanja unapred.</p>
                <ul class="mt-8 flex flex-wrap gap-x-6 gap-y-2 text-sm font-medium text-ink">
                    <li class="flex items-center gap-2"><x-ico name="check" class="size-4 text-good" />Plaćanje pri preuzimanju</li>
                    <li class="flex items-center gap-2"><x-ico name="check" class="size-4 text-good" />Potvrda na mejl</li>
                </ul>
            </div>
            @if ($featured->first())
                <div class="relative">
                    <div class="aspect-[16/11] overflow-hidden bg-surface-2" style="border-radius: 6px 90px 6px 6px;">
                        <x-vehicle-image :image="$featured->first()->primaryImage" :eager="true" sizes="(min-width: 1024px) 560px, 100vw" />
                    </div>
                    <div class="absolute -bottom-4 left-4 card flex items-center gap-4 px-4 py-3 sm:left-6">
                        <div>
                            <p class="text-xs text-ink-2">{{ $featured->first()->name }}</p>
                            <p class="text-lg font-bold">od <x-money :cents="$featured->first()->daily_price" /> <span class="text-sm font-normal text-ink-2">/ dan</span></p>
                        </div>
                        <span class="badge badge-signal">Istaknuto</span>
                    </div>
                </div>
            @endif
        </div>

        <div class="wrap relative z-10 pb-6">
            <div class="card p-5 sm:p-6">
                <h2 class="mb-4 text-xl font-bold">Pronađite slobodno vozilo</h2>
                @include('partials.search-form', ['locations' => $locations, 'minDate' => $minDate, 'values' => [], 'idPrefix' => 'home'])
            </div>
        </div>
    </section>

    {{-- Istaknuta vozila --}}
    <section class="section" aria-labelledby="istaknuta">
        <div class="wrap">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="eyebrow">Vozni park</p>
                    <h2 id="istaknuta" class="h2 mt-2">Istaknuta vozila</h2>
                </div>
                <a href="{{ route('fleet.index') }}" class="btn btn-ghost btn-sm">Sva vozila <x-ico name="arrow" class="size-4" /></a>
            </div>
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($featured as $vehicle)
                    <x-vehicle-card :vehicle="$vehicle" />
                @endforeach
            </div>
        </div>
    </section>

    {{-- Zašto mi --}}
    <section class="section bg-surface" aria-labelledby="zasto">
        <div class="wrap grid gap-10 lg:grid-cols-[1fr_1.4fr]">
            <div>
                <p class="eyebrow">Zašto mi</p>
                <h2 id="zasto" class="h2 mt-2">Iznajmljivanje bez iznenađenja</h2>
                <p class="lead mt-4">Deset minuta za rezervaciju, jasni uslovi i ljudi koje možete da pozovete kada zatreba.</p>
            </div>
            <ul class="grid gap-x-8 gap-y-8 sm:grid-cols-2">
                @foreach ([
                    ['tag', 'Jasna cena', 'Cena po danu, depozit i svi dodaci vide se pre potvrde rezervacije.'],
                    ['shield', 'Održavana vozila', 'Vozila se redovno servisiraju i predaju očišćena. (Demo tekst, uskladiti sa stvarnom ponudom.)'],
                    ['pin', 'Gde vam odgovara', 'Preuzimanje u poslovnici, na aerodromu ili na adresi koju izaberete.'],
                    ['clock', 'Brza potvrda', 'Rezervaciju potvrđujemo mejlom, a pomoć dobijate i telefonom.'],
                ] as [$icon, $heading, $text])
                    <li class="flex gap-4">
                        <span class="grid size-11 shrink-0 place-items-center bg-brand-soft text-brand-text" style="border-radius: 4px 12px 4px 4px;"><x-ico :name="$icon" /></span>
                        <div>
                            <h3 class="text-lg font-bold">{{ $heading }}</h3>
                            <p class="mt-1 text-ink-2">{{ $text }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- Poziv na akciju --}}
    <section class="section">
        <div class="wrap">
            <div class="grid items-center gap-6 bg-brand p-8 text-on-brand sm:p-12 md:grid-cols-[1fr_auto]" style="border-radius: 6px 60px 6px 6px;">
                <div>
                    <h2 class="font-display text-3xl font-bold sm:text-4xl" style="color: var(--on-brand)">Imate pitanje pre rezervacije?</h2>
                    <p class="mt-2 max-w-xl" style="color: var(--on-brand)">Javite nam se, rado ćemo preporučiti vozilo prema broju putnika i dužini puta.</p>
                </div>
                <a href="{{ route('contact') }}" class="btn btn-signal">Kontaktirajte nas</a>
            </div>
        </div>
    </section>
</x-layouts.app>
