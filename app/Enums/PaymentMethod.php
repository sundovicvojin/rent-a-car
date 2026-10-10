<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasLabel, HasColor
{
    case PayOnPickup = 'pay_on_pickup';
    case Card = 'card';
    case BankTransfer = 'bank_transfer';

    public function getLabel(): string
    {
        return match ($this) {
            self::PayOnPickup => 'Plaćanje pri preuzimanju',
            self::Card => 'Kartica (faza 2)',
            self::BankTransfer => 'Virman (faza 2)',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PayOnPickup => 'gray',
            self::Card => 'gray',
            self::BankTransfer => 'gray',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $c) => [$c->value => $c->getLabel()])->all();
    }
}
