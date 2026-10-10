<x-mail::message>
# Hvala, {{ $reservation->customer->name }}!

Vaša rezervacija **{{ $reservation->number }}** je primljena i čeka potvrdu. Javićemo vam se mejlom ili telefonom.

<x-mail::panel>
**Vozilo:** {{ $reservation->vehicle->name }}
**Preuzimanje:** {{ $reservation->starts_on->format('d.m.Y') }}, {{ $reservation->pickupLabel() }}
**Vraćanje:** {{ $reservation->ends_on->format('d.m.Y') }}, {{ $reservation->returnLabel() }}
**Broj dana:** {{ $reservation->days }}
@foreach ($reservation->extras as $extra)
**Dodatak:** {{ $extra->name }}
@endforeach
**Ukupno:** {{ \App\Support\Money::format($reservation->total) }}
**Depozit pri preuzimanju:** {{ \App\Support\Money::format($reservation->deposit) }}
</x-mail::panel>

Plaćanje se vrši pri preuzimanju vozila.

<x-mail::button :url="$url">Pogledaj rezervaciju</x-mail::button>

{{ $company->name }}@if ($company->phone), {{ $company->phone }}@endif
</x-mail::message>
