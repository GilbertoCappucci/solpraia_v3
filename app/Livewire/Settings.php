<?php

namespace App\Livewire;

use App\Services\GlobalSettingService;
use App\Services\UserPreferenceService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Settings extends Component
{
    public $title = 'Configurações';
    public $activeTab = 'alerts'; // 'alerts' ou 'pix'

    // Global Settings (apenas admin)
    public $timeLimitPending;
    public $timeLimitInProduction;
    public $timeLimitInTransit;
    public $timeLimitClosed;
    public $timeLimitReleasing;

    // PIX Fields (apenas admin)
    public $pixKey;
    public $pixKeyType;
    public $pixName;
    public $pixCity;

    protected $globalSettingService;
    protected $userPreferenceService;

    public function boot(GlobalSettingService $globalSettingService, UserPreferenceService $userPreferenceService)
    {
        $this->globalSettingService = $globalSettingService;
        $this->userPreferenceService = $userPreferenceService;

        // Recarrega configurações do banco a cada request
        if (Auth::check()) {
            $this->globalSettingService->loadGlobalSettings(Auth::user());
            $this->userPreferenceService->loadUserPreferences(Auth::user());
        }
    }

    public function mount()
    {
        // Verifica se o usuário é admin
        if (Auth::user()->user_id !== null) {
            // Usuário comum não pode acessar configurações globais
            session()->flash('error', 'Apenas administradores podem acessar as configurações.');
            return redirect()->route('tables');
        }

        // Carrega Global Settings (time limits)
        $this->timeLimitPending = $this->globalSettingService->getSetting('time_limit_pending', 15);
        $this->timeLimitInProduction = $this->globalSettingService->getSetting('time_limit_in_production', 30);
        $this->timeLimitInTransit = $this->globalSettingService->getSetting('time_limit_in_transit', 10);
        $this->timeLimitClosed = $this->globalSettingService->getSetting('time_limit_closed', 5);
        $this->timeLimitReleasing = $this->globalSettingService->getSetting('time_limit_releasing', 10);

        // Load PIX Settings
        $this->pixKey = $this->globalSettingService->getSetting('pix_key');
        $this->pixKeyType = $this->globalSettingService->getSetting('pix_key_type', 'CPF');
        $this->pixName = $this->globalSettingService->getSetting('pix_name');
        $this->pixCity = $this->globalSettingService->getSetting('pix_city');
    }

    public function saveSettings()
    {
        // Verifica se o usuário é admin
        if (Auth::user()->user_id !== null) {
            session()->flash('error', 'Apenas administradores podem alterar configurações globais.');
            return;
        }

        $this->validate([
            'timeLimitPending' => 'required|integer|min:1|max:120',
            'timeLimitInProduction' => 'required|integer|min:1|max:120',
            'timeLimitInTransit' => 'required|integer|min:1|max:120',
            'timeLimitClosed' => 'required|integer|min:1|max:120',
            'timeLimitReleasing' => 'required|integer|min:1|max:120',
            'pixKey' => 'nullable|string',
            'pixKeyType' => 'nullable|string|in:CPF,CNPJ,PHONE,EMAIL,RANDOM',
            'pixName' => 'nullable|string',
            'pixCity' => 'nullable|string',
        ]);

        $user = Auth::user();

        try {
            // Atualiza as configurações globais no banco e sessão
            $this->globalSettingService->updateSettings($user, [
                'time_limit_pending' => $this->timeLimitPending,
                'time_limit_in_production' => $this->timeLimitInProduction,
                'time_limit_in_transit' => $this->timeLimitInTransit,
                'time_limit_closed' => $this->timeLimitClosed,
                'time_limit_releasing' => $this->timeLimitReleasing,

                // PIX
                'pix_key' => $this->pixKey,
                'pix_key_type' => $this->pixKeyType ?: 'CPF',
                'pix_name' => $this->pixName,
                'pix_city' => $this->pixCity,
            ]);

            session()->flash('success', 'Configurações salvas com sucesso!');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }

        return redirect()->route('tables');
    }

    public function render()
    {
        return view('livewire.settings');
    }
}
