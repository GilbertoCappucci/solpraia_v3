<?php

use App\Livewire\Orders;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return view('welcome');
})->name('home');

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
    Route::get('orders', Orders::class)->name('orders');
});

// =============================================
// ROTAS COMPARTILHADAS (admin + device)
// =============================================
Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

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

