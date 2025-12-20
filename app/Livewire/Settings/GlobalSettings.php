<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use App\Models\GlobalSetting;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class GlobalSettings extends Component
{
    public $activeTab = 'alerts';
    public $timeLimitPending;
    public $timeLimitInProduction;
    public $timeLimitInTransit;
    public $timeLimitClosed;
    public $timeLimitReleasing;
    public $pixKeyType;
    public $pixKey;
    public $pixName;
    public $pixCity;
    public $pix_enabled;
    public $pollingInterval;
    public $menuId = null;
    public $menus = [];

    public function mount()
    {
        if (! Gate::allows('access-global-settings')) {
            abort(403, 'Unauthorized access.');
        }

        $settings = GlobalSetting::firstOrNew(['user_id' => Auth::id()]);

        $this->loadSettings($settings);
        
        $this->menus = \App\Models\Menu::where('user_id', Auth::id())
            ->get();
    }

    public function loadSettings(GlobalSetting $settings)
    {
        $this->timeLimitPending = $settings->time_limit_pending;
        $this->timeLimitInProduction = $settings->time_limit_in_production;
        $this->timeLimitInTransit = $settings->time_limit_in_transit;
        $this->timeLimitClosed = $settings->time_limit_closed;
        $this->timeLimitReleasing = $settings->time_limit_releasing;
        $this->pixKeyType = $settings->pix_key_type;
        $this->pixKey = $settings->pix_key;
        $this->pixName = $settings->pix_name;
        $this->pixCity = $settings->pix_city;
        $this->pix_enabled = $settings->pix_enabled;
        $this->menuId = $settings->menu_id;
        $this->pollingInterval = $settings->polling_interval;
    }

    public function save(?string $propertyName = null)
    {

        if (! Gate::allows('access-global-settings')) {
            abort(403, 'Unauthorized action.');
        }

        $rules = [
            'timeLimitPending' => 'required|integer|min:1|max:120',
            'timeLimitInProduction' => 'required|integer|min:1|max:120',
            'timeLimitInTransit' => 'required|integer|min:1|max:120',
            'timeLimitClosed' => 'required|integer|min:1|max:120',
            'timeLimitReleasing' => 'required|integer|min:1|max:120',
            'pixKeyType' => 'required|string|in:CPF,CNPJ,PHONE,EMAIL,RANDOM',
            'pixKey' => 'required|string|max:255',
            'pixName' => 'required|string|max:255',
            'pixCity' => 'nullable|string|max:255',
            'pix_enabled' => 'nullable|boolean',
            'menuId' => 'nullable|exists:menus,id',
        ];

        try {
            if ($propertyName) {
                // Valida apenas a propriedade que foi alterada
                $this->validateOnly($propertyName, $rules);
            } else {
                // Valida todas as propriedades (se chamado manualmente sem um nome de propriedade)
                $this->validate($rules);
            }

            GlobalSetting::updateOrCreate(
                ['user_id' => Auth::id()],
                [
                    'time_limit_pending' => $this->timeLimitPending,
                    'time_limit_in_production' => $this->timeLimitInProduction,
                    'time_limit_in_transit' => $this->timeLimitInTransit,
                    'time_limit_closed' => $this->timeLimitClosed,
                    'time_limit_releasing' => $this->timeLimitReleasing,
                    'pix_key_type' => $this->pixKeyType,
                    'pix_key' => $this->pixKey,
                    'pix_name' => $this->pixName,
                    'pix_city' => $this->pixCity,
                    'pix_enabled' => $this->pix_enabled,
                    'menu_id' => $this->menuId,
                ]
            );

        } catch (ValidationException $e) {
            throw $e;
        }
    }

    public function updated($name, $value)
    {
        if ($name !== 'activeTab') {
            try {
                $this->save($name); // Passa o nome da propriedade para o save()
            } catch (ValidationException $e) {
                logger('Validation Exception in GlobalSettings updated:', ['errors' => $e->errors(), 'properties' => $this->all()]);
                throw $e; // Re-throw para que o Livewire possa exibir os erros na UI
            }
        }
    }

    public function render()
    {
        return view('livewire.settings.global-settings');
    }
}
