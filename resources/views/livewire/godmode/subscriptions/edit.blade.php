<?php

use App\Models\Subscription;
use function Livewire\Volt\{layout, title, state, mount, action};

layout('components.layouts.admin');
title(fn () => __('Edit Langganan'));

state([
    'subscription' => null,
    'status' => 'pending',
]);

mount(function (Subscription $subscription) {
    $this->subscription = $subscription;
    $this->subscription->load(['anggota.user', 'layanan', 'payments']);
    $this->status = $subscription->status;
});

$update = action(function () {
    $validated = $this->validate([
        'status' => ['required', 'in:pending,active,expired,canceled'],
    ], [
        'status.required' => 'Status langganan harus dipilih.',
        'status.in' => 'Status langganan tidak valid.',
    ]);

    // Validasi: subscription hanya bisa diaktifkan jika ada payment dengan status 'paid'
    if ($validated['status'] === 'active') {
        $hasPaidPayment = $this->subscription->payments()
            ->where('status', 'paid')
            ->exists();
        
        if (!$hasPaidPayment) {
            $this->addError('status', 'Langganan tidak dapat diaktifkan karena belum ada pembayaran yang berstatus Paid. Silakan update status pembayaran terlebih dahulu.');
            return;
        }
    }

    // Update subscription - hanya status
    $this->subscription->update([
        'status' => $validated['status'],
    ]);

    $this->dispatch('toast', message: __('Status langganan berhasil diupdate.'), variant: 'success');
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
            <!-- Informasi Langganan (Readonly) -->
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 p-4 space-y-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Informasi Langganan') }}</h3>
                
                <div>
                    <flux:label>{{ __('Anggota') }}</flux:label>
                    <flux:input 
                        :value="($subscription->anggota->user->name ?? 'Anggota #' . $subscription->anggota_id) . ' (' . ($subscription->anggota->user->email ?? '') . ')'" 
                        disabled 
                        class="mt-2" />
                </div>

                <div>
                    <flux:label>{{ __('Layanan') }}</flux:label>
                    <flux:input :value="$subscription->layanan->name ?? 'Layanan #' . $subscription->layanan_id" disabled class="mt-2" />
                </div>

                <div>
                    <flux:label>{{ __('Tanggal Mulai') }}</flux:label>
                    <flux:input :value="$subscription->start_date->format('d/m/Y')" disabled class="mt-2" />
                </div>

                <div>
                    <flux:label>{{ __('Tanggal Berakhir') }}</flux:label>
                    <flux:input :value="$subscription->end_date ? $subscription->end_date->format('d/m/Y') : '-'" disabled class="mt-2" />
                </div>

                @if ($subscription->notes)
                <div>
                    <flux:label>{{ __('Catatan') }}</flux:label>
                    <flux:textarea :value="$subscription->notes" disabled rows="3" class="mt-2" />
                </div>
                @endif

                <div>
                    <flux:label>{{ __('Pembayaran') }}</flux:label>
                    <div class="mt-2 space-y-2">
                        @forelse ($subscription->payments as $payment)
                        <div class="flex items-center justify-between rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-3">
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $payment->formatted_amount }}
                                </span>
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $payment->status_badge_color }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 capitalize">
                                    {{ $payment->method }}
                                </span>
                            </div>
                            @if ($payment->status === 'paid')
                            <span class="text-xs text-green-600 dark:text-green-400 font-medium">✓ Paid</span>
                            @endif
                        </div>
                        @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada pembayaran</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Status (Editable) -->
            <div>
                <flux:label>{{ __('Status') }} <span class="text-red-500">*</span></flux:label>
                <flux:radio.group wire:model="status" name="status" variant="cards" class="mt-2 max-sm:flex-col" required>
                    <flux:radio value="pending">
                        <flux:radio.indicator />
                        <div class="flex-1">
                            <flux:heading class="leading-4">{{ __('Pending') }}</flux:heading>
                            <flux:text size="sm" class="mt-2">{{ __('Menunggu pembayaran atau verifikasi') }}</flux:text>
                        </div>
                    </flux:radio>
                    <flux:radio value="active">
                        <flux:radio.indicator />
                        <div class="flex-1">
                            <flux:heading class="leading-4">{{ __('Active') }}</flux:heading>
                            <flux:text size="sm" class="mt-2">{{ __('Langganan aktif dan dapat digunakan') }}</flux:text>
                        </div>
                    </flux:radio>
                    <flux:radio value="expired">
                        <flux:radio.indicator />
                        <div class="flex-1">
                            <flux:heading class="leading-4">{{ __('Expired') }}</flux:heading>
                            <flux:text size="sm" class="mt-2">{{ __('Langganan telah berakhir') }}</flux:text>
                        </div>
                    </flux:radio>
                    <flux:radio value="canceled">
                        <flux:radio.indicator />
                        <div class="flex-1">
                            <flux:heading class="leading-4">{{ __('Canceled') }}</flux:heading>
                            <flux:text size="sm" class="mt-2">{{ __('Langganan dibatalkan') }}</flux:text>
                        </div>
                    </flux:radio>
                </flux:radio.group>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ __('Hanya status yang dapat diubah. Untuk mengaktifkan langganan, pastikan ada pembayaran dengan status Paid terlebih dahulu.') }}
                </p>
                @error('status')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

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

