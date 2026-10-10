<?php

namespace App\Support;

/** Novac se uvek čuva kao ceo broj u centima; ovde se samo formatira i pretvara. */
final class Money
{
    public static function format(int $cents, bool $withSymbol = true): string
    {
        $formatted = number_format($cents / 100, $cents % 100 === 0 ? 0 : 2, ',', '.');

        return $withSymbol ? $formatted.' '.config('rentacar.currency_symbol') : $formatted;
    }

    public static function toCents(float|int|string|null $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    public static function toAmount(?int $cents): float
    {
        return ($cents ?? 0) / 100;
    }
}
