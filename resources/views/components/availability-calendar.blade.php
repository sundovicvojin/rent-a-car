@props(['busy', 'months' => 3])
@php
    use Carbon\CarbonImmutable;

    $today = CarbonImmutable::today();
    $busyDays = [];
    foreach ($busy as $period) {
        for ($d = CarbonImmutable::parse($period['start']); $d->lt(CarbonImmutable::parse($period['end'])); $d = $d->addDay()) {
            $busyDays[$d->toDateString()] = true;
        }
    }
    $weekdays = ['Pon', 'Uto', 'Sre', 'Čet', 'Pet', 'Sub', 'Ned'];
    $monthNames = [1 => 'Januar', 'Februar', 'Mart', 'April', 'Maj', 'Jun', 'Jul', 'Avgust', 'Septembar', 'Oktobar', 'Novembar', 'Decembar'];
@endphp
<div {{ $attributes }}>
    <ul class="mb-4 flex flex-wrap gap-x-5 gap-y-2 text-sm text-ink-2" aria-label="Legenda kalendara">
        <li class="flex items-center gap-2"><span class="cal-day size-6" data-free="true" aria-hidden="true"></span>Slobodno</li>
        <li class="flex items-center gap-2"><span class="cal-day size-6" data-busy="true" aria-hidden="true"></span>Zauzeto</li>
    </ul>
    <div class="grid gap-6 md:grid-cols-3">
        @for ($m = 0; $m < $months; $m++)
            @php
                $first = $today->startOfMonth()->addMonths($m);
                $offset = $first->dayOfWeekIso - 1;
            @endphp
            <section aria-label="{{ $monthNames[$first->month] }} {{ $first->year }}">
                <h3 class="mb-2 font-display text-lg font-bold">{{ $monthNames[$first->month] }} <span class="num font-normal text-ink-2">{{ $first->year }}</span></h3>
                <div class="grid grid-cols-7 gap-1 text-center" role="grid">
                    @foreach ($weekdays as $wd)<div class="text-xs font-medium text-ink-2" role="columnheader">{{ $wd }}</div>@endforeach
                    @for ($i = 0; $i < $offset; $i++)<div role="gridcell" aria-hidden="true"></div>@endfor
                    @for ($day = $first; $day->month === $first->month; $day = $day->addDay())
                        @php
                            $past = $day->lt($today);
                            $isBusy = ! $past && isset($busyDays[$day->toDateString()]);
                        @endphp
                        <div role="gridcell" class="cal-day" @if ($past) data-past="true" @elseif ($isBusy) data-busy="true" @else data-free="true" @endif
                             aria-label="{{ $day->format('d.m.Y') }}: {{ $past ? 'prošlo' : ($isBusy ? 'zauzeto' : 'slobodno') }}">{{ $day->day }}</div>
                    @endfor
                </div>
            </section>
        @endfor
    </div>
</div>
