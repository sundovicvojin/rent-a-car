<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use InvalidArgumentException;

/**
 * Period najma po datumima. Kraj je isključiv: 14. do 17. su 3 dana, a vozilo je
 * 17. ponovo slobodno. Najmanje trajanje je 1 dan (isti datum se tretira kao 1 dan).
 */
final class DateRange
{
    public readonly CarbonImmutable $start;

    public readonly CarbonImmutable $end;

    public function __construct(CarbonInterface|string $start, CarbonInterface|string $end)
    {
        $start = CarbonImmutable::parse($start)->startOfDay();
        $end = CarbonImmutable::parse($end)->startOfDay();

        if ($end->lessThan($start)) {
            throw new InvalidArgumentException('Datum vraćanja ne može biti pre datuma preuzimanja.');
        }

        $min = $start->addDays(config('rentacar.min_rental_days', 1));

        $this->start = $start;
        $this->end = $end->lessThan($min) ? $min : $end;
    }

    public function days(): int
    {
        return (int) $this->start->diffInDays($this->end);
    }

    /** @return \Generator<CarbonImmutable> svaki dan najma (bez dana vraćanja) */
    public function eachDay(): \Generator
    {
        for ($d = $this->start; $d->lessThan($this->end); $d = $d->addDay()) {
            yield $d;
        }
    }

    public function overlaps(self $other): bool
    {
        return $this->start->lessThan($other->end) && $this->end->greaterThan($other->start);
    }
}
