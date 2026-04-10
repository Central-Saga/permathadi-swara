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
title(fn () => __('Edit User'));

state([
    'user' => null,
    'name' => '',
    'email' => '',
    'password' => '',
    'password_confirmation' => '',
    'role' => null,
    'is_active' => true,
]);

mount(function (User $user) {
    $this->user = $user;
    $this->user->load('roles');
    $this->name = $user->name;
    $this->email = $user->email;
    $this->is_active = $user->is_active;
    $this->role = $user->roles->first()?->name ?? old('role');
});

$updateProfile = action(function () {
    $currentUser = auth()->user();
    $rules = [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$this->user->id],
        'role' => ['nullable', 'exists:roles,name'],
    ];

    if (! empty($this->password)) {
        $rules['password'] = ['required', 'string', Password::defaults(), 'confirmed'];
    }

    $validated = $this->validate($rules);

    if (! $currentUser->hasRole('Super Admin')) {
        if ($this->user->hasRole('Super Admin') && ! empty($validated['role']) && $validated['role'] !== 'Super Admin') {
            $this->dispatch('toast', message: __('Anda tidak memiliki izin untuk mengubah role Super Admin'), variant: 'danger');

            return;
        }

        if ($this->user->hasRole('Admin') && $this->user->id !== $currentUser->id && ! empty($validated['role']) && $validated['role'] !== 'Admin') {
            $this->dispatch('toast', message: __('Anda tidak memiliki izin untuk mengubah role Admin lain'), variant: 'danger');

            return;
        }
    }

    $this->user->update([
        'name' => $validated['name'],
        'email' => $validated['email'],
    ]);

    if (! empty($validated['password'])) {
        $this->user->update([
            'password' => Hash::make($validated['password']),
        ]);
    }

    if (! empty($validated['role'])) {
        $this->user->syncRoles([$validated['role']]);
    } else {
        $this->user->syncRoles([]);
    }

    $this->dispatch('toast', message: __('Profil user berhasil diupdate.'), variant: 'success');
    $this->redirect(route('godmode.users.index'), navigate: true);
});

$toggleStatus = action(function () {
    $currentUser = auth()->user();

    $canToggle = true;

    if ($this->user->id === $currentUser->id && $this->user->hasRole('Super Admin')) {
        $canToggle = false;
        $this->dispatch('toast', message: __('Anda tidak dapat menonaktifkan akun Super Admin Anda sendiri'), variant: 'danger');

        return;
    }

    if (! $currentUser->hasRole('Super Admin') && $this->user->hasRole('Super Admin')) {
        $canToggle = false;
        $this->dispatch('toast', message: __('Anda tidak memiliki izin untuk mengubah status Super Admin'), variant: 'danger');

        return;
    }

    if (! $currentUser->hasRole('Super Admin') && $this->user->hasRole('Admin')) {
        $canToggle = false;
        $this->dispatch('toast', message: __('Anda tidak memiliki izin untuk mengubah status Admin'), variant: 'danger');

        return;
    }

    if ($canToggle) {
        $newStatus = ! $this->user->is_active;
        $this->user->update(['is_active' => $newStatus]);
        $this->is_active = $newStatus;

        $this->dispatch('toast',
            message: __('User :name sekarang :status', [
                'name' => $this->user->name,
                'status' => $newStatus ? __('Aktif') : __('Non Aktif'),
            ]),
            variant: 'success'
        );
    }
});

$roles = computed(fn () => Role::all()); ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Edit User') }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Ubah informasi user') }}</p>
        </div>
        <flux:button :href="route('godmode.users.index')" variant="ghost" icon="arrow-left" wire:navigate>
            {{ __('Kembali') }}
        </flux:button>
    </div>

    @php
        $isSuperAdminCurrent = auth()->user()->hasRole('Super Admin');
        $isSuperAdminTarget = $this->user->hasRole('Super Admin');
        $isAdminTarget = $this->user->hasRole('Admin');
        $isSelf = $this->user->id === auth()->id();

        // Cek apakah bisa toggle status
        $canToggleStatus = true;
        if ($isSelf && $isSuperAdminTarget) {
            $canToggleStatus = false; // Super Admin tidak bisa toggle diri sendiri
        } elseif (! $isSuperAdminCurrent) {
            if ($isSuperAdminTarget || $isAdminTarget) {
                $canToggleStatus = false; // Admin tidak bisa toggle Super Admin atau Admin lain
            }
        }
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Card Edit Profil -->
        <flux:card>
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Edit Profil') }}</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Ubah nama, email, password, dan role') }}</p>
            </div>

            <form wire:submit="updateProfile" class="space-y-6">
                <flux:input wire:model="name" name="name" :label="__('Nama')" type="text" required autofocus />

                <flux:input wire:model="email" name="email" :label="__('Email')" type="email" required />

                <flux:input wire:model="password" name="password" :label="__('Password')" type="password" viewable />

                <flux:input wire:model="password_confirmation" name="password_confirmation"
                    :label="__('Konfirmasi Password')" type="password" viewable />

                <flux:radio.group wire:model="role" name="role" label="{{ __('Role') }}" variant="segmented" size="sm">
                    @foreach ($this->roles as $roleItem)
                    @php
                        $canSelect = true;
                        if (! $isSuperAdminCurrent) {
                            // Admin tidak bisa mengubah role Super Admin
                            if ($isSuperAdminTarget && $roleItem->name !== 'Super Admin') {
                                $canSelect = false;
                            }
                            // Admin tidak bisa mengubah role Admin lain (kecuali dirinya sendiri)
                            if ($isAdminTarget && ! $isSelf && $roleItem->name !== 'Admin') {
                                $canSelect = false;
                            }
                        }
                    @endphp
                    @if ($canSelect)
                    <flux:radio value="{{ $roleItem->name }}" :label="$roleItem->name" />
                    @endif
                    @endforeach
                </flux:radio.group>

                <div class="flex items-center gap-4">
                    <flux:button type="submit" variant="primary">
                        {{ __('Simpan Profil') }}
                    </flux:button>
                    <flux:button :href="route('godmode.users.index')" variant="ghost" wire:navigate>
                        {{ __('Batal') }}
                    </flux:button>
                </div>
            </form>
        </flux:card>

        <!-- Card Status - Terpisah -->
        <flux:card>
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Status Akun') }}</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Kelola status aktif/nonaktif user') }}</p>
            </div>

            <div class="space-y-6">
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-zinc-800 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">{{ __('Status Saat Ini') }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('User dapat login jika status aktif') }}</p>
                    </div>
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium {{ $this->user->statusBadgeColor() }}">
                        {{ $this->user->statusLabel() }}
                    </span>
                </div>

                @if ($canToggleStatus)
                <div class="flex items-center gap-4">
                    <flux:button wire:click="toggleStatus" variant="{{ $this->user->is_active ? 'filled' : 'primary' }}" color="{{ $this->user->is_active ? 'red' : 'green' }}"
                        icon="{{ $this->user->is_active ? 'x-mark' : 'check' }}"
                        class="w-full {{ $this->user->is_active ? '!bg-red-600 hover:!bg-red-700' : '!bg-green-600 hover:!bg-green-700' }} !text-white">
                        {{ $this->user->is_active ? __('Nonaktifkan User') : __('Aktifkan User') }}
                    </flux:button>
                </div>
                @else
                <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                        @if ($isSelf && $isSuperAdminTarget)
                            {{ __('Anda tidak dapat menonaktifkan akun Super Admin Anda sendiri.') }}
                        @else
                            {{ __('Anda tidak memiliki izin untuk mengubah status user ini.') }}
                        @endif
                    </p>
                </div>
                @endif
            </div>
        </flux:card>
    </div>
</div>
