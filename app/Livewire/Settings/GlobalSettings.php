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
            ->whereNull('menu_id')
            ->get();
    }

    public function loadSettings(GlobalSetting $settings)
    {
        $this->timeLimitPending = $settings->time_limit_pending ?? 30;
        $this->timeLimitInProduction = $settings->time_limit_in_production ?? 60;
        $this->timeLimitInTransit = $settings->time_limit_in_transit ?? 15;
        $this->timeLimitClosed = $settings->time_limit_closed ?? 30;
        $this->timeLimitReleasing = $settings->time_limit_releasing ?? 10;
        $this->pixKeyType = $settings->pix_key_type ?? 'CPF';
        $this->pixKey = $settings->pix_key ?? '';
        $this->pixName = $settings->pix_name ?? '';
        $this->pixCity = $settings->pix_city ?? '';
        $this->menuId = $settings->menu_id;
    }

    public function save()
    {
        if (! Gate::allows('access-global-settings')) {
            abort(403, 'Unauthorized action.');
        }

        $this->validate([
            'timeLimitPending' => 'required|integer|min:1|max:120',
            'timeLimitInProduction' => 'required|integer|min:1|max:120',
            'timeLimitInTransit' => 'required|integer|min:1|max:120',
            'timeLimitClosed' => 'required|integer|min:1|max:120',
            'timeLimitReleasing' => 'required|integer|min:1|max:120',
            'pixKeyType' => 'required|string|in:CPF,CNPJ,PHONE,EMAIL,RANDOM',
            'pixKey' => 'required|string|max:255',
            'pixName' => 'required|string|max:255',
            'pixCity' => 'nullable|string|max:255',
            'menuId' => 'nullable|exists:menus,id',
        ]);

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
                'menu_id' => $this->menuId,
            ]
        );

        // Atualiza na sessão imediatamente
        app(\App\Services\GlobalSettingService::class)->syncToSession(
            GlobalSetting::where('user_id', Auth::id())->first()
        );

        session()->flash('message', 'Salvo com sucesso!');
    }

    public function render()
    {
        return view('livewire.settings.global-settings');
    }
}
