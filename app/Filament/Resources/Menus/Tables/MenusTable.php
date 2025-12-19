<?php

namespace App\Filament\Resources\Menus\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use App\Models\MenuItem;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Illuminate\Support\Facades\DB;

class MenusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('menu.name')
                    ->searchable(),
                IconColumn::make('active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('syncFromParent')
                    ->label('Sincronizar com Pai')
                    ->modalHeading('Sincronizar Itens do Menu Pai')
                    ->modalDescription('Isso copiará todos os itens do menu pai para este menu, aplicando o fator de ajuste de preço informado.')
                    ->modalSubmitActionLabel('Sincronizar Agora')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->visible(fn($record) => $record->menu_id !== null)
                    ->form([
                        TextInput::make('factor')
                            ->label('Fator de Ajuste (%)')
                            ->placeholder('Ex: 10 ou -5')
                            ->helperText('Informe um valor positivo para acréscimo ou negativo para desconto.')
                            ->numeric()
                            ->default(0)
                            ->required(),
                        Toggle::make('clear_existing')
                            ->label('Remover itens atuais antes de sincronizar?')
                            ->default(true),
                    ])
                    ->action(function ($record, array $data) {
                        $factor = 1 + ($data['factor'] / 100);
                        $parentItems = MenuItem::where('menu_id', $record->menu_id)->get();

                        if ($parentItems->isEmpty()) {
                            Notification::make()
                                ->title('Aviso')
                                ->body('O menu pai não possui itens para sincronizar.')
                                ->warning()
                                ->send();
                            return;
                        }

                        DB::transaction(function () use ($record, $parentItems, $factor, $data) {
                            if ($data['clear_existing']) {
                                $record->menuItems()->delete();
                            }

                            foreach ($parentItems as $item) {
                                // Pega o preço do item no pai ou o preço base do produto
                                $originalPrice = $item->price ?? $item->product?->price ?? 0;

                                MenuItem::create([
                                    'menu_id' => $record->id,
                                    'product_id' => $item->product_id,
                                    'price' => $originalPrice * $factor,
                                    'active' => $item->active,
                                ]);
                            }
                        });

                        Notification::make()
                            ->title('Sincronização concluída!')
                            ->body("Foram copiados {$parentItems->count()} itens com sucesso.")
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
