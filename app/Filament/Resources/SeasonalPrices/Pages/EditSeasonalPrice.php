<?php

namespace App\Filament\Resources\SeasonalPrices\Pages;

use App\Filament\Resources\SeasonalPrices\SeasonalPriceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSeasonalPrice extends EditRecord
{
    protected static string $resource = SeasonalPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
