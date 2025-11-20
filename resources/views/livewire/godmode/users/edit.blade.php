<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Volt\Component;
use Spatie\Permission\Models\Role;
use function Livewire\Volt\{layout, title};

layout('components.layouts.app');
title(fn () => __('Edit User'));

new class extends Component {
    public User $user;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public array $roles = [];

    public function mount(User $user): void
    {
        $this->user = $user;
        $this->user->load('roles');
        $this->name = $user->name;
        $this->email = $user->email;
        $this->roles = $user->roles->pluck('id')->toArray();
    }

    public function update(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $this->user->id],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
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

        if (isset($validated['roles'])) {
            $this->user->syncRoles($validated['roles']);
        } else {
            $this->user->syncRoles([]);
        }

        $this->redirect(route('godmode.users.index'), navigate: true);
    }

    public function with(): array
    {
        return [
            'allRoles' => Role::all(),
        ];
    }
}; ?>

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

                <flux:input wire:model="password" name="password" :label="__('Password Baru')" type="password" viewable
                    :placeholder="__('Kosongkan jika tidak ingin mengubah password')" />

                <flux:input wire:model="password_confirmation" name="password_confirmation" :label="__('Konfirmasi Password Baru')" type="password" viewable />

                <div>
                    <flux:label>{{ __('Role') }}</flux:label>
                    <div class="mt-2 space-y-2">
                        @foreach ($allRoles as $role)
                            <flux:checkbox wire:model="roles" name="roles[]" value="{{ $role->id }}" :label="$role->name" />
                        @endforeach
                    </div>
                </div>

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

