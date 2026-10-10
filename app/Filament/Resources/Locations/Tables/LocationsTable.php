<?php

namespace App\Filament\Resources\Locations\Tables;

use App\Support\Money;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LocationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Naziv')->searchable(),
                TextColumn::make('address')->label('Adresa'),
                TextColumn::make('delivery_fee')->label('Doplata')->formatStateUsing(fn ($state) => Money::format((int) $state)),
                IconColumn::make('is_active')->label('Aktivna')->boolean(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
