<?php

use App\Models\Payment;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Collection;
use function Livewire\Volt\{layout, title, state, action, computed, on, uses};

uses(WithPagination::class);

layout('components.layouts.admin');
title(fn () => __('Pembayaran'));

state([
    'search' => fn () => request()->get('search', ''),
    'sortBy' => fn () => request()->get('sort_by', 'created_at'),
    'sortDir' => fn () => request()->get('sort_dir', 'desc'),
    'selectedPayment' => null,
    'showDetailModal' => false,
    'showDeleteModal' => false,
    'paymentToDelete' => null,
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

$sortIconAmount = computed(function () {
    if ($this->sortBy !== 'amount') {
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

$showDetail = action(function ($paymentId) {
    $this->selectedPayment = Payment::with(['subscription.anggota.user', 'subscription.layanan'])->findOrFail($paymentId);
    $this->showDetailModal = true;
});

$closeDetail = action(function () {
    $this->showDetailModal = false;
    $this->selectedPayment = null;
});

$openDeleteModal = action(function ($paymentId) {
    $this->paymentToDelete = Payment::with(['subscription.anggota.user', 'subscription.layanan'])->findOrFail($paymentId);
    $this->showDeleteModal = true;
});

$closeDeleteModal = action(function () {
    $this->showDeleteModal = false;
    $this->paymentToDelete = null;
});

$deletePayment = action(function () {
    if ($this->paymentToDelete) {
        $this->paymentToDelete->delete();
        $this->dispatch('toast', message: __('Pembayaran berhasil dihapus.'), variant: 'success');
        $this->closeDeleteModal();
    }
});

$exportExcel = action(function () {
    $query = Payment::with(['subscription.anggota.user', 'subscription.layanan']);

    if (!empty($this->search)) {
        $query->whereHas('subscription.anggota.user', function ($q) {
            $q->where('name', 'like', "%{$this->search}%")
              ->orWhere('email', 'like', "%{$this->search}%");
        })->orWhereHas('subscription.layanan', function ($q) {
            $q->where('name', 'like', "%{$this->search}%");
        })->orWhere('method', 'like', "%{$this->search}%")
          ->orWhere('status', 'like', "%{$this->search}%");
    }

    $allowedSorts = ['amount', 'method', 'status', 'paid_at', 'created_at'];
    if (in_array($this->sortBy, $allowedSorts)) {
        $query->orderBy($this->sortBy, $this->sortDir);
    } else {
        $query->orderBy('created_at', 'desc');
    }

    $payments = $query->get();

    $data = $payments->map(function ($payment) {
        return [
            $payment->id,
            $payment->subscription->anggota->user->name ?? '-',
            $payment->subscription->layanan->name ?? '-',
            number_format($payment->amount, 0, ',', '.'),
            ucfirst($payment->method),
            ucfirst($payment->status),
            $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : '-',
            $payment->created_at->format('d/m/Y H:i'),
        ];
    });

    $filename = 'payments_' . now()->format('Y-m-d_His') . '.xlsx';

    $export = new class(Collection::make($data)) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
        public function __construct(public Collection $data) {}

        public function collection() {
            return $this->data;
        }

        public function headings(): array {
            return ['ID', 'Nama Anggota', 'Nama Layanan', 'Amount', 'Method', 'Status', 'Paid At', 'Created At'];
        }
    };

    return Excel::download($export, $filename);
});

$exportPdf = action(function () {
    $query = Payment::with(['subscription.anggota.user', 'subscription.layanan']);

    if (!empty($this->search)) {
        $query->whereHas('subscription.anggota.user', function ($q) {
            $q->where('name', 'like', "%{$this->search}%")
              ->orWhere('email', 'like', "%{$this->search}%");
        })->orWhereHas('subscription.layanan', function ($q) {
            $q->where('name', 'like', "%{$this->search}%");
        })->orWhere('method', 'like', "%{$this->search}%")
          ->orWhere('status', 'like', "%{$this->search}%");
    }

    $allowedSorts = ['amount', 'method', 'status', 'paid_at', 'created_at'];
    if (in_array($this->sortBy, $allowedSorts)) {
        $query->orderBy($this->sortBy, $this->sortDir);
    } else {
        $query->orderBy('created_at', 'desc');
    }

    $payments = $query->get();

    $html = view('exports.payments-pdf', ['payments' => $payments])->render();

    $pdf = Pdf::loadHTML($html);
    $filename = 'payments_' . now()->format('Y-m-d_His') . '.pdf';

    return Response::streamDownload(function () use ($pdf) {
        echo $pdf->output();
    }, $filename);
});

$payments = computed(function () {
    $query = Payment::with(['subscription.anggota.user', 'subscription.layanan']);

    // Search
    if (!empty($this->search)) {
        $query->whereHas('subscription.anggota.user', function ($q) {
            $q->where('name', 'like', "%{$this->search}%")
              ->orWhere('email', 'like', "%{$this->search}%");
        })->orWhereHas('subscription.layanan', function ($q) {
            $q->where('name', 'like', "%{$this->search}%");
        })->orWhere('method', 'like', "%{$this->search}%")
          ->orWhere('status', 'like', "%{$this->search}%");
    }

    // Sorting
    $allowedSorts = ['amount', 'method', 'status', 'paid_at', 'created_at'];
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
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Pembayaran') }}</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Kelola data pembayaran langganan') }}</p>
            </div>
            <div class="flex items-center gap-2">
                @can('mengekspor payment')
                <flux:button wire:click="exportExcel" variant="ghost" icon="table-cells"
                    class="!bg-green-600 hover:!bg-green-700 dark:!bg-green-500 dark:hover:!bg-green-600 !text-white">
                    {{ __('Excel') }}
                </flux:button>
                <flux:button wire:click="exportPdf" variant="ghost" icon="document-text"
                    class="!bg-red-900 hover:!bg-red-950 dark:!bg-red-950 dark:hover:!bg-red-900 !text-white">
                    {{ __('PDF') }}
                </flux:button>
                @endcan
                @can('membuat payment')
                <flux:button :href="route('godmode.payments.create')" variant="primary" icon="plus" wire:navigate>
                    {{ __('Tambah Pembayaran') }}
                </flux:button>
                @endcan
            </div>
        </div>

        <flux:card>
            <div class="mb-4">
                <flux:input wire:model.live.debounce.500ms="search" name="search" type="search" icon="magnifying-glass"
                    placeholder="{{ __('Cari nama anggota, layanan, method, atau status...') }}" class="w-full" />
            </div>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Langganan') }}</flux:table.column>
                    <flux:table.column>
                        <button wire:click="sort('amount')"
                            class="flex items-center gap-1 hover:text-orange-600 dark:hover:text-orange-400">
                            {{ __('Amount') }}
                            <flux:icon :name="$this->sortIconAmount" variant="mini" />
                        </button>
                    </flux:table.column>
                    <flux:table.column>{{ __('Method') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Paid At') }}</flux:table.column>
                    <flux:table.column>
                        <button wire:click="sort('created_at')"
                            class="flex items-center gap-1 hover:text-orange-600 dark:hover:text-orange-400">
                            {{ __('Created At') }}
                            <flux:icon :name="$this->sortIconCreatedAt" variant="mini" />
                        </button>
                    </flux:table.column>
                    @if (auth()->user()->can('melihat payment') || auth()->user()->can('mengubah payment') || auth()->user()->can('menghapus payment'))
                    <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                    @endif
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($this->payments as $payment)
                    <flux:table.row>
                        <flux:table.cell>
                            <div class="font-medium text-gray-900 dark:text-white">{{ $payment->subscription->anggota->user->name ?? '-' }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $payment->subscription->layanan->name ?? '-' }}</div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="text-gray-600 dark:text-gray-400 font-semibold">{{ $payment->formatted_amount }}</div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="text-gray-600 dark:text-gray-400">{{ ucfirst($payment->method) }}</div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $payment->status_badge_color }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="text-gray-600 dark:text-gray-400">{{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : '-' }}</div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="text-gray-600 dark:text-gray-400">{{ $payment->created_at->format('d/m/Y H:i') }}</div>
                        </flux:table.cell>
                        @if (auth()->user()->can('melihat payment') || auth()->user()->can('mengubah payment') || auth()->user()->can('menghapus payment'))
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                @can('melihat payment')
                                <flux:button wire:click="showDetail({{ $payment->id }})" variant="ghost" size="sm"
                                    icon="eye"
                                    class="!p-2 !bg-purple-600 hover:!bg-purple-700 dark:!bg-purple-500 dark:hover:!bg-purple-600 !text-white !rounded-md"
                                    title="{{ __('Detail') }}" />
                                @endcan
                                @can('mengubah payment')
                                <flux:button :href="route('godmode.payments.edit', $payment)" variant="ghost" size="sm"
                                    icon="pencil" wire:navigate
                                    class="!p-2 !bg-blue-600 hover:!bg-blue-700 dark:!bg-blue-500 dark:hover:!bg-blue-600 !text-white !rounded-md"
                                    title="{{ __('Edit') }}" />
                                @endcan
                                @can('menghapus payment')
                                <flux:button wire:click="openDeleteModal({{ $payment->id }})"
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
                            colspan="{{ auth()->user()->can('melihat payment') || auth()->user()->can('mengubah payment') || auth()->user()->can('menghapus payment') ? '7' : '6' }}"
                            class="text-center text-gray-500 dark:text-gray-400">
                            {{ __('Tidak ada pembayaran') }}
                        </flux:table.cell>
                    </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Menampilkan') }} {{ $this->payments->firstItem() ?? 0 }} {{ __('sampai') }} {{
                    $this->payments->lastItem() ?? 0 }}
                    {{ __('dari') }} {{ $this->payments->total() }} {{ __('hasil') }}
                </div>
                @if ($this->payments->hasPages())
                <div>
                    {{ $this->payments->links() }}
                </div>
                @endif
            </div>
        </flux:card>
    </div>

    <!-- Detail Modal -->
    @if ($showDetailModal && $selectedPayment)
    <flux:modal name="detail-payment" :show="$showDetailModal" wire:model="showDetailModal" class="max-w-2xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Detail Pembayaran') }}</flux:heading>
                <flux:subheading>{{ __('Informasi lengkap pembayaran') }}</flux:subheading>
            </div>

            <div class="space-y-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">{{ __('Data Langganan') }}</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <flux:label>{{ __('Anggota') }}</flux:label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedPayment->subscription->anggota->user->name ?? '-' }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $selectedPayment->subscription->anggota->user->email ?? '-' }}</div>
                        </div>
                        <div>
                            <flux:label>{{ __('Layanan') }}</flux:label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedPayment->subscription->layanan->name ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">{{ __('Data Pembayaran') }}</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <flux:label>{{ __('Amount') }}</flux:label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-white font-semibold">{{ $selectedPayment->formatted_amount }}</div>
                        </div>
                        <div>
                            <flux:label>{{ __('Method') }}</flux:label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ ucfirst($selectedPayment->method) }}</div>
                        </div>
                        <div>
                            <flux:label>{{ __('Status') }}</flux:label>
                            <div class="mt-1">
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $selectedPayment->status_badge_color }}">
                                    {{ ucfirst($selectedPayment->status) }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <flux:label>{{ __('Paid At') }}</flux:label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedPayment->paid_at ? $selectedPayment->paid_at->format('d/m/Y H:i') : '-' }}</div>
                        </div>
                        @if ($selectedPayment->getFirstMediaUrl('payment_proof'))
                        <div class="col-span-2">
                            <flux:label>{{ __('Bukti Pembayaran') }}</flux:label>
                            <div class="mt-2">
                                <img src="{{ $selectedPayment->getFirstMediaUrl('payment_proof') }}" alt="Bukti Pembayaran" class="max-w-full h-auto rounded-lg border border-gray-300 dark:border-gray-700">
                            </div>
                        </div>
                        @endif
                        <div>
                            <flux:label>{{ __('Created At') }}</flux:label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedPayment->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                        <div>
                            <flux:label>{{ __('Updated At') }}</flux:label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedPayment->updated_at->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:button variant="ghost" wire:click="closeDetail">
                    {{ __('Tutup') }}
                </flux:button>
                @can('mengubah payment')
                <flux:button :href="route('godmode.payments.edit', $selectedPayment)" variant="primary" wire:navigate>
                    {{ __('Edit') }}
                </flux:button>
                @endcan
            </div>
        </div>
    </flux:modal>
    @endif

    <!-- Delete Confirmation Modal -->
    <flux:modal name="delete-payment" :show="$showDeleteModal" wire:model="showDeleteModal" class="max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Hapus Pembayaran') }}</flux:heading>
                <flux:subheading>{{ __('Apakah Anda yakin ingin menghapus pembayaran ini?') }}</flux:subheading>
            </div>

            @if ($paymentToDelete)
            <div class="rounded-lg bg-red-50 dark:bg-red-900/20 p-4">
                <p class="text-sm text-red-800 dark:text-red-200">
                    Pembayaran untuk <strong>{{ $paymentToDelete->subscription->anggota->user->name ?? '-' }}</strong> 
                    ({{ $paymentToDelete->subscription->layanan->name ?? '-' }}) 
                    sebesar <strong>{{ $paymentToDelete->formatted_amount }}</strong> akan dihapus secara permanen.
                </p>
            </div>
            @endif

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="ghost" wire:click="closeDeleteModal">{{ __('Batal') }}</flux:button>
                </flux:modal.close>
                <flux:button wire:click="deletePayment" variant="danger">{{ __('Hapus') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>

