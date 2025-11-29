<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf
            
            <div class="space-y-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Informasi Akun</h3>
                
                <!-- Name -->
                <flux:input
                    name="name"
                    :label="__('Nama Lengkap')"
                    type="text"
                    required
                    autofocus
                    autocomplete="name"
                    :placeholder="__('Nama lengkap')"
                    value="{{ old('name') }}"
                />

                <!-- Email Address -->
                <flux:input
                    name="email"
                    :label="__('Email address')"
                    type="email"
                    required
                    autocomplete="email"
                    placeholder="email@example.com"
                    value="{{ old('email') }}"
                />

                <!-- Password -->
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="new-password"
                    :placeholder="__('Password')"
                    viewable
                />

                <!-- Confirm Password -->
                <flux:input
                    name="password_confirmation"
                    :label="__('Confirm password')"
                    type="password"
                    required
                    autocomplete="new-password"
                    :placeholder="__('Confirm password')"
                    viewable
                />
            </div>

            <div class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Informasi Anggota</h3>
                
                <!-- Telepon -->
                <flux:input
                    name="telepon"
                    :label="__('Nomor Telepon')"
                    type="tel"
                    required
                    autocomplete="tel"
                    placeholder="081234567890"
                    value="{{ old('telepon') }}"
                />

                <!-- Alamat -->
                <flux:textarea
                    name="alamat"
                    :label="__('Alamat')"
                    :placeholder="__('Alamat lengkap')"
                    rows="3"
                >{{ old('alamat') }}</flux:textarea>

                <!-- Tanggal Lahir -->
                <flux:input
                    name="tanggal_lahir"
                    :label="__('Tanggal Lahir')"
                    type="date"
                    autocomplete="bday"
                    value="{{ old('tanggal_lahir') }}"
                />
            </div>

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                    {{ __('Create account') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts.auth>
