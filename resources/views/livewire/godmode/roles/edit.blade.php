<?php

use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use function Livewire\Volt\{layout, title, state, mount, action, computed};

layout('components.layouts.admin');
title(fn () => __('Edit Role'));

state([
    'role' => null,
    'name' => '',
    'permissions' => [],
]);

mount(function (Role $role) {
    $this->role = $role;
    $this->role->load('permissions');
    $this->name = $role->name;
    $this->permissions = $role->permissions->pluck('name')->toArray();
});

$update = action(function () {
    $validated = $this->validate([
        'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($this->role->id)],
        'permissions' => ['nullable', 'array'],
        'permissions.*' => ['exists:permissions,name'],
    ]);

    $this->role->update([
        'name' => $validated['name'],
    ]);

    if (isset($validated['permissions'])) {
        $this->role->syncPermissions($validated['permissions']);
    } else {
        $this->role->syncPermissions([]);
    }

    $this->redirect(route('godmode.roles.index'), navigate: true);
});

$getPermissions = computed(function () {
    return Permission::orderBy('name')->get();
}); ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Edit Role') }}</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Ubah role dan permission') }}</p>
            </div>
            <flux:button :href="route('godmode.roles.index')" variant="ghost" icon="arrow-left" wire:navigate>
                {{ __('Kembali') }}
            </flux:button>
        </div>

        <flux:card>
            <form wire:submit="update" class="space-y-6">
                <flux:input wire:model="name" name="name" :label="__('Nama Role')" type="text" required autofocus />

                <div>
                    <flux:label>{{ __('Permission') }}</flux:label>
                    <flux:pillbox wire:model="permissions" multiple searchable placeholder="{{ __('Pilih permission...') }}"
                        class="mt-2">
                        @php
                        $allPermissions = $this->getPermissions()->toArray() ?? [];
                        @endphp
                        @forelse ($allPermissions as $permission)
                        <flux:pillbox.option value="{{ $permission['name'] }}">{{ $permission['name'] }}
                        </flux:pillbox.option>
                        @empty
                        <div class="p-4 text-sm text-gray-500 dark:text-gray-400">{{ __('Tidak ada permission tersedia') }}
                        </div>
                        @endforelse
                    </flux:pillbox>
                </div>

                <div class="flex items-center gap-4">
                    <flux:button type="submit" variant="primary">
                        {{ __('Simpan') }}
                    </flux:button>
                    <flux:button :href="route('godmode.roles.index')" variant="ghost" wire:navigate>
                        {{ __('Batal') }}
                    </flux:button>
                </div>
            </form>
        </flux:card>
</div>

