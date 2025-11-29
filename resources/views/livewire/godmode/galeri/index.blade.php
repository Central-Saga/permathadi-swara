<?php

use App\Models\Galeri;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Collection;
use function Livewire\Volt\{layout, title, state, action, computed, on, uses};

uses(WithPagination::class);

layout('components.layouts.admin');
title(fn () => __('Galeri'));

state([
    'search' => fn () => request()->get('search', ''),
    'sortBy' => fn () => request()->get('sort_by', 'created_at'),
    'sortDir' => fn () => request()->get('sort_dir', 'desc'),
    'selectedGaleri' => null,
    'showDetailModal' => false,
    'showDeleteModal' => false,
    'galeriToDelete' => null,
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

$sortIconTitle = computed(function () {
    if ($this->sortBy !== 'title') {
        return 'chevrons-up-down';
    }
    return $this->sortDir === 'asc' ? 'chevron-up' : 'chevron-down';
});

$sortIconCreatedAt = computed(function () {
    if ($this->sortBy !== 'created_at') {
        return 'chevrons-up-down';
    }
    return $this->sortDir === 'asc' ? 'chevron-up' : 'chevron-down';
});

$showDetail = action(function ($galeriId) {
    $this->selectedGaleri = Galeri::with('media')->findOrFail($galeriId);
    $this->showDetailModal = true;
});

$closeDetail = action(function () {
    $this->showDetailModal = false;
    $this->selectedGaleri = null;
});

$openDeleteModal = action(function ($galeriId) {
    $this->galeriToDelete = Galeri::with('media')->findOrFail($galeriId);
    $this->showDeleteModal = true;
});

$closeDeleteModal = action(function () {
    $this->showDeleteModal = false;
    $this->galeriToDelete = null;
});

$deleteGaleri = action(function () {
    if ($this->galeriToDelete) {
        $this->galeriToDelete->delete();
        $this->dispatch('toast', message: __('Galeri berhasil dihapus.'), variant: 'success');
        $this->closeDeleteModal();
    }
});

$exportExcel = action(function () {
    $query = Galeri::with('media');

    if (!empty($this->search)) {
        $query->where(function ($q) {
            $q->where('title', 'like', "%{$this->search}%")
              ->orWhere('description', 'like', "%{$this->search}%");
        });
    }

    $allowedSorts = ['title', 'is_published', 'published_at', 'created_at'];
    if (in_array($this->sortBy, $allowedSorts)) {
        $query->orderBy($this->sortBy, $this->sortDir);
    } else {
        $query->orderBy('created_at', 'desc');
    }

    $galeri = $query->get();

    $data = $galeri->map(function ($item) {
        return [
            $item->id,
            $item->title,
            $item->description ? \Illuminate\Support\Str::limit($item->description, 100) : '-',
            $item->is_published ? 'Published' : 'Draft',
            $item->published_at ? $item->published_at->format('d/m/Y H:i') : '-',
            $item->getMedia('galeri_images')->count(),
            $item->created_at->format('d/m/Y H:i'),
        ];
    });

    $filename = 'galeri_' . now()->format('Y-m-d_His') . '.xlsx';

    $export = new class(Collection::make($data)) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
        public function __construct(public Collection $data) {}

        public function collection() {
            return $this->data;
        }

        public function headings(): array {
            return ['ID', 'Title', 'Description', 'Status', 'Published At', 'Jumlah Gambar', 'Created At'];
        }
    };

    return Excel::download($export, $filename);
});

$exportPdf = action(function () {
    $query = Galeri::with('media');

    if (!empty($this->search)) {
        $query->where(function ($q) {
            $q->where('title', 'like', "%{$this->search}%")
              ->orWhere('description', 'like', "%{$this->search}%");
        });
    }

    $allowedSorts = ['title', 'is_published', 'published_at', 'created_at'];
    if (in_array($this->sortBy, $allowedSorts)) {
        $query->orderBy($this->sortBy, $this->sortDir);
    } else {
        $query->orderBy('created_at', 'desc');
    }

    $galeri = $query->get();

    $html = view('exports.galeri-pdf', ['galeri' => $galeri])->render();

    $pdf = Pdf::loadHTML($html);
    $filename = 'galeri_' . now()->format('Y-m-d_His') . '.pdf';

    return Response::streamDownload(function () use ($pdf) {
        echo $pdf->output();
    }, $filename);
});

$galeri = computed(function () {
    $query = Galeri::with('media');

    // Search
    if (!empty($this->search)) {
        $query->where(function ($q) {
            $q->where('title', 'like', "%{$this->search}%")
              ->orWhere('description', 'like', "%{$this->search}%");
        });
    }

    // Sorting
    $allowedSorts = ['title', 'is_published', 'published_at', 'created_at'];
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
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Galeri') }}</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Kelola galeri dokumentasi sanggar') }}</p>
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
                <flux:button :href="route('godmode.galeri.create')" variant="primary" icon="plus" wire:navigate>
                    {{ __('Tambah Galeri') }}
                </flux:button>
            </div>
        </div>

        <flux:card>
            <div class="mb-4">
                <flux:input wire:model.live.debounce.500ms="search" name="search" type="search" icon="magnifying-glass"
                    placeholder="{{ __('Cari judul atau deskripsi...') }}" class="w-full" />
            </div>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Gambar') }}</flux:table.column>
                    <flux:table.column>
                        <button wire:click="sort('title')"
                            class="flex items-center gap-1 hover:text-orange-600 dark:hover:text-orange-400">
                            {{ __('Judul') }}
                            <flux:icon :name="$this->sortIconTitle" variant="mini" />
                        </button>
                    </flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>
                        <button wire:click="sort('created_at')"
                            class="flex items-center gap-1 hover:text-orange-600 dark:hover:text-orange-400">
                            {{ __('Dibuat') }}
                            <flux:icon :name="$this->sortIconCreatedAt" variant="mini" />
                        </button>
                    </flux:table.column>
                    <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($this->galeri as $item)
                    <flux:table.row>
                        <flux:table.cell>
                            @if($item->getFirstMediaUrl('galeri_images'))
                            @php
                            $thumbUrl = $item->getFirstMediaUrl('galeri_images', 'thumb');
                            $originalUrl = $item->getFirstMediaUrl('galeri_images');
                            $imageUrl = $thumbUrl ?: $originalUrl;
                            @endphp
                            <img src="{{ $imageUrl }}" alt="{{ $item->title }}"
                                class="h-16 w-16 rounded-lg object-cover border border-gray-300 dark:border-gray-700" />
                            @else
                            <div
                                class="flex h-16 w-16 items-center justify-center rounded-lg bg-gray-200 dark:bg-gray-700">
                                <flux:icon name="photo" class="h-8 w-8 text-gray-400" />
                            </div>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="font-medium text-gray-900 dark:text-white">{{ $item->title }}</div>
                            @if($item->description)
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                {{ \Illuminate\Support\Str::limit($item->description, 60) }}
                            </div>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <span
                                class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $item->is_published ? 'bg-green-50 text-green-700 ring-green-700/10 dark:bg-green-900/50 dark:text-green-200' : 'bg-gray-50 text-gray-700 ring-gray-700/10 dark:bg-gray-900/50 dark:text-gray-200' }}">
                                {{ $item->is_published ? __('Published') : __('Draft') }}
                            </span>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="text-sm text-gray-600 dark:text-gray-400">{{ $item->created_at->format('d/m/Y
                                H:i') }}</div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <flux:button wire:click="showDetail({{ $item->id }})" variant="ghost" size="sm"
                                    icon="eye"
                                    class="!p-2 !bg-purple-600 hover:!bg-purple-700 dark:!bg-purple-500 dark:hover:!bg-purple-600 !text-white !rounded-md"
                                    title="{{ __('Detail') }}" />
                                <flux:button :href="route('godmode.galeri.edit', $item)" variant="ghost" size="sm"
                                    icon="pencil" wire:navigate
                                    class="!p-2 !bg-blue-600 hover:!bg-blue-700 dark:!bg-blue-500 dark:hover:!bg-blue-600 !text-white !rounded-md"
                                    title="{{ __('Edit') }}" />
                                <flux:button wire:click="openDeleteModal({{ $item->id }})" variant="ghost" size="sm"
                                    icon="trash"
                                    class="!p-2 !bg-red-600 hover:!bg-red-700 dark:!bg-red-500 dark:hover:!bg-red-600 !text-white !rounded-md"
                                    title="{{ __('Hapus') }}" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                    @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center text-gray-500 dark:text-gray-400">
                            {{ __('Tidak ada galeri') }}
                        </flux:table.cell>
                    </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Menampilkan') }} {{ $this->galeri->firstItem() ?? 0 }} {{ __('sampai') }} {{
                    $this->galeri->lastItem() ?? 0 }}
                    {{ __('dari') }} {{ $this->galeri->total() }} {{ __('hasil') }}
                </div>
                @if ($this->galeri->hasPages())
                <div>
                    {{ $this->galeri->links() }}
                </div>
                @endif
            </div>
        </flux:card>
    </div>

    <!-- Detail Modal -->
    @if ($showDetailModal && $selectedGaleri)
    <flux:modal name="detail-galeri" :show="$showDetailModal" wire:model="showDetailModal" class="max-w-4xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Detail Galeri') }}</flux:heading>
                <flux:subheading>{{ __('Informasi lengkap galeri') }}</flux:subheading>
            </div>

            <div class="space-y-4">
                <div>
                    <flux:label>{{ __('Judul') }}</flux:label>
                    <div class="mt-1 text-sm text-gray-900 dark:text-white font-semibold">{{ $selectedGaleri->title }}
                    </div>
                </div>

                @if($selectedGaleri->description)
                <div>
                    <flux:label>{{ __('Deskripsi') }}</flux:label>
                    <div class="mt-1 text-sm text-gray-600 dark:text-gray-400 whitespace-pre-wrap">{{
                        $selectedGaleri->description }}</div>
                </div>
                @endif

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <flux:label>{{ __('Status') }}</flux:label>
                        <div class="mt-1">
                            <span
                                class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $selectedGaleri->is_published ? 'bg-green-50 text-green-700 ring-green-700/10 dark:bg-green-900/50 dark:text-green-200' : 'bg-gray-50 text-gray-700 ring-gray-700/10 dark:bg-gray-900/50 dark:text-gray-200' }}">
                                {{ $selectedGaleri->is_published ? __('Published') : __('Draft') }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <flux:label>{{ __('Published At') }}</flux:label>
                        <div class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $selectedGaleri->published_at ? $selectedGaleri->published_at->format('d/m/Y H:i') : '-'
                            }}
                        </div>
                    </div>
                </div>

                @if($selectedGaleri->getMedia('galeri_images')->count() > 0)
                <div>
                    <flux:label>{{ __('Gambar') }} ({{ $selectedGaleri->getMedia('galeri_images')->count() }})
                    </flux:label>
                    <div class="mt-2 grid grid-cols-3 gap-4">
                        @foreach($selectedGaleri->getMedia('galeri_images') as $media)
                        <div class="relative">
                            @php
                            $thumbUrl = $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : null;
                            $imageUrl = $thumbUrl ?: $media->getUrl();
                            @endphp
                            <img src="{{ $imageUrl }}" alt="{{ $media->name }}"
                                class="w-full h-32 object-cover rounded-lg border border-gray-300 dark:border-gray-700" />
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <flux:label>{{ __('Created At') }}</flux:label>
                        <div class="mt-1 text-sm text-gray-900 dark:text-white">{{
                            $selectedGaleri->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <div>
                        <flux:label>{{ __('Updated At') }}</flux:label>
                        <div class="mt-1 text-sm text-gray-900 dark:text-white">{{
                            $selectedGaleri->updated_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:button variant="ghost" wire:click="closeDetail">
                    {{ __('Tutup') }}
                </flux:button>
                <flux:button :href="route('godmode.galeri.edit', $selectedGaleri)" variant="primary" wire:navigate>
                    {{ __('Edit') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
    @endif

    <!-- Delete Confirmation Modal -->
    <flux:modal name="delete-galeri" :show="$showDeleteModal" wire:model="showDeleteModal" class="max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Hapus Galeri') }}</flux:heading>
                <flux:subheading>{{ __('Apakah Anda yakin ingin menghapus galeri ini?') }}</flux:subheading>
            </div>

            @if ($galeriToDelete)
            <div class="rounded-lg bg-red-50 dark:bg-red-900/20 p-4">
                <p class="text-sm text-red-800 dark:text-red-200">
                    Galeri <strong>{{ $galeriToDelete->title }}</strong> dengan
                    <strong>{{ $galeriToDelete->getMedia('galeri_images')->count() }}</strong> gambar akan dihapus
                    secara permanen.
                </p>
            </div>
            @endif

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="ghost" wire:click="closeDeleteModal">{{ __('Batal') }}</flux:button>
                </flux:modal.close>
                <flux:button wire:click="deleteGaleri" variant="danger">{{ __('Hapus') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>