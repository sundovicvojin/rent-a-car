<?php

namespace App\Filament\Resources\VehicleBlocks\Schemas;

use App\Models\Reservation;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class VehicleBlockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('vehicle_id')->label('Vozilo')->relationship('vehicle', 'name')->required()->searchable()->preload()->live(),
            DatePicker::make('starts_on')->label('Početak blokade')->required()->native(false)->displayFormat('d.m.Y')->live(),
            DatePicker::make('ends_on')->label('Vozilo je ponovo slobodno od')->required()->native(false)->displayFormat('d.m.Y')
                ->afterOrEqual('starts_on')
                ->helperText('Poslednji dan blokade je dan pre ovog datuma.')
                ->rules([
                    fn (Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                        if (! $get('vehicle_id') || ! $get('starts_on')) {
                            return;
                        }
                        $conflict = Reservation::query()->blocking()->where('vehicle_id', $get('vehicle_id'))
                            ->where('starts_on', '<', $value)->where('ends_on', '>', $get('starts_on'))->exists();
                        if ($conflict) {
                            $fail('U tom periodu vozilo već ima aktivnu rezervaciju. Prvo je otkažite ili izmenite period.');
                        }
                    },
                ]),
            TextInput::make('reason')->label('Razlog')->placeholder('npr. Redovan servis')->maxLength(200),
        ]);
    }
}
