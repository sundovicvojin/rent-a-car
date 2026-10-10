<?php

namespace App\Filament\Resources\Reservations\Schemas;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ReservationStatus;
use App\Models\Extra;
use App\Models\Location;
use App\Models\Vehicle;
use App\Support\Money;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReservationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            // --- Ručno pravljenje rezervacije (cena se računa na serveru) ---
            Section::make('Nova rezervacija')
                ->description('Cena, popust i dostava se računaju automatski pri čuvanju.')
                ->columns(2)
                ->visibleOn('create')
                ->schema([
                    Select::make('customer_id')->label('Klijent')->relationship('customer', 'name')->searchable()->preload()->required()
                        ->createOptionForm([
                            TextInput::make('name')->label('Ime i prezime')->required()->maxLength(120),
                            TextInput::make('phone')->label('Telefon')->tel()->required()->maxLength(40),
                            TextInput::make('email')->label('Mejl')->email()->required()->maxLength(160),
                        ]),
                    Select::make('vehicle_id')->label('Vozilo')->required()->searchable()
                        ->options(fn () => Vehicle::query()->bookable()->orderBy('name')->pluck('name', 'id')),
                    DatePicker::make('starts_on')->label('Preuzimanje')->required()->native(false)->displayFormat('d.m.Y')->live(),
                    DatePicker::make('ends_on')->label('Vraćanje')->required()->native(false)->displayFormat('d.m.Y')->afterOrEqual('starts_on'),
                    Select::make('pickup_location_id')->label('Lokacija preuzimanja')->required()
                        ->options(fn () => Location::query()->active()->orderBy('id')->pluck('name', 'id')),
                    Select::make('return_location_id')->label('Lokacija vraćanja')->required()
                        ->options(fn () => Location::query()->active()->orderBy('id')->pluck('name', 'id')),
                    CheckboxList::make('extra_ids')->label('Dodaci')->columnSpanFull()->columns(2)->dehydrated(false)
                        ->options(fn () => Extra::query()->active()->orderBy('id')->pluck('name', 'id')),
                    Toggle::make('send_mail')->label('Pošalji mejl potvrde klijentu i firmi')->dehydrated(false),
                ]),

            // --- Pregled postojeće rezervacije ---
            Section::make('Rezervacija')
                ->columns(3)
                ->visibleOn('edit')
                ->schema([
                    TextInput::make('number')->label('Broj rezervacije')->disabled()->dehydrated(false),
                    TextInput::make('customer_name')->label('Klijent')->disabled()->dehydrated(false)
                        ->formatStateUsing(fn ($record) => $record?->customer?->name.' ('.$record?->customer?->phone.')'),
                    TextInput::make('vehicle_name')->label('Vozilo')->disabled()->dehydrated(false)
                        ->formatStateUsing(fn ($record) => $record?->vehicle?->name),
                    TextInput::make('period')->label('Termin')->disabled()->dehydrated(false)
                        ->formatStateUsing(fn ($record) => $record ? $record->starts_on->format('d.m.Y').' – '.$record->ends_on->format('d.m.Y').' ('.$record->days.' d.)' : null),
                    TextInput::make('locations')->label('Lokacije')->disabled()->dehydrated(false)
                        ->formatStateUsing(fn ($record) => $record ? $record->pickupLocation->name.' → '.$record->returnLocation->name : null),
                    TextInput::make('total_label')->label('Ukupno')->disabled()->dehydrated(false)
                        ->formatStateUsing(fn ($record) => $record ? Money::format($record->total).' (depozit '.Money::format($record->deposit).')' : null),
                    Textarea::make('customer_note')->label('Napomena klijenta')->disabled()->dehydrated(false)->columnSpanFull()->rows(2),
                ]),

            Section::make('Status i plaćanje')->columns(3)->schema([
                Select::make('status')->label('Status')->options(ReservationStatus::options())->required()->default('confirmed'),
                Select::make('payment_method')->label('Način plaćanja')->options(PaymentMethod::options())->required()->default('pay_on_pickup'),
                Select::make('payment_status')->label('Status plaćanja')->options(PaymentStatus::options())->required()->default('unpaid'),
                Textarea::make('internal_note')->label('Interna napomena')->rows(3)->columnSpanFull(),
            ]),
        ]);
    }
}
