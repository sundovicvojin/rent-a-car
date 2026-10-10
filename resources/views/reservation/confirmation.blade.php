<x-layouts.app title="Rezervacija primljena" description="Potvrda rezervacije vozila." :noindex="true">
    <div class="wrap max-w-3xl pb-6 pt-10">
        <p class="badge badge-good"><x-ico name="check" class="size-4" /> Rezervacija je primljena</p>
        <h1 class="h2 mt-4" style="font-size: clamp(2rem, 4.5vw, 3.2rem)">Hvala, {{ $reservation->customer->name }}</h1>
        <p class="lead mt-3">Poslali smo potvrdu na <strong class="text-ink">{{ $reservation->customer->email }}</strong>. Rezervacija čeka našu potvrdu, a javićemo vam se mejlom ili telefonom.</p>

        <div class="card mt-8 p-6 sm:p-8">
            <p class="text-sm text-ink-2">Broj rezervacije</p>
            <p class="num text-3xl font-bold">{{ $reservation->number }}</p>
            <dl class="mt-6 grid gap-5 text-sm sm:grid-cols-2">
                <div><dt class="text-ink-2">Vozilo</dt><dd class="font-semibold">{{ $reservation->vehicle->name }}</dd></div>
                <div><dt class="text-ink-2">Trajanje</dt><dd class="font-semibold"><span class="num">{{ $reservation->days }}</span> {{ $reservation->days === 1 ? 'dan' : 'dana' }}</dd></div>
                <div><dt class="text-ink-2">Preuzimanje</dt><dd class="font-semibold"><span class="num">{{ $reservation->starts_on->format('d.m.Y') }}</span>, {{ $reservation->pickupLabel() }}</dd></div>
                <div><dt class="text-ink-2">Vraćanje</dt><dd class="font-semibold"><span class="num">{{ $reservation->ends_on->format('d.m.Y') }}</span>, {{ $reservation->returnLabel() }}</dd></div>
                @foreach ($reservation->extras as $extra)<div><dt class="text-ink-2">Dodatak</dt><dd class="font-semibold">{{ $extra->name }}</dd></div>@endforeach
            </dl>
            <div class="mt-6 flex items-baseline justify-between border-t border-line pt-4">
                <span class="font-semibold">Ukupno za plaćanje pri preuzimanju</span>
                <span class="text-2xl font-bold"><x-money :cents="$reservation->total" /></span>
            </div>
            <p class="mt-2 text-sm text-ink-2">Depozit pri preuzimanju: <x-money :cents="$reservation->deposit" />.</p>
        </div>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('home') }}" class="btn btn-primary">Nazad na početnu</a>
            <a href="{{ route('contact') }}" class="btn btn-ghost">Kontakt</a>
        </div>
    </div>
</x-layouts.app>
