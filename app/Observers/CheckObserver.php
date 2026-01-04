<?php

namespace App\Observers;

use App\Events\CheckUpdated;
use App\Models\Check;

class CheckObserver
{
    /**
     * Handle the Check "created" event.
     */
    public function created(Check $check): void
    {
        logger('🚀 CheckObserver: Check created', ['id' => $check->id, 'status' => $check->status]);
        event(new CheckUpdated($check));
    }

    /**
     * Handle the Check "updated" event.
     */
    public function updated(Check $check): void
    {
        logger('🔄 CheckObserver: Check updated', [
            'id' => $check->id, 
            'status' => $check->status,
            'total' => $check->total,
            'changes' => $check->getChanges()
        ]);
        event(new CheckUpdated($check));
    }

    /**
     * Handle the Check "deleted" event.
     */
    public function deleted(Check $check): void
    {
        event(new CheckUpdated($check));
    }

    /**
     * Handle the Check "restored" event.
     */
    public function restored(Check $check): void
    {
        event(new CheckUpdated($check));
    }
}