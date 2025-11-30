<?php

use App\Mail\ContactConfirmation;
use App\Mail\ContactMessageNotification;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Mail;
use function Livewire\Volt\{layout, state, action};

layout('components.layouts.landing');

state([
    'name' => '',
    'email' => '',
    'phone' => '',
    'subject' => '',
    'message' => '',
]);

$submit = action(function () {
    $validated = $this->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255'],
        'phone' => ['nullable', 'string', 'max:20'],
        'subject' => ['required', 'string', 'max:255'],
        'message' => ['required', 'string', 'max:5000'],
    ]);

    $contactMessage = ContactMessage::create($validated);

    // Kirim 2 email secara background (queue):
    // 1. Email notifikasi ke admin (mirayasmin34@gmail.com)
    $adminEmail = env('CONTACT_EMAIL', 'mirayasmin34@gmail.com');
    if ($adminEmail) {
        // Email akan di-queue secara otomatis karena Mailable implement ShouldQueue
        Mail::to($adminEmail)->queue(new ContactMessageNotification($contactMessage));
    }

    // 2. Email konfirmasi ke customer
    // Email akan di-queue secara otomatis karena Mailable implement ShouldQueue
    Mail::to($contactMessage->email)->queue(new ContactConfirmation($contactMessage));

    $this->reset('name', 'email', 'phone', 'subject', 'message');

    $this->dispatch('message-sent');
}); ?>

<div>
    <x-landing.kontak-hero />

    <div class="bg-white py-24 sm:py-32 dark:bg-gray-900">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl">
                <flux:card>
                    <form wire:submit="submit" class="space-y-6">
                        <flux:input wire:model="name" name="name" :label="__('Nama')" type="text" required autofocus />

                        <flux:input wire:model="email" name="email" :label="__('Email')" type="email" required />

                        <flux:input wire:model="phone" name="phone" :label="__('Telepon')" type="tel"
                            placeholder="{{ __('Contoh: 081234567890') }}" />

                        <flux:input wire:model="subject" name="subject" :label="__('Subjek')" type="text" required />

                        <flux:editor wire:model="message" name="message" :label="__('Pesan')"
                            description="Tulis pesan Anda di sini" required />

                        <div class="flex items-center gap-4">
                            <flux:button type="submit" variant="primary" class="w-full">
                                {{ __('Kirim Pesan') }}
                            </flux:button>
                        </div>
                    </form>
                </flux:card>

                <x-action-message on="message-sent" class="mt-4">
                    <div
                        class="rounded-md bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4">
                        <div class="flex">
                            <div class="shrink-0">
                                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor"
                                    aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ __('Pesan Anda
                                    berhasil dikirim. Kami akan menghubungi Anda segera.') }}</p>
                            </div>
                        </div>
                    </div>
                </x-action-message>
            </div>
        </div>
    </div>
</div>