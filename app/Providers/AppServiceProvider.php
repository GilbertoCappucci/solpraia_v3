<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Enums\RoleEnum; // Import RoleEnum
use App\Models\GlobalSetting;
use App\Observers\GlobalSettingObserver;
use Filament\Facades\Filament;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\View\PanelsRenderHook;

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

        // Registrar Observer do GlobalSetting
        GlobalSetting::observe(GlobalSettingObserver::class);

        // Definir Gates de autorização
        // Admin sempre tem acesso a tudo
        Gate::before(function (User $user, string $ability) {
            if ($user->isAdmin()) {
                return true;
            }
        });

        Gate::define('access-dashboard', fn(User $user) => $user->canAccessDashboard());
        Gate::define('access-orders', fn(User $user) => $user->canAccessOrders());

        // Novo Gate para configurações globais - apenas para administradores explicitamente
        Gate::define('access-global-settings', function (User $user) {
            return $user->role === RoleEnum::ADMIN;
        });

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
