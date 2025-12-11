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
    
    protected $settingService;
    
    public function boot(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }
    
    public function mount()
    {
        // Carrega time limits
        $this->timeLimitPending = $this->settingService->getSetting('time_limits.pending', 15);
        $this->timeLimitInProduction = $this->settingService->getSetting('time_limits.in_production', 30);
        $this->timeLimitInTransit = $this->settingService->getSetting('time_limits.in_transit', 10);
        $this->timeLimitClosed = $this->settingService->getSetting('time_limits.closed', 5);
        $this->timeLimitReleasing = $this->settingService->getSetting('time_limits.releasing', 10);
    }
    
    public function saveSettings()
    {
        $this->validate([
            'timeLimitPending' => 'required|integer|min:1|max:120',
            'timeLimitInProduction' => 'required|integer|min:1|max:120',
            'timeLimitInTransit' => 'required|integer|min:1|max:120',
            'timeLimitClosed' => 'required|integer|min:1|max:120',
            'timeLimitReleasing' => 'required|integer|min:1|max:120',
        ]);

        $user = Auth::user();
        
        // Atualiza as configurações no banco e sessão
        $this->settingService->updateSettings($user, [
            'time_limits.pending' => $this->timeLimitPending,
            'time_limits.in_production' => $this->timeLimitInProduction,
            'time_limits.in_transit' => $this->timeLimitInTransit,
            'time_limits.closed' => $this->timeLimitClosed,
            'time_limits.releasing' => $this->timeLimitReleasing,
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
