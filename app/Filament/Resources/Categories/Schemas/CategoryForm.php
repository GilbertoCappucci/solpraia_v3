<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Hidden::make('user_id')
                    ->default(Auth::id()),
                \Filament\Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name', fn($query) => $query->whereNull('category_id')->where('user_id', Auth::id()))
                    ->searchable()
                    ->preload(),
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),
                Toggle::make('active')
                    ->required(),
            ]);
    }
}
