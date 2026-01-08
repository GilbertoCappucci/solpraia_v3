<?php

namespace App\Filament\Resources\Menus\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MenuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Hidden::make('admin_id')
                    ->default(Auth::id()),
                TextInput::make('name')
                    ->required(),
                Select::make('menu_id')
                    ->label('Menu Pai')
                    ->relationship(
                        name: 'menu',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn(Builder $query, ?\App\Models\Menu $record) =>
                        $query->where('menus.admin_id', Auth::id())
                            ->whereNull('menus.menu_id')
                            ->when($record, fn($q) => $q->where('menus.id', '!=', $record->id))
                    ),
                Toggle::make('active')
                    ->required(),
            ]);
    }
}
