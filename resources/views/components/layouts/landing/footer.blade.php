<footer class="w-full lg:max-w-4xl max-w-[335px] mt-10 text-sm text-[#706f6c] dark:text-[#A1A09A]">
    <div class="border-t border-[#e3e3e0] dark:border-[#3E3E3A] pt-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-center md:text-start">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </div>
            <div class="flex items-center gap-4">
                @if (Route::has('login'))
                <a href="{{ route('login') }}"
                    class="text-[#706f6c] dark:text-[#A1A09A] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC] transition-colors"
                    wire:navigate>
                    Login
                </a>
                @endif
                @if (Route::has('register'))
                <a href="{{ route('register') }}"
                    class="text-[#706f6c] dark:text-[#A1A09A] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC] transition-colors"
                    wire:navigate>
                    Register
                </a>
                @endif
            </div>
        </div>
    </div>
</footer>

