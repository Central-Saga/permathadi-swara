@php
function getSortUrl($currentSortBy, $currentSortDir, $column) {
$newSortDir = ($currentSortBy === $column && $currentSortDir === 'asc') ? 'desc' : 'asc';
return request()->fullUrlWithQuery(['sort_by' => $column, 'sort_dir' => $newSortDir]);
}

function getSortIcon($currentSortBy, $currentSortDir, $column) {
if ($currentSortBy !== $column) {
return 'chevrons-up-down';
}
return $currentSortDir === 'asc' ? 'chevron-up' : 'chevron-down';
}
@endphp

<x-layouts.app :title="__('User')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('User') }}</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Kelola pengguna sistem') }}</p>
            </div>
            <flux:button :href="route('godmode.users.create')" variant="primary" icon="plus" wire:navigate>
                {{ __('Tambah User') }}
            </flux:button>
        </div>

        <flux:card>
            <div class="mb-4" x-data="{
                     search: '{{ request('search') }}',
                     timeout: null,
                     performSearch() {
                         clearTimeout(this.timeout);
                         this.timeout = setTimeout(() => {
                             const params = new URLSearchParams(window.location.search);
                             if (this.search) {
                                 params.set('search', this.search);
                             } else {
                                 params.delete('search');
                             }
                             params.set('sort_by', '{{ $sortBy ?? 'name' }}');
                             params.set('sort_dir', '{{ $sortDir ?? 'asc' }}');
                             window.location.href = '{{ route('godmode.users.index') }}?' + params.toString();
                         }, 500);
                     }
                 }" x-init="$watch('search', () => performSearch())">
                <flux:input x-model="search" name="search" type="search" icon="magnifying-glass"
                    placeholder="{{ __('Cari nama atau email...') }}" class="w-full" />
            </div>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>
                        <a href="{{ getSortUrl($sortBy ?? 'name', $sortDir ?? 'asc', 'name') }}"
                            class="flex items-center gap-1 hover:text-orange-600 dark:hover:text-orange-400">
                            {{ __('Nama') }}
                            <flux:icon :name="getSortIcon($sortBy ?? 'name', $sortDir ?? 'asc', 'name')"
                                variant="mini" />
                        </a>
                    </flux:table.column>
                    <flux:table.column>
                        <a href="{{ getSortUrl($sortBy ?? 'name', $sortDir ?? 'asc', 'email') }}"
                            class="flex items-center gap-1 hover:text-orange-600 dark:hover:text-orange-400">
                            {{ __('Email') }}
                            <flux:icon :name="getSortIcon($sortBy ?? 'name', $sortDir ?? 'asc', 'email')"
                                variant="mini" />
                        </a>
                    </flux:table.column>
                    <flux:table.column>{{ __('Role') }}</flux:table.column>
                    <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($users as $user)
                    <flux:table.row>
                        <flux:table.cell>
                            <div class="font-medium text-gray-900 dark:text-white">{{ $user->name }}</div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="text-gray-600 dark:text-gray-400">{{ $user->email }}</div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex flex-wrap gap-1">
                                @forelse ($user->roles as $role)
                                <span
                                    class="inline-flex items-center rounded-md bg-orange-50 px-2 py-1 text-xs font-medium text-orange-700 ring-1 ring-inset ring-orange-700/10 dark:bg-orange-900/50 dark:text-orange-200">
                                    {{ $role->name }}
                                </span>
                                @empty
                                <span class="text-sm text-gray-400 dark:text-gray-500">{{ __('Tidak ada role') }}</span>
                                @endforelse
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <flux:button :href="route('godmode.users.edit', $user)" variant="ghost" size="sm"
                                    icon="pencil" wire:navigate
                                    class="!p-2 !bg-blue-600 hover:!bg-blue-700 dark:!bg-blue-500 dark:hover:!bg-blue-600 !text-white !rounded-md"
                                    title="{{ __('Edit') }}" />
                                <form action="{{ route('godmode.users.destroy', $user) }}" method="POST"
                                    onsubmit="return confirm('{{ __('Apakah Anda yakin ingin menghapus user ini?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <flux:button type="submit" variant="ghost" size="sm" icon="trash"
                                        class="!p-2 !bg-red-600 hover:!bg-red-700 dark:!bg-red-500 dark:hover:!bg-red-600 !text-white !rounded-md"
                                        title="{{ __('Hapus') }}" />
                                </form>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                    @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4" class="text-center text-gray-500 dark:text-gray-400">
                            {{ __('Tidak ada user') }}
                        </flux:table.cell>
                    </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Menampilkan') }} {{ $users->firstItem() ?? 0 }} {{ __('sampai') }} {{ $users->lastItem() ?? 0
                    }}
                    {{ __('dari') }} {{ $users->total() }} {{ __('hasil') }}
                </div>
                @if ($users->hasPages())
                <div>
                    {{ $users->links() }}
                </div>
                @endif
            </div>
        </flux:card>
    </div>
</x-layouts.app>
