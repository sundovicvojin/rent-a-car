<?php

namespace App\Filament\Resources\Customers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Ime i prezime')->searchable()->sortable(),
                TextColumn::make('phone')->label('Telefon')->searchable(),
                TextColumn::make('email')->label('Mejl')->searchable(),
                TextColumn::make('reservations_count')->label('Rezervacija')->counts('reservations')->sortable(),
                TextColumn::make('created_at')->label('Dodat')->date('d.m.Y')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
