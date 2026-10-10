@php
    $primary = $vehicle->images->first();
    $q = array_filter($query);
@endphp
<x-layouts.app :title="$vehicle->name" :description="\Illuminate\Support\Str::limit(strip_tags($vehicle->description ?? ($vehicle->name.' – iznajmljivanje vozila.')), 155)"
    ogType="product" :ogImage="$primary?->url('lg')"
    :jsonLd="['@context' => 'https://schema.org', '@type' => 'Car', 'name' => $vehicle->name, 'brand' => $vehicle->brand, 'model' => $vehicle->model, 'vehicleModelDate' => (string) $vehicle->year, 'fuelType' => $vehicle->fuel->getLabel(), 'vehicleTransmission' => $vehicle->transmission->getLabel(), 'offers' => ['@type' => 'Offer', 'priceCurrency' => 'EUR', 'price' => number_format($vehicle->daily_price / 100, 2, '.', ''), 'availability' => 'https://schema.org/InStock']]">
    <div class="wrap pt-6">
        <nav aria-label="Putanja" class="text-sm text-ink-2">
            <a href="{{ route('fleet.index', $q) }}" class="inline-flex min-h-11 items-center gap-1 no-underline">← Vozni park</a>
        </nav>
    </div>

    <div class="wrap grid gap-10 pb-10 pt-2 lg:grid-cols-[1.5fr_1fr]">
        {{-- Galerija --}}
        <div data-gallery>
            <div class="aspect-[16/10] overflow-hidden bg-surface-2" style="border-radius: 6px 60px 6px 6px;">
                @if ($primary)
                    <img data-gallery-main src="{{ $primary->url('lg') }}" srcset="{{ $primary->srcset() }}" sizes="(min-width: 1024px) 720px, 100vw"
                         alt="{{ $primary->alt }}" width="1600" height="1000" fetchpriority="high" class="h-full w-full object-cover">
                @else
                    <x-vehicle-image :image="null" />
                @endif
            </div>
            @if ($vehicle->images->count() > 1)
                <ul class="mt-3 grid grid-cols-4 gap-3" aria-label="Slike vozila">
                    @foreach ($vehicle->images as $img)
                        <li>
                            <button type="button" class="block aspect-[16/10] w-full overflow-hidden border-2 border-transparent bg-surface-2 aria-[current=true]:border-brand-text"
                                    data-gallery-thumb data-large="{{ $img->url('lg') }}" data-alt="{{ $img->alt }}" aria-current="{{ $loop->first ? 'true' : 'false' }}" aria-label="Prikaži sliku {{ $loop->iteration }}" style="border-radius: 4px;">
                                <img src="{{ $img->url('sm') }}" alt="" width="480" height="300" loading="lazy" class="h-full w-full object-cover">
                            </button>
                        </li>
                    @endforeach
                </ul>
            @endif
            @if ($vehicle->images->contains('is_placeholder', true))
                <p class="mt-3 text-sm text-ink-2">Privremene ilustracije, fotografije vozila uskoro.</p>
            @endif
        </div>

        {{-- Cena i rezervacija --}}
        <div>
            <p class="eyebrow">{{ $vehicle->category->name }}</p>
            <h1 class="h2 mt-2" style="font-size: clamp(2rem, 4vw, 3rem)">{{ $vehicle->name }}</h1>
            <p class="mt-5 flex items-end gap-2">
                <span class="text-4xl font-bold"><x-money :cents="$vehicle->daily_price" /></span>
                <span class="pb-1 text-ink-2">po danu</span>
            </p>
            <p class="mt-1 text-sm text-ink-2">Depozit pri preuzimanju: <x-money :cents="$vehicle->deposit" class="font-semibold text-ink" /></p>

            <div class="card mt-6 p-5">
                <h2 class="text-lg font-bold">Proverite termin</h2>
                <form action="{{ route('fleet.show', $vehicle) }}" method="get" class="mt-4 grid gap-3 min-[420px]:grid-cols-2" novalidate>
                    <div class="field">
                        <label class="label" for="d-od">Preuzimanje</label>
                        <input class="input num" type="date" id="d-od" name="od" min="{{ now()->toDateString() }}" value="{{ $query['od'] ?? '' }}" required>
                    </div>
                    <div class="field">
                        <label class="label" for="d-do">Vraćanje</label>
                        <input class="input num" type="date" id="d-do" name="do" min="{{ now()->toDateString() }}" value="{{ $query['do'] ?? '' }}" required>
                    </div>
                    @foreach (['lokacija', 'vracanje'] as $k)@if (! empty($query[$k]))<input type="hidden" name="{{ $k }}" value="{{ $query[$k] }}">@endif @endforeach
                    <button class="btn btn-ghost min-[420px]:col-span-2" type="submit">Izračunaj cenu</button>
                </form>

                @if ($range)
                    <div class="mt-5 border-t border-line pt-5" role="status">
                        @if ($available)
                            <p class="flex items-center gap-2 font-semibold text-good"><x-ico name="check" /> Slobodno u izabranom terminu</p>
                            <dl class="mt-3 space-y-1.5 text-sm">
                                @foreach ($quote->rentalLines as $line)
                                    <div class="flex justify-between gap-4"><dt class="text-ink-2"><span class="num">{{ $line['days'] }}</span> × <x-money :cents="$line['daily_price']" />@if ($line['label']) ({{ $line['label'] }})@endif</dt><dd><x-money :cents="$line['days'] * $line['daily_price']" /></dd></div>
                                @endforeach
                                @if ($quote->discountTotal)
                                    <div class="flex justify-between gap-4"><dt class="text-ink-2">Popust za duži najam (<span class="num">{{ $quote->discountPercent }}</span>%)</dt><dd class="text-good">−<x-money :cents="$quote->discountTotal" /></dd></div>
                                @endif
                                <div class="flex justify-between gap-4 border-t border-line pt-2 text-base font-bold"><dt>Ukupno za <span class="num">{{ $quote->days }}</span> {{ $quote->days === 1 ? 'dan' : 'dana' }}</dt><dd><x-money :cents="$quote->rentalTotal - $quote->discountTotal" /></dd></div>
                            </dl>
                            <p class="mt-2 text-xs text-ink-2">Dostava i dodaci se računaju u sledećem koraku.</p>
                        @else
                            <p class="flex items-center gap-2 font-semibold text-bad"><x-ico name="info" /> Vozilo je zauzeto u izabranom terminu</p>
                            <p class="mt-1 text-sm text-ink-2">Izaberite druge datume ili pogledajte <a href="{{ route('fleet.index', array_filter(['od' => $query['od'] ?? null, 'do' => $query['do'] ?? null])) }}">slična slobodna vozila</a>.</p>
                        @endif
                    </div>
                @endif

                <a href="{{ route('reservation.create', ['vozilo' => $vehicle->slug] + $q) }}"
                   class="btn btn-primary btn-block mt-5 {{ $range && ! $available ? 'pointer-events-none' : '' }}" @if ($range && ! $available) aria-disabled="true" tabindex="-1" @endif>Rezerviši ovo vozilo <x-ico name="arrow" /></a>
                <p class="mt-3 text-center text-sm text-ink-2">Plaćanje pri preuzimanju. Bez unosa kartice.</p>
            </div>
        </div>
    </div>

    {{-- Specifikacije --}}
    <section class="section bg-surface" aria-labelledby="spec">
        <div class="wrap grid gap-10 lg:grid-cols-[1fr_1.5fr]">
            <div>
                <h2 id="spec" class="h2">Specifikacije</h2>
                @if ($vehicle->description)<p class="mt-4 text-ink-2">{{ $vehicle->description }}</p>@endif
            </div>
            <dl data-reveal class="grid grid-cols-2 gap-px bg-line sm:grid-cols-3" style="border: 1px solid var(--line); border-radius: 6px; overflow: hidden;">
                @foreach ([
                    ['calendar', 'Godište', $vehicle->year],
                    ['gear', 'Menjač', $vehicle->transmission->getLabel()],
                    ['fuel', 'Gorivo', $vehicle->fuel->getLabel()],
                    ['seats', 'Sedišta', $vehicle->seats],
                    ['key', 'Vrata', $vehicle->doors],
                    ['bag', 'Kofera', $vehicle->luggage],
                ] as [$icon, $label, $value])
                    <div class="bg-surface p-4">
                        <dt class="flex items-center gap-2 text-sm text-ink-2"><x-ico :name="$icon" class="size-4" />{{ $label }}</dt>
                        <dd class="num mt-1 text-lg font-semibold">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    {{-- Kalendar --}}
    <section class="section" aria-labelledby="kalendar">
        <div class="wrap">
            <h2 id="kalendar" class="h2">Kalendar zauzetosti</h2>
            <p class="lead mt-2 mb-6">Pregled narednih meseci. Dan vraćanja ostaje slobodan za novo preuzimanje.</p>
            <x-availability-calendar data-reveal :busy="$busy" />
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="pb-8" aria-labelledby="slicna">
            <div class="wrap">
                <h2 id="slicna" class="h2 mb-6">Slična vozila</h2>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $r)<x-vehicle-card :vehicle="$r" :query="$q" />@endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.app>
