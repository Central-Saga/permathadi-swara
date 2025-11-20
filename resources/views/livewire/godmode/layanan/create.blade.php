<?php

use App\Models\Layanan;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use function Livewire\Volt\{layout, title, state, action, computed, uses};

uses(WithFileUploads::class);

layout('components.layouts.admin');
title(fn () => __('Tambah Layanan'));

state([
    'name' => '',
    'slug' => '',
    'description' => '',
    'price' => '',
    'is_active' => 1,
    'cover_image' => null,
    'slugManuallyEdited' => false,
]);

$generateSlug = action(function () {
    // Always generate new slug from name
    if (empty($this->name)) {
        if (!$this->slugManuallyEdited) {
            $this->slug = '';
        }
        return;
    }

    // Generate expected slug from current name
    $expectedSlug = Str::slug($this->name);

    // Only auto-update if slug hasn't been manually edited
    // Check if current slug matches what would be generated from name
    if (!$this->slugManuallyEdited || $this->slug === $expectedSlug || empty($this->slug)) {
        $baseSlug = $expectedSlug;
        $slug = $baseSlug;
        $counter = 1;

        // Check if slug exists and make it unique
        while (Layanan::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $this->slug = $slug;
        $this->slugManuallyEdited = false; // Reset flag since we're auto-generating
    }
});

$markSlugAsEdited = action(function () {
    // Check if slug matches what would be generated from name
    $expectedSlug = Str::slug($this->name);
    if ($this->slug !== $expectedSlug && !empty($this->slug)) {
        $this->slugManuallyEdited = true;
    }
});

$store = action(function () {
    $validated = $this->validate([
        'name' => ['required', 'string', 'max:100'],
        'slug' => ['nullable', 'string', 'max:120', 'unique:layanan,slug'],
        'description' => ['nullable', 'string'],
        'price' => ['nullable', 'numeric', 'min:0'],
        'is_active' => ['required', 'in:0,1'],
        'cover_image' => ['nullable', 'image', 'max:5120'], // 5MB max
    ]);

    // Generate slug if empty
    if (empty($validated['slug']) && !empty($validated['name'])) {
        $validated['slug'] = Str::slug($validated['name']);

        // Ensure slug is unique
        $baseSlug = $validated['slug'];
        $counter = 1;
        while (Layanan::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $baseSlug . '-' . $counter;
            $counter++;
        }
    }

    // Create layanan
    $layanan = Layanan::create([
        'name' => $validated['name'],
        'slug' => $validated['slug'] ?? null,
        'description' => $validated['description'] ?? null,
        'price' => $validated['price'] ?? null,
        'is_active' => (bool) $validated['is_active'],
    ]);

    // Attach cover image if uploaded
    if ($this->cover_image) {
        $layanan->addMedia($this->cover_image->getRealPath())
            ->usingName($validated['name'])
            ->usingFileName($this->cover_image->getClientOriginalName())
            ->toMediaCollection('layanan_cover');
    }

    $this->dispatch('toast', message: __('Layanan berhasil dibuat.'), variant: 'success');
    $this->redirect(route('godmode.layanan.index'), navigate: true);
}); ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Tambah Layanan') }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Buat layanan baru untuk sanggar') }}</p>
        </div>
        <flux:button :href="route('godmode.layanan.index')" variant="ghost" icon="arrow-left" wire:navigate>
            {{ __('Kembali') }}
        </flux:button>
    </div>

    <flux:card>
        <form wire:submit="store" class="space-y-6">
            <div class="space-y-4">
                <div x-data>
                    <flux:input wire:model.live.debounce.300ms="name" name="name" :label="__('Nama Layanan')"
                        type="text" required autofocus x-on:input.debounce.300ms="$wire.generateSlug()" />
                </div>

                <div x-data class="mt-4">
                    <flux:input wire:model.live.debounce.300ms="slug" name="slug" :label="__('Slug')" type="text"
                        placeholder="{{ __('Akan otomatis di-generate dari nama (unique)') }}"
                        x-on:input.debounce.300ms="$wire.markSlugAsEdited()" />
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {{ __('Slug akan otomatis dibuat dari nama layanan dan dijamin unique. Anda bisa mengubahnya
                        manual jika diperlukan.') }}
                    </p>
                </div>

                <flux:textarea wire:model="description" name="description" :label="__('Deskripsi Lengkap')" rows="5" />

                <flux:input wire:model="price" name="price" :label="__('Harga Langganan')" type="number" step="0.01"
                    min="0" placeholder="{{ __('Contoh: 500000') }}" />

                <flux:radio.group wire:model="is_active" name="is_active" :label="__('Status Aktif')"
                    variant="segmented" size="sm">
                    <flux:radio value="1" :label="__('Aktif')" />
                    <flux:radio value="0" :label="__('Tidak Aktif')" />
                </flux:radio.group>

                <div>
                    <flux:label>{{ __('Gambar Cover') }}</flux:label>
                    <div class="mt-2">
                        @if ($cover_image)
                        <div class="mb-4">
                            <p class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Preview Gambar')
                                }}</p>
                            <img src="{{ $cover_image->temporaryUrl() }}" alt="Preview"
                                class="h-48 w-full rounded-lg object-cover border border-gray-300 dark:border-gray-600" />
                        </div>
                        @endif
                        <flux:file-upload wire:model="cover_image" label="{{ __('Upload Gambar Cover') }}">
                            <flux:file-upload.dropzone heading="{{ __('Drop file atau klik untuk memilih') }}"
                                text="JPG, PNG, GIF up to 5MB" with-progress inline />
                        </flux:file-upload>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('Format: JPG, PNG, GIF. Maksimal 5MB') }}
                        </p>
                    </div>
                    @error('cover_image')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center gap-4">
                <flux:button type="submit" variant="primary">
                    {{ __('Simpan') }}
                </flux:button>
                <flux:button :href="route('godmode.layanan.index')" variant="ghost" wire:navigate>
                    {{ __('Batal') }}
                </flux:button>
            </div>
        </form>
    </flux:card>
</div>