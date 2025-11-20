<?php

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use function Livewire\Volt\{layout, title, state, mount, action};

layout('components.layouts.admin');
title(fn () => __('Edit Anggota'));

state([
    'anggota' => null,
    'user' => null,
    // User fields
    'name' => '',
    'email' => '',
    'password' => '',
    'password_confirmation' => '',
    // Anggota fields
    'telepon' => '',
    'alamat' => '',
    'tanggal_lahir' => '',
    'tanggal_registrasi' => '',
    'status' => 'Aktif',
    'catatan' => '',
]);

mount(function (Anggota $anggota) {
    $this->anggota = $anggota;
    $this->anggota->load('user');
    $this->user = $anggota->user;
    
    if ($this->user) {
        $this->name = $this->user->name;
        $this->email = $this->user->email;
    }
    
    $this->telepon = $anggota->telepon;
    $this->alamat = $anggota->alamat ?? '';
    $this->tanggal_lahir = $anggota->tanggal_lahir ? $anggota->tanggal_lahir->format('Y-m-d') : '';
    $this->tanggal_registrasi = $anggota->tanggal_registrasi->format('Y-m-d');
    $this->status = $anggota->status;
    $this->catatan = $anggota->catatan ?? '';
});

$update = action(function () {
    $rules = [
        // User validation
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $this->user->id],
        // Anggota validation
        'telepon' => ['required', 'string', 'max:20'],
        'alamat' => ['nullable', 'string'],
        'tanggal_lahir' => ['nullable', 'date'],
        'tanggal_registrasi' => ['required', 'date'],
        'status' => ['required', 'in:Aktif,Non Aktif'],
        'catatan' => ['nullable', 'string'],
    ];

    if (!empty($this->password)) {
        $rules['password'] = ['required', 'string', Password::defaults(), 'confirmed'];
    }

    $validated = $this->validate($rules);

    // Update user
    if ($this->user) {
        $this->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (!empty($validated['password'])) {
            $this->user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }
    }

    // Update anggota
    $this->anggota->update([
        'telepon' => $validated['telepon'],
        'alamat' => $validated['alamat'] ?? null,
        'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
        'tanggal_registrasi' => $validated['tanggal_registrasi'],
        'status' => $validated['status'],
        'catatan' => $validated['catatan'] ?? null,
    ]);

    $this->dispatch('toast', message: __('Anggota berhasil diupdate.'), variant: 'success');
    $this->redirect(route('godmode.anggota.index'), navigate: true);
}); ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Edit Anggota') }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Ubah informasi anggota dan user') }}</p>
        </div>
        <flux:button :href="route('godmode.anggota.index')" variant="ghost" icon="arrow-left" wire:navigate>
            {{ __('Kembali') }}
        </flux:button>
    </div>

    <flux:card>
        <form wire:submit="update" class="space-y-6">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Data User') }}</h2>
                <div class="space-y-4">
                    <flux:input wire:model="name" name="name" :label="__('Nama')" type="text" required autofocus />

                    <flux:input wire:model="email" name="email" :label="__('Email')" type="email" required />

                    <flux:input wire:model="password" name="password" :label="__('Password')" type="password" viewable />
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Kosongkan jika tidak ingin mengubah password') }}</p>

                    <flux:input wire:model="password_confirmation" name="password_confirmation"
                        :label="__('Konfirmasi Password')" type="password" viewable />
                </div>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Data Anggota') }}</h2>
                <div class="space-y-4">
                    <flux:input wire:model="telepon" name="telepon" :label="__('Telepon')" type="text" required />

                    <flux:textarea wire:model="alamat" name="alamat" :label="__('Alamat')" rows="3" />

                    <div>
                        <flux:label>{{ __('Tanggal Lahir') }}</flux:label>
                        <flux:date-picker wire:model="tanggal_lahir" name="tanggal_lahir">
                            <x-slot name="trigger">
                                <flux:date-picker.input />
                            </x-slot>
                        </flux:date-picker>
                    </div>

                    <div>
                        <flux:label>{{ __('Tanggal Registrasi') }} <span class="text-red-500">*</span></flux:label>
                        <flux:date-picker wire:model="tanggal_registrasi" name="tanggal_registrasi" with-today required>
                            <x-slot name="trigger">
                                <flux:date-picker.input />
                            </x-slot>
                        </flux:date-picker>
                    </div>

                    <flux:radio.group wire:model="status" name="status" :label="__('Status')" variant="segmented" size="sm">
                        <flux:radio value="Aktif" :label="__('Aktif')" />
                        <flux:radio value="Non Aktif" :label="__('Non Aktif')" />
                    </flux:radio.group>

                    <flux:textarea wire:model="catatan" name="catatan" :label="__('Catatan')" rows="3" />
                </div>
            </div>

            <div class="flex items-center gap-4">
                <flux:button type="submit" variant="primary">
                    {{ __('Simpan') }}
                </flux:button>
                <flux:button :href="route('godmode.anggota.index')" variant="ghost" wire:navigate>
                    {{ __('Batal') }}
                </flux:button>
            </div>
        </form>
    </flux:card>

    <div 
        x-data
        x-on:toast.window="
            if (window.Flux && typeof window.Flux.toast === 'function') {
                window.Flux.toast({
                    variant: $event.detail.variant || 'success',
                    text: $event.detail.message
                });
            }
        "
    ></div>
</div>

