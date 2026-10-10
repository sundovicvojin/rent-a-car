<?php

namespace App\Filament\Resources\Vehicles\Tables;

use App\Enums\Fuel;
use App\Enums\Transmission;
use App\Enums\VehicleStatus;
use App\Support\Money;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VehiclesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Naziv')->searchable()->sortable(),
                TextColumn::make('plate')->label('Tablice')->searchable(),
                TextColumn::make('category.name')->label('Kategorija')->sortable(),
                TextColumn::make('year')->label('Godište')->sortable(),
                TextColumn::make('transmission')->label('Menjač')->badge(),
                TextColumn::make('fuel')->label('Gorivo')->badge(),
                TextColumn::make('daily_price')->label('Cena / dan')->formatStateUsing(fn ($state) => Money::format((int) $state))->sortable(),
                TextColumn::make('status')->label('Status')->badge(),
                IconColumn::make('is_featured')->label('Istaknuto')->boolean(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status')->options(VehicleStatus::options()),
                SelectFilter::make('category_id')->label('Kategorija')->relationship('category', 'name'),
                SelectFilter::make('transmission')->label('Menjač')->options(Transmission::options()),
                SelectFilter::make('fuel')->label('Gorivo')->options(Fuel::options()),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('name');
    }
}
