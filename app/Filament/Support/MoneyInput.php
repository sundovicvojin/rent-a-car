<?php

namespace App\Filament\Support;

use App\Support\Money;
use Filament\Forms\Components\TextInput;

/** Polje za iznos u evrima; u bazi se čuva ceo broj centi. */
class MoneyInput
{
    public static function make(string $name): TextInput
    {
        return TextInput::make($name)
            ->numeric()
            ->minValue(0)
            ->step(0.01)
            ->prefix(config('rentacar.currency_symbol'))
            ->formatStateUsing(fn ($state) => $state === null ? null : Money::toAmount((int) $state))
            ->dehydrateStateUsing(fn ($state) => $state === null || $state === '' ? null : Money::toCents($state));
    }
}
