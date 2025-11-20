<x-layouts.app :title="__('Tambah User')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Tambah User') }}</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Buat user baru untuk sistem') }}</p>
            </div>
            <flux:button :href="route('godmode.users.index')" variant="ghost" wire:navigate>
                {{ __('Kembali') }}
            </flux:button>
        </div>

        <flux:card>
            <form action="{{ route('godmode.users.store') }}" method="POST" class="space-y-6">
                @csrf

                <flux:input name="name" :label="__('Nama')" type="text" required autofocus
                    value="{{ old('name') }}" :error="$errors->first('name')" />

                <flux:input name="email" :label="__('Email')" type="email" required
                    value="{{ old('email') }}" :error="$errors->first('email')" />

                <flux:input name="password" :label="__('Password')" type="password" required viewable
                    :error="$errors->first('password')" />

                <flux:input name="password_confirmation" :label="__('Konfirmasi Password')" type="password" required
                    viewable :error="$errors->first('password_confirmation')" />

                <div>
                    <flux:label>{{ __('Role') }}</flux:label>
                    <div class="mt-2 space-y-2">
                        @foreach ($roles as $role)
                            <flux:checkbox name="roles[]" value="{{ $role->id }}" :label="$role->name"
                                :checked="in_array($role->id, old('roles', []))" />
                        @endforeach
                    </div>
                    @error('roles')
                        <flux:error class="mt-1">{{ $message }}</flux:error>
                    @enderror
                </div>

                <div class="flex items-center gap-4">
                    <flux:button type="submit" variant="primary">
                        {{ __('Simpan') }}
                    </flux:button>
                    <flux:button :href="route('godmode.users.index')" variant="ghost" wire:navigate>
                        {{ __('Batal') }}
                    </flux:button>
                </div>
            </form>
        </flux:card>
    </div>
</x-layouts.app>

