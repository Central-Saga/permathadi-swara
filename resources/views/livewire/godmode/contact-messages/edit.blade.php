<?php

use App\Models\ContactMessage;
use function Livewire\Volt\{layout, title, state, mount, action};

layout('components.layouts.admin');
title(fn () => __('Edit Pesan Kontak'));

state([
    'message' => null,
    'status' => 'new',
]);

mount(function (ContactMessage $contactMessage) {
    $this->message = $contactMessage;
    $this->status = $contactMessage->status;
});

$update = action(function () {
    $validated = $this->validate([
        'status' => ['required', 'in:new,read,archived'],
    ], [
        'status.required' => 'Status harus dipilih.',
        'status.in' => 'Status tidak valid.',
    ]);

    $this->message->update([
        'status' => $validated['status'],
    ]);

    $this->dispatch('toast', message: __('Status pesan berhasil diupdate.'), variant: 'success');
    $this->redirect(route('godmode.contact-messages.index'), navigate: true);
}); ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Edit Pesan Kontak') }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Update status pesan kontak') }}</p>
        </div>
        <flux:button :href="route('godmode.contact-messages.index')" variant="ghost" icon="arrow-left" wire:navigate>
            {{ __('Kembali') }}
        </flux:button>
    </div>

    <flux:card>
        <form wire:submit="update" class="space-y-6">
            <div>
                <flux:label>{{ __('Status') }} <span class="text-red-500">*</span></flux:label>
                <flux:select 
                    variant="listbox"
                    wire:model="status" 
                    name="status" 
                    class="mt-2" 
                    required>
                    <flux:select.option value="new">{{ __('New') }}</flux:select.option>
                    <flux:select.option value="read">{{ __('Read') }}</flux:select.option>
                    <flux:select.option value="archived">{{ __('Archived') }}</flux:select.option>
                </flux:select>
                @error('status')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-4">
                <flux:button type="submit" variant="primary">
                    {{ __('Simpan') }}
                </flux:button>
                <flux:button :href="route('godmode.contact-messages.index')" variant="ghost" wire:navigate>
                    {{ __('Batal') }}
                </flux:button>
            </div>
        </form>
    </flux:card>

    <flux:card>
        <div class="space-y-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">{{ __('Detail Pesan') }}</h2>
            </div>

            <div class="space-y-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">{{ __('Data Pengirim') }}</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <flux:label>{{ __('Nama') }}</flux:label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $message->name }}</div>
                        </div>
                        <div>
                            <flux:label>{{ __('Email') }}</flux:label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $message->email ?? '-' }}</div>
                        </div>
                        <div>
                            <flux:label>{{ __('Phone') }}</flux:label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $message->phone ?? '-' }}</div>
                        </div>
                        <div>
                            <flux:label>{{ __('Status Saat Ini') }}</flux:label>
                            <div class="mt-1">
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $message->status_badge_color }}">
                                    {{ ucfirst($message->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">{{ __('Pesan') }}</h3>
                    <div class="space-y-4">
                        <div>
                            <flux:label>{{ __('Subject') }}</flux:label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-white font-semibold">{{ $message->subject }}</div>
                        </div>
                        <div>
                            <flux:label>{{ __('Message') }}</flux:label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-white whitespace-pre-wrap bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">{{ $message->message }}</div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <flux:label>{{ __('Created At') }}</flux:label>
                                <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $message->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                            <div>
                                <flux:label>{{ __('Updated At') }}</flux:label>
                                <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $message->updated_at->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </flux:card>
</div>

