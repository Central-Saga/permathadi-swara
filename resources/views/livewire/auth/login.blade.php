<x-layouts.auth>
    <div class="flex flex-col gap-8">
        <div class="flex w-full flex-col text-center space-y-2">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                {{ __('Log in to your account') }}
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('Enter your email and password below to log in') }}
            </p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <div class="space-y-2">
                <flux:input name="email" :label="__('Email address')" type="email" required autofocus
                    autocomplete="email" placeholder="email@example.com"
                    class="[&_input]:border-gray-300 [&_input]:focus:border-orange-500 [&_input]:focus:ring-orange-500 dark:[&_input]:border-gray-700 dark:[&_input]:focus:border-orange-500" />
            </div>

            <!-- Password -->
            <div class="space-y-2">
                <div class="relative">
                    <flux:input name="password" :label="__('Password')" type="password" required
                        autocomplete="current-password" :placeholder="__('Password')" viewable
                        class="[&_input]:border-gray-300 [&_input]:focus:border-orange-500 [&_input]:focus:ring-orange-500 dark:[&_input]:border-gray-700 dark:[&_input]:focus:border-orange-500" />
                </div>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
                <flux:checkbox name="remember" :label="__('Remember me')" :checked="old('remember')"
                    class="[&_input]:border-gray-300 [&_input]:checked:bg-orange-600 [&_input]:checked:border-orange-600 dark:[&_input]:border-gray-700" />
            </div>

            <div class="flex items-center justify-end pt-2">
                <button type="submit"
                    class="w-full bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-500 hover:to-red-500 text-white font-semibold py-3 px-4 rounded-lg shadow-lg shadow-orange-500/50 hover:shadow-xl hover:shadow-orange-500/60 transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                    data-test="login-button">
                    {{ __('Log in') }}
                </button>
            </div>
        </form>

        @if (Route::has('register'))
        <div
            class="space-x-1 text-sm text-center rtl:space-x-reverse text-gray-600 dark:text-gray-400 pt-2 border-t border-gray-200 dark:border-gray-800">
            <span>{{ __('Don\'t have an account?') }}</span>
            <flux:link :href="route('register')" wire:navigate
                class="font-semibold text-orange-600 hover:text-orange-500 dark:text-orange-400 dark:hover:text-orange-300 transition-colors">
                {{ __('Sign up') }}
            </flux:link>
        </div>
        @endif
    </div>
</x-layouts.auth>
