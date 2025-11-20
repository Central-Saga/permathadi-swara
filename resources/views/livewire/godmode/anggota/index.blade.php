<?php

use App\Models\Anggota;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Collection;
use function Livewire\Volt\{layout, title, state, mount, action, computed, on, uses};

uses(WithPagination::class);

layout('components.layouts.admin');
title(fn () => __('Anggota'));

state([
    'search' => fn () => request()->get('search', ''),
    'sortBy' => fn () => request()->get('sort_by', 'tanggal_registrasi'),
    'sortDir' => fn () => request()->get('sort_dir', 'desc'),
    'selectedAnggota' => null,
    'showDetailModal' => false,
    'showDeleteModal' => false,
    'anggotaToDelete' => null,
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

$sortIconTanggalRegistrasi = computed(function () {
    if ($this->sortBy !== 'tanggal_registrasi') {
        return 'chevrons-up-down';
    }
    return $this->sortDir === 'asc' ? 'chevron-up' : 'chevron-down';
});

$showDetail = action(function ($anggotaId) {
    $this->selectedAnggota = Anggota::with('user')->findOrFail($anggotaId);
    $this->showDetailModal = true;
});

$closeDetail = action(function () {
    $this->showDetailModal = false;
    $this->selectedAnggota = null;
});

$openDeleteModal = action(function ($anggotaId) {
    $this->anggotaToDelete = Anggota::with('user')->findOrFail($anggotaId);
    $this->showDeleteModal = true;
});

$closeDeleteModal = action(function () {
    $this->showDeleteModal = false;
    $this->anggotaToDelete = null;
});

$deleteAnggota = action(function () {
    if ($this->anggotaToDelete) {
        $user = $this->anggotaToDelete->user;
        $this->anggotaToDelete->delete();
        if ($user) {
            $user->delete();
        }
        $this->dispatch('toast', message: __('Anggota berhasil dihapus.'), variant: 'success');
        $this->closeDeleteModal();
    }
});

$exportExcel = action(function () {
    $query = Anggota::with('user');

    if (!empty($this->search)) {
        $query->whereHas('user', function ($q) {
            $q->where('name', 'like', "%{$this->search}%")
              ->orWhere('email', 'like', "%{$this->search}%");
        })->orWhere('telepon', 'like', "%{$this->search}%")
          ->orWhere('alamat', 'like', "%{$this->search}%");
    }

    $allowedSorts = ['tanggal_registrasi', 'status', 'created_at'];
    if (in_array($this->sortBy, $allowedSorts)) {
        $query->orderBy($this->sortBy, $this->sortDir);
    } else {
        $query->orderBy('tanggal_registrasi', 'desc');
    }

    $anggotas = $query->get();

    $data = $anggotas->map(function ($anggota) {
        return [
            $anggota->user->name ?? '-',
            $anggota->user->email ?? '-',
            $anggota->telepon,
            $anggota->alamat ?? '-',
            $anggota->tanggal_lahir ? $anggota->tanggal_lahir->format('d/m/Y') : '-',
            $anggota->tanggal_registrasi->format('d/m/Y'),
            $anggota->status,
            $anggota->catatan ?? '-',
            $anggota->created_at->format('d/m/Y H:i'),
        ];
    });

    $filename = 'anggota_' . now()->format('Y-m-d_His') . '.xlsx';

    $export = new class(Collection::make($data)) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
        public function __construct(public Collection $data) {}

        public function collection() {
            return $this->data;
        }

        public function headings(): array {
            return ['Nama', 'Email', 'Telepon', 'Alamat', 'Tanggal Lahir', 'Tanggal Registrasi', 'Status', 'Catatan', 'Dibuat'];
        }
    };

    return Excel::download($export, $filename);
});

$exportPdf = action(function () {
    $query = Anggota::with('user');

    if (!empty($this->search)) {
        $query->whereHas('user', function ($q) {
            $q->where('name', 'like', "%{$this->search}%")
              ->orWhere('email', 'like', "%{$this->search}%");
        })->orWhere('telepon', 'like', "%{$this->search}%")
          ->orWhere('alamat', 'like', "%{$this->search}%");
    }

    $allowedSorts = ['tanggal_registrasi', 'status', 'created_at'];
    if (in_array($this->sortBy, $allowedSorts)) {
        $query->orderBy($this->sortBy, $this->sortDir);
    } else {
        $query->orderBy('tanggal_registrasi', 'desc');
    }

    $anggotas = $query->get();

    $html = view('exports.anggota-pdf', ['anggotas' => $anggotas])->render();

    $pdf = Pdf::loadHTML($html);
    $filename = 'anggota_' . now()->format('Y-m-d_His') . '.pdf';

    return Response::streamDownload(function () use ($pdf) {
        echo $pdf->output();
    }, $filename);
});

$anggotas = computed(function () {
    $query = Anggota::with('user');

    // Search
    if (!empty($this->search)) {
        $query->whereHas('user', function ($q) {
            $q->where('name', 'like', "%{$this->search}%")
              ->orWhere('email', 'like', "%{$this->search}%");
        })->orWhere('telepon', 'like', "%{$this->search}%")
          ->orWhere('alamat', 'like', "%{$this->search}%");
    }

    // Sorting
    $allowedSorts = ['tanggal_registrasi', 'status', 'created_at'];
    if (!in_array($this->sortBy, $allowedSorts)) {
        $this->sortBy = 'tanggal_registrasi';
    }

    if (!in_array($this->sortDir, ['asc', 'desc'])) {
        $this->sortDir = 'desc';
    }

    $query->orderBy($this->sortBy, $this->sortDir);

    return $query->paginate(15);
}); ?>

<div>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Anggota') }}</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Kelola data anggota sanggar') }}</p>
            </div>
            <div class="flex items-center gap-2">
                @can('mengekspor anggota')
                <flux:button wire:click="exportExcel" variant="ghost" icon="table-cells"
                    class="!bg-green-600 hover:!bg-green-700 dark:!bg-green-500 dark:hover:!bg-green-600 !text-white">
                    {{ __('Excel') }}
                </flux:button>
                <flux:button wire:click="exportPdf" variant="ghost" icon="document-text"
                    class="!bg-red-900 hover:!bg-red-950 dark:!bg-red-950 dark:hover:!bg-red-900 !text-white">
                    {{ __('PDF') }}
                </flux:button>
                @endcan
                @can('membuat anggota')
                <flux:button :href="route('godmode.anggota.create')" variant="primary" icon="plus" wire:navigate>
                    {{ __('Tambah Anggota') }}
                </flux:button>
                @endcan
            </div>
        </div>

        <flux:card>
            <div class="mb-4">
                <flux:input wire:model.live.debounce.500ms="search" name="search" type="search" icon="magnifying-glass"
                    placeholder="{{ __('Cari nama, email, telepon, atau alamat...') }}" class="w-full" />
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
                    <flux:table.column>{{ __('Telepon') }}</flux:table.column>
                    <flux:table.column>{{ __('Alamat') }}</flux:table.column>
                    <flux:table.column>
                        <button wire:click="sort('tanggal_registrasi')"
                            class="flex items-center gap-1 hover:text-orange-600 dark:hover:text-orange-400">
                            {{ __('Tanggal Registrasi') }}
                            <flux:icon :name="$this->sortIconTanggalRegistrasi" variant="mini" />
                        </button>
                    </flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    @if (auth()->user()->can('melihat anggota') || auth()->user()->can('mengubah anggota') || auth()->user()->can('menghapus anggota'))
                    <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                    @endif
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($this->anggotas as $anggota)
                    <flux:table.row>
                        <flux:table.cell>
                            <div class="font-medium text-gray-900 dark:text-white">{{ $anggota->user->name ?? '-' }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $anggota->user->email ?? '-' }}</div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="text-gray-600 dark:text-gray-400">{{ $anggota->telepon }}</div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="text-gray-600 dark:text-gray-400">{{ Str::limit($anggota->alamat ?? '-', 50) }}</div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="text-gray-600 dark:text-gray-400">{{ $anggota->tanggal_registrasi->format('d/m/Y') }}</div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $anggota->status === 'Aktif' ? 'bg-green-50 text-green-700 ring-green-700/10 dark:bg-green-900/50 dark:text-green-200' : 'bg-red-50 text-red-700 ring-red-700/10 dark:bg-red-900/50 dark:text-red-200' }}">
                                {{ $anggota->status }}
                            </span>
                        </flux:table.cell>
                        @if (auth()->user()->can('melihat anggota') || auth()->user()->can('mengubah anggota') || auth()->user()->can('menghapus anggota'))
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                @can('melihat anggota')
                                <flux:button wire:click="showDetail({{ $anggota->id }})" variant="ghost" size="sm"
                                    icon="eye"
                                    class="!p-2 !bg-purple-600 hover:!bg-purple-700 dark:!bg-purple-500 dark:hover:!bg-purple-600 !text-white !rounded-md"
                                    title="{{ __('Detail') }}" />
                                @endcan
                                @can('mengubah anggota')
                                <flux:button :href="route('godmode.anggota.edit', $anggota)" variant="ghost" size="sm"
                                    icon="pencil" wire:navigate
                                    class="!p-2 !bg-blue-600 hover:!bg-blue-700 dark:!bg-blue-500 dark:hover:!bg-blue-600 !text-white !rounded-md"
                                    title="{{ __('Edit') }}" />
                                @endcan
                                @can('menghapus anggota')
                                <flux:button wire:click="openDeleteModal({{ $anggota->id }})"
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
                            colspan="{{ auth()->user()->can('melihat anggota') || auth()->user()->can('mengubah anggota') || auth()->user()->can('menghapus anggota') ? '6' : '5' }}"
                            class="text-center text-gray-500 dark:text-gray-400">
                            {{ __('Tidak ada anggota') }}
                        </flux:table.cell>
                    </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Menampilkan') }} {{ $this->anggotas->firstItem() ?? 0 }} {{ __('sampai') }} {{
                    $this->anggotas->lastItem() ?? 0
                    }}
                    {{ __('dari') }} {{ $this->anggotas->total() }} {{ __('hasil') }}
                </div>
                @if ($this->anggotas->hasPages())
                <div>
                    {{ $this->anggotas->links() }}
                </div>
                @endif
            </div>
        </flux:card>
    </div>

    <!-- Detail Modal -->
    @if ($showDetailModal && $selectedAnggota)
    <flux:modal name="detail-anggota" :show="$showDetailModal" wire:model="showDetailModal" class="max-w-2xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Detail Anggota') }}</flux:heading>
                <flux:subheading>{{ __('Informasi lengkap anggota dan user') }}</flux:subheading>
            </div>

            <div class="space-y-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">{{ __('Data User') }}</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <flux:label>{{ __('Nama') }}</flux:label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedAnggota->user->name ?? '-' }}</div>
                        </div>
                        <div>
                            <flux:label>{{ __('Email') }}</flux:label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedAnggota->user->email ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">{{ __('Data Anggota') }}</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <flux:label>{{ __('Telepon') }}</flux:label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedAnggota->telepon }}</div>
                        </div>
                        <div>
                            <flux:label>{{ __('Tanggal Lahir') }}</flux:label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedAnggota->tanggal_lahir ? $selectedAnggota->tanggal_lahir->format('d/m/Y') : '-' }}</div>
                        </div>
                        <div class="col-span-2">
                            <flux:label>{{ __('Alamat') }}</flux:label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedAnggota->alamat ?? '-' }}</div>
                        </div>
                        <div>
                            <flux:label>{{ __('Tanggal Registrasi') }}</flux:label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedAnggota->tanggal_registrasi->format('d/m/Y') }}</div>
                        </div>
                        <div>
                            <flux:label>{{ __('Status') }}</flux:label>
                            <div class="mt-1">
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $selectedAnggota->status === 'Aktif' ? 'bg-green-50 text-green-700 ring-green-700/10 dark:bg-green-900/50 dark:text-green-200' : 'bg-red-50 text-red-700 ring-red-700/10 dark:bg-red-900/50 dark:text-red-200' }}">
                                    {{ $selectedAnggota->status }}
                                </span>
                            </div>
                        </div>
                        @if ($selectedAnggota->catatan)
                        <div class="col-span-2">
                            <flux:label>{{ __('Catatan') }}</flux:label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedAnggota->catatan }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:button variant="ghost" wire:click="closeDetail">
                    {{ __('Tutup') }}
                </flux:button>
                @can('mengubah anggota')
                <flux:button :href="route('godmode.anggota.edit', $selectedAnggota)" variant="primary" wire:navigate>
                    {{ __('Edit') }}
                </flux:button>
                @endcan
            </div>
        </div>
    </flux:modal>
    @endif

    <!-- Delete Confirmation Modal -->
    <flux:modal name="delete-anggota" :show="$showDeleteModal" wire:model="showDeleteModal" class="max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Hapus Anggota') }}</flux:heading>
                <flux:subheading>{{ __('Apakah Anda yakin ingin menghapus anggota ini?') }}</flux:subheading>
            </div>

            @if ($anggotaToDelete)
            <div class="rounded-lg bg-red-50 dark:bg-red-900/20 p-4">
                <p class="text-sm text-red-800 dark:text-red-200">
                    <strong>{{ $anggotaToDelete->user->name ?? '-' }}</strong> ({{ $anggotaToDelete->user->email ?? '-' }}) akan dihapus secara permanen.
                </p>
                <p class="text-sm text-red-700 dark:text-red-300 mt-2">
                    {{ __('User terkait juga akan dihapus.') }}
                </p>
            </div>
            @endif

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="ghost" wire:click="closeDeleteModal">{{ __('Batal') }}</flux:button>
                </flux:modal.close>
                <flux:button wire:click="deleteAnggota" variant="danger">{{ __('Hapus') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>

