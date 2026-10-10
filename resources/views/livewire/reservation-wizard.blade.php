<div class="grid gap-8 lg:grid-cols-[1fr_360px]" x-data x-on:step-changed.window="$nextTick(() => document.getElementById('korak-naslov')?.focus())">
    <div>
        {{-- Koraci --}}
        <ol class="mb-8 grid grid-cols-5 gap-2" aria-label="Koraci rezervacije">
            @foreach ($steps as $n => $label)
                <li class="min-w-0" @if ($n === $step) aria-current="step" @endif>
                    @if ($n < $step)
                        <button type="button" wire:click="goTo({{ $n }})" class="block w-full min-h-11 text-left">
                            <span class="block h-1.5 bg-brand" style="border-radius: 2px;"></span>
                            <span class="mt-2 flex items-center gap-1.5 text-xs font-semibold text-brand-text sm:text-sm"><span class="num">{{ $n }}.</span><span class="hidden sm:inline">{{ $label }}</span><span class="sr-only sm:hidden">{{ $label }}</span></span>
                        </button>
                    @else
                        <div class="min-h-11">
                            <span class="block h-1.5 {{ $n === $step ? 'bg-brand-text' : 'bg-line' }}" style="border-radius: 2px;"></span>
                            <span class="mt-2 flex items-center gap-1.5 text-xs font-semibold sm:text-sm {{ $n === $step ? 'text-ink' : 'text-ink-2' }}"><span class="num">{{ $n }}.</span><span class="hidden sm:inline">{{ $label }}</span><span class="sr-only sm:hidden">{{ $label }}</span></span>
                        </div>
                    @endif
                </li>
            @endforeach
        </ol>

        <form wire:submit="{{ $step === 5 ? 'submit' : 'next' }}" class="card p-5 sm:p-8" novalidate>
            <h2 id="korak-naslov" tabindex="-1" class="text-2xl font-bold outline-none">{{ $step }}. {{ $steps[$step] }}</h2>

            <div wire:key="step-{{ $step }}" class="step-in">

            {{-- Korak 1 --}}
            @if ($step === 1)
                <div class="mt-6 grid gap-5 sm:grid-cols-2">
                    <div class="field">
                        <label class="label" for="r-od">Datum preuzimanja <span class="req" aria-hidden="true">*</span></label>
                        <input class="input num" type="date" id="r-od" wire:model="starts_on" min="{{ $minDate }}" required @error('starts_on') aria-invalid="true" aria-describedby="r-od-err" @enderror>
                        @error('starts_on')<p class="error" id="r-od-err">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <label class="label" for="r-do">Datum vraćanja <span class="req" aria-hidden="true">*</span></label>
                        <input class="input num" type="date" id="r-do" wire:model="ends_on" min="{{ $minDate }}" required @error('ends_on') aria-invalid="true" aria-describedby="r-do-err" @enderror>
                        @error('ends_on')<p class="error" id="r-do-err">{{ $message }}</p>@enderror
                    </div>
                    <div class="field" @if ($pickup_on_map) hidden @endif>
                        <label class="label" for="r-pick">Lokacija preuzimanja <span class="req" aria-hidden="true">*</span></label>
                        <select class="select" id="r-pick" wire:model="pickup_location_id" @error('pickup_location_id') aria-invalid="true" aria-describedby="r-pick-err" @enderror>
                            @foreach ($this->locations as $l)<option value="{{ $l->id }}">{{ $l->name }}@if ($l->delivery_fee) (+{{ \App\Support\Money::format($l->delivery_fee) }})@endif</option>@endforeach
                        </select>
                        @error('pickup_location_id')<p class="error" id="r-pick-err">{{ $message }}</p>@enderror
                    </div>
                    <div class="field" @if ($return_on_map) hidden @endif>
                        <label class="label" for="r-ret">Lokacija vraćanja <span class="req" aria-hidden="true">*</span></label>
                        <select class="select" id="r-ret" wire:model="return_location_id" @error('return_location_id') aria-invalid="true" aria-describedby="r-ret-err" @enderror>
                            @foreach ($this->locations as $l)<option value="{{ $l->id }}">{{ $l->name }}@if ($l->delivery_fee) (+{{ \App\Support\Money::format($l->delivery_fee) }})@endif</option>@endforeach
                        </select>
                        @error('return_location_id')<p class="error" id="r-ret-err">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="check cursor-pointer" for="r-pickup-map">
                            <input type="checkbox" id="r-pickup-map" wire:model.live="pickup_on_map">
                            <span class="text-sm font-semibold">Dostavite vozilo na adresu koju izaberem na mapi</span>
                        </label>
                        @if ($pickup_on_map)
                            <div class="mt-3 space-y-3">
                                <p class="hint" id="r-pickup-map-hint">Kliknite na mapu da označite tačku. Doplata se računa po kilometru od naše poslovnice (<span class="num">{{ \App\Support\Money::format($delivery['fee_per_km']) }}</span> / km, do <span class="num">{{ $delivery['max_km'] }}</span> km).</p>
                                <div wire:ignore x-data x-init="window.initDeliveryMap($el, $wire, 'pickup')" data-lat="{{ $delivery['lat'] }}" data-lng="{{ $delivery['lng'] }}" data-max-km="{{ $delivery['max_km'] }}" data-point-lat="{{ $pickup_lat }}" data-point-lng="{{ $pickup_lng }}"
                                     class="h-72 w-full overflow-hidden border border-field bg-surface-2" style="border-radius: 6px;" role="application" aria-label="Mapa za izbor mesta: preuzimanje" aria-describedby="r-pickup-map-hint"></div>
                                @error('pickup_lat')<p class="error" role="alert">{{ $message }}</p>@enderror
                                @if ($pickup_lat !== null)
                                    @php $d = app(\App\Services\DeliveryService::class)->forPoint($pickup_lat, $pickup_lng); @endphp
                                    <p class="text-sm font-semibold text-good"><x-ico name="check" class="inline size-4" /> Tačka izabrana, <span class="num">{{ $d['km'] }}</span> km od poslovnice, doplata <x-money :cents="$d['fee']" />.</p>
                                @endif
                                <div class="field">
                                    <label class="label" for="r-pickup-addr">Adresa ili opis mesta</label>
                                    <input class="input" id="r-pickup-addr" type="text" wire:model="pickup_address" maxlength="200" @error('pickup_address') aria-invalid="true" @enderror>
                                    @error('pickup_address')<p class="error">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="sm:col-span-2">
                        <label class="check cursor-pointer" for="r-return-map">
                            <input type="checkbox" id="r-return-map" wire:model.live="return_on_map">
                            <span class="text-sm font-semibold">Preuzmite vozilo sa adrese koju izaberem na mapi</span>
                        </label>
                        @if ($return_on_map)
                            <div class="mt-3 space-y-3">
                                <p class="hint" id="r-return-map-hint">Kliknite na mapu da označite tačku. Doplata se računa po kilometru od naše poslovnice (<span class="num">{{ \App\Support\Money::format($delivery['fee_per_km']) }}</span> / km, do <span class="num">{{ $delivery['max_km'] }}</span> km).</p>
                                <div wire:ignore x-data x-init="window.initDeliveryMap($el, $wire, 'return')" data-lat="{{ $delivery['lat'] }}" data-lng="{{ $delivery['lng'] }}" data-max-km="{{ $delivery['max_km'] }}" data-point-lat="{{ $return_lat }}" data-point-lng="{{ $return_lng }}"
                                     class="h-72 w-full overflow-hidden border border-field bg-surface-2" style="border-radius: 6px;" role="application" aria-label="Mapa za izbor mesta: vraćanje" aria-describedby="r-return-map-hint"></div>
                                @error('return_lat')<p class="error" role="alert">{{ $message }}</p>@enderror
                                @if ($return_lat !== null)
                                    @php $d = app(\App\Services\DeliveryService::class)->forPoint($return_lat, $return_lng); @endphp
                                    <p class="text-sm font-semibold text-good"><x-ico name="check" class="inline size-4" /> Tačka izabrana, <span class="num">{{ $d['km'] }}</span> km od poslovnice, doplata <x-money :cents="$d['fee']" />.</p>
                                @endif
                                <div class="field">
                                    <label class="label" for="r-return-addr">Adresa ili opis mesta</label>
                                    <input class="input" id="r-return-addr" type="text" wire:model="return_address" maxlength="200" @error('return_address') aria-invalid="true" @enderror>
                                    @error('return_address')<p class="error">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <p class="hint mt-4">Dani se računaju po datumima: od 14. do 17. su 3 dana. Doplata za dostavu se naplaćuje za svaku lokaciju posebno.</p>
            @endif

            {{-- Korak 2 --}}
            @if ($step === 2)
                @error('vehicle_id')<p class="error mt-4" role="alert">{{ $message }}</p>@enderror
                @if ($this->vehicles->isEmpty())
                    <p class="mt-6 text-ink-2">Nema slobodnih vozila u izabranom terminu. Vratite se na prvi korak i izaberite druge datume.</p>
                @else
                    <fieldset class="mt-6">
                        <legend class="sr-only">Izaberite vozilo</legend>
                        <ul class="grid gap-4">
                            @foreach ($this->vehicles as $v)
                                <li>
                                    <label class="flex cursor-pointer gap-4 border-2 p-3 transition-colors has-[:checked]:border-brand-text has-[:checked]:bg-brand-soft has-[:focus-visible]:outline has-[:focus-visible]:outline-[3px] has-[:focus-visible]:outline-offset-2" style="border-radius: 8px; border-color: var(--field); outline-color: var(--brand-text);">
                                        <input type="radio" class="sr-only" name="vehicle" value="{{ $v->id }}" wire:click="selectVehicle({{ $v->id }})" @checked($vehicle_id === $v->id)>
                                        <div class="aspect-[16/10] w-28 shrink-0 overflow-hidden bg-surface-2 sm:w-40" style="border-radius: 4px;"><x-vehicle-image :image="$v->primaryImage" sizes="160px" /></div>
                                        <div class="flex min-w-0 flex-1 flex-col justify-between">
                                            <div>
                                                <p class="font-display text-lg font-bold">{{ $v->name }}</p>
                                                <p class="text-sm text-ink-2">{{ $v->transmission->getLabel() }} · {{ $v->fuel->getLabel() }} · <span class="num">{{ $v->seats }}</span> sedišta</p>
                                            </div>
                                            <p class="mt-2 text-sm text-ink-2"><x-money :cents="$v->daily_price" class="font-semibold text-ink" /> po danu · ukupno <x-money :cents="$v->period_total" class="font-semibold text-ink" /></p>
                                        </div>
                                        @if ($vehicle_id === $v->id)<x-ico name="check" class="size-6 shrink-0 text-brand-text" />@endif
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                    </fieldset>
                @endif
            @endif

            {{-- Korak 3 --}}
            @if ($step === 3)
                <p class="hint mt-2">Dodaci nisu obavezni. Cena se odmah ažurira.</p>
                <fieldset class="mt-5">
                    <legend class="sr-only">Dodatna oprema i usluge</legend>
                    <ul class="divide-y divide-line">
                        @foreach ($this->extras as $e)
                            <li class="py-3">
                                <label class="check cursor-pointer" for="extra-{{ $e->id }}">
                                    <input type="checkbox" id="extra-{{ $e->id }}" value="{{ $e->id }}" wire:model.live="extra_ids">
                                    <span class="flex flex-1 flex-wrap items-baseline justify-between gap-x-4">
                                        <span><span class="font-semibold">{{ $e->name }}</span>@if ($e->description)<span class="block text-sm text-ink-2">{{ $e->description }}</span>@endif</span>
                                        <span class="num whitespace-nowrap font-semibold">{{ \App\Support\Money::format($e->price) }} <span class="font-sans text-sm font-normal text-ink-2">{{ $e->billing === \App\Enums\BillingType::PerDay ? '/ dan' : 'jednokratno' }}</span></span>
                                    </span>
                                </label>
                            </li>
                        @endforeach
                    </ul>
                </fieldset>
            @endif

            {{-- Korak 4 --}}
            @if ($step === 4)
                <div class="honeypot" aria-hidden="true"><label for="r-web">Ne popunjavajte</label><input type="text" id="r-web" wire:model="website" tabindex="-1" autocomplete="off"></div>
                <div class="mt-6 grid gap-5 sm:grid-cols-2">
                    <div class="field sm:col-span-2">
                        <label class="label" for="r-name">Ime i prezime <span class="req" aria-hidden="true">*</span></label>
                        <input class="input" id="r-name" type="text" wire:model="name" autocomplete="name" required @error('name') aria-invalid="true" aria-describedby="r-name-err" @enderror>
                        @error('name')<p class="error" id="r-name-err">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <label class="label" for="r-phone">Telefon <span class="req" aria-hidden="true">*</span></label>
                        <input class="input num" id="r-phone" type="tel" wire:model="phone" autocomplete="tel" required @error('phone') aria-invalid="true" aria-describedby="r-phone-err" @enderror>
                        @error('phone')<p class="error" id="r-phone-err">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <label class="label" for="r-email">Mejl <span class="req" aria-hidden="true">*</span></label>
                        <input class="input" id="r-email" type="email" wire:model="email" autocomplete="email" required @error('email') aria-invalid="true" aria-describedby="r-email-err" @enderror>
                        @error('email')<p class="error" id="r-email-err">{{ $message }}</p>@enderror
                    </div>
                    <div class="field sm:col-span-2">
                        <label class="label" for="r-note">Napomena</label>
                        <textarea class="textarea" id="r-note" rows="3" wire:model="note" @error('note') aria-invalid="true" aria-describedby="r-note-err" @enderror></textarea>
                        <p class="hint">Na primer vreme dolaska ili broj leta.</p>
                        @error('note')<p class="error" id="r-note-err">{{ $message }}</p>@enderror
                    </div>
                    <div class="field sm:col-span-2">
                        <label class="check cursor-pointer" for="r-consent">
                            <input type="checkbox" id="r-consent" wire:model="consent" required @error('consent') aria-invalid="true" aria-describedby="r-consent-err" @enderror>
                            <span class="text-sm">Saglasan/saglasna sam da se moji podaci obrađuju radi realizacije rezervacije, u skladu sa <a href="{{ route('privacy') }}" target="_blank" rel="noopener">politikom privatnosti</a> i <a href="{{ route('terms') }}" target="_blank" rel="noopener">uslovima najma</a>. <span class="req" aria-hidden="true" style="color: var(--bad)">*</span></span>
                        </label>
                        @error('consent')<p class="error" id="r-consent-err">{{ $message }}</p>@enderror
                    </div>
                </div>
            @endif

            {{-- Korak 5 --}}
            @if ($step === 5 && $this->quote)
                <div class="mt-6 grid gap-6 sm:grid-cols-2">
                    <dl class="space-y-3 text-sm">
                        <div><dt class="text-ink-2">Vozilo</dt><dd class="font-semibold">{{ $this->vehicle->name }}</dd></div>
                        <div><dt class="text-ink-2">Preuzimanje</dt><dd class="font-semibold"><span class="num">{{ $this->range->start->format('d.m.Y') }}</span>, {{ $this->quote->pickupCustom ? 'dostava na adresu'.($this->quote->pickupCustom['address'] ? ': '.$this->quote->pickupCustom['address'] : '') : $this->locations->firstWhere('id', $pickup_location_id)?->name }}</dd></div>
                        <div><dt class="text-ink-2">Vraćanje</dt><dd class="font-semibold"><span class="num">{{ $this->range->end->format('d.m.Y') }}</span>, {{ $this->quote->returnCustom ? 'preuzimanje sa adrese'.($this->quote->returnCustom['address'] ? ': '.$this->quote->returnCustom['address'] : '') : $this->locations->firstWhere('id', $return_location_id)?->name }}</dd></div>
                    </dl>
                    <dl class="space-y-3 text-sm">
                        <div><dt class="text-ink-2">Klijent</dt><dd class="font-semibold">{{ $name }}</dd></div>
                        <div><dt class="text-ink-2">Kontakt</dt><dd class="font-semibold">{{ $phone }}<br>{{ $email }}</dd></div>
                        @if ($note)<div><dt class="text-ink-2">Napomena</dt><dd>{{ $note }}</dd></div>@endif
                    </dl>
                </div>
                <p class="mt-6 flex items-center gap-2 text-sm"><x-ico name="info" class="size-4 shrink-0 text-brand-text" />Plaćanje se vrši pri preuzimanju vozila. Ne unosite podatke o kartici.</p>
                @error('consent')<p class="error mt-3" role="alert">{{ $message }}</p>@enderror
            @endif

            </div>

            <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                @if ($step > 1)
                    <button type="button" class="btn btn-ghost" wire:click="back">Nazad</button>
                @else
                    <span></span>
                @endif
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="next,submit">
                    <span wire:loading.remove wire:target="next,submit">{{ $step === 5 ? 'Potvrdi rezervaciju' : 'Nastavi' }}</span>
                    <span wire:loading wire:target="next,submit">Molimo sačekajte…</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Pregled cene --}}
    <aside aria-label="Pregled rezervacije" class="lg:sticky lg:top-24 lg:self-start">
        <div class="card p-5" aria-live="polite">
            <h2 class="text-lg font-bold">Pregled cene</h2>
            @if ($this->quote)
                @php $q = $this->quote; @endphp
                <p class="mt-1 text-sm text-ink-2">{{ $this->vehicle->name }}, <span class="num">{{ $q->days }}</span> {{ $q->days === 1 ? 'dan' : 'dana' }}</p>
                <dl class="mt-4 space-y-2 text-sm">
                    @foreach ($q->rentalLines as $line)
                        <div class="flex justify-between gap-3"><dt class="text-ink-2"><span class="num">{{ $line['days'] }}</span> × <x-money :cents="$line['daily_price']" />@if ($line['label'])<span class="block text-xs">{{ $line['label'] }}</span>@endif</dt><dd><x-money :cents="$line['days'] * $line['daily_price']" /></dd></div>
                    @endforeach
                    @if ($q->discountTotal)<div class="flex justify-between gap-3"><dt class="text-ink-2">Popust za duži najam (<span class="num">{{ $q->discountPercent }}</span>%)</dt><dd class="text-good">−<x-money :cents="$q->discountTotal" /></dd></div>@endif
                    @if ($q->pickupFee)<div class="flex justify-between gap-3"><dt class="text-ink-2">Dostava, preuzimanje @if ($q->pickupCustom) (<span class="num">{{ $q->pickupCustom['km'] }}</span> km)@endif</dt><dd><x-money :cents="$q->pickupFee" /></dd></div>@endif
                    @if ($q->returnFee)<div class="flex justify-between gap-3"><dt class="text-ink-2">Dostava, vraćanje @if ($q->returnCustom) (<span class="num">{{ $q->returnCustom['km'] }}</span> km)@endif</dt><dd><x-money :cents="$q->returnFee" /></dd></div>@endif
                    @foreach ($q->extras as $x)<div class="flex justify-between gap-3"><dt class="text-ink-2">{{ $x['name'] }}</dt><dd><x-money :cents="$x['total']" /></dd></div>@endforeach
                    <div class="flex items-baseline justify-between gap-3 border-t border-line pt-3 text-base font-bold"><dt>Ukupno</dt><dd class="text-2xl"><x-money :cents="$q->total()" /></dd></div>
                </dl>
                <p class="mt-3 text-xs text-ink-2">Depozit pri preuzimanju: <x-money :cents="$q->deposit" class="font-semibold" />. Plaćanje pri preuzimanju.</p>
            @else
                <p class="mt-2 text-sm text-ink-2">Cena će biti prikazana kada izaberete termin i vozilo.</p>
                @if ($this->range)<p class="mt-2 text-sm">Termin: <span class="num font-semibold">{{ $this->range->start->format('d.m.Y') }}</span> do <span class="num font-semibold">{{ $this->range->end->format('d.m.Y') }}</span> (<span class="num">{{ $this->range->days() }}</span> {{ $this->range->days() === 1 ? 'dan' : 'dana' }})</p>@endif
            @endif
        </div>
    </aside>
</div>
