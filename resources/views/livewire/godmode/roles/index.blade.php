<?php

use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Collection;
use function Livewire\Volt\{layout, title, state, mount, action, computed, on, uses};

uses(WithPagination::class);

layout('components.layouts.admin');
title(fn () => __('Role'));

state([
    'search' => fn () => request()->get('search', ''),
    'sortBy' => fn () => request()->get('sort_by', 'name'),
    'sortDir' => fn () => request()->get('sort_dir', 'asc'),
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

$sortIconName = computed(function () {
    if ($this->sortBy !== 'name') {
        return 'chevrons-up-down';
    }
    return $this->sortDir === 'asc' ? 'chevron-up' : 'chevron-down';
});

$deleteRole = action(function ($roleId) {
    $role = Role::findOrFail($roleId);
    $role->delete();
    $this->dispatch('role-deleted');
});

$exportExcel = action(function () {
    $query = Role::with('permissions');

    if (!empty($this->search)) {
        $query->where('name', 'like', "%{$this->search}%");
    }

    $allowedSorts = ['name', 'created_at'];
    if (in_array($this->sortBy, $allowedSorts)) {
        $query->orderBy($this->sortBy, $this->sortDir);
    } else {
        $query->orderBy('name', 'asc');
    }

    $roles = $query->get();

    $data = $roles->map(function ($role) {
        return [
            $role->name,
            $role->permissions->pluck('name')->implode(', '),
            $role->permissions->count(),
            $role->created_at->format('d/m/Y H:i'),
        ];
    });

    $filename = 'roles_' . now()->format('Y-m-d_His') . '.xlsx';

    $export = new class(Collection::make($data)) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
        public function __construct(public Collection $data) {}

        public function collection() {
            return $this->data;
        }

        public function headings(): array {
            return ['Nama Role', 'Permission', 'Jumlah Permission', 'Dibuat'];
        }
    };

    return Excel::download($export, $filename);
});

$exportPdf = action(function () {
    $query = Role::with('permissions');

    if (!empty($this->search)) {
        $query->where('name', 'like', "%{$this->search}%");
    }

    $allowedSorts = ['name', 'created_at'];
    if (in_array($this->sortBy, $allowedSorts)) {
        $query->orderBy($this->sortBy, $this->sortDir);
    } else {
        $query->orderBy('name', 'asc');
    }

    $roles = $query->get();

    $html = view('exports.roles-pdf', ['roles' => $roles])->render();

    $pdf = Pdf::loadHTML($html);
    $filename = 'roles_' . now()->format('Y-m-d_His') . '.pdf';

    return Response::streamDownload(function () use ($pdf) {
        echo $pdf->output();
    }, $filename);
});

$roles = computed(function () {
    $query = Role::with('permissions');

    // Search
    if (!empty($this->search)) {
        $query->where('name', 'like', "%{$this->search}%");
    }

    // Sorting
    $allowedSorts = ['name', 'created_at'];
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
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Role') }}</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Kelola role dan permission sistem') }}</p>
            </div>
            <div class="flex items-center gap-2">
                @can('mengekspor role')
                <flux:button wire:click="exportExcel" variant="ghost" icon="table-cells"
                    class="!bg-green-600 hover:!bg-green-700 dark:!bg-green-500 dark:hover:!bg-green-600 !text-white">
                    {{ __('Excel') }}
                </flux:button>
                <flux:button wire:click="exportPdf" variant="ghost" icon="document-text"
                    class="!bg-red-900 hover:!bg-red-950 dark:!bg-red-950 dark:hover:!bg-red-900 !text-white">
                    {{ __('PDF') }}
                </flux:button>
                @endcan
                @can('membuat role')
                <flux:button :href="route('godmode.roles.create')" variant="primary" icon="plus" wire:navigate>
                    {{ __('Tambah Role') }}
                </flux:button>
                @endcan
            </div>
        </div>

        <flux:card>
            <div class="mb-4">
                <flux:input wire:model.live.debounce.500ms="search" name="search" type="search" icon="magnifying-glass"
                    placeholder="{{ __('Cari nama role...') }}" class="w-full" />
            </div>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>
                        <button wire:click="sort('name')"
                            class="flex items-center gap-1 hover:text-orange-600 dark:hover:text-orange-400">
                            {{ __('Nama Role') }}
                            <flux:icon :name="$this->sortIconName" variant="mini" />
                        </button>
                    </flux:table.column>
                    <flux:table.column>{{ __('Permission') }}</flux:table.column>
                    @if (auth()->user()->can('mengubah role') || auth()->user()->can('menghapus role'))
                    <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                    @endif
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($this->roles as $role)
                    <flux:table.row>
                        <flux:table.cell>
                            <div class="font-medium text-gray-900 dark:text-white">{{ $role->name }}</div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex flex-wrap gap-1">
                                @forelse ($role->permissions->take(5) as $permission)
                                <span
                                    class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10 dark:bg-blue-900/50 dark:text-blue-200">
                                    {{ $permission->name }}
                                </span>
                                @empty
                                <span class="text-sm text-gray-400 dark:text-gray-500">{{ __('Tidak ada permission')
                                    }}</span>
                                @endforelse
                                @if ($role->permissions->count() > 5)
                                <span
                                    class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700 ring-1 ring-inset ring-gray-700/10 dark:bg-gray-900/50 dark:text-gray-200">
                                    +{{ $role->permissions->count() - 5 }} lainnya
                                </span>
                                @endif
                            </div>
                        </flux:table.cell>
                        @if (auth()->user()->can('mengubah role') || auth()->user()->can('menghapus role'))
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                @can('mengubah role')
                                <flux:button :href="route('godmode.roles.edit', $role)" variant="ghost" size="sm"
                                    icon="pencil" wire:navigate
                                    class="!p-2 !bg-blue-600 hover:!bg-blue-700 dark:!bg-blue-500 dark:hover:!bg-blue-600 !text-white !rounded-md"
                                    title="{{ __('Edit') }}" />
                                @endcan
                                @can('menghapus role')
                                <flux:button wire:click="deleteRole({{ $role->id }})"
                                    wire:confirm="{{ __('Apakah Anda yakin ingin menghapus role ini?') }}"
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
                        <flux:table.cell
                            colspan="{{ auth()->user()->can('mengubah role') || auth()->user()->can('menghapus role') ? '3' : '2' }}"
                            class="text-center text-gray-500 dark:text-gray-400">
                            {{ __('Tidak ada role') }}
                        </flux:table.cell>
                    </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Menampilkan') }} {{ $this->roles->firstItem() ?? 0 }} {{ __('sampai') }} {{
                    $this->roles->lastItem() ?? 0
                    }}
                    {{ __('dari') }} {{ $this->roles->total() }} {{ __('hasil') }}
                </div>
                @if ($this->roles->hasPages())
                <div>
                    {{ $this->roles->links() }}
                </div>
                @endif
            </div>
        </flux:card>
    </div>

    <x-action-message on="role-deleted">
        {{ __('Role berhasil dihapus.') }}
    </x-action-message>
</div>