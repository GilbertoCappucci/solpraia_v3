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

    public function mount()
    {
        if (! Gate::allows('access-global-settings')) {
            abort(403, 'Unauthorized access.');
        }

        $settings = GlobalSetting::firstOrNew(['user_id' => Auth::id()]);
        $this->loadSettings($settings);
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
    }

    /**
     * This hook is called when a public property is updated.
     */
    public function updated($propertyName)
    {
        if (! Gate::allows('access-global-settings')) {
            abort(403, 'Unauthorized action.');
        }

        $settings = GlobalSetting::firstOrNew(['user_id' => Auth::id()]);

        // Map component properties to model attributes and validation rules
        $propertyMap = [
            'timeLimitPending' => ['attribute' => 'time_limit_pending', 'rule' => 'required|integer|min:1|max:120'],
            'timeLimitInProduction' => ['attribute' => 'time_limit_in_production', 'rule' => 'required|integer|min:1|max:120'],
            'timeLimitInTransit' => ['attribute' => 'time_limit_in_transit', 'rule' => 'required|integer|min:1|max:120'],
            'timeLimitClosed' => ['attribute' => 'time_limit_closed', 'rule' => 'required|integer|min:1|max:120'],
            'timeLimitReleasing' => ['attribute' => 'time_limit_releasing', 'rule' => 'required|integer|min:1|max:120'],
            'pixKeyType' => ['attribute' => 'pix_key_type', 'rule' => 'required|string|in:CPF,CNPJ,PHONE,EMAIL,RANDOM'],
            'pixKey' => ['attribute' => 'pix_key', 'rule' => 'nullable|string|max:255'],
            'pixName' => ['attribute' => 'pix_name', 'rule' => 'nullable|string|max:255'],
            'pixCity' => ['attribute' => 'pix_city', 'rule' => 'nullable|string|max:255'],
        ];

        if (isset($propertyMap[$propertyName])) {
            $attribute = $propertyMap[$propertyName]['attribute'];
            $rule = $propertyMap[$propertyName]['rule'];
            
            // Validate the single property
            $this->validateOnly($propertyName, [$propertyName => $rule]);

            // Update and save the setting
            $settings->$attribute = $this->$propertyName;
            $settings->save();

            // Flash a saved message. This can be displayed in the view.
            session()->flash('message', 'Salvo!');
        }
    }

    public function render()
    {
        return view('livewire.settings.global-settings');
    }
}
