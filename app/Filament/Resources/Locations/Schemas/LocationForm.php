<?php

namespace App\Filament\Resources\Locations\Schemas;

use App\Filament\Support\MoneyInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Naziv')->required()->maxLength(120),
            TextInput::make('address')->label('Adresa')->maxLength(200),
            MoneyInput::make('delivery_fee')->label('Doplata za dostavu')->required()->default(0)
                ->helperText('Naplaćuje se i za preuzimanje i za vraćanje na ovoj lokaciji. 0 za sedište firme.'),
            TextInput::make('latitude')->label('Geografska širina')->numeric()->minValue(-90)->maxValue(90),
            TextInput::make('longitude')->label('Geografska dužina')->numeric()->minValue(-180)->maxValue(180),
            Toggle::make('is_active')->label('Aktivna')->default(true),
        ]);
    }
}
