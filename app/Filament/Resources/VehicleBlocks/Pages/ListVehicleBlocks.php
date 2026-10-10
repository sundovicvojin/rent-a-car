<?php

namespace App\Filament\Resources\VehicleBlocks\Pages;

use App\Filament\Resources\VehicleBlocks\VehicleBlockResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVehicleBlocks extends ListRecords
{
    protected static string $resource = VehicleBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
