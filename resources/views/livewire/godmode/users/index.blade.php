<?php

use App\Models\User;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use function Livewire\Volt\{layout, title};

layout('components.layouts.app');
title(fn () => __('User'));

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $sortBy = 'name';
    public string $sortDir = 'asc';

    public function mount(): void
    {
        $this->search = request()->get('search', '');
        $this->sortBy = request()->get('sort_by', 'name');
        $this->sortDir = request()->get('sort_dir', 'asc');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sort($column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'asc';
        }
    }

    public function getSortIcon($column): string
    {
        if ($this->sortBy !== $column) {
            return 'chevrons-up-down';
        }
        return $this->sortDir === 'asc' ? 'chevron-up' : 'chevron-down';
    }

    public function deleteUser($userId): void
    {
        $user = User::findOrFail($userId);
        $user->delete();
        $this->dispatch('user-deleted');
    }

    public function with(): array
    {
        $query = User::with('roles');

        // Search
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        // Sorting
        $allowedSorts = ['name', 'email', 'created_at'];
        if (!in_array($this->sortBy, $allowedSorts)) {
            $this->sortBy = 'name';
        }

        if (!in_array($this->sortDir, ['asc', 'desc'])) {
            $this->sortDir = 'asc';
        }

        $query->orderBy($this->sortBy, $this->sortDir);

        return [
            'users' => $query->paginate(15),
        ];
    }
}; ?>

<div>
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
            <div class="mb-4">
                <flux:input wire:model.live.debounce.500ms="search" name="search" type="search" icon="magnifying-glass"
                    placeholder="{{ __('Cari nama atau email...') }}" class="w-full" />
            </div>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>
                        <button wire:click="sort('name')"
                            class="flex items-center gap-1 hover:text-orange-600 dark:hover:text-orange-400">
                            {{ __('Nama') }}
                            <flux:icon :name="$this->getSortIcon('name')" variant="mini" />
                        </button>
                    </flux:table.column>
                    <flux:table.column>
                        <button wire:click="sort('email')"
                            class="flex items-center gap-1 hover:text-orange-600 dark:hover:text-orange-400">
                            {{ __('Email') }}
                            <flux:icon :name="$this->getSortIcon('email')" variant="mini" />
                        </button>
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
                                <span class="text-sm text-gray-400 dark:text-gray-500">{{ __('Tidak ada role')
                                    }}</span>
                                @endforelse
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <flux:button :href="route('godmode.users.edit', $user)" variant="ghost" size="sm"
                                    icon="pencil" wire:navigate
                                    class="!p-2 !bg-blue-600 hover:!bg-blue-700 dark:!bg-blue-500 dark:hover:!bg-blue-600 !text-white !rounded-md"
                                    title="{{ __('Edit') }}" />
                                <flux:button wire:click="deleteUser({{ $user->id }})"
                                    wire:confirm="{{ __('Apakah Anda yakin ingin menghapus user ini?') }}"
                                    variant="ghost" size="sm" icon="trash"
                                    class="!p-2 !bg-red-600 hover:!bg-red-700 dark:!bg-red-500 dark:hover:!bg-red-600 !text-white !rounded-md"
                                    title="{{ __('Hapus') }}" />
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
                    {{ __('Menampilkan') }} {{ $users->firstItem() ?? 0 }} {{ __('sampai') }} {{ $users->lastItem()
                    ?? 0 }}
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

    <x-action-message on="user-deleted">
        {{ __('User berhasil dihapus.') }}
    </x-action-message>
</div>
