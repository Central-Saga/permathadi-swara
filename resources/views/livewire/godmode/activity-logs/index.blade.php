<?php

use Spatie\Activitylog\Models\Activity;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Collection;
use function Livewire\Volt\{layout, title, state, action, computed, on, uses};

uses(WithPagination::class);

layout('components.layouts.admin');
title(fn () => __('Activity Log'));

state([
    'search' => fn () => request()->get('search', ''),
    'filterUser' => fn () => request()->get('filter_user', ''),
    'filterModel' => fn () => request()->get('filter_model', ''),
    'filterEvent' => fn () => request()->get('filter_event', ''),
    'filterDateFrom' => fn () => request()->get('filter_date_from', ''),
    'filterDateTo' => fn () => request()->get('filter_date_to', ''),
    'sortBy' => fn () => request()->get('sort_by', 'created_at'),
    'sortDir' => fn () => request()->get('sort_dir', 'desc'),
    'selectedActivity' => null,
    'showDetailModal' => false,
]);

on(['updatingSearch' => function () {
    $this->resetPage();
}, 'updatingFilterUser' => function () {
    $this->resetPage();
}, 'updatingFilterModel' => function () {
    $this->resetPage();
}, 'updatingFilterEvent' => function () {
    $this->resetPage();
}, 'updatingFilterDateFrom' => function () {
    $this->resetPage();
}, 'updatingFilterDateTo' => function () {
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

$sortIconCreatedAt = computed(function () {
    if ($this->sortBy !== 'created_at') {
        return 'chevrons-up-down';
    }
    return $this->sortDir === 'asc' ? 'chevron-up' : 'chevron-down';
});

$showDetail = action(function ($activityId) {
    $this->selectedActivity = Activity::with('causer')->findOrFail($activityId);
    $this->showDetailModal = true;
});

$closeDetail = action(function () {
    $this->showDetailModal = false;
    $this->selectedActivity = null;
});

$exportExcel = action(function () {
    $query = Activity::with('causer');

    // Search
    if (!empty($this->search)) {
        $query->where(function ($q) {
            $q->where('description', 'like', "%{$this->search}%")
              ->orWhere('event', 'like', "%{$this->search}%")
              ->orWhere('subject_type', 'like', "%{$this->search}%")
              ->orWhereHas('causer', function ($q) {
                  $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
              });
        });
    }

    // Filter User
    if (!empty($this->filterUser)) {
        $query->where('causer_id', $this->filterUser);
    }

    // Filter Model
    if (!empty($this->filterModel)) {
        $query->where('subject_type', 'like', "%{$this->filterModel}%");
    }

    // Filter Event
    if (!empty($this->filterEvent)) {
        $query->where('event', $this->filterEvent);
    }

    // Filter Date From
    if (!empty($this->filterDateFrom)) {
        $query->whereDate('created_at', '>=', $this->filterDateFrom);
    }

    // Filter Date To
    if (!empty($this->filterDateTo)) {
        $query->whereDate('created_at', '<=', $this->filterDateTo);
    }

    // Sorting
    $allowedSorts = ['created_at', 'event', 'subject_type'];
    if (in_array($this->sortBy, $allowedSorts)) {
        $query->orderBy($this->sortBy, $this->sortDir);
    } else {
        $query->orderBy('created_at', 'desc');
    }

    $activities = $query->get();

    $data = $activities->map(function ($activity) {
        return [
            $activity->id,
            $activity->causer->name ?? 'System',
            $activity->subject_type ? class_basename($activity->subject_type) : '-',
            $activity->event,
            $activity->description,
            $activity->created_at->format('d/m/Y H:i:s'),
        ];
    });

    $filename = 'activity_logs_' . now()->format('Y-m-d_His') . '.xlsx';

    $export = new class(Collection::make($data)) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
        public function __construct(public Collection $data) {}

        public function collection() {
            return $this->data;
        }

        public function headings(): array {
            return ['ID', 'User', 'Model', 'Event', 'Description', 'Created At'];
        }
    };

    return Excel::download($export, $filename);
});

$exportPdf = action(function () {
    $query = Activity::with('causer');

    // Search
    if (!empty($this->search)) {
        $query->where(function ($q) {
            $q->where('description', 'like', "%{$this->search}%")
              ->orWhere('event', 'like', "%{$this->search}%")
              ->orWhere('subject_type', 'like', "%{$this->search}%")
              ->orWhereHas('causer', function ($q) {
                  $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
              });
        });
    }

    // Filter User
    if (!empty($this->filterUser)) {
        $query->where('causer_id', $this->filterUser);
    }

    // Filter Model
    if (!empty($this->filterModel)) {
        $query->where('subject_type', 'like', "%{$this->filterModel}%");
    }

    // Filter Event
    if (!empty($this->filterEvent)) {
        $query->where('event', $this->filterEvent);
    }

    // Filter Date From
    if (!empty($this->filterDateFrom)) {
        $query->whereDate('created_at', '>=', $this->filterDateFrom);
    }

    // Filter Date To
    if (!empty($this->filterDateTo)) {
        $query->whereDate('created_at', '<=', $this->filterDateTo);
    }

    // Sorting
    $allowedSorts = ['created_at', 'event', 'subject_type'];
    if (in_array($this->sortBy, $allowedSorts)) {
        $query->orderBy($this->sortBy, $this->sortDir);
    } else {
        $query->orderBy('created_at', 'desc');
    }

    $activities = $query->get();

    $html = view('exports.activity-logs-pdf', ['activities' => $activities])->render();

    $pdf = Pdf::loadHTML($html);
    $filename = 'activity_logs_' . now()->format('Y-m-d_His') . '.pdf';

    return Response::streamDownload(function () use ($pdf) {
        echo $pdf->output();
    }, $filename);
});

$activities = computed(function () {
    $query = Activity::with('causer');

    // Search
    if (!empty($this->search)) {
        $query->where(function ($q) {
            $q->where('description', 'like', "%{$this->search}%")
              ->orWhere('event', 'like', "%{$this->search}%")
              ->orWhere('subject_type', 'like', "%{$this->search}%")
              ->orWhereHas('causer', function ($q) {
                  $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
              });
        });
    }

    // Filter User
    if (!empty($this->filterUser)) {
        $query->where('causer_id', $this->filterUser);
    }

    // Filter Model
    if (!empty($this->filterModel)) {
        $query->where('subject_type', 'like', "%{$this->filterModel}%");
    }

    // Filter Event
    if (!empty($this->filterEvent)) {
        $query->where('event', $this->filterEvent);
    }

    // Filter Date From
    if (!empty($this->filterDateFrom)) {
        $query->whereDate('created_at', '>=', $this->filterDateFrom);
    }

    // Filter Date To
    if (!empty($this->filterDateTo)) {
        $query->whereDate('created_at', '<=', $this->filterDateTo);
    }

    // Sorting
    $allowedSorts = ['created_at', 'event', 'subject_type'];
    if (in_array($this->sortBy, $allowedSorts)) {
        $query->orderBy($this->sortBy, $this->sortDir);
    } else {
        $query->orderBy('created_at', 'desc');
    }

    return $query->paginate(15);
});

$users = computed(function () {
    return \App\Models\User::orderBy('name')->get();
});

$modelTypes = computed(function () {
    return Activity::select('subject_type')
        ->whereNotNull('subject_type')
        ->distinct()
        ->get()
        ->map(function ($activity) {
            return class_basename($activity->subject_type);
        })
        ->unique()
        ->sort()
        ->values();
});

$eventTypes = computed(function () {
    return Activity::select('event')
        ->whereNotNull('event')
        ->distinct()
        ->pluck('event')
        ->unique()
        ->sort()
        ->values();
});

?>

<div>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Activity Log') }}</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Riwayat aktivitas pengguna dalam sistem') }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                @can('mengekspor activity log')
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
            <div class="mb-4 space-y-4">
                <flux:input wire:model.live.debounce.500ms="search" name="search" type="search" icon="magnifying-glass"
                    placeholder="{{ __('Cari aktivitas...') }}" class="w-full" />

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
                    <flux:select wire:model.live="filterUser" name="filterUser" placeholder="{{ __('Semua User') }}">
                        <option value="">{{ __('Semua User') }}</option>
                        @foreach($this->users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model.live="filterModel" name="filterModel" placeholder="{{ __('Semua Model') }}">
                        <option value="">{{ __('Semua Model') }}</option>
                        @foreach($this->modelTypes as $modelType)
                        <option value="{{ $modelType }}">{{ $modelType }}</option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model.live="filterEvent" name="filterEvent" placeholder="{{ __('Semua Event') }}">
                        <option value="">{{ __('Semua Event') }}</option>
                        @foreach($this->eventTypes as $eventType)
                        <option value="{{ $eventType }}">{{ ucfirst($eventType) }}</option>
                        @endforeach
                    </flux:select>

                    <flux:input wire:model.live.debounce.500ms="filterDateFrom" name="filterDateFrom" type="date"
                        placeholder="{{ __('Dari Tanggal') }}" />

                    <flux:input wire:model.live.debounce.500ms="filterDateTo" name="filterDateTo" type="date"
                        placeholder="{{ __('Sampai Tanggal') }}" />
                </div>
            </div>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="w-16">ID</flux:table.column>
                    <flux:table.column>User</flux:table.column>
                    <flux:table.column>Model</flux:table.column>
                    <flux:table.column>Event</flux:table.column>
                    <flux:table.column>Description</flux:table.column>
                    <flux:table.column>
                        <button wire:click="sort('created_at')"
                            class="flex items-center gap-1 hover:text-orange-600 dark:hover:text-orange-400">
                            {{ __('Tanggal') }}
                            <flux:icon :name="$this->sortIconCreatedAt" variant="mini" />
                        </button>
                    </flux:table.column>
                    <flux:table.column class="w-24">Aksi</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse($this->activities as $activity)
                    <flux:table.row>
                        <flux:table.cell>{{ $activity->id }}</flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-200">
                                        {{ $activity->causer ? Str::substr($activity->causer->name, 0, 1) : 'S' }}
                                    </span>
                                </span>
                                <span class="font-medium">{{ $activity->causer->name ?? 'System' }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <span
                                class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700 ring-1 ring-inset ring-gray-600/20 dark:bg-gray-800 dark:text-gray-300">
                                {{ $activity->subject_type ? class_basename($activity->subject_type) : '-' }}
                            </span>
                        </flux:table.cell>
                        <flux:table.cell>
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium
                                @if($activity->event === 'created') bg-green-50 text-green-700 ring-green-700/10 dark:bg-green-900/50 dark:text-green-200
                                @elseif($activity->event === 'updated') bg-blue-50 text-blue-700 ring-blue-700/10 dark:bg-blue-900/50 dark:text-blue-200
                                @elseif($activity->event === 'deleted') bg-red-50 text-red-700 ring-red-700/10 dark:bg-red-900/50 dark:text-red-200
                                @else bg-gray-50 text-gray-700 ring-gray-700/10 dark:bg-gray-900/50 dark:text-gray-200
                                @endif">
                                {{ ucfirst($activity->event ?? '-') }}
                            </span>
                        </flux:table.cell>
                        <flux:table.cell>
                            <span class="truncate max-w-xs">{{ $activity->description ?? '-' }}</span>
                        </flux:table.cell>
                        <flux:table.cell>
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $activity->created_at->format('d/m/Y H:i') }}
                            </span>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:button wire:click="showDetail({{ $activity->id }})" variant="ghost" size="sm"
                                icon="eye">
                                {{ __('Detail') }}
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                    @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="text-center text-gray-500 dark:text-gray-400">
                            {{ __('Tidak ada data activity log.') }}
                        </flux:table.cell>
                    </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            <div class="mt-4">
                {{ $this->activities->links() }}
            </div>
        </flux:card>
    </div>

    <!-- Detail Modal -->
    <flux:modal name="detail-activity-log" :show="$showDetailModal" wire:model="showDetailModal" class="max-w-4xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Detail Activity Log') }}</flux:heading>
                <flux:subheading>{{ __('Informasi lengkap activity log') }}</flux:subheading>
            </div>

            @if($selectedActivity)
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <flux:label>{{ __('ID') }}</flux:label>
                        <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedActivity->id }}</div>
                    </div>

                    <div>
                        <flux:label>{{ __('User') }}</flux:label>
                        <div class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $selectedActivity->causer->name ?? 'System' }}
                            @if($selectedActivity->causer)
                            <span class="text-gray-500">({{ $selectedActivity->causer->email }})</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <flux:label>{{ __('Model') }}</flux:label>
                        <div class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $selectedActivity->subject_type ? class_basename($selectedActivity->subject_type) : '-'
                            }}
                        </div>
                    </div>

                    <div>
                        <flux:label>{{ __('Event') }}</flux:label>
                        <div class="mt-1">
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium
                                @if($selectedActivity->event === 'created') bg-green-50 text-green-700 ring-green-700/10 dark:bg-green-900/50 dark:text-green-200
                                @elseif($selectedActivity->event === 'updated') bg-blue-50 text-blue-700 ring-blue-700/10 dark:bg-blue-900/50 dark:text-blue-200
                                @elseif($selectedActivity->event === 'deleted') bg-red-50 text-red-700 ring-red-700/10 dark:bg-red-900/50 dark:text-red-200
                                @else bg-gray-50 text-gray-700 ring-gray-700/10 dark:bg-gray-900/50 dark:text-gray-200
                                @endif">
                                {{ ucfirst($selectedActivity->event ?? '-') }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <flux:label>{{ __('Subject ID') }}</flux:label>
                        <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedActivity->subject_id ?? '-'
                            }}</div>
                    </div>

                    <div>
                        <flux:label>{{ __('Created At') }}</flux:label>
                        <div class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $selectedActivity->created_at->format('d/m/Y H:i:s') }}
                        </div>
                    </div>
                </div>

                <div>
                    <flux:label>{{ __('Description') }}</flux:label>
                    <div class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedActivity->description ?? '-' }}
                    </div>
                </div>

                <div>
                    <flux:label>{{ __('Properties') }}</flux:label>
                    <div class="mt-1 rounded-lg bg-gray-50 p-4 dark:bg-gray-800 max-h-96 overflow-auto">
                        <pre
                            class="text-xs text-gray-900 dark:text-white whitespace-pre-wrap">{{ json_encode($selectedActivity->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
            </div>
            @endif

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:button variant="ghost" wire:click="closeDetail">
                    {{ __('Tutup') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>