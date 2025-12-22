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
use App\Models\Category;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
                        Select::make('category_id')
                            ->label('Categoria Específica')
                            ->options(Category::where('user_id', Auth::id())->pluck('name', 'id'))
                            ->placeholder('Todas as Categorias')
                            ->searchable(),
                        TextInput::make('factor')
                            ->label('Fator de Ajuste (%)')
                            ->placeholder('Ex: 10 ou -5')
                            ->helperText('Informe um valor positivo para acréscimo ou negativo para desconto.')
                            ->numeric()
                            ->default(0)
                            ->required(),
                        Toggle::make('clear_existing')
                            ->label('Remover itens atuais antes de sincronizar?')
                            ->helperText('Se uma categoria for selecionada, apenas os itens dessa categoria serão removidos.')
                            ->default(true),
                    ])
                    ->action(function ($record, array $data) {
                        $factor = 1 + ($data['factor'] / 100);
                        $categoryId = $data['category_id'] ?? null;

                        $query = MenuItem::where('menu_id', $record->menu_id);

                        if ($categoryId) {
                            $categoryIds = Category::where('id', $categoryId)
                                ->orWhere('category_id', $categoryId)
                                ->pluck('id')
                                ->toArray();

                            $query->whereHas('product', fn($q) => $q->whereIn('category_id', $categoryIds));
                        }

                        $parentItems = $query->get();

                        if ($parentItems->isEmpty()) {
                            Notification::make()
                                ->title('Aviso')
                                ->body('Não há itens' . ($categoryId ? ' desta categoria (ou subcategorias)' : '') . ' no menu pai para sincronizar.')
                                ->warning()
                                ->send();
                            return;
                        }

                        DB::transaction(function () use ($record, $parentItems, $factor, $data, $categoryId) {
                            if ($data['clear_existing']) {
                                if ($categoryId) {
                                    $categoryIds = Category::where('id', $categoryId)
                                        ->orWhere('category_id', $categoryId)
                                        ->pluck('id')
                                        ->toArray();

                                    $record->menuItems()
                                        ->whereHas('product', fn($q) => $q->whereIn('category_id', $categoryIds))
                                        ->delete();
                                } else {
                                    $record->menuItems()->delete();
                                }
                            }

                            foreach ($parentItems as $item) {
                                $originalPrice = $item->price;

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
