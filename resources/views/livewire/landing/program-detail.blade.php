<?php

use App\Models\Layanan;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{layout, state, mount, action};

layout('components.layouts.landing');

state([
    'layanan' => null,
    'showLoginModal' => false,
]);

mount(function (Layanan $layanan) {
    // Ensure layanan is active, otherwise redirect
    if (!$layanan->is_active) {
        return redirect()->route('landing.program');
    }

    $this->layanan = $layanan;
});


$handleSubscribeClick = action(function () {
    if (!Auth::check()) {
        $this->showLoginModal = true;
        return;
    }

    // Redirect to subscribe page using slug explicitly
    return redirect()->route('landing.subscribe', ['layanan' => $this->layanan->slug]);
});

$closeLoginModal = action(function () {
    $this->showLoginModal = false;
});

?>

<div class="relative isolate bg-white dark:bg-gray-900 min-h-full">
    <!-- Background Blur Effects -->
    <div aria-hidden="true" class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80"
        data-gsap="program-detail-blur-1">
        <div style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"
            class="relative left-[calc(50%-11rem)] aspect-1155/678 w-144.5 -translate-x-1/2 rotate-30 bg-gradient-to-tr from-orange-400 to-red-500 opacity-30 sm:left-[calc(50%-30rem)] sm:w-288.75 dark:opacity-20">
        </div>
    </div>

    <div
        class="relative mx-auto max-w-2xl px-4 pt-16 pb-12 sm:px-6 sm:pt-24 sm:pb-16 lg:grid lg:max-w-7xl lg:grid-cols-2 lg:gap-x-8 lg:px-8">
        <!-- Product details -->
        <div class="lg:max-w-lg lg:self-end" data-gsap="program-detail-info">
            <div class="mt-4" data-gsap="program-detail-title">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                    {{ $this->layanan->name }}
                </h1>
            </div>

            <section aria-labelledby="information-heading" class="mt-4">
                <h2 id="information-heading" class="sr-only">Informasi Program</h2>

                <div class="flex items-center" data-gsap="program-detail-price">
                    <p class="text-lg text-gray-900 dark:text-white sm:text-xl">
                        Rp {{ number_format($this->layanan->price, 0, ',', '.') }}
                    </p>

                    <div class="ml-4 border-l border-gray-300 dark:border-gray-700 pl-4">
                        <div class="flex items-center">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    @if ($this->layanan->duration >= 365)
                                    {{ round($this->layanan->duration / 365) }} Tahun
                                    @elseif ($this->layanan->duration >= 30)
                                    {{ round($this->layanan->duration / 30) }} Bulan
                                    @else
                                    {{ $this->layanan->duration }} Hari
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($this->layanan->description)
                <div class="mt-4 space-y-6" data-gsap="program-detail-description">
                    <p class="text-base text-gray-500 dark:text-gray-400 whitespace-pre-line">
                        {{ $this->layanan->description }}
                    </p>
                </div>
                @endif

                <div class="mt-6 flex items-center" data-gsap="program-detail-status">
                    <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" aria-hidden="true"
                        class="size-5 shrink-0 text-green-500">
                        <path
                            d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z"
                            clip-rule="evenodd" fill-rule="evenodd" />
                    </svg>
                    <p class="ml-2 text-sm text-gray-500 dark:text-gray-400">Program tersedia dan siap untuk
                        didaftarkan
                    </p>
                </div>
            </section>
        </div>

        <!-- Product image -->
        <div class="mt-10 lg:col-start-2 lg:row-span-2 lg:mt-0 lg:self-center" data-gsap="program-detail-image">
            @if ($this->layanan->getFirstMedia('layanan_cover'))
            <div class="aspect-square w-full overflow-hidden rounded-lg">
                <x-optimized-image :model="$this->layanan" collection="layanan_cover" :alt="$this->layanan->name"
                    sizes="(max-width: 768px) 100vw, 800px" loading="eager" class="h-full w-full object-cover"
                    :responsive="true" :placeholder="true" />
            </div>
            @else
            <div class="aspect-square w-full overflow-hidden rounded-lg">
                <img 
                    src="{{ asset('images/dummy/' . (($this->layanan->id % 8) + 1) . '.jpg') }}" 
                    alt="{{ $this->layanan->name }}"
                    class="h-full w-full object-cover"
                    loading="eager"
                />
            </div>
            @endif
        </div>

        <!-- Product form -->
        <div class="mt-10 lg:col-start-1 lg:row-start-2 lg:max-w-lg lg:self-start" data-gsap="program-detail-actions">
            <section aria-labelledby="options-heading">
                <h2 id="options-heading" class="sr-only">Aksi Program</h2>

                <div class="mt-10">
                    @auth
                    <a href="{{ route('landing.subscribe', $this->layanan->slug) }}"
                        class="flex w-full items-center justify-center gap-2 rounded-md border border-transparent bg-orange-600 px-8 py-3 text-base font-medium text-white hover:bg-orange-700 focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-gray-50 focus:outline-hidden dark:focus:ring-offset-gray-900 transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Berlangganan Sekarang</span>
                    </a>
                    @else
                    <button type="button" wire:click="handleSubscribeClick"
                        class="flex w-full items-center justify-center gap-2 rounded-md border border-transparent bg-orange-600 px-8 py-3 text-base font-medium text-white hover:bg-orange-700 focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-gray-50 focus:outline-hidden dark:focus:ring-offset-gray-900 transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Berlangganan Sekarang</span>
                    </button>
                    @endauth
                </div>
                <div class="mt-4">
                    <a href="{{ route('landing.program') }}"
                        class="flex w-full items-center justify-center rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-8 py-3 text-base font-medium text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 focus:ring-offset-gray-50 dark:focus:ring-offset-gray-900 focus:outline-hidden transition-colors">
                        Kembali ke Program
                    </a>
                </div>
            </section>
        </div>
    </div>

    <!-- Login Modal -->
    <flux:modal name="login-required" wire:model="showLoginModal" focusable class="max-w-md">
        <div class="space-y-6">
            <div class="text-center">
                <div
                    class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-orange-100 dark:bg-orange-900/30">
                    <svg class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                </div>
                <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">
                    Login Diperlukan
                </h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Anda harus login terlebih dahulu untuk dapat berlangganan program ini.
                </p>
            </div>

            <div class="flex flex-col gap-3">
                <flux:button :href="route('login')" variant="primary" class="w-full" wire:navigate
                    wire:click="closeLoginModal">
                    Masuk ke Akun
                </flux:button>
                <flux:button :href="route('register')" variant="ghost" class="w-full" wire:navigate
                    wire:click="closeLoginModal">
                    Buat Akun Baru
                </flux:button>
            </div>

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="ghost" wire:click="closeLoginModal">
                        Tutup
                    </flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>