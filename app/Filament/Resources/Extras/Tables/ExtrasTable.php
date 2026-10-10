<?php

namespace App\Filament\Resources\Extras\Tables;

use App\Support\Money;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExtrasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Naziv')->searchable(),
                TextColumn::make('price')->label('Cena')->formatStateUsing(fn ($state) => Money::format((int) $state)),
                TextColumn::make('billing')->label('Naplata')->badge(),
                IconColumn::make('is_active')->label('Aktivan')->boolean(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
