<x-layouts.app title="Vozni park" description="Pregled vozila za iznajmljivanje: ekonomska, kompaktna, SUV i kombi vozila. Filtrirajte po menjaču, gorivu i broju sedišta.">
    <div class="wrap pb-6 pt-10">
        <p class="eyebrow">Vozni park</p>
        <h1 class="h2 mt-2" style="font-size: clamp(2rem, 4.5vw, 3.4rem)">Izaberite vozilo</h1>
        <p class="lead mt-3">
            @if ($range)
                Slobodna vozila od <span class="num">{{ $range->start->format('d.m.Y') }}</span> do <span class="num">{{ $range->end->format('d.m.Y') }}</span>
                (<span class="num">{{ $range->days() }}</span> {{ $range->days() === 1 ? 'dan' : 'dana' }}).
            @else
                Unesite datume da vidite samo slobodna vozila i ukupnu cenu.
            @endif
        </p>
    </div>

    <div class="wrap grid gap-8 pb-4 lg:grid-cols-[300px_1fr]">
        <aside aria-label="Filteri">
            <form action="{{ route('fleet.index') }}" method="get" class="card space-y-5 p-5 lg:sticky lg:top-24" novalidate>
                <fieldset class="space-y-4">
                    <legend class="mb-1 text-base font-bold">Termin</legend>
                    <div class="field">
                        <label class="label" for="f-od">Preuzimanje</label>
                        <input class="input num" type="date" id="f-od" name="od" min="{{ $minDate }}" value="{{ old('od', $filters['od'] ?? '') }}" @error('od') aria-invalid="true" aria-describedby="f-od-err" @enderror>
                        @error('od')<p class="error" id="f-od-err">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <label class="label" for="f-do">Vraćanje</label>
                        <input class="input num" type="date" id="f-do" name="do" min="{{ $minDate }}" value="{{ old('do', $filters['do'] ?? '') }}" @error('do') aria-invalid="true" aria-describedby="f-do-err" @enderror>
                        @error('do')<p class="error" id="f-do-err">{{ $message }}</p>@enderror
                    </div>
                    @if (! empty($filters['lokacija']))
                        <input type="hidden" name="lokacija" value="{{ $filters['lokacija'] }}">
                    @endif
                </fieldset>

                <fieldset class="space-y-4">
                    <legend class="mb-1 text-base font-bold">Filteri</legend>
                    <div class="field">
                        <label class="label" for="f-kat">Kategorija</label>
                        <select class="select" id="f-kat" name="kategorija">
                            <option value="">Sve kategorije</option>
                            @foreach ($categories as $c)<option value="{{ $c->slug }}" @selected(($filters['kategorija'] ?? '') === $c->slug)>{{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label class="label" for="f-menjac">Menjač</label>
                        <select class="select" id="f-menjac" name="menjac">
                            <option value="">Svejedno</option>
                            @foreach ($transmissions as $value => $label)<option value="{{ $value }}" @selected(($filters['menjac'] ?? '') === $value)>{{ $label }}</option>@endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label class="label" for="f-gorivo">Gorivo</label>
                        <select class="select" id="f-gorivo" name="gorivo">
                            <option value="">Svejedno</option>
                            @foreach ($fuels as $value => $label)<option value="{{ $value }}" @selected(($filters['gorivo'] ?? '') === $value)>{{ $label }}</option>@endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label class="label" for="f-sedista">Najmanje sedišta</label>
                        <select class="select num" id="f-sedista" name="sedista">
                            <option value="">Svejedno</option>
                            @foreach ([4, 5, 7, 9] as $n)<option value="{{ $n }}" @selected((string) ($filters['sedista'] ?? '') === (string) $n)>{{ $n }}+</option>@endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label class="label" for="f-sort">Sortiranje</label>
                        <select class="select" id="f-sort" name="sort">
                            <option value="">Po nazivu</option>
                            <option value="cena_rastuce" @selected(($filters['sort'] ?? '') === 'cena_rastuce')>Cena: od najniže</option>
                            <option value="cena_opadajuce" @selected(($filters['sort'] ?? '') === 'cena_opadajuce')>Cena: od najviše</option>
                        </select>
                    </div>
                </fieldset>

                <div class="flex flex-col gap-2">
                    <button class="btn btn-primary" type="submit">Primeni</button>
                    <a href="{{ route('fleet.index') }}" class="btn btn-ghost btn-sm">Poništi filtere</a>
                </div>
            </form>
        </aside>

        <section aria-label="Lista vozila" aria-live="polite">
            <p class="mb-4 text-sm text-ink-2"><span class="num font-semibold text-ink">{{ $vehicles->total() }}</span> {{ $vehicles->total() === 1 ? 'vozilo' : 'vozila' }}</p>
            @if ($vehicles->isEmpty())
                <div class="card p-8">
                    <h2 class="text-xl font-bold">Nema slobodnih vozila za izabrane uslove</h2>
                    <p class="mt-2 text-ink-2">Pokušajte sa drugim datumima ili uklonite neki filter. Možete nas i pozvati, pomoći ćemo vam da pronađete rešenje.</p>
                    <a href="{{ route('fleet.index') }}" class="btn btn-primary mt-5">Prikaži sva vozila</a>
                </div>
            @else
                <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($vehicles as $vehicle)
                        <x-vehicle-card :vehicle="$vehicle" :total="$totals[$vehicle->id] ?? null" :days="$range?->days()"
                            :query="array_filter(['od' => $filters['od'] ?? null, 'do' => $filters['do'] ?? null, 'lokacija' => $filters['lokacija'] ?? null])" />
                    @endforeach
                </div>
                {{ $vehicles->links('partials.pagination') }}
            @endif
        </section>
    </div>
</x-layouts.app>
