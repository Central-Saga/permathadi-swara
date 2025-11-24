<?php

use App\Models\Payment;
use App\Models\Subscription;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use function Livewire\Volt\{layout, title, state, mount, action, computed, uses};

uses(WithFileUploads::class);

layout('components.layouts.admin');
title(fn () => __('Edit Pembayaran'));

state([
    'payment' => null,
    'subscription_id' => '',
    'amount' => '',
    'method' => 'transfer',
    'status' => 'pending',
    'proof_file' => null,
]);

mount(function (Payment $payment) {
    $this->payment = $payment;
    $this->payment->load(['subscription.anggota.user', 'subscription.layanan']);
    
    $this->subscription_id = $payment->subscription_id;
    $this->amount = number_format($payment->amount, 0, '', '');
    $this->method = $payment->method;
    $this->status = $payment->status;
});

$getSubscriptions = computed(function () {
    return Subscription::with(['anggota.user', 'layanan'])
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($subscription) {
            return [
                'id' => $subscription->id,
                'label' => ($subscription->anggota->user->name ?? 'Anggota #' . $subscription->anggota_id) . 
                          ' - ' . ($subscription->layanan->name ?? 'Layanan #' . $subscription->layanan_id) .
                          ($subscription->layanan->price ? ' (Rp ' . number_format($subscription->layanan->price, 0, ',', '.') . ')' : ''),
                'price' => $subscription->layanan->price ?? 0,
            ];
        });
});

$updateAmount = action(function () {
    if ($this->subscription_id) {
        $subscription = Subscription::with('layanan')->find($this->subscription_id);
        if ($subscription && $subscription->layanan && $subscription->layanan->price) {
            $this->amount = number_format($subscription->layanan->price, 0, '', '');
        }
    }
});

$update = action(function () {
    $subscription = Subscription::with('layanan')->findOrFail($this->subscription_id);
    $expectedAmount = $subscription->layanan->price ?? 0;
    
    // Convert amount to number (remove formatting)
    $amountValue = str_replace(['.', ','], '', $this->amount);
    $amountValue = (float) $amountValue;

    $validated = $this->validate([
        'subscription_id' => ['required', 'exists:subscriptions,id'],
        'amount' => ['required', 'numeric', 'min:0'],
        'method' => ['required', 'in:cash,transfer,qris,other'],
        'status' => ['required', 'in:pending,paid,failed'],
        'proof_file' => ['nullable', 'image', 'max:5120'], // 5MB max
    ], [
        'subscription_id.required' => 'Langganan harus dipilih.',
        'subscription_id.exists' => 'Langganan yang dipilih tidak valid.',
        'amount.required' => 'Amount harus diisi.',
        'amount.numeric' => 'Amount harus berupa angka.',
        'amount.min' => 'Amount harus lebih besar dari 0.',
        'method.required' => 'Method pembayaran harus dipilih.',
        'method.in' => 'Method pembayaran tidak valid.',
        'status.required' => 'Status pembayaran harus dipilih.',
        'status.in' => 'Status pembayaran tidak valid.',
        'proof_file.image' => 'Bukti pembayaran harus berupa gambar.',
        'proof_file.max' => 'Ukuran bukti pembayaran maksimal 5MB.',
    ]);

    // Validasi amount harus sesuai dengan harga layanan
    if (abs($amountValue - $expectedAmount) > 0.01) {
        $this->addError('amount', 'Amount harus sesuai dengan harga layanan (Rp ' . number_format($expectedAmount, 0, ',', '.') . ').');
        return;
    }

    // Validasi proof_file required jika status = 'paid' dan belum ada bukti sebelumnya
    $hasExistingProof = $this->payment->getFirstMediaUrl('payment_proof');
    if ($validated['status'] === 'paid' && !$this->proof_file && !$hasExistingProof) {
        $this->addError('proof_file', 'Bukti pembayaran wajib diisi jika status adalah Paid.');
        return;
    }

    // Update payment
    $this->payment->update([
        'subscription_id' => $validated['subscription_id'],
        'amount' => $amountValue,
        'method' => $validated['method'],
        'status' => $validated['status'],
        'paid_at' => $validated['status'] === 'paid' ? ($this->payment->paid_at ?? now()) : null,
    ]);

    // Replace proof file if new file uploaded
    if ($this->proof_file) {
        // Delete old file
        $this->payment->clearMediaCollection('payment_proof');
        
        // Add new file
        $this->payment->addMedia($this->proof_file->getRealPath())
            ->usingName('Payment Proof - ' . $this->payment->id)
            ->usingFileName($this->proof_file->getClientOriginalName())
            ->toMediaCollection('payment_proof');
    }

    $this->dispatch('toast', message: __('Pembayaran berhasil diupdate.'), variant: 'success');
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
            <div>
                <flux:label>{{ __('Langganan') }} <span class="text-red-500">*</span></flux:label>
                <flux:select wire:model.live="subscription_id" name="subscription_id" variant="listbox" searchable
                    placeholder="{{ __('Pilih langganan...') }}" class="mt-2" required
                    x-on:change="$wire.updateAmount()">
                    @forelse ($this->getSubscriptions as $sub)
                    <flux:select.option value="{{ $sub['id'] }}">
                        {{ $sub['label'] }}
                    </flux:select.option>
                    @empty
                    <flux:select.option disabled>{{ __('Tidak ada langganan tersedia') }}</flux:select.option>
                    @endforelse
                </flux:select>
                @error('subscription_id')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <flux:label>{{ __('Amount') }} <span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="amount" name="amount" type="text" required class="mt-2" />
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ __('Amount harus sesuai dengan harga layanan dari langganan yang dipilih') }}
                </p>
                @error('amount')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <flux:label>{{ __('Method Pembayaran') }} <span class="text-red-500">*</span></flux:label>
                <flux:select wire:model="method" name="method" class="mt-2" required>
                    <flux:select.option value="cash">{{ __('Cash') }}</flux:select.option>
                    <flux:select.option value="transfer">{{ __('Transfer') }}</flux:select.option>
                    <flux:select.option value="qris">{{ __('QRIS') }}</flux:select.option>
                    <flux:select.option value="other">{{ __('Lainnya') }}</flux:select.option>
                </flux:select>
                @error('method')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <flux:label>{{ __('Status') }} <span class="text-red-500">*</span></flux:label>
                <flux:select wire:model="status" name="status" class="mt-2" required>
                    <flux:select.option value="pending">{{ __('Pending') }}</flux:select.option>
                    <flux:select.option value="paid">{{ __('Paid') }}</flux:select.option>
                    <flux:select.option value="failed">{{ __('Failed') }}</flux:select.option>
                </flux:select>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ __('Jika status adalah Paid, bukti pembayaran wajib diisi (jika belum ada)') }}
                </p>
                @error('status')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <flux:label>{{ __('Bukti Pembayaran') }} @if($status === 'paid' && !$payment->getFirstMediaUrl('payment_proof')) <span class="text-red-500">*</span> @endif</flux:label>
                <div class="mt-2">
                    @if ($proof_file)
                    <div class="mb-4">
                        <p class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Preview Gambar Baru') }}</p>
                        <img src="{{ $proof_file->temporaryUrl() }}" alt="Preview"
                            class="h-48 w-full rounded-lg object-cover border border-gray-300 dark:border-gray-600" />
                    </div>
                    @elseif ($payment->getFirstMediaUrl('payment_proof'))
                    <div class="mb-4">
                        <p class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Bukti Pembayaran Saat Ini') }}</p>
                        <img src="{{ $payment->getFirstMediaUrl('payment_proof') }}" alt="Bukti Pembayaran"
                            class="h-48 w-full rounded-lg object-cover border border-gray-300 dark:border-gray-600" />
                    </div>
                    @endif
                    <flux:file-upload wire:model="proof_file" label="{{ __('Upload Bukti Pembayaran') }}">
                        <flux:file-upload.dropzone heading="{{ __('Drop file atau klik untuk memilih') }}"
                            text="JPG, PNG, GIF up to 5MB" with-progress inline />
                    </flux:file-upload>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Format: JPG, PNG, GIF. Maksimal 5MB. Upload gambar baru untuk mengganti bukti pembayaran lama.') }}
                    </p>
                </div>
                @error('proof_file')
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

