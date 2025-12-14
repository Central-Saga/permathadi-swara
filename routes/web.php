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

    // Subscribe route for anggota
    Volt::route('subscribe/{layanan:slug}', 'landing.subscribe')->name('landing.subscribe');

    // Renew subscription route for anggota
    Volt::route('renew/{subscription}', 'landing.renew')->name('landing.renew');

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
    Volt::route('users', 'godmode.users.index')
        ->middleware(['permission:melihat user'])
        ->name('users.index');
    Volt::route('users/create', 'godmode.users.create')
        ->middleware(['permission:membuat user'])
        ->name('users.create');
    Volt::route('users/{user}/edit', 'godmode.users.edit')
        ->middleware(['permission:mengubah user'])
        ->name('users.edit');

    // Roles routes
    Volt::route('roles', 'godmode.roles.index')
        ->middleware(['permission:melihat role'])
        ->name('roles.index');
    Volt::route('roles/create', 'godmode.roles.create')
        ->middleware(['permission:membuat role'])
        ->name('roles.create');
    Volt::route('roles/{role}/edit', 'godmode.roles.edit')
        ->middleware(['permission:mengubah role'])
        ->name('roles.edit');

    // Anggota routes
    Volt::route('anggota', 'godmode.anggota.index')
        ->middleware(['permission:melihat anggota'])
        ->name('anggota.index');
    Volt::route('anggota/create', 'godmode.anggota.create')
        ->middleware(['permission:membuat anggota'])
        ->name('anggota.create');
    Volt::route('anggota/{anggota}/edit', 'godmode.anggota.edit')
        ->middleware(['permission:mengubah anggota'])
        ->name('anggota.edit');

    // Layanan routes
    Volt::route('layanan', 'godmode.layanan.index')
        ->middleware(['permission:melihat layanan'])
        ->name('layanan.index');
    Volt::route('layanan/create', 'godmode.layanan.create')
        ->middleware(['permission:membuat layanan'])
        ->name('layanan.create');
    Volt::route('layanan/{layanan}/edit', 'godmode.layanan.edit')
        ->middleware(['permission:mengubah layanan'])
        ->name('layanan.edit');

    // Subscriptions routes
    Volt::route('subscriptions', 'godmode.subscriptions.index')
        ->middleware(['permission:melihat subscription'])
        ->name('subscriptions.index');
    Volt::route('subscriptions/create', 'godmode.subscriptions.create')
        ->middleware(['permission:membuat subscription'])
        ->name('subscriptions.create');
    Volt::route('subscriptions/{subscription}/edit', 'godmode.subscriptions.edit')
        ->middleware(['permission:mengubah subscription'])
        ->name('subscriptions.edit');

    // Payments routes
    Volt::route('payments', 'godmode.payments.index')
        ->middleware(['permission:melihat payment'])
        ->name('payments.index');
    Volt::route('payments/create', 'godmode.payments.create')
        ->middleware(['permission:membuat payment'])
        ->name('payments.create');
    Volt::route('payments/{payment}/edit', 'godmode.payments.edit')
        ->middleware(['permission:mengubah payment'])
        ->name('payments.edit');

    // Create payment from subscription
    Volt::route('subscriptions/{subscription}/payments/create', 'godmode.payments.create')
        ->middleware(['permission:membuat payment'])
        ->name('subscriptions.payments.create');

    // Contact Messages routes
    Volt::route('contact-messages', 'godmode.contact-messages.index')
        ->middleware(['permission:melihat pesan kontak'])
        ->name('contact-messages.index');
    Volt::route('contact-messages/{contactMessage}/edit', 'godmode.contact-messages.edit')
        ->middleware(['permission:mengubah pesan kontak'])
        ->name('contact-messages.edit');

    // Galeri routes
    Volt::route('galeri', 'godmode.galeri.index')
        ->middleware(['permission:melihat galeri'])
        ->name('galeri.index');
    Volt::route('galeri/create', 'godmode.galeri.create')
        ->middleware(['permission:membuat galeri'])
        ->name('galeri.create');
    Volt::route('galeri/{galeri}/edit', 'godmode.galeri.edit')
        ->middleware(['permission:mengubah galeri'])
        ->name('galeri.edit');

    // Activity Logs routes
    Volt::route('activity-logs', 'godmode.activity-logs.index')
        ->middleware(['permission:melihat activity log'])
        ->name('activity-logs.index');

    // Log Viewer routes
    Volt::route('log-viewer', 'godmode.log-viewer.index')
        ->middleware(['permission:melihat log viewer'])
        ->name('log-viewer.index');
});
