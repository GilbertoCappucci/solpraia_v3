<?php

namespace App\Events;

use App\Models\GlobalSetting;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GlobalSettingEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $globalSetting;
    public $adminId;

    /**
     * Create a new event instance.
     */
    public function __construct(GlobalSetting $globalSetting)
    {
        $this->globalSetting = $globalSetting;
        $this->adminId = $this->globalSetting->user_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('global-setting-updated.' . $this->adminId),
        ];
    }

    public function broadcastAs()
    {
        return 'global.setting.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'globalSettingUpdated' => $this->globalSetting,
        ];
    }
}
