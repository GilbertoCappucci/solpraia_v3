<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\User;
use BackedEnum;
use Dom\Text;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use App\Enums\RoleEnum;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Facades\Hash;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('admin_id')
                    ->default(Auth::id()),
                
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Nome'),
                
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->label('E-mail'),
                
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->label('Senha')
                    ->maxLength(255),
                
                TextInput::make('confirm_password')
                    ->password()
                    ->revealable()
                    ->label('Confirmar Senha')
                    ->same('password')
                    ->dehydrated(false)
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->maxLength(255),

                Select::make('role')
                    ->options([
                        RoleEnum::ADMIN->value => 'Admin',
                        RoleEnum::DEVICE->value => 'Device',
                    ])
                    ->required()
                    ->default(RoleEnum::DEVICE->value)
                    ->label('Função'),
                
                Toggle::make('active')
                    ->label('Ativo')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('role')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(fn (User $record): string => 
                        Cache::has('user-is-online-' . $record->id) ? 'Online' : 'Offline'
                    )
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Online' => 'success',
                        'Offline' => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i'),
                ToggleColumn::make('active')
                    ->label('Ativo')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageUsers::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('admin_id', Auth::id())
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
