<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('landing.welcome');
})->name('home');

Route::get('tentang', function () {
    return view('landing.tentang');
})->name('landing.tentang');

Route::get('program', function () {
    return view('landing.program');
})->name('landing.program');

Route::get('galeri', function () {
    return view('landing.galeri');
})->name('landing.galeri');

Route::get('kontak', function () {
    return view('landing.kontak');
})->name('landing.kontak');

Route::view('dashboard', 'godmode.dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
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
