<?php

namespace App\Filament\Resources\VehicleBlocks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VehicleBlocksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('vehicle.name')->label('Vozilo')->searchable(),
                TextColumn::make('starts_on')->label('Od')->date('d.m.Y')->sortable(),
                TextColumn::make('ends_on')->label('Slobodno od')->date('d.m.Y')->sortable(),
                TextColumn::make('reason')->label('Razlog'),
            ])
            ->defaultSort('starts_on', 'desc')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
