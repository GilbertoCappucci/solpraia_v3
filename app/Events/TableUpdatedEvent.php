<?php

namespace App\Events;

use App\Models\Table;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TableUpdatedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $table;

    /**
     * Create a new event instance.
     */
    public function __construct(Table $table)
    {
        $this->table = $table;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        logger('📡 Broadcasting TableUpdatedEvent', ['tableId' => $this->table->id, 'userId' => $this->table->admin_id]);
        return [
            new PrivateChannel('tables-updated.' . $this->table->admin_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'table.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'tableId' => $this->table->id,
            'userId' => $this->table->admin_id,
            'status' => $this->table->status,
            'number' => $this->table->number,
            'name' => $this->table->name,
            'updated_at' => $this->table->updated_at->toISOString(),
        ];
    }
}