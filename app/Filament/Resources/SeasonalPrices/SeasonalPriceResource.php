<?php

namespace App\Filament\Resources\SeasonalPrices;

use App\Filament\Resources\SeasonalPrices\Pages\CreateSeasonalPrice;
use App\Filament\Resources\SeasonalPrices\Pages\EditSeasonalPrice;
use App\Filament\Resources\SeasonalPrices\Pages\ListSeasonalPrices;
use App\Filament\Resources\SeasonalPrices\Schemas\SeasonalPriceForm;
use App\Filament\Resources\SeasonalPrices\Tables\SeasonalPricesTable;
use App\Models\SeasonalPrice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SeasonalPriceResource extends Resource
{
    protected static ?string $model = SeasonalPrice::class;

    protected static ?string $modelLabel = 'Sezonsko pravilo';

    protected static ?string $pluralModelLabel = 'Sezonske cene';

    protected static ?string $navigationLabel = 'Sezonske cene';

    protected static string|\UnitEnum|null $navigationGroup = 'Cene';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSun;

    public static function form(Schema $schema): Schema
    {
        return SeasonalPriceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SeasonalPricesTable::configure($table);
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
            'index' => ListSeasonalPrices::route('/'),
            'create' => CreateSeasonalPrice::route('/create'),
            'edit' => EditSeasonalPrice::route('/{record}/edit'),
        ];
    }
}
