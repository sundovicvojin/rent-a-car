<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Naziv')->required()->maxLength(80),
            TextInput::make('slug')->label('Adresa (slug)')->unique(ignoreRecord: true)->helperText('Ostavite prazno za automatsko generisanje.'),
            TextInput::make('sort_order')->label('Redosled')->numeric()->default(0),
        ]);
    }
}
