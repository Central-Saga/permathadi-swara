<?php

use App\Models\Anggota;
use App\Models\Layanan;
use App\Models\Subscription;
use function Livewire\Volt\{layout, title, state, action, computed};

layout('components.layouts.admin');
title(fn () => __('Tambah Langganan'));

state([
    'anggota_id' => '',
    'layanan_id' => '',
    'status' => 'pending',
    'start_date' => '',
    'end_date' => '',
    'notes' => '',
]);

$getAnggotas = computed(function () {
    return Anggota::with('user')->get()->map(function ($anggota) {
        return [
            'id' => $anggota->id,
            'name' => $anggota->user->name ?? 'Anggota #' . $anggota->id,
            'email' => $anggota->user->email ?? '',
        ];
    });
});

$getLayanans = computed(function () {
    return Layanan::where('is_active', true)->orderBy('name')->get();
});

$store = action(function () {
    $validated = $this->validate([
        'anggota_id' => ['required', 'exists:anggota,id'],
        'layanan_id' => ['required', 'exists:layanan,id'],
        'status' => ['required', 'in:pending,active,expired,canceled'],
        'start_date' => ['required', 'date'],
        'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        'notes' => ['nullable', 'string'],
    ]);

    Subscription::create($validated);

    $this->dispatch('toast', message: __('Langganan berhasil dibuat.'), variant: 'success');
    $this->redirect(route('godmode.subscriptions.index'), navigate: true);
}); ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Tambah Langganan') }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Buat langganan baru untuk anggota') }}</p>
        </div>
        <flux:button :href="route('godmode.subscriptions.index')" variant="ghost" icon="arrow-left" wire:navigate>
            {{ __('Kembali') }}
        </flux:button>
    </div>

    <flux:card>
        <form wire:submit="store" class="space-y-6">
            <div>
                <flux:label>{{ __('Anggota') }} <span class="text-red-500">*</span></flux:label>
                <flux:select wire:model="anggota_id" name="anggota_id" searchable placeholder="{{ __('Pilih anggota...') }}" class="mt-2" required>
                    @forelse ($this->getAnggotas as $anggota)
                    <flux:option value="{{ $anggota['id'] }}">
                        {{ $anggota['name'] }} ({{ $anggota['email'] }})
                    </flux:option>
                    @empty
                    <flux:option disabled>{{ __('Tidak ada anggota tersedia') }}</flux:option>
                    @endforelse
                </flux:select>
            </div>

            <div>
                <flux:label>{{ __('Layanan') }} <span class="text-red-500">*</span></flux:label>
                <flux:select wire:model="layanan_id" name="layanan_id" searchable placeholder="{{ __('Pilih layanan...') }}" class="mt-2" required>
                    @forelse ($this->getLayanans as $layanan)
                    <flux:option value="{{ $layanan->id }}">
                        {{ $layanan->name }}
                    </flux:option>
                    @empty
                    <flux:option disabled>{{ __('Tidak ada layanan tersedia') }}</flux:option>
                    @endforelse
                </flux:select>
            </div>

            <div>
                <flux:label>{{ __('Tanggal Mulai') }} <span class="text-red-500">*</span></flux:label>
                <flux:date-picker wire:model="start_date" name="start_date" with-today required>
                    <x-slot name="trigger">
                        <flux:date-picker.input />
                    </x-slot>
                </flux:date-picker>
            </div>

            <div>
                <flux:label>{{ __('Tanggal Berakhir') }}</flux:label>
                <flux:date-picker wire:model="end_date" name="end_date" with-today>
                    <x-slot name="trigger">
                        <flux:date-picker.input />
                    </x-slot>
                </flux:date-picker>
            </div>

            <div>
                <flux:label>{{ __('Status') }} <span class="text-red-500">*</span></flux:label>
                <flux:select wire:model="status" name="status" class="mt-2" required>
                    <flux:option value="pending">{{ __('Pending') }}</flux:option>
                    <flux:option value="active">{{ __('Active') }}</flux:option>
                    <flux:option value="expired">{{ __('Expired') }}</flux:option>
                    <flux:option value="canceled">{{ __('Canceled') }}</flux:option>
                </flux:select>
            </div>

            <flux:textarea wire:model="notes" name="notes" :label="__('Catatan')" rows="3" />

            <div class="flex items-center gap-4">
                <flux:button type="submit" variant="primary">
                    {{ __('Simpan') }}
                </flux:button>
                <flux:button :href="route('godmode.subscriptions.index')" variant="ghost" wire:navigate>
                    {{ __('Batal') }}
                </flux:button>
            </div>
        </form>
    </flux:card>
</div>

