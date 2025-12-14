<?php

use App\Http\Middleware\RedirectByRole;
use App\Livewire\Orders;
use App\Livewire\Tables;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use App\Livewire\Settings\GlobalSettings;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return view('welcome');

})->name('home');

// Rota que redireciona baseado na role
Route::get('/home', function () {
    return redirect('/');
})->middleware([RedirectByRole::class])->name('home.redirect');

// =============================================
// ROTAS ADMIN
// =============================================
Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    // Nova rota para Configurações Globais (admin apenas)
    Route::get('settings/global', GlobalSettings::class)->name('settings.global')->middleware('can:access-global-settings');
});

// =============================================
// ROTAS DEVICE - Mobile
// =============================================
Route::middleware(['auth', 'role:device'])->group(function () {
    Route::get('tables', Tables::class)->name('tables');
    Route::get('orders/{tableId}', Orders::class)->name('orders');
    Route::get('menu/{tableId}', \App\Livewire\Menu::class)->name('menu');
    Route::get('check/{checkId}', \App\Livewire\CheckComponent::class)->name('check');
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
});