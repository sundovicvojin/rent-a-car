<x-filament-panels::page>
    @php
        $months = [1 => 'Januar', 'Februar', 'Mart', 'April', 'Maj', 'Jun', 'Jul', 'Avgust', 'Septembar', 'Oktobar', 'Novembar', 'Decembar'];
        $palette = ['pending' => 'var(--warning-500)', 'confirmed' => 'var(--primary-500)', 'active' => 'var(--success-500)', 'completed' => 'var(--gray-400)', 'block' => 'var(--danger-500)'];
        $names = ['pending' => 'Na čekanju', 'confirmed' => 'Potvrđena', 'active' => 'Aktivna', 'completed' => 'Završena', 'block' => 'Blokada (servis)'];
    @endphp

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <x-filament::button color="gray" size="sm" wire:click="previousMonth">← Prethodni</x-filament::button>
            <x-filament::button color="gray" size="sm" wire:click="today">Danas</x-filament::button>
            <x-filament::button color="gray" size="sm" wire:click="nextMonth">Sledeći →</x-filament::button>
        </div>
        <h2 class="text-xl font-bold">{{ $months[$first->month] }} {{ $first->year }}</h2>
        <ul class="flex flex-wrap gap-4 text-sm" aria-label="Legenda">
            @foreach ($names as $key => $label)
                <li class="flex items-center gap-2"><span class="inline-block size-3 rounded-sm" style="background: {{ $palette[$key] }}"></span>{{ $label }}</li>
            @endforeach
        </ul>
    </div>

    <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <table class="w-full border-collapse text-xs" style="min-width: 960px">
            <thead>
                <tr>
                    <th scope="col" class="sticky left-0 z-10 bg-white p-2 text-left font-semibold dark:bg-gray-900" style="min-width: 160px">Vozilo</th>
                    @foreach ($days as $d)
                        @php $date = $first->addDays($d - 1); @endphp
                        <th scope="col" class="p-1 text-center font-medium {{ $date->isToday() ? 'text-primary-600' : '' }} {{ $date->isWeekend() ? 'bg-gray-50 dark:bg-white/5' : '' }}">{{ $d }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($vehicles as $vehicle)
                    <tr class="border-t border-gray-200 dark:border-white/10">
                        <th scope="row" class="sticky left-0 z-10 bg-white p-2 text-left font-medium dark:bg-gray-900">{{ $vehicle->name }}<span class="block font-normal text-gray-500">{{ $vehicle->plate }}</span></th>
                        @foreach ($days as $d)
                            @php
                                $date = $first->addDays($d - 1)->toDateString();
                                $cell = $cells[$vehicle->id][$date] ?? null;
                            @endphp
                            <td class="h-9 p-0 text-center {{ $first->addDays($d - 1)->isWeekend() ? 'bg-gray-50 dark:bg-white/5' : '' }}">
                                @if ($cell)
                                    @if ($cell['id'])
                                        <a href="{{ \App\Filament\Resources\Reservations\ReservationResource::getUrl('edit', ['record' => $cell['id']]) }}" title="{{ $cell['label'] }}" aria-label="{{ $date }}: {{ $cell['label'] }}" class="block h-7 w-full" style="background: {{ $palette[$cell['type']] ?? $palette['confirmed'] }}"></a>
                                    @else
                                        <span title="{{ $cell['label'] }}" aria-label="{{ $date }}: {{ $cell['label'] }}" class="block h-7 w-full" style="background: repeating-linear-gradient(135deg, {{ $palette['block'] }}, {{ $palette['block'] }} 4px, transparent 4px, transparent 8px)"></span>
                                    @endif
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
