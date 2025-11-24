<?php

use App\Models\ContactMessage;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Collection;
use function Livewire\Volt\{layout, title, state, action, computed, on, uses};

uses(WithPagination::class);

layout('components.layouts.admin');
title(fn () => __('Pesan Kontak'));

state([
    'search' => fn () => request()->get('search', ''),
    'statusFilter' => fn () => request()->get('status', ''),
    'sortBy' => fn () => request()->get('sort_by', 'created_at'),
    'sortDir' => fn () => request()->get('sort_dir', 'desc'),
    'selectedMessage' => null,
    'showDetailModal' => false,
    'status' => '',
]);

on(['updatingSearch' => function () {
    $this->resetPage();
}, 'updatingStatusFilter' => function () {
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

$sortIconStatus = computed(function () {
    if ($this->sortBy !== 'status') {
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

$showDetail = action(function ($messageId) {
    $this->selectedMessage = ContactMessage::findOrFail($messageId);
    $this->showDetailModal = true;
});

$closeDetail = action(function () {
    $this->showDetailModal = false;
    $this->selectedMessage = null;
});

$updateStatus = action(function ($messageId, $status) {
    $message = ContactMessage::findOrFail($messageId);
    $message->update(['status' => $status]);
    $this->dispatch('toast', message: __('Status pesan berhasil diupdate.'), variant: 'success');
    // Reset status state
    $this->status = '';
});

$exportExcel = action(function () {
    $query = ContactMessage::query();

    if (!empty($this->search)) {
        $query->where(function ($q) {
            $q->where('name', 'like', "%{$this->search}%")
              ->orWhere('email', 'like', "%{$this->search}%")
              ->orWhere('phone', 'like', "%{$this->search}%")
              ->orWhere('subject', 'like', "%{$this->search}%")
              ->orWhere('message', 'like', "%{$this->search}%");
        });
    }

    if (!empty($this->statusFilter)) {
        $query->where('status', $this->statusFilter);
    }

    $allowedSorts = ['status', 'created_at'];
    if (in_array($this->sortBy, $allowedSorts)) {
        $query->orderBy($this->sortBy, $this->sortDir);
    } else {
        $query->orderBy('created_at', 'desc');
    }

    $messages = $query->get();

    $data = $messages->map(function ($message) {
        return [
            $message->id,
            $message->name,
            $message->email ?? '-',
            $message->phone ?? '-',
            $message->subject,
            ucfirst($message->status),
            $message->created_at->format('d/m/Y H:i'),
        ];
    });

    $filename = 'contact_messages_' . now()->format('Y-m-d_His') . '.xlsx';

    $export = new class(Collection::make($data)) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
        public function __construct(public Collection $data) {}

        public function collection() {
            return $this->data;
        }

        public function headings(): array {
            return ['ID', 'Nama', 'Email', 'Phone', 'Subject', 'Status', 'Created At'];
        }
    };

    return Excel::download($export, $filename);
});

$exportPdf = action(function () {
    $query = ContactMessage::query();

    if (!empty($this->search)) {
        $query->where(function ($q) {
            $q->where('name', 'like', "%{$this->search}%")
              ->orWhere('email', 'like', "%{$this->search}%")
              ->orWhere('phone', 'like', "%{$this->search}%")
              ->orWhere('subject', 'like', "%{$this->search}%")
              ->orWhere('message', 'like', "%{$this->search}%");
        });
    }

    if (!empty($this->statusFilter)) {
        $query->where('status', $this->statusFilter);
    }

    $allowedSorts = ['status', 'created_at'];
    if (in_array($this->sortBy, $allowedSorts)) {
        $query->orderBy($this->sortBy, $this->sortDir);
    } else {
        $query->orderBy('created_at', 'desc');
    }

    $messages = $query->get();

    $html = view('exports.contact-messages-pdf', ['messages' => $messages])->render();

    $pdf = Pdf::loadHTML($html);
    $filename = 'contact_messages_' . now()->format('Y-m-d_His') . '.pdf';

    return Response::streamDownload(function () use ($pdf) {
        echo $pdf->output();
    }, $filename);
});

$messages = computed(function () {
    $query = ContactMessage::query();

    // Search
    if (!empty($this->search)) {
        $query->where(function ($q) {
            $q->where('name', 'like', "%{$this->search}%")
              ->orWhere('email', 'like', "%{$this->search}%")
              ->orWhere('phone', 'like', "%{$this->search}%")
              ->orWhere('subject', 'like', "%{$this->search}%")
              ->orWhere('message', 'like', "%{$this->search}%");
        });
    }

    // Status Filter
    if (!empty($this->statusFilter)) {
        $query->where('status', $this->statusFilter);
    }

    // Sorting
    $allowedSorts = ['status', 'created_at'];
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
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Pesan Kontak') }}</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Kelola pesan yang masuk dari form kontak') }}</p>
            </div>
            <div class="flex items-center gap-2">
                @can('mengekspor pesan kontak')
                <flux:button wire:click="exportExcel" variant="ghost" icon="table-cells"
                    class="!bg-green-600 hover:!bg-green-700 dark:!bg-green-500 dark:hover:!bg-green-600 !text-white">
                    {{ __('Excel') }}
                </flux:button>
                <flux:button wire:click="exportPdf" variant="ghost" icon="document-text"
                    class="!bg-red-900 hover:!bg-red-950 dark:!bg-red-950 dark:hover:!bg-red-900 !text-white">
                    {{ __('PDF') }}
                </flux:button>
                @endcan
            </div>
        </div>

        <flux:card>
            <div class="mb-4 flex flex-col gap-4 md:flex-row">
                <div class="flex-1">
                    <flux:input wire:model.live.debounce.500ms="search" name="search" type="search" icon="magnifying-glass"
                        placeholder="{{ __('Cari nama, email, phone, subject, atau pesan...') }}" class="w-full" />
                </div>
                <div class="w-full md:w-48">
                    <flux:select wire:model.live="statusFilter" name="statusFilter" placeholder="{{ __('Semua Status') }}">
                        <flux:select.option value="">{{ __('Semua Status') }}</flux:select.option>
                        <flux:select.option value="new">{{ __('New') }}</flux:select.option>
                        <flux:select.option value="read">{{ __('Read') }}</flux:select.option>
                        <flux:select.option value="archived">{{ __('Archived') }}</flux:select.option>
                    </flux:select>
                </div>
            </div>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Nama') }}</flux:table.column>
                    <flux:table.column>{{ __('Email') }}</flux:table.column>
                    <flux:table.column>{{ __('Phone') }}</flux:table.column>
                    <flux:table.column>{{ __('Subject') }}</flux:table.column>
                    <flux:table.column>
                        <button wire:click="sort('status')"
                            class="flex items-center gap-1 hover:text-orange-600 dark:hover:text-orange-400">
                            {{ __('Status') }}
                            <flux:icon :name="$this->sortIconStatus" variant="mini" />
                        </button>
                    </flux:table.column>
                    <flux:table.column>
                        <button wire:click="sort('created_at')"
                            class="flex items-center gap-1 hover:text-orange-600 dark:hover:text-orange-400">
                            {{ __('Created At') }}
                            <flux:icon :name="$this->sortIconCreatedAt" variant="mini" />
                        </button>
                    </flux:table.column>
                    @if (auth()->user()->can('melihat pesan kontak') || auth()->user()->can('mengubah pesan kontak'))
                    <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                    @endif
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($this->messages as $message)
                    <flux:table.row>
                        <flux:table.cell>
                            <div class="font-medium text-gray-900 dark:text-white">{{ $message->name }}</div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="text-gray-600 dark:text-gray-400">{{ $message->email ?? '-' }}</div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="text-gray-600 dark:text-gray-400">{{ $message->phone ?? '-' }}</div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="text-gray-600 dark:text-gray-400">{{ Str::limit($message->subject, 50) }}</div>
                        </flux:table.cell>
                        <flux:table.cell>
                            @can('mengubah pesan kontak')
                            <flux:select 
                                wire:change="updateStatus({{ $message->id }}, $event.target.value)"
                                value="{{ $message->status }}"
                                class="!min-w-[120px]">
                                <flux:select.option value="new">{{ __('New') }}</flux:select.option>
                                <flux:select.option value="read">{{ __('Read') }}</flux:select.option>
                                <flux:select.option value="archived">{{ __('Archived') }}</flux:select.option>
                            </flux:select>
                            @else
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $message->status_badge_color }}">
                                {{ ucfirst($message->status) }}
                            </span>
                            @endcan
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="text-gray-600 dark:text-gray-400">{{ $message->created_at->format('d/m/Y H:i') }}</div>
                        </flux:table.cell>
                        @if (auth()->user()->can('melihat pesan kontak') || auth()->user()->can('mengubah pesan kontak'))
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                @can('melihat pesan kontak')
                                <flux:button wire:click="showDetail({{ $message->id }})" variant="ghost" size="sm"
                                    icon="eye"
                                    class="!p-2 !bg-purple-600 hover:!bg-purple-700 dark:!bg-purple-500 dark:hover:!bg-purple-600 !text-white !rounded-md"
                                    title="{{ __('Detail') }}" />
                                @endcan
                                @can('mengubah pesan kontak')
                                <flux:button :href="route('godmode.contact-messages.edit', $message)" variant="ghost" size="sm"
                                    icon="pencil" wire:navigate
                                    class="!p-2 !bg-blue-600 hover:!bg-blue-700 dark:!bg-blue-500 dark:hover:!bg-blue-600 !text-white !rounded-md"
                                    title="{{ __('Edit') }}" />
                                @endcan
                            </div>
                        </flux:table.cell>
                        @endif
                    </flux:table.row>
                    @empty
                    <flux:table.row>
                        <flux:table.cell
                            colspan="{{ auth()->user()->can('melihat pesan kontak') || auth()->user()->can('mengubah pesan kontak') ? '7' : '6' }}"
                            class="text-center text-gray-500 dark:text-gray-400">
                            {{ __('Tidak ada pesan kontak') }}
                        </flux:table.cell>
                    </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Menampilkan') }} {{ $this->messages->firstItem() ?? 0 }} {{ __('sampai') }} {{
                    $this->messages->lastItem() ?? 0 }}
                    {{ __('dari') }} {{ $this->messages->total() }} {{ __('hasil') }}
                </div>
                @if ($this->messages->hasPages())
                <div>
                    {{ $this->messages->links() }}
                </div>
                @endif
            </div>
        </flux:card>
    </div>

    <!-- Detail Modal -->
    @if ($showDetailModal && $selectedMessage)
    <flux:modal name="detail-message" :show="$showDetailModal" wire:model="showDetailModal" class="max-w-2xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Detail Pesan Kontak') }}</flux:heading>
                <flux:subheading>{{ __('Informasi lengkap pesan kontak') }}</flux:subheading>
            </div>

            <div class="space-y-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">{{ __('Data Pengirim') }}</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <flux:label>{{ __('Nama') }}</flux:label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedMessage->name }}</div>
                        </div>
                        <div>
                            <flux:label>{{ __('Email') }}</flux:label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedMessage->email ?? '-' }}</div>
                        </div>
                        <div>
                            <flux:label>{{ __('Phone') }}</flux:label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedMessage->phone ?? '-' }}</div>
                        </div>
                        <div>
                            <flux:label>{{ __('Status') }}</flux:label>
                            <div class="mt-1">
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $selectedMessage->status_badge_color }}">
                                    {{ ucfirst($selectedMessage->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">{{ __('Pesan') }}</h3>
                    <div class="space-y-4">
                        <div>
                            <flux:label>{{ __('Subject') }}</flux:label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-white font-semibold">{{ $selectedMessage->subject }}</div>
                        </div>
                        <div>
                            <flux:label>{{ __('Message') }}</flux:label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $selectedMessage->message }}</div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <flux:label>{{ __('Created At') }}</flux:label>
                                <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedMessage->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                            <div>
                                <flux:label>{{ __('Updated At') }}</flux:label>
                                <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedMessage->updated_at->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:button variant="ghost" wire:click="closeDetail">
                    {{ __('Tutup') }}
                </flux:button>
                @can('mengubah pesan kontak')
                <flux:button :href="route('godmode.contact-messages.edit', $selectedMessage)" variant="primary" wire:navigate>
                    {{ __('Edit') }}
                </flux:button>
                @endcan
            </div>
        </div>
    </flux:modal>
    @endif
</div>

