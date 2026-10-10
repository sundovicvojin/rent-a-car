<?php

namespace App\Filament\Widgets;

use App\Enums\ReservationStatus;
use App\Filament\Resources\Reservations\ReservationResource;
use App\Models\Reservation;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class TodayPickups extends TableWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = 'Današnja preuzimanja';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Reservation::query()->with(['customer', 'vehicle', 'pickupLocation'])
                ->whereDate('starts_on', now()->toDateString())
                ->whereIn('status', [ReservationStatus::Pending->value, ReservationStatus::Confirmed->value, ReservationStatus::Active->value]))
            ->columns([
                TextColumn::make('vehicle.name')->label('Vozilo'),
                TextColumn::make('customer.name')->label('Klijent'),
                TextColumn::make('pickupLocation.name')->label('Lokacija'),
                TextColumn::make('status')->label('Status')->badge(),
            ])
            ->recordActions([Action::make('open')->label('Otvori')->url(fn ($record) => ReservationResource::getUrl('edit', ['record' => $record]))])
            ->emptyStateHeading('Nema preuzimanja danas')
            ->paginated(false);
    }
}
