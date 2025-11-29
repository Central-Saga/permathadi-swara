<header class="absolute inset-x-0 top-0 z-50" x-data="{ mobileMenuOpen: false }" data-gsap="navbar">
    <nav aria-label="Global" class="flex items-center justify-between p-6 lg:px-8">
        <div class="flex lg:flex-1">
            <a href="{{ route('home') }}" class="-m-1.5 p-1.5" wire:navigate>
                <span class="sr-only">Permathadi Swara</span>
                <x-app-logo class="h-8 w-auto" />
            </a>
        </div>
        <div class="flex lg:hidden">
            <button type="button" @click="mobileMenuOpen = true"
                class="-m-2.5 inline-flex items-center justify-center rounded-md p-2.5 text-gray-500 dark:text-gray-400">
                <span class="sr-only">Buka menu</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon"
                    aria-hidden="true" class="size-6">
                    <path d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </button>
        </div>
        <div class="hidden lg:flex lg:gap-x-12">
            <a href="{{ route('home') }}" class="text-sm/6 font-semibold text-gray-900 dark:text-white">Beranda</a>
            <a href="{{ route('landing.tentang') }}"
                class="text-sm/6 font-semibold text-gray-900 dark:text-white">Tentang Kami</a>
            <a href="{{ route('landing.program') }}"
                class="text-sm/6 font-semibold text-gray-900 dark:text-white">Program</a>
            <a href="{{ route('landing.galeri') }}"
                class="text-sm/6 font-semibold text-gray-900 dark:text-white">Galeri</a>
            <a href="{{ route('landing.kontak') }}"
                class="text-sm/6 font-semibold text-gray-900 dark:text-white">Kontak</a>
        </div>
        <div class="hidden lg:flex lg:flex-1 lg:justify-end">
            @auth
            @if(auth()->user()->hasRole('Anggota') || auth()->user()->isAnggota())
            <flux:dropdown position="bottom" align="end">
                <button type="button"
                    class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                    <span class="hidden sm:inline">Hi, {{ explode(' ', auth()->user()->name)[0] }}</span>
                </button>

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-full">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-full bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-200">
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>
                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs text-gray-500 dark:text-gray-400">{{
                                        auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="user" wire:navigate>
                            {{ __('Profile Saya') }}
                        </flux:menu.item>
                        <flux:menu.item :href="route('landing.history')" icon="clock" wire:navigate>
                            {{ __('History Saya') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Keluar') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
            @else
            <a href="{{ route('dashboard') }}" class="text-sm/6 font-semibold text-gray-900 dark:text-white">Dashboard
                <span aria-hidden="true">&rarr;</span></a>
            @endif
            @else
            <a href="{{ route('login') }}" class="text-sm/6 font-semibold text-gray-900 dark:text-white">Masuk <span
                    aria-hidden="true">&rarr;</span></a>
            @endauth
        </div>
    </nav>

    <!-- Mobile menu -->
    <div x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 lg:hidden" style="display: none;">
        <div class="fixed inset-0 bg-gray-900/50" @click="mobileMenuOpen = false"></div>
        <div
            class="fixed inset-y-0 right-0 z-50 w-full overflow-y-auto bg-white p-6 sm:max-w-sm sm:ring-1 sm:ring-gray-900/10 dark:bg-gray-900 dark:sm:ring-gray-100/10">
            <div class="flex items-center justify-between">
                <a href="{{ route('home') }}" class="-m-1.5 p-1.5" wire:navigate>
                    <span class="sr-only">Permathadi Swara</span>
                    <x-app-logo class="h-8 w-auto" />
                </a>
                <button type="button" @click="mobileMenuOpen = false"
                    class="-m-2.5 rounded-md p-2.5 text-gray-700 dark:text-gray-200">
                    <span class="sr-only">Tutup menu</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon"
                        aria-hidden="true" class="size-6">
                        <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
            <div class="mt-6 flow-root">
                <div class="-my-6 divide-y divide-gray-500/10 dark:divide-gray-500/25">
                    <div class="space-y-2 py-6">
                        <a href="{{ route('home') }}" @click="mobileMenuOpen = false"
                            class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-gray-900 hover:bg-gray-50 dark:text-white dark:hover:bg-white/5">Beranda</a>
                        <a href="{{ route('landing.tentang') }}" @click="mobileMenuOpen = false"
                            class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-gray-900 hover:bg-gray-50 dark:text-white dark:hover:bg-white/5">Tentang
                            Kami</a>
                        <a href="{{ route('landing.program') }}" @click="mobileMenuOpen = false"
                            class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-gray-900 hover:bg-gray-50 dark:text-white dark:hover:bg-white/5">Program</a>
                        <a href="{{ route('landing.galeri') }}" @click="mobileMenuOpen = false"
                            class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-gray-900 hover:bg-gray-50 dark:text-white dark:hover:bg-white/5">Galeri</a>
                        <a href="{{ route('landing.kontak') }}" @click="mobileMenuOpen = false"
                            class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-gray-900 hover:bg-gray-50 dark:text-white dark:hover:bg-white/5">Kontak</a>
                    </div>
                    <div class="py-6">
                        @auth
                        @if(auth()->user()->hasRole('Anggota') || auth()->user()->isAnggota())
                        <div class="space-y-2">
                            <a href="{{ route('profile.edit') }}" @click="mobileMenuOpen = false"
                                class="-mx-3 block rounded-lg px-3 py-2.5 text-base/7 font-semibold text-gray-900 hover:bg-gray-50 dark:text-white dark:hover:bg-white/5">Profile Saya</a>
                            <a href="{{ route('landing.history') }}" @click="mobileMenuOpen = false"
                                class="-mx-3 block rounded-lg px-3 py-2.5 text-base/7 font-semibold text-gray-900 hover:bg-gray-50 dark:text-white dark:hover:bg-white/5">History Saya</a>
                            <form method="POST" action="{{ route('logout') }}" class="-mx-3">
                                @csrf
                                <button type="submit"
                                    class="w-full text-left -mx-3 block rounded-lg px-3 py-2.5 text-base/7 font-semibold text-gray-900 hover:bg-gray-50 dark:text-white dark:hover:bg-white/5">Keluar</button>
                            </form>
                        </div>
                        @else
                        <a href="{{ route('dashboard') }}"
                            class="-mx-3 block rounded-lg px-3 py-2.5 text-base/7 font-semibold text-gray-900 hover:bg-gray-50 dark:text-white dark:hover:bg-white/5">Dashboard</a>
                        @endif
                        @else
                        <a href="{{ route('login') }}"
                            class="-mx-3 block rounded-lg px-3 py-2.5 text-base/7 font-semibold text-gray-900 hover:bg-gray-50 dark:text-white dark:hover:bg-white/5">Masuk</a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
