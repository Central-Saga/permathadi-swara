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
    $this->permissions = $role->permissions->pluck('id')->toArray();
});

$update = action(function () {
    $validated = $this->validate([
        'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($this->role->id)],
        'permissions' => ['nullable', 'array'],
        'permissions.*' => ['exists:permissions,id'],
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

$permissions = computed(function () {
    return Permission::all()->groupBy(function ($permission) {
        $parts = explode(' ', $permission->name);
        return $parts[1] ?? 'other';
    });
}); ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Edit Role') }}</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Ubah role dan permission') }}</p>
            </div>
            <flux:button :href="route('godmode.roles.index')" variant="ghost" wire:navigate>
                {{ __('Kembali') }}
            </flux:button>
        </div>

        <flux:card>
            <form wire:submit="update" class="space-y-6">
                <flux:input wire:model="name" name="name" :label="__('Nama Role')" type="text" required autofocus />

                <div>
                    <flux:label>{{ __('Permission') }}</flux:label>
                    <div class="mt-2 space-y-4">
                        @foreach ($this->permissions as $group => $groupPermissions)
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                            <h3 class="mb-3 font-semibold text-gray-900 dark:text-white capitalize">
                                {{ $group }}
                            </h3>
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($groupPermissions as $permission)
                                <flux:checkbox wire:model="permissions" name="permissions[]" value="{{ $permission->id }}"
                                    :label="$permission->name" />
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
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

