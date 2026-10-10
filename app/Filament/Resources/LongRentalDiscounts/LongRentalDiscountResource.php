<?php

namespace App\Filament\Resources\LongRentalDiscounts;

use App\Filament\Resources\LongRentalDiscounts\Pages\CreateLongRentalDiscount;
use App\Filament\Resources\LongRentalDiscounts\Pages\EditLongRentalDiscount;
use App\Filament\Resources\LongRentalDiscounts\Pages\ListLongRentalDiscounts;
use App\Filament\Resources\LongRentalDiscounts\Schemas\LongRentalDiscountForm;
use App\Filament\Resources\LongRentalDiscounts\Tables\LongRentalDiscountsTable;
use App\Models\LongRentalDiscount;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LongRentalDiscountResource extends Resource
{
    protected static ?string $model = LongRentalDiscount::class;

    protected static ?string $modelLabel = 'Popust za duži najam';

    protected static ?string $pluralModelLabel = 'Popusti za duži najam';

    protected static ?string $navigationLabel = 'Popusti za duži najam';

    protected static string|\UnitEnum|null $navigationGroup = 'Cene';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    public static function form(Schema $schema): Schema
    {
        return LongRentalDiscountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LongRentalDiscountsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLongRentalDiscounts::route('/'),
            'create' => CreateLongRentalDiscount::route('/create'),
            'edit' => EditLongRentalDiscount::route('/{record}/edit'),
        ];
    }
}
