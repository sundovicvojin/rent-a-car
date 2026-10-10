<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Ime i prezime')->required()->maxLength(120),
            TextInput::make('phone')->label('Telefon')->tel()->required()->maxLength(40),
            TextInput::make('email')->label('Mejl')->email()->required()->maxLength(160),
            Textarea::make('notes')->label('Beleške')->rows(3),
        ]);
    }
}
