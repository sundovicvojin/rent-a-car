<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use App\Filament\Resources\Reservations\ReservationResource;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReservationsRelationManager extends RelationManager
{
    protected static string $relationship = 'reservations';

    protected static ?string $title = 'Istorija rezervacija';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('number')
            ->columns([
                TextColumn::make('number')->label('Broj')->fontFamily('mono'),
                TextColumn::make('vehicle.name')->label('Vozilo'),
                TextColumn::make('starts_on')->label('Preuzimanje')->date('d.m.Y'),
                TextColumn::make('ends_on')->label('Vraćanje')->date('d.m.Y'),
                TextColumn::make('total')->label('Ukupno')->formatStateUsing(fn ($state) => Money::format((int) $state)),
                TextColumn::make('status')->label('Status')->badge(),
            ])
            ->defaultSort('starts_on', 'desc')
            ->recordActions([
                Action::make('open')->label('Otvori')->url(fn ($record) => ReservationResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
