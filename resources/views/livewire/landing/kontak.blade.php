<?php

use App\Models\ContactMessage;
use Livewire\Volt\Component;
use function Livewire\Volt\{layout};

layout('layouts.landing');

new class extends Component {
    public string $name = '';
    public string $email = '';
    public string $subject = '';
    public string $message = '';

    public function submit(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        ContactMessage::create($validated);

        $this->reset('name', 'email', 'subject', 'message');
        
        $this->dispatch('message-sent');
    }
}; ?>

<div>
    <div class="pt-24 pb-24 sm:pt-32 sm:pb-32">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl lg:text-center">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl dark:text-white">Kontak Kami</h2>
                <p class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-400">
                    Hubungi kami untuk informasi lebih lanjut tentang program dan pendaftaran.
                </p>
            </div>
            
            <div class="mx-auto mt-16 max-w-2xl">
                <flux:card>
                    <form wire:submit="submit" class="space-y-6">
                        <flux:input wire:model="name" name="name" :label="__('Nama')" type="text" required autofocus />

                        <flux:input wire:model="email" name="email" :label="__('Email')" type="email" required />

                        <flux:input wire:model="subject" name="subject" :label="__('Subjek')" type="text" required />

                        <flux:textarea wire:model="message" name="message" :label="__('Pesan')" rows="6" required />

                        <div class="flex items-center gap-4">
                            <flux:button type="submit" variant="primary" class="w-full">
                                {{ __('Kirim Pesan') }}
                            </flux:button>
                        </div>
                    </form>
                </flux:card>

                <x-action-message on="message-sent" class="mt-4">
                    <flux:alert variant="success">
                        {{ __('Pesan Anda berhasil dikirim. Kami akan menghubungi Anda segera.') }}
                    </flux:alert>
                </x-action-message>
            </div>
        </div>
    </div>
</div>

