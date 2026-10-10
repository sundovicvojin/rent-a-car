<?php

namespace App\Filament\Resources\LongRentalDiscounts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LongRentalDiscountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('min_days')->label('Od koliko dana')->numeric()->required()->minValue(2),
            TextInput::make('percent')->label('Popust (%)')->numeric()->required()->minValue(1)->maxValue(90),
            Toggle::make('is_active')->label('Aktivan')->default(true),
        ]);
    }
}
