<?php

use App\Http\Middleware\RedirectByRole;
use App\Livewire\Order\Orders;
use App\Livewire\Table\Tables;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use App\Livewire\Settings\GlobalSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Rota de logout customizada para o Filament (redireciona para /)
Route::post('/admin/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('filament.admin.auth.logout');

// Rota que redireciona baseado na role
Route::get('/home', function () {
    return redirect('/');
})->middleware([RedirectByRole::class])->name('home.redirect');

// =============================================
// ROTAS ADMIN
// =============================================
Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

// =============================================
// ROTAS DEVICE - Mobile
// =============================================
Route::middleware(['auth', 'role:device'])->group(function () {
    Route::get('tables', Tables::class)->name('tables');
    Route::get('orders/{tableId}', Orders::class)->name('orders');
    Route::get('menu/{tableId}', \App\Livewire\Menu\Menus::class)->name('menu');
    Route::get('check/{checkId}', \App\Livewire\Check::class)->name('check');
    Route::get('payment', \App\Livewire\Payment::class)->name('payment');
    // Removida a rota antiga de settings gerais do device
    // Route::get('settings/app', \App\Livewire\Settings::class)->name('settings.app');
});

// =============================================
// ROTAS COMPARTILHADAS (admin + device + users em geral)
// =============================================
Route::middleware(['auth'])->group(function () {
    // Redireciona a rota base 'settings' para o perfil do usuário
    Route::redirect('settings', 'settings/profile');

    // Rotas de perfil e segurança permanecem
    Route::get('settings/profile', Profile::class)->name('profile.edit');
    Route::get('settings/password', Password::class)->name('user-password.edit');
    Route::get('settings/appearance', Appearance::class)->name('appearance.edit');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');

    // Rota para Configurações Globais (stub para testes e acesso admin)
    Route::get('settings/global', function () {
        return redirect('settings/profile');
    })->middleware(['can:access-global-settings'])->name('settings.global');
});
