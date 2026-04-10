<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

use function Livewire\Volt\action;
use function Livewire\Volt\computed;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;
use function Livewire\Volt\title;

layout('components.layouts.admin');
title(fn () => __('Tambah User'));

state([
    'name' => '',
    'email' => '',
    'password' => '',
    'password_confirmation' => '',
    'role' => null,
    'is_active' => true,
]);

mount(function () {
    $this->role = old('role');
});

$store = action(function () {
    $validated = $this->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'password' => ['required', 'string', Password::defaults(), 'confirmed'],
        'role' => ['nullable', 'exists:roles,name'],
        'is_active' => ['boolean'],
    ]);

    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        'is_active' => $validated['is_active'],
    ]);

    if (! empty($validated['role'])) {
        $user->assignRole($validated['role']);
    }

    $this->dispatch('toast', message: __('User berhasil dibuat.'), variant: 'success');
    $this->redirect(route('godmode.users.index'), navigate: true);
});

$roles = computed(fn () => Role::all()); ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Tambah User') }}</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Buat user baru untuk sistem') }}</p>
            </div>
            <flux:button :href="route('godmode.users.index')" variant="ghost" icon="arrow-left" wire:navigate>
                {{ __('Kembali') }}
            </flux:button>
        </div>

        <flux:card>
            <form wire:submit="store" class="space-y-6">
                <flux:input wire:model="name" name="name" :label="__('Nama')" type="text" required autofocus />

                <flux:input wire:model="email" name="email" :label="__('Email')" type="email" required />

                <flux:input wire:model="password" name="password" :label="__('Password')" type="password" required viewable />

                <flux:input wire:model="password_confirmation" name="password_confirmation" :label="__('Konfirmasi Password')" type="password" required viewable />

                <flux:radio.group wire:model="role" name="role" label="{{ __('Role') }}" variant="segmented" size="sm">
                    @foreach ($this->roles as $roleItem)
                    <flux:radio value="{{ $roleItem->name }}" :label="$roleItem->name" />
                    @endforeach
                </flux:radio.group>

                <flux:switch wire:model="is_active" name="is_active" label="{{ __('Status Aktif') }}" description="{{ __('User dapat login jika status aktif') }}" />

                <div class="flex items-center gap-4">
                    <flux:button type="submit" variant="primary">
                        {{ __('Simpan') }}
                    </flux:button>
                    <flux:button :href="route('godmode.users.index')" variant="ghost" wire:navigate>
                        {{ __('Batal') }}
                    </flux:button>
                </div>
            </form>
        </flux:card>
</div>

