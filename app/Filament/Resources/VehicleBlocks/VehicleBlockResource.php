<?php

namespace App\Filament\Resources\VehicleBlocks;

use App\Filament\Resources\VehicleBlocks\Pages\CreateVehicleBlock;
use App\Filament\Resources\VehicleBlocks\Pages\EditVehicleBlock;
use App\Filament\Resources\VehicleBlocks\Pages\ListVehicleBlocks;
use App\Filament\Resources\VehicleBlocks\Schemas\VehicleBlockForm;
use App\Filament\Resources\VehicleBlocks\Tables\VehicleBlocksTable;
use App\Models\VehicleBlock;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class VehicleBlockResource extends Resource
{
    protected static ?string $model = VehicleBlock::class;

    protected static ?string $modelLabel = 'Blokada vozila';

    protected static ?string $pluralModelLabel = 'Blokade (servis)';

    protected static ?string $navigationLabel = 'Blokade (servis)';

    protected static string|\UnitEnum|null $navigationGroup = 'Rezervacije';

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrench;

    public static function form(Schema $schema): Schema
    {
        return VehicleBlockForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VehicleBlocksTable::configure($table);
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
            'index' => ListVehicleBlocks::route('/'),
            'create' => CreateVehicleBlock::route('/create'),
            'edit' => EditVehicleBlock::route('/{record}/edit'),
        ];
    }
}
