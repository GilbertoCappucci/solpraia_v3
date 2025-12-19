<?php

namespace App\Filament\Resources\MenuItems\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MenuItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('menu_id')
                    ->relationship(
                        name: 'menu',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn(Builder $query) =>
                        $query->where('menus.user_id', Auth::id())->whereNull('menus.menu_id')
                    )
                    ->live()
                    ->required(),
                Select::make('product_id')
                    ->relationship(
                        name: 'product',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn(Builder $query, Get $get) =>
                        $query->whereHas('category', fn($q) => $q->where('user_id', Auth::id()))
                            ->when(
                                $get('menu_id'),
                                fn($q, $menuId) =>
                                $q->whereDoesntHave('menuItems', fn($sq) => $sq->where('menu_id', $menuId))
                            )
                    )
                    ->required(),
                TextInput::make('price')
                    ->numeric(),
                Toggle::make('active')
                    ->required(),
            ]);
    }
}
