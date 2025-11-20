<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

Volt::route('/', 'landing.welcome')->name('home');
Volt::route('tentang', 'landing.tentang')->name('landing.tentang');
Volt::route('program', 'landing.program')->name('landing.program');
Volt::route('galeri', 'landing.galeri')->name('landing.galeri');
Volt::route('kontak', 'landing.kontak')->name('landing.kontak');

Volt::route('dashboard', 'godmode.dashboard')
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
    // Users routes
    Volt::route('users', 'godmode.users.index')->name('users.index');
    Volt::route('users/create', 'godmode.users.create')->name('users.create');
    Volt::route('users/{user}/edit', 'godmode.users.edit')->name('users.edit');
    
    // Roles routes
    Volt::route('roles', 'godmode.roles.index')->name('roles.index');
    Volt::route('roles/create', 'godmode.roles.create')->name('roles.create');
    Volt::route('roles/{role}/edit', 'godmode.roles.edit')->name('roles.edit');
    
    // Anggota routes
    Volt::route('anggota', 'godmode.anggota.index')->name('anggota.index');
    Volt::route('anggota/create', 'godmode.anggota.create')->name('anggota.create');
    Volt::route('anggota/{anggota}/edit', 'godmode.anggota.edit')->name('anggota.edit');
});
