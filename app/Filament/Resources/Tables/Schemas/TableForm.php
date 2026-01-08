<?php

namespace App\Filament\Resources\Tables\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TableForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('name')
                    ->default(null),
                TextInput::make('number')
                    ->required()
                    ->numeric(),
                Select::make('status')
                    ->options([
            'close' => 'Close',
            'free' => 'Free',
            'occupied' => 'Occupied',
            'reserved' => 'Reserved',
            'releasing' => 'Releasing',
        ])
                    ->default('free')
                    ->required(),
            ]);
    }
}
