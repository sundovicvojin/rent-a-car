@props(['vehicle', 'total' => null, 'days' => null, 'query' => []])
<article data-reveal class="vehicle-card group card relative flex flex-col overflow-hidden hover:shadow-lg" style="border-top-right-radius: 40px;">
    <div class="relative aspect-[16/10] overflow-hidden bg-surface-2">
        <x-vehicle-image :image="$vehicle->primaryImage" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.03] motion-reduce:transition-none" />
        <span class="badge badge-signal absolute left-3 top-3">{{ $vehicle->category->name }}</span>
    </div>
    <div class="flex flex-1 flex-col gap-4 p-5">
        <div>
            <h3 class="text-xl font-bold">
                <a href="{{ route('fleet.show', ['vehicle' => $vehicle] + $query) }}" class="text-ink no-underline after:absolute after:inset-0 after:content-['']">{{ $vehicle->name }}</a>
            </h3>
            <p class="num text-sm text-ink-2">{{ $vehicle->year }}</p>
        </div>
        <ul class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-ink-2" aria-label="Specifikacije">
            <li class="flex items-center gap-1.5"><x-ico name="gear" class="size-4" />{{ $vehicle->transmission->getLabel() }}</li>
            <li class="flex items-center gap-1.5"><x-ico name="fuel" class="size-4" />{{ $vehicle->fuel->getLabel() }}</li>
            <li class="flex items-center gap-1.5"><x-ico name="seats" class="size-4" /><span class="num">{{ $vehicle->seats }}</span> sedišta</li>
        </ul>
        <div class="mt-auto flex items-end justify-between gap-3 border-t border-line pt-4">
            <div>
                <p class="text-xs text-ink-2">Cena po danu</p>
                <p class="text-2xl font-bold"><x-money :cents="$vehicle->daily_price" /></p>
            </div>
            @if ($total)
                <div class="text-right">
                    <p class="text-xs text-ink-2">Ukupno za <span class="num">{{ $days }}</span> {{ $days === 1 ? 'dan' : 'dana' }}</p>
                    <p class="text-lg font-semibold"><x-money :cents="$total" /></p>
                </div>
            @endif
        </div>
    </div>
</article>
