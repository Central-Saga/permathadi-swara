<?php

use App\Models\Anggota;
use App\Models\Subscription;
use App\Models\Payment;
use App\Models\ContactMessage;
use function Livewire\Volt\{layout, title, computed};

layout('components.layouts.admin');
title(fn () => __('Dashboard'));

$totalAnggota = computed(function () {
    return Anggota::count();
});

$subscriptionStats = computed(function () {
    return [
        'total' => Subscription::count(),
        'active' => Subscription::where('status', 'active')->count(),
        'pending' => Subscription::where('status', 'pending')->count(),
        'expired' => Subscription::where('status', 'expired')->count(),
    ];
});

$paymentStats = computed(function () {
    return [
        'total' => Payment::count(),
        'paid' => Payment::where('status', 'paid')->count(),
        'pending' => Payment::where('status', 'pending')->count(),
        'failed' => Payment::where('status', 'failed')->count(),
        'totalRevenue' => Payment::where('status', 'paid')->sum('amount'),
    ];
});

$contactMessageStats = computed(function () {
    return [
        'total' => ContactMessage::count(),
        'new' => ContactMessage::where('status', 'new')->count(),
        'read' => ContactMessage::where('status', 'read')->count(),
        'archived' => ContactMessage::where('status', 'archived')->count(),
    ];
});

?>

<div>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <!-- Header Section -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Dashboard') }}</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Overview sistem dan statistik') }}</p>
            </div>
        </div>

        <!-- Statistics Cards Grid -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <!-- Anggota Card -->
            <flux:card class="hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('Total Anggota') }}</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $this->totalAnggota }}</p>
                    </div>
                    <div class="rounded-lg bg-orange-100 dark:bg-orange-900/30 p-3">
                        <flux:icon name="user-group" class="h-8 w-8 text-orange-600 dark:text-orange-400" />
                    </div>
                </div>
                <div class="mt-4">
                    <flux:button :href="route('godmode.anggota.index')" variant="ghost" size="sm" wire:navigate
                        class="w-full">
                        {{ __('Lihat Detail') }}
                    </flux:button>
                </div>
            </flux:card>

            <!-- Subscription Card -->
            <flux:card class="hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('Total Langganan') }}</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                            {{ $this->subscriptionStats['total'] }}</p>
                        <div class="mt-2 flex gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <span class="inline-flex items-center gap-1">
                                <span class="h-2 w-2 rounded-full bg-green-500"></span>
                                {{ $this->subscriptionStats['active'] }} {{ __('Aktif') }}
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <span class="h-2 w-2 rounded-full bg-orange-500"></span>
                                {{ $this->subscriptionStats['pending'] }} {{ __('Pending') }}
                            </span>
                        </div>
                    </div>
                    <div class="rounded-lg bg-blue-100 dark:bg-blue-900/30 p-3">
                        <flux:icon name="document-text" class="h-8 w-8 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
                <div class="mt-4">
                    <flux:button :href="route('godmode.subscriptions.index')" variant="ghost" size="sm" wire:navigate
                        class="w-full">
                        {{ __('Lihat Detail') }}
                    </flux:button>
                </div>
            </flux:card>

            <!-- Payment Card -->
            <flux:card class="hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('Total Pembayaran') }}</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                            {{ $this->paymentStats['total'] }}</p>
                        <p class="mt-1 text-sm font-semibold text-green-600 dark:text-green-400">
                            {{ __('Revenue') }}: Rp {{ number_format($this->paymentStats['totalRevenue'], 0, ',', '.')
                            }}
                        </p>
                        <div class="mt-2 flex gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <span class="inline-flex items-center gap-1">
                                <span class="h-2 w-2 rounded-full bg-green-500"></span>
                                {{ $this->paymentStats['paid'] }} {{ __('Lunas') }}
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <span class="h-2 w-2 rounded-full bg-orange-500"></span>
                                {{ $this->paymentStats['pending'] }} {{ __('Pending') }}
                            </span>
                        </div>
                    </div>
                    <div class="rounded-lg bg-green-100 dark:bg-green-900/30 p-3">
                        <flux:icon name="banknotes" class="h-8 w-8 text-green-600 dark:text-green-400" />
                    </div>
                </div>
                <div class="mt-4">
                    <flux:button :href="route('godmode.payments.index')" variant="ghost" size="sm" wire:navigate
                        class="w-full">
                        {{ __('Lihat Detail') }}
                    </flux:button>
                </div>
            </flux:card>

            <!-- Contact Message Card -->
            <flux:card class="hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('Pesan Kontak') }}</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                            {{ $this->contactMessageStats['total'] }}</p>
                        <div class="mt-2 flex gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <span class="inline-flex items-center gap-1">
                                <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                                {{ $this->contactMessageStats['new'] }} {{ __('Baru') }}
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <span class="h-2 w-2 rounded-full bg-green-500"></span>
                                {{ $this->contactMessageStats['read'] }} {{ __('Dibaca') }}
                            </span>
                        </div>
                    </div>
                    <div class="rounded-lg bg-purple-100 dark:bg-purple-900/30 p-3">
                        <flux:icon name="envelope" class="h-8 w-8 text-purple-600 dark:text-purple-400" />
                    </div>
                </div>
                <div class="mt-4">
                    <flux:button :href="route('godmode.contact-messages.index')" variant="ghost" size="sm" wire:navigate
                        class="w-full">
                        {{ __('Lihat Detail') }}
                    </flux:button>
                </div>
            </flux:card>
        </div>

        <!-- Additional Statistics Section -->
        <div class="grid gap-4 md:grid-cols-2">
            <!-- Subscription Breakdown -->
            <flux:card>
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Detail Langganan') }}</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Breakdown status langganan') }}</p>
                </div>
                <div class="space-y-3">
                    <div class="flex items-center justify-between rounded-lg bg-green-50 dark:bg-green-900/20 p-3">
                        <div class="flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full bg-green-500"></span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ __('Aktif') }}</span>
                        </div>
                        <span class="text-lg font-bold text-gray-900 dark:text-white">
                            {{ $this->subscriptionStats['active'] }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-orange-50 dark:bg-orange-900/20 p-3">
                        <div class="flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full bg-orange-500"></span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ __('Pending') }}</span>
                        </div>
                        <span class="text-lg font-bold text-gray-900 dark:text-white">
                            {{ $this->subscriptionStats['pending'] }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-gray-50 dark:bg-gray-800 p-3">
                        <div class="flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full bg-gray-500"></span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ __('Kedaluwarsa')
                                }}</span>
                        </div>
                        <span class="text-lg font-bold text-gray-900 dark:text-white">
                            {{ $this->subscriptionStats['expired'] }}</span>
                    </div>
                </div>
            </flux:card>

            <!-- Payment Breakdown -->
            <flux:card>
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Detail Pembayaran') }}</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Breakdown status pembayaran') }}</p>
                </div>
                <div class="space-y-3">
                    <div class="flex items-center justify-between rounded-lg bg-green-50 dark:bg-green-900/20 p-3">
                        <div class="flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full bg-green-500"></span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ __('Lunas') }}</span>
                        </div>
                        <span class="text-lg font-bold text-gray-900 dark:text-white">
                            {{ $this->paymentStats['paid'] }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-orange-50 dark:bg-orange-900/20 p-3">
                        <div class="flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full bg-orange-500"></span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ __('Pending') }}</span>
                        </div>
                        <span class="text-lg font-bold text-gray-900 dark:text-white">
                            {{ $this->paymentStats['pending'] }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-red-50 dark:bg-red-900/20 p-3">
                        <div class="flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full bg-red-500"></span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ __('Gagal') }}</span>
                        </div>
                        <span class="text-lg font-bold text-gray-900 dark:text-white">
                            {{ $this->paymentStats['failed'] }}</span>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>
</div>