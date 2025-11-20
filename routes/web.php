<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

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

    // Two-factor authentication disabled
    // Volt::route('settings/two-factor', 'settings.two-factor')
    //     ->middleware(
    //         when(
    //             Features::canManageTwoFactorAuthentication()
    //                 && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
    //             ['password.confirm'],
    //             [],
    //         ),
    //     )
    //     ->name('two-factor.show');
});

Route::middleware(['auth'])->prefix('godmode')->name('godmode.')->group(function () {
    Route::bind('role', function ($value) {
        return Role::findOrFail($value);
    });

    Route::resource('users', \App\Http\Controllers\Godmode\UserController::class);
    Route::resource('roles', \App\Http\Controllers\Godmode\RoleController::class);
});
