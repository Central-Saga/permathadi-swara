<x-layouts.app :title="__('Tambah Role')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Tambah Role') }}</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Buat role baru dengan permission') }}</p>
            </div>
            <flux:button :href="route('godmode.roles.index')" variant="ghost" wire:navigate>
                {{ __('Kembali') }}
            </flux:button>
        </div>

        <flux:card>
            <form action="{{ route('godmode.roles.store') }}" method="POST" class="space-y-6">
                @csrf

                <flux:input name="name" :label="__('Nama Role')" type="text" required autofocus
                    value="{{ old('name') }}" :error="$errors->first('name')" />

                <div>
                    <flux:label>{{ __('Permission') }}</flux:label>
                    <div class="mt-2 space-y-4">
                        @foreach ($permissions as $group => $groupPermissions)
                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                                <h3 class="mb-3 font-semibold text-gray-900 dark:text-white capitalize">
                                    {{ $group }}
                                </h3>
                                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                    @foreach ($groupPermissions as $permission)
                                        <flux:checkbox name="permissions[]" value="{{ $permission->id }}"
                                            :label="$permission->name"
                                            :checked="in_array($permission->id, old('permissions', []))" />
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('permissions')
                        <flux:error class="mt-1">{{ $message }}</flux:error>
                    @enderror
                </div>

                <div class="flex items-center gap-4">
                    <flux:button type="submit" variant="primary">
                        {{ __('Simpan') }}
                    </flux:button>
                    <flux:button :href="route('godmode.roles.index')" variant="ghost" wire:navigate>
                        {{ __('Batal') }}
                    </flux:button>
                </div>
            </form>
        </flux:card>
    </div>
</x-layouts.app>

