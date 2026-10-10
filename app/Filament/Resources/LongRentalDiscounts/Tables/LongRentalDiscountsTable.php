<?php

namespace App\Filament\Resources\LongRentalDiscounts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LongRentalDiscountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('min_days')->label('Od (dana)')->sortable(),
                TextColumn::make('percent')->label('Popust')->suffix(' %'),
                IconColumn::make('is_active')->label('Aktivan')->boolean(),
            ])
            ->defaultSort('min_days')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
