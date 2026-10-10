<?php

namespace App\Filament\Resources\SeasonalPrices\Tables;

use App\Enums\PriceType;
use App\Support\Money;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SeasonalPricesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Naziv')->searchable(),
                TextColumn::make('vehicle.name')->label('Vozilo')->placeholder('Sva vozila'),
                TextColumn::make('starts_on')->label('Od')->date('d.m.Y')->sortable(),
                TextColumn::make('ends_on')->label('Do')->date('d.m.Y')->sortable(),
                TextColumn::make('value')->label('Vrednost')->formatStateUsing(fn ($state, $record) => $record->type === PriceType::Fixed
                    ? Money::format((int) $state).' / dan'
                    : ($state > 0 ? '+' : '').$state.' %'),
                IconColumn::make('is_active')->label('Aktivno')->boolean(),
            ])
            ->defaultSort('starts_on')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
