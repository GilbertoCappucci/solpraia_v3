<?php

namespace App\Observers;

use App\Events\GlobalSettingEvent;
use App\Models\GlobalSetting;

class GlobalSettingObserver
{
    public function updated(GlobalSetting $settings)
    {
        logger('GlobalSetting updated:', ['settings' => $settings]);
        event(new GlobalSettingEvent($settings));
    }

    public function created(GlobalSetting $settings)
    {
        logger('GlobalSetting created:', ['settings' => $settings]);
        event(new GlobalSettingEvent($settings));
    }
}
