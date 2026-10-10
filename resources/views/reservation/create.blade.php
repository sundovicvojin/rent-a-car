<x-layouts.app title="Rezervacija" description="Rezervišite vozilo u pet jednostavnih koraka. Plaćanje pri preuzimanju." :livewire="true" :noindex="true">
    <div class="wrap pb-4 pt-10">
        <p class="eyebrow">Rezervacija</p>
        <h1 class="h2 mt-2 mb-8" style="font-size: clamp(2rem, 4.5vw, 3.4rem)">Rezervišite vozilo</h1>
        <livewire:reservation-wizard />
    </div>
</x-layouts.app>
