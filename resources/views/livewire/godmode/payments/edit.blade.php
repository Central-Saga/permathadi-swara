<?php

use App\Models\Payment;
use function Livewire\Volt\{layout, title, state, mount, action};

layout('components.layouts.admin');
title(fn () => __('Edit Pembayaran'));

state([
    'payment' => null,
    'status' => 'pending',
]);

mount(function (Payment $payment) {
    $this->payment = $payment;
    $this->payment->load(['subscription.anggota.user', 'subscription.layanan']);
    $this->status = $payment->status;
});

$update = action(function () {
    $validated = $this->validate([
        'status' => ['required', 'in:pending,paid,failed'],
    ], [
        'status.required' => 'Status pembayaran harus dipilih.',
        'status.in' => 'Status pembayaran tidak valid.',
    ]);

    // Update payment - hanya status
    $this->payment->update([
        'status' => $validated['status'],
        'paid_at' => $validated['status'] === 'paid' ? ($this->payment->paid_at ?? now()) : null,
    ]);

    $this->dispatch('toast', message: __('Status pembayaran berhasil diupdate.'), variant: 'success');
    $this->redirect(route('godmode.payments.index'), navigate: true);
}); ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Edit Pembayaran') }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Ubah informasi pembayaran') }}</p>
        </div>
        <flux:button :href="route('godmode.payments.index')" variant="ghost" icon="arrow-left" wire:navigate>
            {{ __('Kembali') }}
        </flux:button>
    </div>

    <flux:card>
        <form wire:submit="update" class="space-y-6">
            <!-- Informasi Pembayaran (Readonly) -->
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 p-4 space-y-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Informasi Pembayaran') }}</h3>
                
                <div>
                    <flux:label>{{ __('Langganan') }}</flux:label>
                    <flux:input 
                        :value="($payment->subscription->anggota->user->name ?? 'Anggota #' . $payment->subscription->anggota_id) . ' - ' . ($payment->subscription->layanan->name ?? 'Layanan #' . $payment->subscription->layanan_id)" 
                        disabled 
                        class="mt-2" />
                </div>

                <div>
                    <flux:label>{{ __('Amount') }}</flux:label>
                    <flux:input :value="'Rp ' . number_format($payment->amount, 0, ',', '.')" disabled class="mt-2" />
                </div>

                <div>
                    <flux:label>{{ __('Method Pembayaran') }}</flux:label>
                    <flux:input :value="ucfirst($payment->method)" disabled class="mt-2" />
                </div>

                @if ($payment->bank_name)
                <div>
                    <flux:label>{{ __('Bank Tujuan') }}</flux:label>
                    <flux:input :value="$payment->bank_name" disabled class="mt-2" />
                </div>
                @endif

                @if ($payment->account_number)
                <div>
                    <flux:label>{{ __('Nomor Rekening') }}</flux:label>
                    <flux:input :value="$payment->account_number" disabled class="mt-2" />
                </div>
                @endif

                @if ($payment->account_holder)
                <div>
                    <flux:label>{{ __('Atas Nama') }}</flux:label>
                    <flux:input :value="$payment->account_holder" disabled class="mt-2" />
                </div>
                @endif

                @if ($payment->getFirstMediaUrl('payment_proof'))
                <div>
                    <flux:label>{{ __('Bukti Pembayaran') }}</flux:label>
                    <div class="mt-2">
                        <a href="{{ $payment->getFirstMediaUrl('payment_proof') }}" target="_blank"
                            class="inline-flex items-center gap-2 text-sm text-orange-600 dark:text-orange-400 hover:text-orange-700 dark:hover:text-orange-300">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Lihat Bukti Pembayaran
                        </a>
                    </div>
                </div>
                @endif
            </div>

            <!-- Status (Editable) -->
            <div>
                <flux:label>{{ __('Status') }} <span class="text-red-500">*</span></flux:label>
                <flux:radio.group wire:model="status" name="status" variant="cards" class="mt-2 max-sm:flex-col" required>
                    <flux:radio value="pending">
                        <flux:radio.indicator />
                        <div class="flex-1">
                            <flux:heading class="leading-4">{{ __('Pending') }}</flux:heading>
                            <flux:text size="sm" class="mt-2">{{ __('Menunggu verifikasi pembayaran') }}</flux:text>
                        </div>
                    </flux:radio>
                    <flux:radio value="paid">
                        <flux:radio.indicator />
                        <div class="flex-1">
                            <flux:heading class="leading-4">{{ __('Paid') }}</flux:heading>
                            <flux:text size="sm" class="mt-2">{{ __('Pembayaran telah dikonfirmasi') }}</flux:text>
                        </div>
                    </flux:radio>
                    <flux:radio value="failed">
                        <flux:radio.indicator />
                        <div class="flex-1">
                            <flux:heading class="leading-4">{{ __('Failed') }}</flux:heading>
                            <flux:text size="sm" class="mt-2">{{ __('Pembayaran gagal atau ditolak') }}</flux:text>
                        </div>
                    </flux:radio>
                </flux:radio.group>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ __('Hanya status yang dapat diubah. Field lain tidak dapat diedit.') }}
                </p>
                @error('status')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-4">
                <flux:button type="submit" variant="primary">
                    {{ __('Simpan') }}
                </flux:button>
                <flux:button :href="route('godmode.payments.index')" variant="ghost" wire:navigate>
                    {{ __('Batal') }}
                </flux:button>
            </div>
        </form>
    </flux:card>
</div>

