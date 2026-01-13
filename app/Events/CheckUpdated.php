<?php

namespace App\Events;

use App\Models\Check;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CheckUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $check;

    /**
     * Create a new event instance.
     */
    public function __construct(Check $check)
    {
        $this->check = $check;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('check-updated.' . $this->check->table->admin_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'check.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'checkId' => $this->check->id,
            'tableId' => $this->check->table_id,
            'adminId' => $this->check->table->admin_id,
            'checkStatus' => $this->check->status,
            'checkTotal' => $this->check->total,
            'updated_at' => $this->check->updated_at->toISOString(),
        ];
    }
}