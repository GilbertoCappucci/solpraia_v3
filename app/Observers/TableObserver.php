<?php

namespace App\Observers;

use App\Events\TableUpdatedEvent;
use Illuminate\Support\Facades\Event;
use App\Models\Table;

class TableObserver
{
    /**
     * Handle the Table "created" event.
     */
    public function created(Table $table): void
    {
        logger('🚀 TableObserver: Table created', ['id' => $table->id, 'status' => $table->status]);
        $this->dispatch($table);
    }

    /**
     * Handle the Table "updated" event.
     */
    public function updated(Table $table): void
    {
        logger('🔄 TableObserver: Table updated', [
            'id' => $table->id, 
            'status' => $table->status,
            'changes' => $table->getChanges()
        ]);
        $this->dispatch($table);
    }

    /**
     * Handle the Table "deleted" event.
     */
    public function deleted(Table $table): void
    {
        logger('🚀 TableObserver: Table deleted', ['id' => $table->id, 'status' => $table->status]);
        $this->dispatch($table);
    }

    /**
     * Handle the Table "restored" event.
     */
    public function restored(Table $table): void
    {
        logger('🚀 TableObserver: Table restored', ['id' => $table->id, 'status' => $table->status]);
        $this->dispatch($table);
    }
    /**
     * Dispatch the TableUpdated event.
     */
    public function dispatch(Table $table): void
    {
        Event::dispatch(new TableUpdatedEvent($table));
    }
}