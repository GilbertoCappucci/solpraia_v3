<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UserObserver
{
    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Verifica se o campo 'active' foi alterado
        if ($user->isDirty('active') && $user->active === false) {
            // Remove usuário do cache de online
            Cache::forget('user-is-online-' . $user->id);
            
            // Invalida todas as sessões do usuário
            // Laravel armazena sessões em DB/file/redis dependendo da config
            DB::table('sessions')
                ->where('admin_id', $user->id)
                ->delete();
        }
    }
}
