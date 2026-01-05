<?php

namespace App\Filament\Resources\GlobalSettings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class GlobalSettingsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Menu
                Select::make('menu_id')
                    ->label('Menu Ativo')
                    ->options(function () {
                        return \App\Models\Menu::where('user_id', Auth::id())
                            ->pluck('name', 'id');
                    })
                    ->nullable()
                    ->placeholder('Nenhum menu selecionado')
                    ->helperText('Este menu será usado para exibir produtos e definir preços no sistema')
                    ->columnSpanFull(),

                // Alertas - Limites de Tempo
                TextInput::make('time_limit_pending')
                    ->label('Pedido Pendente')
                    ->helperText('Aguardando início do preparo')
                    ->numeric()
                    ->minValue(1)
                    ->suffix('minutos')
                    ->default(15),

                TextInput::make('time_limit_in_production')
                    ->label('Pedido Em Produção')
                    ->helperText('Em preparo na cozinha')
                    ->numeric()
                    ->minValue(1)
                    ->suffix('minutos')
                    ->default(30),

                TextInput::make('time_limit_in_transit')
                    ->label('Pedido Em Trânsito')
                    ->helperText('A caminho da mesa')
                    ->numeric()
                    ->minValue(1)
                    ->suffix('minutos')
                    ->default(10),

                TextInput::make('time_limit_closed')
                    ->label('Check Fechado')
                    ->helperText('Aguardando pagamento')
                    ->numeric()
                    ->minValue(1)
                    ->suffix('minutos')
                    ->default(60),

                TextInput::make('time_limit_releasing')
                    ->label('Mesa Liberando')
                    ->helperText('Aguardando limpeza')
                    ->numeric()
                    ->minValue(1)
                    ->suffix('minutos')
                    ->default(15),

                // PIX
                Toggle::make('pix_enabled')
                    ->label('Ativar Pagamento por PIX')
                    ->default(false)
                    ->columnSpanFull(),

                Select::make('pix_key_type')
                    ->label('Tipo de Chave PIX')
                    ->options([
                        'CPF' => 'CPF',
                        'CNPJ' => 'CNPJ',
                        'PHONE' => 'Telefone',
                        'EMAIL' => 'E-mail',
                        'RANDOM' => 'Chave Aleatória',
                    ])
                    ->default('CPF'),

                TextInput::make('pix_key')
                    ->label('Chave PIX')
                    ->maxLength(255)
                    ->placeholder('Digite sua chave PIX'),

                TextInput::make('pix_name')
                    ->label('Nome do Beneficiário')
                    ->maxLength(255)
                    ->placeholder('Nome que aparecerá no banco'),

                TextInput::make('pix_city')
                    ->label('Cidade do Beneficiário')
                    ->maxLength(255)
                    ->placeholder('Cidade da conta bancária'),

            ]);
    }
}
