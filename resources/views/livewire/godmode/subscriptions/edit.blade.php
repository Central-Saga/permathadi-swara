<?php

use App\Models\Anggota;
use App\Models\Layanan;
use App\Models\Subscription;
use function Livewire\Volt\{layout, title, state, mount, action, computed};

layout('components.layouts.admin');
title(fn () => __('Edit Langganan'));

state([
    'subscription' => null,
    'anggota_id' => '',
    'layanan_id' => '',
    'status' => 'pending',
    'start_date' => '',
    'end_date' => '',
    'notes' => '',
]);

mount(function (Subscription $subscription) {
    $this->subscription = $subscription;
    $this->subscription->load(['anggota.user', 'layanan']);
    
    $this->anggota_id = $subscription->anggota_id;
    $this->layanan_id = $subscription->layanan_id;
    $this->status = $subscription->status;
    $this->start_date = $subscription->start_date->format('Y-m-d');
    $this->end_date = $subscription->end_date ? $subscription->end_date->format('Y-m-d') : '';
    $this->notes = $subscription->notes ?? '';
});

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
    return Layanan::orderBy('name')->get();
});

$calculateEndDate = action(function () {
    if ($this->layanan_id && $this->start_date) {
        $layanan = Layanan::find($this->layanan_id);
        if ($layanan && $layanan->duration) {
            $startDate = \Carbon\Carbon::parse($this->start_date);
            $endDate = $startDate->copy()->addDays($layanan->duration);
            $this->end_date = $endDate->format('Y-m-d');
        }
    } else {
        $this->end_date = '';
    }
});

$formattedEndDate = computed(function () {
    if ($this->end_date) {
        try {
            return \Carbon\Carbon::parse($this->end_date)->format('d/m/Y');
        } catch (\Exception $e) {
            return $this->end_date;
        }
    }
    return '';
});

$update = action(function () {
    $rules = [
        'anggota_id' => ['required', 'exists:anggota,id'],
        'layanan_id' => ['required', 'exists:layanan,id'],
        'status' => ['required', 'in:pending,active,expired,canceled'],
        'start_date' => ['required', 'date', 'after_or_equal:today'],
        'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        'notes' => ['nullable', 'string'],
    ];

    $validated = $this->validate($rules);

    // Auto-calculate end_date if not set
    if (empty($validated['end_date']) && !empty($validated['layanan_id']) && !empty($validated['start_date'])) {
        $layanan = Layanan::find($validated['layanan_id']);
        if ($layanan && $layanan->duration) {
            $startDate = \Carbon\Carbon::parse($validated['start_date']);
            $validated['end_date'] = $startDate->copy()->addDays($layanan->duration)->format('Y-m-d');
        }
    }

    $this->subscription->update($validated);

    $this->dispatch('toast', message: __('Langganan berhasil diupdate.'), variant: 'success');
    $this->redirect(route('godmode.subscriptions.index'), navigate: true);
}); ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Edit Langganan') }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Ubah informasi langganan') }}</p>
        </div>
        <flux:button :href="route('godmode.subscriptions.index')" variant="ghost" icon="arrow-left" wire:navigate>
            {{ __('Kembali') }}
        </flux:button>
    </div>

    <flux:card>
        <form wire:submit="update" class="space-y-6">
            <div>
                <flux:label>{{ __('Anggota') }} <span class="text-red-500">*</span></flux:label>
                <flux:select wire:model="anggota_id" name="anggota_id" variant="listbox" searchable placeholder="{{ __('Pilih anggota...') }}" class="mt-2" required>
                    @forelse ($this->getAnggotas as $anggota)
                    <flux:select.option value="{{ $anggota['id'] }}">
                        {{ $anggota['name'] }} ({{ $anggota['email'] }})
                    </flux:select.option>
                    @empty
                    <flux:select.option disabled>{{ __('Tidak ada anggota tersedia') }}</flux:select.option>
                    @endforelse
                </flux:select>
            </div>

            <div>
                <flux:label>{{ __('Layanan') }} <span class="text-red-500">*</span></flux:label>
                <flux:select wire:model.live="layanan_id" name="layanan_id" variant="listbox" searchable placeholder="{{ __('Pilih layanan...') }}" class="mt-2" required
                    x-on:change="$wire.calculateEndDate()">
                    @forelse ($this->getLayanans as $layanan)
                    <flux:select.option value="{{ $layanan->id }}">
                        {{ $layanan->name }} ({{ $layanan->duration ?? 30 }} hari)
                    </flux:select.option>
                    @empty
                    <flux:select.option disabled>{{ __('Tidak ada layanan tersedia') }}</flux:select.option>
                    @endforelse
                </flux:select>
            </div>

            <div>
                <flux:label>{{ __('Tanggal Mulai') }} <span class="text-red-500">*</span></flux:label>
                <flux:date-picker wire:model.live="start_date" name="start_date" min="{{ now()->format('Y-m-d') }}" required
                    x-on:change="$wire.calculateEndDate()">
                    <x-slot name="trigger">
                        <flux:date-picker.input />
                    </x-slot>
                </flux:date-picker>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ __('Tidak bisa memilih tanggal sebelum hari ini') }}
                </p>
            </div>

            <div>
                <flux:label>{{ __('Tanggal Berakhir') }}</flux:label>
                <flux:input value="{{ $this->formattedEndDate }}" name="end_date_display" type="text" readonly
                    placeholder="{{ __('Akan dihitung otomatis') }}" />
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ __('Tanggal berakhir dihitung otomatis dari tanggal mulai + durasi layanan') }}
                </p>
            </div>

            <div>
                <flux:label>{{ __('Status') }} <span class="text-red-500">*</span></flux:label>
                <flux:select wire:model="status" name="status" class="mt-2" required>
                    <flux:select.option value="pending">{{ __('Pending') }}</flux:select.option>
                    <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                    <flux:select.option value="expired">{{ __('Expired') }}</flux:select.option>
                    <flux:select.option value="canceled">{{ __('Canceled') }}</flux:select.option>
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

