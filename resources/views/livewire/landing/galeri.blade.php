<?php

use App\Models\Galeri;
use function Livewire\Volt\{layout, state, computed, action};

layout('components.layouts.landing');

$galeri = computed(function () {
    return Galeri::published()
        ->with('media')
        ->orderBy('published_at', 'desc')
        ->get();
});

state([
    'selectedImage' => null,
    'showLightbox' => false,
]);

$openLightbox = action(function ($mediaId) {
    $this->selectedImage = $mediaId;
    $this->showLightbox = true;
});

$closeLightbox = action(function () {
    $this->showLightbox = false;
    $this->selectedImage = null;
});

?>

<div>
    <!-- Hero Section -->
    <div class="relative isolate pt-14">
        <div aria-hidden="true"
            class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80"
            data-gsap="galeri-hero-blur-1">
            <div style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"
                class="relative left-[calc(50%-11rem)] aspect-1155/678 w-144.5 -translate-x-1/2 rotate-30 bg-gradient-to-tr from-orange-400 to-red-500 opacity-30 sm:left-[calc(50%-30rem)] sm:w-288.75 dark:opacity-20">
            </div>
        </div>
        <div class="py-24 sm:py-32 lg:pb-40">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <h1 class="text-5xl font-semibold tracking-tight text-balance text-gray-900 sm:text-7xl dark:text-white"
                        data-gsap="galeri-hero-title">
                        Galeri Dokumentasi
                    </h1>
                    <p class="mt-8 text-lg font-medium text-pretty text-gray-500 sm:text-xl/8 dark:text-gray-400"
                        data-gsap="galeri-hero-description">
                        Dokumentasi kegiatan, pertunjukan, dan momen-momen berharga dari Sanggar Tabuh Permatadhi Swara.
                        Setiap foto menceritakan perjalanan kami dalam melestarikan seni tradisional Bali.
                    </p>
                </div>
            </div>
        </div>
        <div aria-hidden="true"
            class="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]"
            data-gsap="galeri-hero-blur-2">
            <div style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"
                class="relative left-[calc(50%+3rem)] aspect-1155/678 w-144.5 -translate-x-1/2 bg-gradient-to-tr from-orange-400 to-red-500 opacity-30 sm:left-[calc(50%+36rem)] sm:w-288.75 dark:opacity-20">
            </div>
        </div>
    </div>

    <!-- Gallery Grid Section -->
    <div class="bg-white py-24 sm:py-32 dark:bg-gray-900" data-gsap="galeri-grid-section"
        data-lazy-section="galeri-grid">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            @if($this->galeri->count() > 0)
            <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($this->galeri as $item)
                @if($item->getMedia('galeri_images')->count() > 0)
                <div class="flex flex-col space-y-4" data-gsap="galeri-item">
                    <div class="space-y-2">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white leading-tight">{{ $item->title }}
                        </h3>
                        @if($item->description)
                        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ $item->description }}</p>
                        @endif
                    </div>
                    <div class="grid grid-cols-1 gap-4">
                        @foreach($item->getMedia('galeri_images') as $media)
                        <div class="group relative cursor-pointer overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800 aspect-square"
                            wire:click="openLightbox({{ $media->id }})" data-gsap="galeri-image"
                            style="position: relative;">
                            <img src="{{ $media->getUrl() }}" srcset="{{ $media->getSrcset() }}"
                                sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 25vw"
                                alt="{{ $item->title }}" loading="lazy"
                                class="h-full w-full object-cover group-hover:scale-110 transition-transform duration-300 pointer-events-none" />
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/0 to-black/0 opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-10 pointer-events-none">
                            </div>
                            <div
                                class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-20 pointer-events-none">
                                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                </svg>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                @endforeach
            </div>
            @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">Belum ada galeri</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Galeri akan segera tersedia.</p>
            </div>
            @endif
        </div>
    </div>


    <!-- Video Section -->
    <div class="bg-gray-50 py-24 sm:py-32 dark:bg-gray-800" data-gsap="galeri-video-section">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center mb-12">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl dark:text-white">Video Dokumentasi</h2>
                <p class="mt-4 text-lg leading-8 text-gray-600 dark:text-gray-300">
                    Saksikan penampilan dan kegiatan kami melalui video berikut.
                </p>
            </div>
            <div class="mx-auto max-w-4xl">
                <div class="relative w-full aspect-video rounded-2xl overflow-hidden shadow-xl">
                     <iframe class="absolute top-0 left-0 w-full h-full"
                        src="https://www.youtube-nocookie.com/embed/GyyPgoxu2Eo?si=-tez6nuruhiBl_Cf"
                        title="YouTube video player"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allowfullscreen>
                    </iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- Lightbox Modal -->
    @if($showLightbox && $selectedImage)
    @php
    $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($selectedImage);
    @endphp
    @if($media)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4" wire:click="closeLightbox"
        style="display: flex;">
        <div class="relative max-w-7xl w-full h-full flex items-center justify-center">
            <button type="button" wire:click.stop="closeLightbox"
                class="absolute top-4 right-4 text-white hover:text-gray-300 transition-colors z-10">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <div wire:click.stop class="pointer-events-auto">
                <img src="{{ $media->getUrl() }}" alt="{{ $media->name }}"
                    class="max-w-full max-h-full object-contain" />
            </div>
        </div>
    </div>
    @endif
    @endif
</div>