<?php

namespace App\Filament\Resources\LongRentalDiscounts\Pages;

use App\Filament\Resources\LongRentalDiscounts\LongRentalDiscountResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLongRentalDiscounts extends ListRecords
{
    protected static string $resource = LongRentalDiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
