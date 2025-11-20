<header class="absolute inset-x-0 top-0 z-50" x-data="{ mobileMenuOpen: false }">
    <nav aria-label="Global" class="flex items-center justify-between p-6 lg:px-8">
        <div class="flex lg:flex-1">
            <a href="{{ route('home') }}" class="-m-1.5 p-1.5" wire:navigate>
                <span class="sr-only">Permathadi Swara</span>
                <x-app-logo class="h-8 w-auto" />
            </a>
        </div>
        <div class="flex lg:hidden">
            <button type="button" @click="mobileMenuOpen = true" class="-m-2.5 inline-flex items-center justify-center rounded-md p-2.5 text-gray-500 dark:text-gray-400">
                <span class="sr-only">Buka menu</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6">
                    <path d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
        </div>
        <div class="hidden lg:flex lg:gap-x-12">
            <a href="#beranda" class="text-sm/6 font-semibold text-gray-900 dark:text-white">Beranda</a>
            <a href="#tentang" class="text-sm/6 font-semibold text-gray-900 dark:text-white">Tentang Kami</a>
            <a href="#program" class="text-sm/6 font-semibold text-gray-900 dark:text-white">Program</a>
            <a href="#galeri" class="text-sm/6 font-semibold text-gray-900 dark:text-white">Galeri</a>
            <a href="#kontak" class="text-sm/6 font-semibold text-gray-900 dark:text-white">Kontak</a>
        </div>
        <div class="hidden lg:flex lg:flex-1 lg:justify-end">
            @auth
                <a href="{{ route('dashboard') }}" class="text-sm/6 font-semibold text-gray-900 dark:text-white">Dashboard <span aria-hidden="true">&rarr;</span></a>
            @else
                <a href="{{ route('login') }}" class="text-sm/6 font-semibold text-gray-900 dark:text-white">Masuk <span aria-hidden="true">&rarr;</span></a>
            @endauth
        </div>
    </nav>
    
    <!-- Mobile menu -->
    <div x-show="mobileMenuOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 lg:hidden"
         style="display: none;">
        <div class="fixed inset-0 bg-gray-900/50" @click="mobileMenuOpen = false"></div>
        <div class="fixed inset-y-0 right-0 z-50 w-full overflow-y-auto bg-white p-6 sm:max-w-sm sm:ring-1 sm:ring-gray-900/10 dark:bg-gray-900 dark:sm:ring-gray-100/10">
            <div class="flex items-center justify-between">
                <a href="{{ route('home') }}" class="-m-1.5 p-1.5" wire:navigate>
                    <span class="sr-only">Permathadi Swara</span>
                    <x-app-logo class="h-8 w-auto" />
                </a>
                <button type="button" @click="mobileMenuOpen = false" class="-m-2.5 rounded-md p-2.5 text-gray-700 dark:text-gray-200">
                    <span class="sr-only">Tutup menu</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6">
                        <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
            <div class="mt-6 flow-root">
                <div class="-my-6 divide-y divide-gray-500/10 dark:divide-gray-500/25">
                    <div class="space-y-2 py-6">
                        <a href="#beranda" @click="mobileMenuOpen = false" class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-gray-900 hover:bg-gray-50 dark:text-white dark:hover:bg-white/5">Beranda</a>
                        <a href="#tentang" @click="mobileMenuOpen = false" class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-gray-900 hover:bg-gray-50 dark:text-white dark:hover:bg-white/5">Tentang Kami</a>
                        <a href="#program" @click="mobileMenuOpen = false" class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-gray-900 hover:bg-gray-50 dark:text-white dark:hover:bg-white/5">Program</a>
                        <a href="#galeri" @click="mobileMenuOpen = false" class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-gray-900 hover:bg-gray-50 dark:text-white dark:hover:bg-white/5">Galeri</a>
                        <a href="#kontak" @click="mobileMenuOpen = false" class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-gray-900 hover:bg-gray-50 dark:text-white dark:hover:bg-white/5">Kontak</a>
                    </div>
                    <div class="py-6">
                        @auth
                            <a href="{{ route('dashboard') }}" class="-mx-3 block rounded-lg px-3 py-2.5 text-base/7 font-semibold text-gray-900 hover:bg-gray-50 dark:text-white dark:hover:bg-white/5">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="-mx-3 block rounded-lg px-3 py-2.5 text-base/7 font-semibold text-gray-900 hover:bg-gray-50 dark:text-white dark:hover:bg-white/5">Masuk</a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

