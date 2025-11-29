<?php

use function Livewire\Volt\{layout, mount};

layout('components.layouts.landing');

mount(function ($layanan) {
    // Ensure layanan is active, otherwise redirect
    if (!$layanan->is_active) {
        return redirect()->route('landing.program');
    }
});

?>

<div>
    <x-landing.program-detail-hero :layanan="$layanan" />

    <div class="bg-white py-24 sm:py-32 dark:bg-gray-900" data-gsap="program-detail-section">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-3xl">
                <div class="space-y-8">
                    <!-- Program Information Card -->
                    <div class="overflow-hidden rounded-2xl bg-gray-50 shadow-sm ring-1 ring-gray-900/5 dark:bg-white/5 dark:ring-white/10" data-gsap="program-detail-card">
                        <div class="p-8 sm:p-10">
                            <div class="space-y-6">
                                <div>
                                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Informasi Program</h2>
                                </div>

                                <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Harga</dt>
                                        <dd class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">
                                            Rp {{ number_format($layanan->price, 0, ',', '.') }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Durasi</dt>
                                        <dd class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">
                                            @if ($layanan->duration >= 365)
                                            {{ round($layanan->duration / 365) }} Tahun
                                            @elseif ($layanan->duration >= 30)
                                            {{ round($layanan->duration / 30) }} Bulan
                                            @else
                                            {{ $layanan->duration }} Hari
                                            @endif
                                        </dd>
                                    </div>
                                </dl>

                                @if ($layanan->description)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Deskripsi</dt>
                                    <dd class="text-base text-gray-600 dark:text-gray-300 whitespace-pre-line">
                                        {{ $layanan->description }}
                                    </dd>
                                </div>
                                @endif

                                <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                                    <div class="flex flex-col sm:flex-row gap-4">
                                        <a href="{{ route('landing.kontak') }}"
                                            class="flex-1 inline-flex items-center justify-center rounded-lg bg-orange-600 px-6 py-3 text-base font-semibold text-white shadow-sm hover:bg-orange-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-600 transition-colors">
                                            Hubungi Kami
                                        </a>
                                        <a href="{{ route('landing.program') }}"
                                            class="flex-1 inline-flex items-center justify-center rounded-lg bg-white px-6 py-3 text-base font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:hover:bg-gray-700 transition-colors">
                                            Kembali ke Program
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

