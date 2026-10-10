<?php

namespace App\Filament\Resources\Extras\Schemas;

use App\Enums\BillingType;
use App\Filament\Support\MoneyInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ExtraForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Naziv')->required()->maxLength(120),
            Textarea::make('description')->label('Opis')->rows(2),
            MoneyInput::make('price')->label('Cena')->required(),
            Select::make('billing')->label('Naplata')->options(BillingType::options())->required()->default('per_day'),
            Toggle::make('is_active')->label('Aktivan')->default(true),
        ]);
    }
}
