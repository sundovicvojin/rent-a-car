<?php

namespace App\Filament\Resources\SeasonalPrices\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class SeasonalPriceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Naziv pravila')->required()->maxLength(120)->placeholder('npr. Letnja sezona'),
            Select::make('vehicle_id')->label('Vozilo')->relationship('vehicle', 'name')->searchable()->preload()
                ->placeholder('Sva vozila')->helperText('Pravilo za jedno vozilo ima prednost nad pravilom za sva vozila.'),
            DatePicker::make('starts_on')->label('Početak')->required()->native(false)->displayFormat('d.m.Y'),
            DatePicker::make('ends_on')->label('Kraj (uključivo)')->required()->native(false)->displayFormat('d.m.Y')->afterOrEqual('starts_on'),
            Select::make('type')->label('Vrsta pravila')->options(\App\Enums\PriceType::options())->required()->default('percent')->live(),
            TextInput::make('value')->label(fn (Get $get) => $get('type') === 'fixed' ? 'Cena po danu (centi)' : 'Procenat (npr. 20 ili -10)')
                ->numeric()->required()
                ->helperText(fn (Get $get) => $get('type') === 'fixed' ? 'Unosi se u centima: 4500 = 45 €.' : 'Pozitivan broj poskupljuje, negativan pojeftinjuje osnovnu cenu.'),
            Toggle::make('is_active')->label('Aktivno')->default(true),
        ]);
    }
}
