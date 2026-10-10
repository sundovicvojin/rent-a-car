<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Reservations\ReservationResource;
use App\Models\Reservation;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class UpcomingReservations extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Nadolazeće rezervacije';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Reservation::query()->blocking()->with(['customer', 'vehicle'])
                ->where('starts_on', '>', now()->toDateString())->orderBy('starts_on')->limit(10))
            ->columns([
                TextColumn::make('number')->label('Broj')->fontFamily('mono'),
                TextColumn::make('starts_on')->label('Preuzimanje')->date('d.m.Y'),
                TextColumn::make('ends_on')->label('Vraćanje')->date('d.m.Y'),
                TextColumn::make('vehicle.name')->label('Vozilo'),
                TextColumn::make('customer.name')->label('Klijent'),
                TextColumn::make('total')->label('Ukupno')->formatStateUsing(fn ($state) => Money::format((int) $state)),
                TextColumn::make('status')->label('Status')->badge(),
            ])
            ->recordActions([Action::make('open')->label('Otvori')->url(fn ($record) => ReservationResource::getUrl('edit', ['record' => $record]))])
            ->emptyStateHeading('Nema nadolazećih rezervacija')
            ->paginated(false);
    }
}
