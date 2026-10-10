<?php

namespace App\Filament\Resources\Vehicles\Schemas;

use App\Enums\Fuel;
use App\Enums\Transmission;
use App\Enums\VehicleStatus;
use App\Filament\Support\MoneyInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VehicleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Osnovni podaci')->columns(2)->schema([
                TextInput::make('brand')->label('Marka')->required()->maxLength(80),
                TextInput::make('model')->label('Model')->required()->maxLength(80),
                TextInput::make('name')->label('Naziv')->helperText('Prikazuje se na sajtu. Ako ostane prazno, koristi se marka i model.')->maxLength(120),
                TextInput::make('year')->label('Godište')->numeric()->required()->minValue(1990)->maxValue((int) date('Y') + 1),
                Select::make('category_id')->label('Kategorija')->relationship('category', 'name')->required()->preload()->searchable()
                    ->createOptionForm([TextInput::make('name')->label('Naziv kategorije')->required()]),
                TextInput::make('plate')->label('Registarska oznaka')->required()->unique(ignoreRecord: true)->maxLength(20),
                Select::make('status')->label('Status')->options(VehicleStatus::options())->default('active')->required()
                    ->helperText('Samo aktivna vozila se prikazuju i mogu se rezervisati.'),
                Toggle::make('is_featured')->label('Istaknuto na početnoj')->inline(false),
            ]),
            Section::make('Specifikacije')->columns(3)->schema([
                TextInput::make('seats')->label('Broj sedišta')->numeric()->required()->minValue(1)->maxValue(20)->default(5),
                TextInput::make('doors')->label('Broj vrata')->numeric()->required()->minValue(2)->maxValue(6)->default(5),
                TextInput::make('luggage')->label('Broj kofera')->numeric()->required()->minValue(0)->maxValue(10)->default(2),
                Select::make('transmission')->label('Menjač')->options(Transmission::options())->required()->default('manual'),
                Select::make('fuel')->label('Gorivo')->options(Fuel::options())->required()->default('petrol'),
            ]),
            Section::make('Cena')->columns(2)->schema([
                MoneyInput::make('daily_price')->label('Cena po danu')->required(),
                MoneyInput::make('deposit')->label('Depozit')->required()->default(0),
            ]),
            Section::make('Opis')->schema([
                Textarea::make('description')->label('Opis vozila')->rows(5),
            ]),
            Section::make('Slike')
                ->description('Prevucite redove za promenu redosleda. Slike se automatski optimizuju i prave se manje WebP verzije.')
                ->schema([
                    Repeater::make('images')->label('Slike vozila')->relationship()
                        ->defaultItems(0)->orderColumn('sort_order')->reorderable()->addActionLabel('Dodaj sliku')
                        ->itemLabel(fn (array $state): ?string => $state['alt'] ?? null)
                        ->columns(2)->collapsible()
                        ->schema([
                            FileUpload::make('path')->label('Slika')->image()->disk('public')->directory('vehicles')
                                ->maxSize(10240)->required()->columnSpanFull(),
                            TextInput::make('alt')->label('Alt tekst (opis slike)')->required()->maxLength(160)
                                ->helperText('Kratko opišite šta je na slici, zbog čitača ekrana i pretrage.'),
                            Toggle::make('is_primary')->label('Glavna slika')->inline(false),
                        ]),
                ]),
        ]);
    }
}
