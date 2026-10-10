<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Fuel: string implements HasLabel, HasColor
{
    case Petrol = 'petrol';
    case Diesel = 'diesel';
    case Hybrid = 'hybrid';
    case Electric = 'electric';
    case Lpg = 'lpg';

    public function getLabel(): string
    {
        return match ($this) {
            self::Petrol => 'Benzin',
            self::Diesel => 'Dizel',
            self::Hybrid => 'Hibrid',
            self::Electric => 'Električni',
            self::Lpg => 'Benzin + plin',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Petrol => 'gray',
            self::Diesel => 'gray',
            self::Hybrid => 'success',
            self::Electric => 'info',
            self::Lpg => 'gray',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $c) => [$c->value => $c->getLabel()])->all();
    }
}
