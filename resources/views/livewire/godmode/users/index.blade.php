<?php

use App\Models\User;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Collection;
use function Livewire\Volt\{layout, title, state, computed, on, action, uses};

uses(WithPagination::class);

layout('components.layouts.admin');
title(fn () => __('User'));

state([
    'search' => fn () => request()->get('search', ''),
    'sortBy' => fn () => request()->get('sort_by', 'name'),
    'sortDir' => fn () => request()->get('sort_dir', 'asc'),
    'showDeleteModal' => false,
    'userToDelete' => null,
]);

on(['updatingSearch' => function () {
    $this->resetPage();
}]);

$sort = action(function ($column) {
    if ($this->sortBy === $column) {
        $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
    } else {
        $this->sortBy = $column;
        $this->sortDir = 'asc';
    }
});

// Helper computed properties untuk mendapatkan sort icon
$sortIconName = computed(function () {
    if ($this->sortBy !== 'name') {
        return 'chevrons-up-down';
    }
    return $this->sortDir === 'asc' ? 'chevron-up' : 'chevron-down';
});

$sortIconEmail = computed(function () {
    if ($this->sortBy !== 'email') {
        return 'chevrons-up-down';
    }
    return $this->sortDir === 'asc' ? 'chevron-up' : 'chevron-down';
});

$openDeleteModal = action(function ($userId) {
    $this->userToDelete = User::findOrFail($userId);
    $this->showDeleteModal = true;
});

$closeDeleteModal = action(function () {
    $this->showDeleteModal = false;
    $this->userToDelete = null;
});

$deleteUser = action(function () {
    if ($this->userToDelete) {
        $this->userToDelete->delete();
        $this->dispatch('toast', message: __('User berhasil dihapus.'), variant: 'success');
        $this->closeDeleteModal();
    }
});

$exportExcel = action(function () {
    $query = User::with('roles')->whereDoesntHave('anggota');
    
    if (!empty($this->search)) {
        $query->where(function ($q) {
            $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%");
        });
    }
    
    $allowedSorts = ['name', 'email', 'created_at'];
    if (in_array($this->sortBy, $allowedSorts)) {
        $query->orderBy($this->sortBy, $this->sortDir);
    } else {
        $query->orderBy('name', 'asc');
    }
    
    $users = $query->get();
    
    $data = $users->map(function ($user) {
        return [
            $user->name,
            $user->email,
            $user->roles->pluck('name')->implode(', '),
            $user->roles->count(),
            $user->created_at->format('d/m/Y H:i'),
        ];
    });
    
    $filename = 'users_' . now()->format('Y-m-d_His') . '.xlsx';
    
    $export = new class(Collection::make($data)) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
        public function __construct(public Collection $data) {}
        
        public function collection() {
            return $this->data;
        }
        
        public function headings(): array {
            return ['Nama', 'Email', 'Role', 'Jumlah Role', 'Dibuat'];
        }
    };
    
    return Excel::download($export, $filename);
});

$exportPdf = action(function () {
    $query = User::with('roles')->whereDoesntHave('anggota');
    
    if (!empty($this->search)) {
        $query->where(function ($q) {
            $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%");
        });
    }
    
    $allowedSorts = ['name', 'email', 'created_at'];
    if (in_array($this->sortBy, $allowedSorts)) {
        $query->orderBy($this->sortBy, $this->sortDir);
    } else {
        $query->orderBy('name', 'asc');
    }
    
    $users = $query->get();
    
    $html = view('exports.users-pdf', ['users' => $users])->render();
    
    $pdf = Pdf::loadHTML($html);
    $filename = 'users_' . now()->format('Y-m-d_His') . '.pdf';
    
    return Response::streamDownload(function () use ($pdf) {
        echo $pdf->output();
    }, $filename);
});

$users = computed(function () {
    $query = User::with('roles')->whereDoesntHave('anggota');

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

    return $query->paginate(15);
}); ?>

<div>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('User') }}</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Kelola pengguna sistem (non-anggota)') }}</p>
            </div>
            <div class="flex items-center gap-2">
                @can('mengekspor user')
                <flux:button wire:click="exportExcel" variant="ghost" icon="table-cells"
                    class="!bg-green-600 hover:!bg-green-700 dark:!bg-green-500 dark:hover:!bg-green-600 !text-white">
                    {{ __('Excel') }}
                </flux:button>
                <flux:button wire:click="exportPdf" variant="ghost" icon="document-text"
                    class="!bg-red-900 hover:!bg-red-950 dark:!bg-red-950 dark:hover:!bg-red-900 !text-white">
                    {{ __('PDF') }}
                </flux:button>
                @endcan
                @can('membuat user')
                <flux:button :href="route('godmode.users.create')" variant="primary" icon="plus" wire:navigate>
                    {{ __('Tambah User') }}
                </flux:button>
                @endcan
            </div>
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
                            <flux:icon :name="$this->sortIconName" variant="mini" />
                        </button>
                    </flux:table.column>
                    <flux:table.column>
                        <button wire:click="sort('email')"
                            class="flex items-center gap-1 hover:text-orange-600 dark:hover:text-orange-400">
                            {{ __('Email') }}
                            <flux:icon :name="$this->sortIconEmail" variant="mini" />
                        </button>
                    </flux:table.column>
                    <flux:table.column>{{ __('Role') }}</flux:table.column>
                    @if (auth()->user()->can('mengubah user') || auth()->user()->can('menghapus user'))
                    <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                    @endif
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($this->users as $user)
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
                        @if (auth()->user()->can('mengubah user') || auth()->user()->can('menghapus user'))
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                @can('mengubah user')
                                <flux:button :href="route('godmode.users.edit', $user)" variant="ghost" size="sm"
                                    icon="pencil" wire:navigate
                                    class="!p-2 !bg-blue-600 hover:!bg-blue-700 dark:!bg-blue-500 dark:hover:!bg-blue-600 !text-white !rounded-md"
                                    title="{{ __('Edit') }}" />
                                @endcan
                                @can('menghapus user')
                                <flux:button wire:click="openDeleteModal({{ $user->id }})"
                                    variant="ghost" size="sm" icon="trash"
                                    class="!p-2 !bg-red-600 hover:!bg-red-700 dark:!bg-red-500 dark:hover:!bg-red-600 !text-white !rounded-md"
                                    title="{{ __('Hapus') }}" />
                                @endcan
                            </div>
                        </flux:table.cell>
                        @endif
                    </flux:table.row>
                    @empty
                    <flux:table.row>
                        <flux:table.cell colspan="{{ auth()->user()->can('mengubah user') || auth()->user()->can('menghapus user') ? '4' : '3' }}" class="text-center text-gray-500 dark:text-gray-400">
                            {{ __('Tidak ada user') }}
                        </flux:table.cell>
                    </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Menampilkan') }} {{ $this->users->firstItem() ?? 0 }} {{ __('sampai') }} {{
                    $this->users->lastItem()
                    ?? 0 }}
                    {{ __('dari') }} {{ $this->users->total() }} {{ __('hasil') }}
                </div>
                @if ($this->users->hasPages())
                <div>
                    {{ $this->users->links() }}
                </div>
                @endif
            </div>
        </flux:card>
    </div>

    <!-- Delete Confirmation Modal -->
    <flux:modal name="delete-user" :show="$showDeleteModal" wire:model="showDeleteModal" class="max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Hapus User') }}</flux:heading>
                <flux:subheading>{{ __('Apakah Anda yakin ingin menghapus user ini?') }}</flux:subheading>
            </div>

            @if ($userToDelete)
            <div class="rounded-lg bg-red-50 dark:bg-red-900/20 p-4">
                <p class="text-sm text-red-800 dark:text-red-200">
                    <strong>{{ $userToDelete->name }}</strong> ({{ $userToDelete->email }}) akan dihapus secara permanen.
                </p>
            </div>
            @endif

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="ghost" wire:click="closeDeleteModal">{{ __('Batal') }}</flux:button>
                </flux:modal.close>
                <flux:button wire:click="deleteUser" variant="danger">{{ __('Hapus') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>