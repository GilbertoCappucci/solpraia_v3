<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use App\Services\GlobalSettingService;
use App\Services\UserPreferenceService;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
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
        
        // Definir Gates de autorização
        // Admin sempre tem acesso a tudo
        Gate::before(function (User $user, string $ability) {
            if ($user->isAdmin()) {
                return true;
            }
        });
        
        Gate::define('access-dashboard', fn (User $user) => $user->canAccessDashboard());
        Gate::define('access-orders', fn (User $user) => $user->canAccessOrders());

        // Atualiza cache quando usuário faz login (sem expiração)
        Event::listen(Login::class, function (Login $event) {
            Cache::forever('user-is-online-' . $event->user->id, true);
            
            // Carrega as configurações do usuário na sessão (inicial)
            // O middleware LoadUserSettings irá recarregar a cada request subsequente
            $globalSettingService = app(GlobalSettingService::class);
            $userPreferenceService = app(UserPreferenceService::class);
            $globalSettingService->loadGlobalSettings($event->user);
            $userPreferenceService->loadUserPreferences($event->user);
        });

        // Remove cache quando usuário faz logout
        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user) {
                Cache::forget('user-is-online-' . $event->user->id);
            }
        });
    }
}
