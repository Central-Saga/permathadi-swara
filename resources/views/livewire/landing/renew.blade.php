<?php

use App\Models\Anggota;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use function Livewire\Volt\{layout, state, mount, action, computed, uses};

uses(WithFileUploads::class);

layout('components.layouts.landing');

state([
    'subscription' => null,
    'currentStep' => 1,
    // Step 1: Renewal Method
    'renewal_method' => 'extend', // 'extend' or 'new'
    'start_date' => null,
    'end_date' => null,
    'notes' => '',
    // Step 2: Payment
    'payment_option' => 'later', // 'now' or 'later'
    'method' => 'transfer',
    'bank_name' => '',
    'account_number' => '',
    'account_holder' => '',
    'proof_file' => null,
]);

mount(function (Subscription $subscription) {
    $user = Auth::user();
    $anggota = $user->anggota;

    if (!$anggota) {
        return redirect()->route('landing.history');
    }

    // Validasi subscription harus milik user yang login
    if ($subscription->anggota_id !== $anggota->id) {
        $this->dispatch('toast', message: 'Anda tidak memiliki akses ke langganan ini.', variant: 'error');
        return redirect()->route('landing.history');
    }

    // Validasi subscription bisa diperpanjang
    if (!$subscription->canBeRenewed()) {
        $this->dispatch('toast', message: 'Langganan ini tidak dapat diperpanjang.', variant: 'warning');
        return redirect()->route('landing.history');
    }

    $this->subscription = $subscription;
    $this->subscription->load(['layanan']);

    // Set default dates
    if ($this->renewal_method === 'extend') {
        // Extend dari end_date yang ada
        $this->start_date = $subscription->end_date ? $subscription->end_date->copy()->addDay()->format('Y-m-d') : now()->format('Y-m-d');
    } else {
        // New subscription dari sekarang
        $this->start_date = now()->format('Y-m-d');
    }
    $this->end_date = \Carbon\Carbon::parse($this->start_date)->addDays($subscription->layanan->duration)->format('Y-m-d');
});

$nextStep = action(function () {
    if ($this->currentStep === 1) {
        // Validate step 1
        $validated = $this->validate([
            'renewal_method' => ['required', 'in:extend,new'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'renewal_method.required' => 'Metode perpanjangan harus dipilih.',
            'renewal_method.in' => 'Metode perpanjangan tidak valid.',
            'start_date.required' => 'Tanggal mulai harus diisi.',
            'start_date.date' => 'Tanggal mulai tidak valid.',
            'end_date.required' => 'Tanggal berakhir harus diisi.',
            'end_date.date' => 'Tanggal berakhir tidak valid.',
            'end_date.after' => 'Tanggal berakhir harus setelah tanggal mulai.',
            'notes.max' => 'Catatan maksimal 1000 karakter.',
        ]);

        $this->currentStep = 2;
    }
});

$previousStep = action(function () {
    if ($this->currentStep === 2) {
        $this->currentStep = 1;
    }
});

$calculateEndDate = action(function () {
    if ($this->start_date && $this->subscription && $this->subscription->layanan && $this->subscription->layanan->duration) {
        try {
            $startDate = \Carbon\Carbon::parse($this->start_date);
            $endDate = $startDate->copy()->addDays($this->subscription->layanan->duration);
            $this->end_date = $endDate->format('Y-m-d');
        } catch (\Exception $e) {
            $this->end_date = '';
        }
    } else {
        $this->end_date = '';
    }
});

$updateStartDate = action(function () {
    if ($this->renewal_method === 'extend' && $this->subscription && $this->subscription->end_date) {
        $this->start_date = $this->subscription->end_date->copy()->addDay()->format('Y-m-d');
    } else {
        $this->start_date = now()->format('Y-m-d');
    }
    $this->calculateEndDate();
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

$submit = action(function () {
    $user = Auth::user();
    $anggota = $user->anggota;

    if (!$anggota) {
        $this->addError('general', 'Data anggota tidak ditemukan.');
        return;
    }

    // Validate step 1
    $validated = $this->validate([
        'renewal_method' => ['required', 'in:extend,new'],
        'start_date' => ['required', 'date'],
        'end_date' => ['required', 'date', 'after:start_date'],
        'notes' => ['nullable', 'string', 'max:1000'],
    ], [
        'renewal_method.required' => 'Metode perpanjangan harus dipilih.',
        'renewal_method.in' => 'Metode perpanjangan tidak valid.',
        'start_date.required' => 'Tanggal mulai harus diisi.',
        'start_date.date' => 'Tanggal mulai tidak valid.',
        'end_date.required' => 'Tanggal berakhir harus diisi.',
        'end_date.date' => 'Tanggal berakhir tidak valid.',
        'end_date.after' => 'Tanggal berakhir harus setelah tanggal mulai.',
        'notes.max' => 'Catatan maksimal 1000 karakter.',
    ]);

    if ($this->renewal_method === 'extend') {
        // Extend existing subscription
        $this->subscription->update([
            'end_date' => $this->end_date,
            'status' => 'active', // Set to active jika expired
            'notes' => $this->notes ? ($this->subscription->notes ? $this->subscription->notes . "\n\nPerpanjangan: " . $this->notes : 'Perpanjangan: ' . $this->notes) : $this->subscription->notes,
        ]);
        $subscription = $this->subscription;
    } else {
        // Create new subscription
        $subscription = Subscription::create([
            'anggota_id' => $anggota->id,
            'layanan_id' => $this->subscription->layanan_id,
            'status' => 'pending',
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'notes' => $this->notes ?: null,
            'renewed_from_id' => $this->subscription->id,
        ]);
    }

    // Create payment if user chose to pay now
    if ($this->payment_option === 'now') {
        // Validate payment fields
        $paymentRules = [
            'method' => ['required', 'in:cash,transfer'],
        ];

        $paymentMessages = [
            'method.required' => 'Metode pembayaran harus dipilih.',
            'method.in' => 'Metode pembayaran tidak valid.',
        ];

        // Jika transfer, wajib bank_name dan proof_file
        if ($this->method === 'transfer') {
            $paymentRules['bank_name'] = ['required', 'string', 'max:255'];
            $paymentRules['proof_file'] = ['required', 'image', 'max:5120']; // 5MB max
            $paymentMessages['bank_name.required'] = 'Bank tujuan harus dipilih.';
            $paymentMessages['proof_file.required'] = 'Bukti pembayaran wajib diisi untuk transfer.';
            $paymentMessages['proof_file.image'] = 'Bukti pembayaran harus berupa gambar.';
            $paymentMessages['proof_file.max'] = 'Ukuran bukti pembayaran maksimal 5MB.';
        }

        $paymentValidated = $this->validate($paymentRules, $paymentMessages);

        // Create payment - status selalu pending
        $payment = Payment::create([
            'subscription_id' => $subscription->id,
            'amount' => $this->subscription->layanan->price,
            'method' => $paymentValidated['method'],
            'status' => 'pending', // Selalu pending
            'paid_at' => null,
            'bank_name' => $this->method === 'transfer' ? ($paymentValidated['bank_name'] ?? null) : null,
            'account_number' => $this->method === 'transfer' ? ($this->account_number ?? null) : null,
            'account_holder' => $this->method === 'transfer' ? ($this->account_holder ?? null) : null,
        ]);

        // Upload proof file jika transfer
        if ($this->method === 'transfer' && $this->proof_file) {
            $media = $payment->addMedia($this->proof_file->getRealPath())
                ->usingName('Payment Proof - ' . $payment->id)
                ->usingFileName($this->proof_file->getClientOriginalName())
                ->toMediaCollection('payment_proof');
        }
    }

    $this->dispatch('toast', message: 'Berhasil memperpanjang langganan!', variant: 'success');
    $this->redirect(route('landing.history'), navigate: true);
});

?>

<div class="relative isolate bg-white dark:bg-gray-900 min-h-full">
    <!-- Background Blur Effects -->
    <div aria-hidden="true" class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80"
        data-gsap="renew-blur-1">
        <div style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"
            class="relative left-[calc(50%-11rem)] aspect-1155/678 w-144.5 -translate-x-1/2 rotate-30 bg-gradient-to-tr from-orange-400 to-red-500 opacity-30 sm:left-[calc(50%-30rem)] sm:w-288.75 dark:opacity-20">
        </div>
    </div>

    <div class="relative mx-auto max-w-4xl px-4 pt-16 pb-12 sm:px-6 sm:pt-24 sm:pb-16 lg:px-8">
        <!-- Program Info Preview -->
        <div class="mb-8" data-gsap="renew-info">
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm">
                <div class="flex items-start gap-4">
                    @if ($this->subscription->layanan->getFirstMedia('layanan_cover'))
                    <div class="h-20 w-20 shrink-0 overflow-hidden rounded-lg">
                        <x-optimized-image :model="$this->subscription->layanan" collection="layanan_cover"
                            :alt="$this->subscription->layanan->name" sizes="80px" loading="eager" class="h-full w-full object-cover"
                            :responsive="true" :placeholder="true" />
                    </div>
                    @endif
                    <div class="flex-1">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                            Perpanjang: {{ $this->subscription->layanan->name }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ Str::limit($this->subscription->layanan->description, 100) }}
                        </p>
                        <div class="mt-2 flex items-center gap-4 text-sm">
                            <span class="font-medium text-gray-900 dark:text-white">
                                Rp {{ number_format($this->subscription->layanan->price, 0, ',', '.') }}
                            </span>
                            <span class="text-gray-500 dark:text-gray-400">
                                @if ($this->subscription->layanan->duration >= 365)
                                {{ round($this->subscription->layanan->duration / 365) }} Tahun
                                @elseif ($this->subscription->layanan->duration >= 30)
                                {{ round($this->subscription->layanan->duration / 30) }} Bulan
                                @else
                                {{ $this->subscription->layanan->duration }} Hari
                                @endif
                            </span>
                        </div>
                        @if ($this->subscription->end_date)
                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Langganan saat ini berakhir: {{ $this->subscription->end_date->format('d/m/Y') }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Wizard Steps Indicator -->
        <div class="mb-8" data-gsap="renew-steps">
            <div class="flex items-center justify-center">
                <div class="flex items-center">
                    <!-- Step 1 -->
                    <div class="flex items-center">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full {{ $this->currentStep >= 1 ? 'bg-orange-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400' }}">
                            @if ($this->currentStep > 1)
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            @else
                            <span class="text-sm font-semibold">1</span>
                            @endif
                        </div>
                        <span
                            class="ml-2 text-sm font-medium {{ $this->currentStep >= 1 ? 'text-orange-600 dark:text-orange-400' : 'text-gray-500 dark:text-gray-400' }}">
                            Metode Perpanjangan
                        </span>
                    </div>

                    <!-- Connector -->
                    <div
                        class="mx-4 h-0.5 w-16 {{ $this->currentStep >= 2 ? 'bg-orange-600' : 'bg-gray-200 dark:bg-gray-700' }}">
                    </div>

                    <!-- Step 2 -->
                    <div class="flex items-center">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full {{ $this->currentStep >= 2 ? 'bg-orange-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400' }}">
                            <span class="text-sm font-semibold">2</span>
                        </div>
                        <span
                            class="ml-2 text-sm font-medium {{ $this->currentStep >= 2 ? 'text-orange-600 dark:text-orange-400' : 'text-gray-500 dark:text-gray-400' }}">
                            Pembayaran
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Wizard Form -->
        <flux:card data-gsap="renew-form">
            <form wire:submit.prevent="submit" class="space-y-6">
                @error('general')
                <div class="rounded-md bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4">
                    <div class="flex">
                        <div class="shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor"
                                aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ $message }}</p>
                        </div>
                    </div>
                </div>
                @enderror

                <!-- Step 1: Renewal Method -->
                @if ($this->currentStep === 1)
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Metode Perpanjangan
                        </h3>
                    </div>

                    <div>
                        <flux:label>{{ __('Pilih Metode Perpanjangan') }} <span class="text-red-500">*</span></flux:label>
                        <flux:radio.group wire:model.live="renewal_method" name="renewal_method" variant="segmented"
                            size="sm" class="mt-2" x-on:change="$wire.updateStartDate()">
                            <flux:radio value="extend" :label="__('Perpanjang Langganan Saat Ini')" />
                            <flux:radio value="new" :label="__('Buat Langganan Baru')" />
                        </flux:radio.group>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            @if ($this->renewal_method === 'extend')
                            Langganan saat ini akan diperpanjang dari tanggal berakhir yang ada.
                            @else
                            Akan dibuat langganan baru terpisah dari langganan saat ini.
                            @endif
                        </p>
                        @error('renewal_method')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <flux:label>{{ __('Tanggal Mulai') }} <span class="text-red-500">*</span></flux:label>
                        <flux:date-picker wire:model.live="start_date" name="start_date"
                            min="{{ now()->format('Y-m-d') }}" required x-on:change="$wire.calculateEndDate()">
                            <x-slot name="trigger">
                                <flux:date-picker.input />
                            </x-slot>
                        </flux:date-picker>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            @if ($this->renewal_method === 'extend')
                            Tanggal mulai akan dihitung dari tanggal berakhir langganan saat ini + 1 hari.
                            @else
                            Tanggal mulai untuk langganan baru.
                            @endif
                        </p>
                        @error('start_date')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <flux:label>{{ __('Tanggal Berakhir') }}</flux:label>
                        <flux:input value="{{ $this->formattedEndDate }}" name="end_date_display" type="text" readonly
                            placeholder="{{ __('Akan dihitung otomatis') }}" />
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ __('Tanggal berakhir dihitung otomatis dari tanggal mulai + durasi layanan') }}
                        </p>
                        @error('end_date')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <flux:textarea wire:model="notes" name="notes" :label="__('Catatan (Opsional)')" rows="4"
                        :placeholder="__('Tambahkan catatan jika diperlukan')" />

                    <div class="flex items-center justify-end gap-4 pt-4">
                        <flux:button type="button" :href="route('landing.history')"
                            variant="ghost" wire:navigate>
                            Batal
                        </flux:button>
                        <flux:button type="button" wire:click="nextStep" variant="primary">
                            Lanjut
                        </flux:button>
                    </div>
                </div>
                @endif

                <!-- Step 2: Payment Details -->
                @if ($this->currentStep === 2)
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Pembayaran
                        </h3>
                    </div>

                    <div>
                        <flux:label>{{ __('Pilih Opsi Pembayaran') }}</flux:label>
                        <flux:radio.group wire:model.live="payment_option" name="payment_option" variant="segmented"
                            size="sm" class="mt-2">
                            <flux:radio value="later" :label="__('Bayar Nanti')" />
                            <flux:radio value="now" :label="__('Bayar Sekarang')" />
                        </flux:radio.group>
                    </div>

                    @if ($this->payment_option === 'now')
                    <div class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-6">
                        <div>
                            <flux:label>{{ __('Jumlah Pembayaran') }}</flux:label>
                            <flux:input :value="'Rp ' . number_format($this->subscription->layanan->price, 0, ',', '.')" disabled
                                class="mt-2" />
                        </div>

                        <flux:radio.group wire:model.live="method" name="method" :label="__('Metode Pembayaran')"
                            variant="segmented" size="sm">
                            <flux:radio value="cash" :label="__('Cash')" />
                            <flux:radio value="transfer" :label="__('Transfer')" />
                        </flux:radio.group>

                        @if ($this->method === 'transfer')
                        <div>
                            <flux:label>{{ __('Pilih Bank Tujuan') }} <span class="text-red-500">*</span></flux:label>
                            <div class="mt-2 space-y-3">
                                @php
                                $banks = config('payment.banks', []);
                                @endphp
                                @foreach($banks as $bank)
                                <label
                                    class="flex items-start gap-3 p-4 border border-gray-200 dark:border-gray-700 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors {{ $this->bank_name === $bank['name'] ? 'ring-2 ring-orange-500 bg-orange-50 dark:bg-orange-900/20' : '' }}">
                                    <input type="radio" wire:model.live="bank_name" name="bank_name"
                                        value="{{ $bank['name'] }}"
                                        x-on:change="$wire.account_number = '{{ $bank['account'] }}'; $wire.account_holder = '{{ $bank['holder'] }}';"
                                        class="mt-1 h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 dark:border-gray-600">
                                    <div class="flex-1">
                                        <div class="font-semibold text-gray-900 dark:text-white">{{ $bank['name'] }}
                                        </div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-2 space-y-1">
                                            <div class="flex items-center gap-2">
                                                <span class="font-medium">No. Rekening:</span>
                                                <span class="font-mono font-semibold text-gray-900 dark:text-white">{{
                                                    $bank['account'] }}</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-medium">Atas Nama:</span>
                                                <span class="text-gray-900 dark:text-white">{{ $bank['holder'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                            @error('bank_name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <flux:label>
                                {{ __('Bukti Pembayaran') }} <span class="text-red-500">*</span>
                            </flux:label>
                            <div class="mt-2">
                                @if ($proof_file)
                                <div class="mb-4">
                                    <p class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ __('Preview Gambar') }}
                                    </p>
                                    <img src="{{ $proof_file->temporaryUrl() }}" alt="Preview"
                                        class="h-48 w-full rounded-lg object-cover border border-gray-300 dark:border-gray-600" />
                                </div>
                                @endif
                                <flux:file-upload wire:model="proof_file" label="{{ __('Upload Bukti Pembayaran') }}">
                                    <flux:file-upload.dropzone heading="{{ __('Drop file atau klik untuk memilih') }}"
                                        text="JPG, PNG, GIF up to 5MB" with-progress inline />
                                </flux:file-upload>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('Format: JPG, PNG, GIF. Maksimal 5MB.') }}
                                </p>
                            </div>
                            @error('proof_file')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        @endif
                    </div>
                    @endif

                    <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <flux:button type="button" wire:click="previousStep" variant="ghost">
                            Kembali
                        </flux:button>
                        <flux:button type="submit" variant="primary">
                            Selesai
                        </flux:button>
                    </div>
                </div>
                @endif
            </form>
        </flux:card>
    </div>
</div>

