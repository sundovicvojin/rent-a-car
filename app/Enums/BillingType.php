<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BillingType: string implements HasLabel, HasColor
{
    case PerDay = 'per_day';
    case Once = 'once';

    public function getLabel(): string
    {
        return match ($this) {
            self::PerDay => 'Po danu',
            self::Once => 'Jednokratno',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PerDay => 'gray',
            self::Once => 'gray',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $c) => [$c->value => $c->getLabel()])->all();
    }
}
