<?php

namespace App\Filament\Resources\GlobalSettings\Pages;

use App\Filament\Resources\GlobalSettings\GlobalSettingsResource;
use App\Models\GlobalSetting;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class EditGlobalSettings extends EditRecord
{
    protected static string $resource = GlobalSettingsResource::class;

    protected static ?string $title = 'Configurações Globais';

    public function mount(int|string|null $record = null): void
    {
        // Verificar permissão
        if (! Gate::allows('access-global-settings')) {
            abort(403, 'Acesso não autorizado.');
        }

        // Carregar ou criar o registro de configurações do usuário
        $setting = GlobalSetting::firstOrCreate(
            ['user_id' => Auth::id()],
            [
                'time_limit_pending' => 15,
                'time_limit_in_production' => 30,
                'time_limit_in_transit' => 10,
                'time_limit_closed' => 60,
                'time_limit_releasing' => 15,
                'pix_enabled' => false,
                'pix_key_type' => 'CPF',
                'pix_key' => '',
                'pix_name' => '',
                'pix_city' => '',
                'menu_id' => null,
            ]
        );

        // Chamar o mount do pai com o ID do registro
        parent::mount($setting->id);
    }

    protected function getHeaderActions(): array
    {
        return [
            // Removidas ações de delete, force delete e restore
        ];
    }

    protected function getRedirectUrl(): ?string
    {
        // Redirecionar para a mesma página após salvar
        return null;
    }
}
