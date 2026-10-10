<?php

namespace App\Filament\Widgets;

use App\Enums\ReservationStatus;
use App\Filament\Resources\Reservations\ReservationResource;
use App\Models\Reservation;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class TodayReturns extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = 'Današnja vraćanja';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Reservation::query()->with(['customer', 'vehicle', 'returnLocation'])
                ->whereDate('ends_on', now()->toDateString())
                ->where('status', ReservationStatus::Active->value))
            ->columns([
                TextColumn::make('vehicle.name')->label('Vozilo'),
                TextColumn::make('customer.name')->label('Klijent'),
                TextColumn::make('returnLocation.name')->label('Lokacija'),
                TextColumn::make('status')->label('Status')->badge(),
            ])
            ->recordActions([Action::make('open')->label('Otvori')->url(fn ($record) => ReservationResource::getUrl('edit', ['record' => $record]))])
            ->emptyStateHeading('Nema vraćanja danas')
            ->paginated(false);
    }
}
