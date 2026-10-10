<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PriceType: string implements HasLabel, HasColor
{
    case Percent = 'percent';
    case Fixed = 'fixed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Percent => 'Procenat',
            self::Fixed => 'Fiksna cena po danu',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Percent => 'gray',
            self::Fixed => 'gray',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $c) => [$c->value => $c->getLabel()])->all();
    }
}
