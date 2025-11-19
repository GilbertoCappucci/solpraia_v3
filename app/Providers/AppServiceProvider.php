<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar Observer do User
        User::observe(UserObserver::class);
        

        // Atualiza cache quando usuário faz login (sem expiração)
        Event::listen(Login::class, function (Login $event) {
            Cache::forever('user-is-online-' . $event->user->id, true);
        });

        // Remove cache quando usuário faz logout
        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user) {
                Cache::forget('user-is-online-' . $event->user->id);
            }
        });
    }
}
