<?php

namespace App\Livewire;

use App\Services\SettingService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Settings extends Component
{
    public $title = 'Configurações';
    public $activeTab = 'alerts'; // 'alerts' ou 'display'

    public $timeLimitPending;
    public $timeLimitInProduction;
    public $timeLimitInTransit;
    public $timeLimitClosed;
    public $timeLimitReleasing;

    // PIX Fields
    public $pixKey;
    public $pixKeyType;
    public $pixName;
    public $pixCity;

    protected $settingService;

    public function boot(SettingService $settingService)
    {
        $this->settingService = $settingService;

        // Recarrega configurações do banco a cada request
        if (Auth::check()) {
            $this->settingService->loadUserSettings(Auth::user());
        }
    }

    public function mount()
    {
        // Carrega time limits
        $this->timeLimitPending = $this->settingService->getSetting('time_limits.pending', 15);
        $this->timeLimitInProduction = $this->settingService->getSetting('time_limits.in_production', 30);
        $this->timeLimitInTransit = $this->settingService->getSetting('time_limits.in_transit', 10);
        $this->timeLimitClosed = $this->settingService->getSetting('time_limits.closed', 5);
        $this->timeLimitReleasing = $this->settingService->getSetting('time_limits.releasing', 10);

        // Load PIX Settings
        $this->pixKey = $this->settingService->getSetting('pix.key');
        $this->pixKeyType = $this->settingService->getSetting('pix.key_type', 'CPF'); // Default to CPF
        $this->pixName = $this->settingService->getSetting('pix.name');
        $this->pixCity = $this->settingService->getSetting('pix.city');
    }

    public function saveSettings()
    {
        $this->validate([
            'timeLimitPending' => 'required|integer|min:1|max:120',
            'timeLimitInProduction' => 'required|integer|min:1|max:120',
            'timeLimitInTransit' => 'required|integer|min:1|max:120',
            'timeLimitClosed' => 'required|integer|min:1|max:120',
            'timeLimitReleasing' => 'required|integer|min:1|max:120',
            'pixKey' => 'nullable|string',
            'pixKeyType' => 'nullable|string',
            'pixName' => 'nullable|string',
            'pixCity' => 'nullable|string',
        ]);

        $user = Auth::user();

        // Atualiza as configurações no banco e sessão
        $this->settingService->updateSettings($user, [
            'time_limits.pending' => $this->timeLimitPending,
            'time_limits.in_production' => $this->timeLimitInProduction,
            'time_limits.in_transit' => $this->timeLimitInTransit,
            'time_limits.closed' => $this->timeLimitClosed,
            'time_limits.releasing' => $this->timeLimitReleasing,

            // PIX
            'pix.key' => $this->pixKey,
            'pix.key_type' => $this->pixKeyType,
            'pix.name' => $this->pixName,
            'pix.city' => $this->pixCity,
        ]);

        // Recarrega as configurações da sessão a partir do banco
        $settings = \App\Models\Setting::where('user_id', $user->id)->first();
        if ($settings) {
            $this->settingService->syncToSession($settings);
        }

        session()->flash('success', 'Configurações salvas com sucesso!');

        return redirect()->route('tables');
    }

    public function render()
    {
        return view('livewire.settings');
    }
}
