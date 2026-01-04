<?php

namespace App\Events;

use App\Models\Check;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CheckUpdated implements ShouldBroadcast
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
            new PrivateChannel('tables-updated.' . $this->check->table->user_id),
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
            'userId' => $this->check->table->user_id,
            'checkStatus' => $this->check->status,
            'checkTotal' => $this->check->total,
            'updated_at' => $this->check->updated_at->toISOString(),
        ];
    }
}