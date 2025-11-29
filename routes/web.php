<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

Volt::route('/', 'landing.welcome')->name('home');
Volt::route('tentang', 'landing.tentang')->name('landing.tentang');
Volt::route('program', 'landing.program')->name('landing.program');
Volt::route('program/{layanan:slug}', 'landing.program-detail')->name('landing.program-detail');
Volt::route('galeri', 'landing.galeri')->name('landing.galeri');
Volt::route('kontak', 'landing.kontak')->name('landing.kontak');

Volt::route('dashboard', 'godmode.dashboard')
    ->middleware(['auth', 'verified', 'permission:akses godmode'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    // History route for anggota
    Volt::route('history', 'landing.history')->name('landing.history');

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

Route::middleware(['auth', 'permission:akses godmode'])->prefix('godmode')->name('godmode.')->group(function () {
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

    // Layanan routes
    Volt::route('layanan', 'godmode.layanan.index')->name('layanan.index');
    Volt::route('layanan/create', 'godmode.layanan.create')->name('layanan.create');
    Volt::route('layanan/{layanan}/edit', 'godmode.layanan.edit')->name('layanan.edit');

    // Subscriptions routes
    Volt::route('subscriptions', 'godmode.subscriptions.index')->name('subscriptions.index');
    Volt::route('subscriptions/create', 'godmode.subscriptions.create')->name('subscriptions.create');
    Volt::route('subscriptions/{subscription}/edit', 'godmode.subscriptions.edit')->name('subscriptions.edit');

    // Payments routes
    Volt::route('payments', 'godmode.payments.index')->name('payments.index');
    Volt::route('payments/create', 'godmode.payments.create')->name('payments.create');
    Volt::route('payments/{payment}/edit', 'godmode.payments.edit')->name('payments.edit');

    // Create payment from subscription
    Volt::route('subscriptions/{subscription}/payments/create', 'godmode.payments.create')->name('subscriptions.payments.create');

    // Contact Messages routes
    Volt::route('contact-messages', 'godmode.contact-messages.index')->name('contact-messages.index');
    Volt::route('contact-messages/{contactMessage}/edit', 'godmode.contact-messages.edit')->name('contact-messages.edit');
});
