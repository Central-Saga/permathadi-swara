<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;
use function Livewire\Volt\{layout, title, state, mount, action, computed};

layout('components.layouts.admin');
title(fn () => __('Edit User'));

state([
    'user' => null,
    'name' => '',
    'email' => '',
    'password' => '',
    'password_confirmation' => '',
    'role' => null,
]);

mount(function (User $user) {
    $this->user = $user;
    $this->user->load('roles');
    $this->name = $user->name;
    $this->email = $user->email;
    $this->role = $user->roles->first()?->name ?? old('role');
});

$update = action(function () {
    $rules = [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $this->user->id],
        'role' => ['nullable', 'exists:roles,name'],
    ];

    if (!empty($this->password)) {
        $rules['password'] = ['required', 'string', Password::defaults(), 'confirmed'];
    }

    $validated = $this->validate($rules);

    $this->user->update([
        'name' => $validated['name'],
        'email' => $validated['email'],
    ]);

    if (!empty($validated['password'])) {
        $this->user->update([
            'password' => Hash::make($validated['password']),
        ]);
    }

    if (!empty($validated['role'])) {
        $this->user->syncRoles([$validated['role']]);
    } else {
        $this->user->syncRoles([]);
    }

    $this->redirect(route('godmode.users.index'), navigate: true);
});

$roles = computed(fn () => Role::all()); ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Edit User') }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Ubah informasi user') }}</p>
        </div>
        <flux:button :href="route('godmode.users.index')" variant="ghost" wire:navigate>
            {{ __('Kembali') }}
        </flux:button>
    </div>

    <flux:card>
        <form wire:submit="update" class="space-y-6">
            <flux:input wire:model="name" name="name" :label="__('Nama')" type="text" required autofocus />

            <flux:input wire:model="email" name="email" :label="__('Email')" type="email" required />

            <flux:input wire:model="password" name="password" :label="__('Password')" type="password" viewable />

            <flux:input wire:model="password_confirmation" name="password_confirmation"
                :label="__('Konfirmasi Password')" type="password" viewable />

            <flux:radio.group wire:model="role" name="role" label="{{ __('Role') }}" variant="segmented" size="sm">
                @foreach ($this->roles as $roleItem)
                <flux:radio value="{{ $roleItem->name }}" :label="$roleItem->name" />
                @endforeach
            </flux:radio.group>

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