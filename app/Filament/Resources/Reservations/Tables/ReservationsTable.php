<?php

namespace App\Filament\Resources\Reservations\Tables;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReservationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['customer', 'vehicle']))
            ->columns([
                TextColumn::make('number')->label('Broj')->searchable()->sortable()->fontFamily('mono'),
                TextColumn::make('customer.name')->label('Klijent')->searchable(),
                TextColumn::make('vehicle.name')->label('Vozilo')->searchable(),
                TextColumn::make('starts_on')->label('Preuzimanje')->date('d.m.Y')->sortable(),
                TextColumn::make('ends_on')->label('Vraćanje')->date('d.m.Y')->sortable(),
                TextColumn::make('total')->label('Ukupno')->formatStateUsing(fn ($state) => Money::format((int) $state))->sortable(),
                TextColumn::make('status')->label('Status')->badge(),
                TextColumn::make('payment_status')->label('Plaćanje')->badge(),
                TextColumn::make('source')->label('Izvor')->formatStateUsing(fn ($state) => $state === 'admin' ? 'Admin' : 'Sajt')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('starts_on', 'desc')
            ->filters([
                SelectFilter::make('status')->label('Status')->options(ReservationStatus::options())->multiple(),
                SelectFilter::make('vehicle_id')->label('Vozilo')->relationship('vehicle', 'name'),
                Filter::make('upcoming')->label('Nadolazeće')->query(fn ($q) => $q->where('starts_on', '>=', now()->toDateString())->blocking()),
            ])
            ->recordActions([
                Action::make('confirm')->label('Potvrdi')->icon('heroicon-o-check')->color('info')
                    ->visible(fn (Reservation $r) => $r->status === ReservationStatus::Pending)
                    ->action(fn (Reservation $r) => $r->update(['status' => ReservationStatus::Confirmed])),
                Action::make('activate')->label('Preuzeto')->icon('heroicon-o-key')->color('success')
                    ->visible(fn (Reservation $r) => $r->status === ReservationStatus::Confirmed)
                    ->action(fn (Reservation $r) => $r->update(['status' => ReservationStatus::Active])),
                Action::make('complete')->label('Završi')->icon('heroicon-o-flag')->color('gray')
                    ->visible(fn (Reservation $r) => $r->status === ReservationStatus::Active)
                    ->action(fn (Reservation $r) => $r->update(['status' => ReservationStatus::Completed])),
                Action::make('cancel')->label('Otkaži')->icon('heroicon-o-x-mark')->color('danger')->requiresConfirmation()
                    ->modalHeading('Otkazati rezervaciju?')->modalDescription('Vozilo postaje slobodno za taj termin.')
                    ->visible(fn (Reservation $r) => in_array($r->status, [ReservationStatus::Pending, ReservationStatus::Confirmed], true))
                    ->action(fn (Reservation $r) => $r->update(['status' => ReservationStatus::Cancelled])),
                EditAction::make()->label('Otvori'),
            ]);
    }
}
