<x-mail::message>
# Nova rezervacija {{ $reservation->number }}

<x-mail::panel>
**Klijent:** {{ $reservation->customer->name }}, {{ $reservation->customer->phone }}, {{ $reservation->customer->email }}
**Vozilo:** {{ $reservation->vehicle->name }} ({{ $reservation->vehicle->plate }})
**Preuzimanje:** {{ $reservation->starts_on->format('d.m.Y') }}, {{ $reservation->pickupLocation->name }}
**Vraćanje:** {{ $reservation->ends_on->format('d.m.Y') }}, {{ $reservation->returnLocation->name }}
**Ukupno:** {{ \App\Support\Money::format($reservation->total) }} ({{ $reservation->days }} dana)
@if ($reservation->customer_note)
**Napomena:** {{ $reservation->customer_note }}
@endif
</x-mail::panel>

<x-mail::button :url="$adminUrl">Otvori u admin panelu</x-mail::button>
</x-mail::message>
