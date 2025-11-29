<?php

use App\Models\Galeri;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use function Livewire\Volt\{layout, title, state, mount, action, uses};

uses(WithFileUploads::class);

layout('components.layouts.admin');
title(fn () => __('Edit Galeri'));

state([
    'galeri' => null,
    'title' => '',
    'description' => '',
    'is_published' => 0,
    'published_at' => null,
    'images' => [],
    'imagesToDelete' => [],
]);

mount(function (Galeri $galeri) {
    $this->galeri = $galeri;
    $this->title = $galeri->title;
    $this->description = $galeri->description ?? '';
    $this->is_published = $galeri->is_published ? 1 : 0;
    $this->published_at = $galeri->published_at ? $galeri->published_at->format('Y-m-d\TH:i') : null;
});

$removeImage = action(function ($index) {
    unset($this->images[$index]);
    $this->images = array_values($this->images);
});

$deleteExistingImage = action(function ($mediaId) {
    $this->imagesToDelete[] = $mediaId;
});

$update = action(function () {
    $rules = [
        'title' => ['required', 'string', 'max:200'],
        'description' => ['nullable', 'string'],
        'is_published' => ['required', 'in:0,1'],
        'published_at' => ['nullable', 'date'],
        'images.*' => ['nullable', 'image', 'max:5120'], // 5MB max per image
    ];

    $validated = $this->validate($rules);

    // Set published_at if is_published is true and published_at is not set
    if ($validated['is_published'] && !$validated['published_at']) {
        $validated['published_at'] = now();
    } elseif (!$validated['is_published']) {
        $validated['published_at'] = null;
    } elseif ($validated['published_at']) {
        $validated['published_at'] = \Carbon\Carbon::parse($validated['published_at']);
    }

    // Update galeri
    $this->galeri->update([
        'title' => $validated['title'],
        'description' => $validated['description'] ?? null,
        'is_published' => (bool) $validated['is_published'],
        'published_at' => $validated['published_at'],
    ]);

    // Delete selected images
    if (!empty($this->imagesToDelete)) {
        foreach ($this->imagesToDelete as $mediaId) {
            $media = $this->galeri->getMedia('galeri_images')->firstWhere('id', $mediaId);
            if ($media) {
                $media->delete();
            }
        }
    }

    // Attach new images if uploaded
    if (!empty($this->images)) {
        foreach ($this->images as $image) {
            $this->galeri->addMedia($image->getRealPath())
                ->usingName($validated['title'])
                ->usingFileName($image->getClientOriginalName())
                ->toMediaCollection('galeri_images');
        }
    }

    $this->dispatch('toast', message: __('Galeri berhasil diupdate.'), variant: 'success');
    $this->redirect(route('godmode.galeri.index'), navigate: true);
}); ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Edit Galeri') }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Ubah informasi galeri') }}</p>
        </div>
        <flux:button :href="route('godmode.galeri.index')" variant="ghost" icon="arrow-left" wire:navigate>
            {{ __('Kembali') }}
        </flux:button>
    </div>

    <flux:card>
        <form wire:submit="update" class="space-y-6">
            <div class="space-y-4">
                <flux:input wire:model="title" name="title" :label="__('Judul Galeri')" type="text" required autofocus />

                <flux:textarea wire:model="description" name="description" :label="__('Deskripsi')" rows="5" />

                <flux:radio.group wire:model="is_published" name="is_published" :label="__('Status Publikasi')"
                    variant="segmented" size="sm">
                    <flux:radio value="1" :label="__('Published')" />
                    <flux:radio value="0" :label="__('Draft')" />
                </flux:radio.group>

                <flux:input wire:model="published_at" name="published_at" :label="__('Tanggal Publikasi')" type="datetime-local" />

                <div>
                    <flux:label>{{ __('Gambar Galeri') }}</flux:label>
                    <div class="mt-2">
                        @if($galeri->getMedia('galeri_images')->count() > 0)
                        <div class="mb-4">
                            <p class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Gambar Saat Ini') }}</p>
                            <div class="grid grid-cols-3 gap-4">
                                @foreach($galeri->getMedia('galeri_images') as $media)
                                    @if(!in_array($media->id, $imagesToDelete))
                                    <div class="relative">
                                        @php
                                            $thumbUrl = $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : null;
                                            $imageUrl = $thumbUrl ?: $media->getUrl();
                                        @endphp
                                        <img src="{{ $imageUrl }}" alt="{{ $media->name }}"
                                            class="h-32 w-full rounded-lg object-cover border border-gray-300 dark:border-gray-600" />
                                        <button type="button" wire:click="deleteExistingImage({{ $media->id }})"
                                            class="absolute top-2 right-2 rounded-full bg-red-600 p-1 text-white hover:bg-red-700">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if (!empty($images))
                        <div class="mb-4">
                            <p class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Gambar Baru') }}</p>
                            <div class="grid grid-cols-3 gap-4">
                                @foreach ($images as $index => $image)
                                <div class="relative">
                                    <img src="{{ $image->temporaryUrl() }}" alt="Preview {{ $index + 1 }}"
                                        class="h-32 w-full rounded-lg object-cover border border-gray-300 dark:border-gray-600" />
                                    <button type="button" wire:click="removeImage({{ $index }})"
                                        class="absolute top-2 right-2 rounded-full bg-red-600 p-1 text-white hover:bg-red-700">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <flux:file-upload wire:model="images" label="{{ __('Upload Gambar Baru') }}" multiple>
                            <flux:file-upload.dropzone heading="{{ __('Drop file atau klik untuk memilih') }}"
                                text="JPG, PNG, GIF up to 5MB (Multiple files)" with-progress inline />
                        </flux:file-upload>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('Format: JPG, PNG, GIF. Maksimal 5MB per gambar. Anda dapat mengupload multiple gambar.') }}
                        </p>
                    </div>
                    @error('images.*')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center gap-4">
                <flux:button type="submit" variant="primary">
                    {{ __('Simpan') }}
                </flux:button>
                <flux:button :href="route('godmode.galeri.index')" variant="ghost" wire:navigate>
                    {{ __('Batal') }}
                </flux:button>
            </div>
        </form>
    </flux:card>
</div>

