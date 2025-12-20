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
                        $query->where('menus.user_id', Auth::id())
                    )
                    ->live()
                    ->required(),
                Select::make('product_id')
                    ->relationship(
                        name: 'product',
                        titleAttribute: 'name',
                        modifyQueryUsing: function(Builder $query, Get $get, ?\App\Models\MenuItem $record = null) // Adiciona o parâmetro $record
                        {
                            $query->whereHas('category', fn($q) => $q->where('user_id', Auth::id()));

                            $selectedMenuId = $get('menu_id');
                            $currentMenuItemId = $record?->id; // Agora usa o $record direto

                            if ($selectedMenuId) {
                                $excludedProductIds = \App\Models\MenuItem::query()
                                                    ->where('menu_id', $selectedMenuId)
                                                    ->when($currentMenuItemId, fn($sq) => $sq->where('id', '!=', $currentMenuItemId))
                                                    ->pluck('product_id')
                                                    ->toArray();

                                $query->whereNotIn('id', $excludedProductIds);
                            }

                            return $query;
                        }
                    )
                    ->required(),
                TextInput::make('price')
                    ->numeric(),
                Toggle::make('active')
                    ->required(),
            ]);
    }
}
