<?php

use App\Models\Subscription;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Collection;
use function Livewire\Volt\{layout, title, state, action, computed, on, uses};

uses(WithPagination::class);

layout('components.layouts.admin');
title(fn () => __('Langganan'));

state([
    'search' => fn () => request()->get('search', ''),
    'sortBy' => fn () => request()->get('sort_by', 'start_date'),
    'sortDir' => fn () => request()->get('sort_dir', 'desc'),
    'selectedSubscription' => null,
    'showDetailModal' => false,
    'showDeleteModal' => false,
    'subscriptionToDelete' => null,
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

$sortIconAnggota = computed(function () {
    if ($this->sortBy !== 'anggota') {
        return 'chevrons-up-down';
    }
    return $this->sortDir === 'asc' ? 'chevron-up' : 'chevron-down';
});

$sortIconStartDate = computed(function () {
    if ($this->sortBy !== 'start_date') {
        return 'chevrons-up-down';
    }
    return $this->sortDir === 'asc' ? 'chevron-up' : 'chevron-down';
});

$showDetail = action(function ($subscriptionId) {
    $this->selectedSubscription = Subscription::with(['anggota.user', 'layanan'])->findOrFail($subscriptionId);
    $this->showDetailModal = true;
});

$closeDetail = action(function () {
    $this->showDetailModal = false;
    $this->selectedSubscription = null;
});

$openDeleteModal = action(function ($subscriptionId) {
    $this->subscriptionToDelete = Subscription::with(['anggota.user', 'layanan'])->findOrFail($subscriptionId);
    $this->showDeleteModal = true;
});

$closeDeleteModal = action(function () {
    $this->showDeleteModal = false;
    $this->subscriptionToDelete = null;
});

$deleteSubscription = action(function () {
    if ($this->subscriptionToDelete) {
        $this->subscriptionToDelete->delete();
        $this->dispatch('toast', message: __('Langganan berhasil dihapus.'), variant: 'success');
        $this->closeDeleteModal();
    }
});

$exportExcel = action(function () {
    $query = Subscription::with(['anggota.user', 'layanan']);

    if (!empty($this->search)) {
        $query->whereHas('anggota.user', function ($q) {
            $q->where('name', 'like', "%{$this->search}%")
              ->orWhere('email', 'like', "%{$this->search}%");
        })->orWhereHas('layanan', function ($q) {
            $q->where('name', 'like', "%{$this->search}%");
        })->orWhere('status', 'like', "%{$this->search}%")
          ->orWhere('notes', 'like', "%{$this->search}%");
    }

    $allowedSorts = ['start_date', 'end_date', 'status', 'created_at'];
    if (in_array($this->sortBy, $allowedSorts)) {
        $query->orderBy($this->sortBy, $this->sortDir);
    } else {
        $query->orderBy('start_date', 'desc');
    }

    $subscriptions = $query->get();

    $data = $subscriptions->map(function ($subscription) {
        return [
            $subscription->anggota->user->name ?? '-',
            $subscription->layanan->name ?? '-',
            $subscription->start_date->format('d/m/Y'),
            $subscription->end_date ? $subscription->end_date->format('d/m/Y') : '-',
            ucfirst($subscription->status),
            $subscription->notes ?? '-',
            $subscription->created_at->format('d/m/Y H:i'),
        ];
    });

    $filename = 'subscriptions_' . now()->format('Y-m-d_His') . '.xlsx';

    $export = new class(Collection::make($data)) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
        public function __construct(public Collection $data) {}

        public function collection() {
            return $this->data;
        }

        public function headings(): array {
            return ['Nama Anggota', 'Nama Layanan', 'Tanggal Mulai', 'Tanggal Berakhir', 'Status', 'Catatan', 'Dibuat'];
        }
    };

    return Excel::download($export, $filename);
});

$exportPdf = action(function () {
    $query = Subscription::with(['anggota.user', 'layanan']);

    if (!empty($this->search)) {
        $query->whereHas('anggota.user', function ($q) {
            $q->where('name', 'like', "%{$this->search}%")
              ->orWhere('email', 'like', "%{$this->search}%");
        })->orWhereHas('layanan', function ($q) {
            $q->where('name', 'like', "%{$this->search}%");
        })->orWhere('status', 'like', "%{$this->search}%")
          ->orWhere('notes', 'like', "%{$this->search}%");
    }

    $allowedSorts = ['start_date', 'end_date', 'status', 'created_at'];
    if (in_array($this->sortBy, $allowedSorts)) {
        $query->orderBy($this->sortBy, $this->sortDir);
    } else {
        $query->orderBy('start_date', 'desc');
    }

    $subscriptions = $query->get();

    $html = view('exports.subscriptions-pdf', ['subscriptions' => $subscriptions])->render();

    $pdf = Pdf::loadHTML($html);
    $filename = 'subscriptions_' . now()->format('Y-m-d_His') . '.pdf';

    return Response::streamDownload(function () use ($pdf) {
        echo $pdf->output();
    }, $filename);
});

$subscriptions = computed(function () {
    $query = Subscription::with(['anggota.user', 'layanan']);

    // Search
    if (!empty($this->search)) {
        $query->whereHas('anggota.user', function ($q) {
            $q->where('name', 'like', "%{$this->search}%")
              ->orWhere('email', 'like', "%{$this->search}%");
        })->orWhereHas('layanan', function ($q) {
            $q->where('name', 'like', "%{$this->search}%");
        })->orWhere('status', 'like', "%{$this->search}%")
          ->orWhere('notes', 'like', "%{$this->search}%");
    }

    // Sorting
    $allowedSorts = ['start_date', 'end_date', 'status', 'created_at'];
    if (!in_array($this->sortBy, $allowedSorts)) {
        $this->sortBy = 'start_date';
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
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Langganan') }}</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Kelola data langganan anggota') }}</p>
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
                <flux:button :href="route('godmode.subscriptions.create')" variant="primary" icon="plus" wire:navigate>
                    {{ __('Tambah Langganan') }}
                </flux:button>
            </div>
        </div>

        <flux:card>
            <div class="mb-4">
                <flux:input wire:model.live.debounce.500ms="search" name="search" type="search" icon="magnifying-glass"
                    placeholder="{{ __('Cari nama anggota, layanan, atau status...') }}" class="w-full" />
            </div>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>
                        <button wire:click="sort('anggota')"
                            class="flex items-center gap-1 hover:text-orange-600 dark:hover:text-orange-400">
                            {{ __('Nama Anggota') }}
                            <flux:icon :name="$this->sortIconAnggota" variant="mini" />
                        </button>
                    </flux:table.column>
                    <flux:table.column>{{ __('Layanan') }}</flux:table.column>
                    <flux:table.column>
                        <button wire:click="sort('start_date')"
                            class="flex items-center gap-1 hover:text-orange-600 dark:hover:text-orange-400">
                            {{ __('Tanggal Mulai') }}
                            <flux:icon :name="$this->sortIconStartDate" variant="mini" />
                        </button>
                    </flux:table.column>
                    <flux:table.column>{{ __('Tanggal Berakhir') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($this->subscriptions as $subscription)
                    <flux:table.row>
                        <flux:table.cell>
                            <div class="font-medium text-gray-900 dark:text-white">{{ $subscription->anggota->user->name ?? '-' }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $subscription->anggota->user->email ?? '-' }}</div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="text-gray-600 dark:text-gray-400">{{ $subscription->layanan->name ?? '-' }}</div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="text-gray-600 dark:text-gray-400">{{ $subscription->start_date->format('d/m/Y') }}</div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="text-gray-600 dark:text-gray-400">{{ $subscription->end_date ? $subscription->end_date->format('d/m/Y') : '-' }}</div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $subscription->status_badge_color }}">
                                {{ ucfirst($subscription->status) }}
                            </span>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <flux:button wire:click="showDetail({{ $subscription->id }})" variant="ghost" size="sm"
                                    icon="eye"
                                    class="!p-2 !bg-purple-600 hover:!bg-purple-700 dark:!bg-purple-500 dark:hover:!bg-purple-600 !text-white !rounded-md"
                                    title="{{ __('Detail') }}" />
                                <flux:button :href="route('godmode.subscriptions.edit', $subscription)" variant="ghost" size="sm"
                                    icon="pencil" wire:navigate
                                    class="!p-2 !bg-blue-600 hover:!bg-blue-700 dark:!bg-blue-500 dark:hover:!bg-blue-600 !text-white !rounded-md"
                                    title="{{ __('Edit') }}" />
                                <flux:button wire:click="openDeleteModal({{ $subscription->id }})"
                                    variant="ghost" size="sm" icon="trash"
                                    class="!p-2 !bg-red-600 hover:!bg-red-700 dark:!bg-red-500 dark:hover:!bg-red-600 !text-white !rounded-md"
                                    title="{{ __('Hapus') }}" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                    @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center text-gray-500 dark:text-gray-400">
                            {{ __('Tidak ada langganan') }}
                        </flux:table.cell>
                    </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Menampilkan') }} {{ $this->subscriptions->firstItem() ?? 0 }} {{ __('sampai') }} {{
                    $this->subscriptions->lastItem() ?? 0
                    }}
                    {{ __('dari') }} {{ $this->subscriptions->total() }} {{ __('hasil') }}
                </div>
                @if ($this->subscriptions->hasPages())
                <div>
                    {{ $this->subscriptions->links() }}
                </div>
                @endif
            </div>
        </flux:card>
    </div>

    <!-- Detail Modal -->
    @if ($showDetailModal && $selectedSubscription)
    <flux:modal name="detail-subscription" :show="$showDetailModal" wire:model="showDetailModal" class="max-w-2xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Detail Langganan') }}</flux:heading>
                <flux:subheading>{{ __('Informasi lengkap langganan') }}</flux:subheading>
            </div>

            <div class="space-y-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">{{ __('Data Anggota') }}</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <flux:label>{{ __('Nama') }}</flux:label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedSubscription->anggota->user->name ?? '-' }}</div>
                        </div>
                        <div>
                            <flux:label>{{ __('Email') }}</flux:label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedSubscription->anggota->user->email ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">{{ __('Data Langganan') }}</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <flux:label>{{ __('Layanan') }}</flux:label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedSubscription->layanan->name ?? '-' }}</div>
                        </div>
                        <div>
                            <flux:label>{{ __('Status') }}</flux:label>
                            <div class="mt-1">
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $selectedSubscription->status_badge_color }}">
                                    {{ ucfirst($selectedSubscription->status) }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <flux:label>{{ __('Tanggal Mulai') }}</flux:label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedSubscription->start_date->format('d/m/Y') }}</div>
                        </div>
                        <div>
                            <flux:label>{{ __('Tanggal Berakhir') }}</flux:label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedSubscription->end_date ? $selectedSubscription->end_date->format('d/m/Y') : '-' }}</div>
                        </div>
                        @if ($selectedSubscription->notes)
                        <div class="col-span-2">
                            <flux:label>{{ __('Catatan') }}</flux:label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedSubscription->notes }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:button variant="ghost" wire:click="closeDetail">
                    {{ __('Tutup') }}
                </flux:button>
                <flux:button :href="route('godmode.subscriptions.edit', $selectedSubscription)" variant="primary" wire:navigate>
                    {{ __('Edit') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
    @endif

    <!-- Delete Confirmation Modal -->
    <flux:modal name="delete-subscription" :show="$showDeleteModal" wire:model="showDeleteModal" class="max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Hapus Langganan') }}</flux:heading>
                <flux:subheading>{{ __('Apakah Anda yakin ingin menghapus langganan ini?') }}</flux:subheading>
            </div>

            @if ($subscriptionToDelete)
            <div class="rounded-lg bg-red-50 dark:bg-red-900/20 p-4">
                <p class="text-sm text-red-800 dark:text-red-200">
                    Langganan <strong>{{ $subscriptionToDelete->anggota->user->name ?? '-' }}</strong> untuk layanan <strong>{{ $subscriptionToDelete->layanan->name ?? '-' }}</strong> akan dihapus secara permanen.
                </p>
            </div>
            @endif

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="ghost" wire:click="closeDeleteModal">{{ __('Batal') }}</flux:button>
                </flux:modal.close>
                <flux:button wire:click="deleteSubscription" variant="danger">{{ __('Hapus') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>

