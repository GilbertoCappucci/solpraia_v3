<?php

namespace App\Filament\Resources\Tabs;

use App\Filament\Resources\Tabs\Pages\CreateTab;
use App\Filament\Resources\Tabs\Pages\EditTab;
use App\Filament\Resources\Tabs\Pages\ListTabs;
use App\Filament\Resources\Tabs\Schemas\TabForm;
use App\Filament\Resources\Tabs\Tables\TabsTable;
use App\Models\Tab;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TabResource extends Resource
{
    protected static ?string $model = Tab::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return TabForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TabsTable::configure($table);
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
            'index' => ListTabs::route('/'),
            'create' => CreateTab::route('/create'),
            'edit' => EditTab::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
