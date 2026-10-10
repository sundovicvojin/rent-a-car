<?php

namespace App\Filament\Resources\VehicleBlocks\Pages;

use App\Filament\Resources\VehicleBlocks\VehicleBlockResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVehicleBlock extends EditRecord
{
    protected static string $resource = VehicleBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
