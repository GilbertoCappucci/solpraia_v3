<?php

namespace App\Filament\Resources\GlobalSettings;

use App\Filament\Resources\GlobalSettings\Pages\EditGlobalSettings;
use App\Filament\Resources\GlobalSettings\Schemas\GlobalSettingsForm;
use App\Models\GlobalSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GlobalSettingsResource extends Resource
{
    protected static ?string $model = GlobalSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Configurações Globais';

    protected static ?string $modelLabel = 'Configuração Global';

    protected static ?string $pluralModelLabel = 'Configurações Globais';

    protected static ?int $navigationSort = 99;

    public static function form(Schema $schema): Schema
    {
        return GlobalSettingsForm::configure($schema);
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
            'index' => EditGlobalSettings::route('/'),
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
