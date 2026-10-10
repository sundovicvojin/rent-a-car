<?php

namespace App\Filament\Resources\SeasonalPrices\Pages;

use App\Filament\Resources\SeasonalPrices\SeasonalPriceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSeasonalPrices extends ListRecords
{
    protected static string $resource = SeasonalPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
