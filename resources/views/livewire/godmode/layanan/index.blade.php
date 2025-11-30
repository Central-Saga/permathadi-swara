<?php

use App\Models\Layanan;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Collection;
use function Livewire\Volt\{layout, title, state, action, computed, on, uses};

uses(WithPagination::class);

layout('components.layouts.admin');
title(fn () => __('Layanan'));

state([
    'search' => fn () => request()->get('search', ''),
    'sortBy' => fn () => request()->get('sort_by', 'created_at'),
    'sortDir' => fn () => request()->get('sort_dir', 'desc'),
    'selectedLayanan' => null,
    'showDetailModal' => false,
    'showDeleteModal' => false,
    'layananToDelete' => null,
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

$sortIconPrice = computed(function () {
    if ($this->sortBy !== 'price') {
        return 'chevrons-up-down';
    }
    return $this->sortDir === 'asc' ? 'chevron-up' : 'chevron-down';
});

$showDetail = action(function ($layananId) {
    $this->selectedLayanan = Layanan::findOrFail($layananId);
    $this->showDetailModal = true;
});

$closeDetail = action(function () {
    $this->showDetailModal = false;
    $this->selectedLayanan = null;
});

$openDeleteModal = action(function ($layananId) {
    $this->layananToDelete = Layanan::findOrFail($layananId);
    $this->showDeleteModal = true;
});

$closeDeleteModal = action(function () {
    $this->showDeleteModal = false;
    $this->layananToDelete = null;
});

$deleteLayanan = action(function () {
    if ($this->layananToDelete) {
        $this->layananToDelete->delete();
        $this->dispatch('toast', message: __('Layanan berhasil dihapus.'), variant: 'success');
        $this->closeDeleteModal();
    }
});

$exportPdf = action(function () {
    $query = Layanan::query();

    if (!empty($this->search)) {
        $query->where(function ($q) {
            $q->where('name', 'like', "%{$this->search}%")
              ->orWhere('description', 'like', "%{$this->search}%")
              ->orWhere('slug', 'like', "%{$this->search}%");
        });
    }

    $allowedSorts = ['name', 'price', 'is_active', 'created_at'];
    if (in_array($this->sortBy, $allowedSorts)) {
        $query->orderBy($this->sortBy, $this->sortDir);
    } else {
        $query->orderBy('created_at', 'desc');
    }

    $layanans = $query->get();

    $html = view('exports.layanan-pdf', ['layanans' => $layanans])->render();

    $pdf = Pdf::loadHTML($html);
    $filename = 'layanan_' . now()->format('Y-m-d_His') . '.pdf';

    return Response::streamDownload(function () use ($pdf) {
        echo $pdf->output();
    }, $filename);
});

$exportExcel = action(function () {
    $query = Layanan::query();

    if (!empty($this->search)) {
        $query->where(function ($q) {
            $q->where('name', 'like', "%{$this->search}%")
              ->orWhere('description', 'like', "%{$this->search}%")
              ->orWhere('slug', 'like', "%{$this->search}%");
        });
    }

    $allowedSorts = ['name', 'price', 'is_active', 'created_at'];
    if (in_array($this->sortBy, $allowedSorts)) {
        $query->orderBy($this->sortBy, $this->sortDir);
    } else {
        $query->orderBy('created_at', 'desc');
    }

    $layanans = $query->get();

    $data = $layanans->map(function ($layanan) {
        return [
            $layanan->name,
            $layanan->slug ?? '-',
            $layanan->description ? \Illuminate\Support\Str::limit($layanan->description, 100) : '-',
            $layanan->price ? number_format($layanan->price, 0, ',', '.') : '-',
            $layanan->is_active ? 'Aktif' : 'Tidak Aktif',
            $layanan->created_at->format('d/m/Y H:i'),
        ];
    });

    $filename = 'layanan_' . now()->format('Y-m-d_His') . '.xlsx';

    $export = new class(Collection::make($data)) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
        public function __construct(public Collection $data) {}

        public function collection() {
            return $this->data;
        }

        public function headings(): array {
            return ['Nama Layanan', 'Slug', 'Deskripsi', 'Harga', 'Status', 'Dibuat'];
        }
    };

    return Excel::download($export, $filename);
});

$layanans = computed(function () {
    $query = Layanan::query();

    // Search
    if (!empty($this->search)) {
        $query->where(function ($q) {
            $q->where('name', 'like', "%{$this->search}%")
              ->orWhere('description', 'like', "%{$this->search}%")
              ->orWhere('slug', 'like', "%{$this->search}%");
        });
    }

    // Sorting
    $allowedSorts = ['name', 'price', 'is_active', 'created_at'];
    if (!in_array($this->sortBy, $allowedSorts)) {
        $this->sortBy = 'created_at';
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
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Layanan') }}</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Kelola data layanan sanggar') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <flux:button wire:click="exportExcel" variant="ghost" icon="table-cells"
                    class="!bg-green-600 hover:!bg-green-700 dark:!bg-green-500 dark:hover:!bg-green-600 !text-white">
                    {{ __('Excel') }}
                </flux:button>
                <flux:button wire:click="exportPdf" variant="ghost" icon="document-text"
                    class="!bg-red-900 hover:!bg-red-950 dark:!bg-red-950 dark:hover:!bg-red-900 !text-white">
                    {{ __('PDF') }}
                </flux:button>
                <flux:button :href="route('godmode.layanan.create')" variant="primary" icon="plus" wire:navigate>
                    {{ __('Tambah Layanan') }}
                </flux:button>
            </div>
        </div>

        <flux:card>
            <div class="mb-4">
                <flux:input wire:model.live.debounce.500ms="search" name="search" type="search" icon="magnifying-glass"
                    placeholder="{{ __('Cari nama, deskripsi, atau slug...') }}" class="w-full" />
            </div>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Gambar') }}</flux:table.column>
                    <flux:table.column>
                        <button wire:click="sort('name')"
                            class="flex items-center gap-1 hover:text-orange-600 dark:hover:text-orange-400">
                            {{ __('Nama Layanan') }}
                            <flux:icon :name="$this->sortIconName" variant="mini" />
                        </button>
                    </flux:table.column>
                    <flux:table.column>
                        <button wire:click="sort('price')"
                            class="flex items-center gap-1 hover:text-orange-600 dark:hover:text-orange-400">
                            {{ __('Harga') }}
                            <flux:icon :name="$this->sortIconPrice" variant="mini" />
                        </button>
                    </flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($this->layanans as $layanan)
                    <flux:table.row>
                        <flux:table.cell>
                            @if ($layanan->getFirstMediaUrl('layanan_cover', 'thumb'))
                            <img src="{{ $layanan->getFirstMediaUrl('layanan_cover', 'thumb') }}" 
                                alt="{{ $layanan->name }}"
                                class="h-16 w-16 rounded-lg object-cover" />
                            @else
                            <div class="flex h-16 w-16 items-center justify-center rounded-lg bg-gray-200 dark:bg-gray-700">
                                <flux:icon name="photo" class="h-8 w-8 text-gray-400" />
                            </div>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="font-medium text-gray-900 dark:text-white">{{ $layanan->name }}</div>
                            @if ($layanan->slug)
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $layanan->slug }}</div>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="text-gray-600 dark:text-gray-400">
                                @if ($layanan->price)
                                Rp {{ number_format($layanan->price, 0, ',', '.') }}
                                @else
                                <span class="text-gray-400">-</span>
                                @endif
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $layanan->is_active ? 'bg-green-50 text-green-700 ring-green-700/10 dark:bg-green-900/50 dark:text-green-200' : 'bg-red-50 text-red-700 ring-red-700/10 dark:bg-red-900/50 dark:text-red-200' }}">
                                {{ $layanan->is_active ? __('Aktif') : __('Tidak Aktif') }}
                            </span>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <flux:button wire:click="showDetail({{ $layanan->id }})" variant="ghost" size="sm"
                                    icon="eye"
                                    class="!p-2 !bg-purple-600 hover:!bg-purple-700 dark:!bg-purple-500 dark:hover:!bg-purple-600 !text-white !rounded-md"
                                    title="{{ __('Detail') }}" />
                                <flux:button :href="route('godmode.layanan.edit', $layanan)" variant="ghost" size="sm"
                                    icon="pencil" wire:navigate
                                    class="!p-2 !bg-blue-600 hover:!bg-blue-700 dark:!bg-blue-500 dark:hover:!bg-blue-600 !text-white !rounded-md"
                                    title="{{ __('Edit') }}" />
                                <flux:button wire:click="openDeleteModal({{ $layanan->id }})"
                                    variant="ghost" size="sm" icon="trash"
                                    class="!p-2 !bg-red-600 hover:!bg-red-700 dark:!bg-red-500 dark:hover:!bg-red-600 !text-white !rounded-md"
                                    title="{{ __('Hapus') }}" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                    @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center text-gray-500 dark:text-gray-400">
                            {{ __('Tidak ada layanan') }}
                        </flux:table.cell>
                    </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Menampilkan') }} {{ $this->layanans->firstItem() ?? 0 }} {{ __('sampai') }} {{
                    $this->layanans->lastItem() ?? 0 }}
                    {{ __('dari') }} {{ $this->layanans->total() }} {{ __('hasil') }}
                </div>
                @if ($this->layanans->hasPages())
                <div>
                    {{ $this->layanans->links() }}
                </div>
                @endif
            </div>
        </flux:card>
    </div>

    <!-- Detail Modal -->
    @if ($showDetailModal && $selectedLayanan)
    <flux:modal name="detail-layanan" :show="$showDetailModal" wire:model="showDetailModal" class="max-w-2xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Detail Layanan') }}</flux:heading>
                <flux:subheading>{{ __('Informasi lengkap layanan') }}</flux:subheading>
            </div>

            <div class="space-y-4">
                @if ($selectedLayanan->getFirstMediaUrl('layanan_cover'))
                <div>
                    <flux:label>{{ __('Gambar Cover') }}</flux:label>
                    <div class="mt-2">
                        <img src="{{ $selectedLayanan->getFirstMediaUrl('layanan_cover') }}" 
                            alt="{{ $selectedLayanan->name }}"
                            class="h-64 w-full rounded-lg object-cover" />
                    </div>
                </div>
                @endif

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <flux:label>{{ __('Nama Layanan') }}</flux:label>
                        <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedLayanan->name }}</div>
                    </div>
                    @if ($selectedLayanan->slug)
                    <div>
                        <flux:label>{{ __('Slug') }}</flux:label>
                        <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedLayanan->slug }}</div>
                    </div>
                    @endif
                    @if ($selectedLayanan->price)
                    <div>
                        <flux:label>{{ __('Harga Langganan') }}</flux:label>
                        <div class="mt-1 text-sm text-gray-900 dark:text-white">
                            Rp {{ number_format($selectedLayanan->price, 0, ',', '.') }}
                        </div>
                    </div>
                    @endif
                    <div>
                        <flux:label>{{ __('Status Aktif') }}</flux:label>
                        <div class="mt-1">
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $selectedLayanan->is_active ? 'bg-green-50 text-green-700 ring-green-700/10 dark:bg-green-900/50 dark:text-green-200' : 'bg-red-50 text-red-700 ring-red-700/10 dark:bg-red-900/50 dark:text-red-200' }}">
                                {{ $selectedLayanan->is_active ? __('Aktif') : __('Tidak Aktif') }}
                            </span>
                        </div>
                    </div>
                    @if ($selectedLayanan->description)
                    <div class="col-span-2">
                        <flux:label>{{ __('Deskripsi Lengkap') }}</flux:label>
                        <div class="mt-1 text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $selectedLayanan->description }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:button variant="ghost" wire:click="closeDetail">
                    {{ __('Tutup') }}
                </flux:button>
                <flux:button :href="route('godmode.layanan.edit', $selectedLayanan)" variant="primary" wire:navigate>
                    {{ __('Edit') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
    @endif

    <!-- Delete Confirmation Modal -->
    <flux:modal name="delete-layanan" :show="$showDeleteModal" wire:model="showDeleteModal" class="max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Hapus Layanan') }}</flux:heading>
                <flux:subheading>{{ __('Apakah Anda yakin ingin menghapus layanan ini?') }}</flux:subheading>
            </div>

            @if ($layananToDelete)
            <div class="rounded-lg bg-red-50 dark:bg-red-900/20 p-4">
                <p class="text-sm text-red-800 dark:text-red-200">
                    <strong>{{ $layananToDelete->name }}</strong> akan dihapus secara permanen.
                </p>
                <p class="text-sm text-red-700 dark:text-red-300 mt-2">
                    {{ __('Gambar dan data terkait juga akan dihapus.') }}
                </p>
            </div>
            @endif

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="ghost" wire:click="closeDeleteModal">{{ __('Batal') }}</flux:button>
                </flux:modal.close>
                <flux:button wire:click="deleteLayanan" variant="danger">{{ __('Hapus') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>

