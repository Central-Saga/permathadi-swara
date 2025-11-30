<header class="w-full lg:max-w-4xl max-w-[335px] text-sm mb-6">
    <nav class="flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center space-x-2" wire:navigate>
            <x-app-logo />
        </a>

        <div class="flex items-center gap-4">
            @auth
            @if(auth()->user()->hasRole('Anggota') || auth()->user()->isAnggota())
            <flux:dropdown position="bottom" align="end">
                <button type="button"
                    class="inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm font-semibold text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-colors">
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
            <a href="{{ url('/dashboard') }}"
                class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal"
                wire:navigate>
                Dashboard
            </a>
            @endif
            @else
            <a href="{{ route('login') }}"
                class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] text-[#1b1b18] border border-transparent hover:border-[#19140035] dark:hover:border-[#3E3E3A] rounded-sm text-sm leading-normal"
                wire:navigate>
                Log in
            </a>

            @if (Route::has('register'))
            <a href="{{ route('register') }}"
                class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal"
                wire:navigate>
                Register
            </a>
            @endif
            @endauth
        </div>
    </nav>
</header>