<?php

namespace App\Filament\Resources\LongRentalDiscounts\Pages;

use App\Filament\Resources\LongRentalDiscounts\LongRentalDiscountResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLongRentalDiscount extends EditRecord
{
    protected static string $resource = LongRentalDiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
