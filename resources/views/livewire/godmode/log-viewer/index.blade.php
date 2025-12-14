<?php

use App\Services\LogViewerService;
use Livewire\WithPagination;
use function Livewire\Volt\{layout, title, state, action, computed, on, uses};

uses(WithPagination::class);

layout('components.layouts.admin');
title(fn () => __('Log Viewer'));

state([
    'selectedFile' => fn () => request()->get('file', ''),
    'search' => fn () => request()->get('search', ''),
    'filterLevel' => fn () => request()->get('filter_level', ''),
    'autoRefresh' => false,
    'refreshInterval' => 5, // seconds
]);

$logViewerService = new LogViewerService();

on(['updatingSearch' => function () {
    $this->resetPage();
}, 'updatingFilterLevel' => function () {
    $this->resetPage();
}, 'updatingSelectedFile' => function () {
    $this->resetPage();
}]);

$logFiles = computed(function () use ($logViewerService) {
    return $logViewerService->getLogFiles();
});

$logEntries = computed(function () use ($logViewerService) {
    if (empty($this->selectedFile)) {
        return [
            'entries' => [],
            'total' => 0,
            'current_page' => 1,
            'per_page' => 50,
            'last_page' => 1,
        ];
    }

    return $logViewerService->readLogFile(
        $this->selectedFile,
        $this->getPage(),
        50,
        $this->filterLevel ?: null,
        $this->search ?: null
    );
});

$logLevels = computed(function () use ($logViewerService) {
    return $logViewerService->getLogLevels();
});

$clearLog = action(function () use ($logViewerService) {
    if (empty($this->selectedFile)) {
        return;
    }

    if ($logViewerService->clearLogFile($this->selectedFile)) {
        $this->dispatch('toast', message: __('Log file berhasil dibersihkan.'), variant: 'success');
        $this->resetPage();
    } else {
        $this->dispatch('toast', message: __('Gagal membersihkan log file.'), variant: 'error');
    }
});

$downloadLog = action(function () use ($logViewerService) {
    if (empty($this->selectedFile)) {
        return;
    }

    $filePath = $logViewerService->downloadLogFile($this->selectedFile);

    if ($filePath) {
        return response()->download($filePath, $this->selectedFile);
    }

    $this->dispatch('toast', message: __('Gagal mengunduh log file.'), variant: 'error');
});

$getLevelColor = action(function ($level) {
    return match(strtolower($level)) {
        'emergency', 'alert', 'critical', 'error' => 'bg-red-50 text-red-700 ring-red-700/10 dark:bg-red-900/50 dark:text-red-200',
        'warning' => 'bg-orange-50 text-orange-700 ring-orange-700/10 dark:bg-orange-900/50 dark:text-orange-200',
        'notice', 'info' => 'bg-blue-50 text-blue-700 ring-blue-700/10 dark:bg-blue-900/50 dark:text-blue-200',
        'debug' => 'bg-gray-50 text-gray-700 ring-gray-700/10 dark:bg-gray-900/50 dark:text-gray-200',
        default => 'bg-gray-50 text-gray-700 ring-gray-700/10 dark:bg-gray-900/50 dark:text-gray-200',
    };
});

?>

<div>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Log Viewer') }}</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Lihat dan analisis file log Laravel') }}</p>
            </div>
            <div class="flex items-center gap-2">
                @if($selectedFile)
                <flux:button wire:click="downloadLog" variant="ghost" icon="arrow-down-tray"
                    class="!bg-blue-600 hover:!bg-blue-700 dark:!bg-blue-500 dark:hover:!bg-blue-600 !text-white">
                    {{ __('Download') }}
                </flux:button>
                <flux:button wire:click="clearLog" variant="ghost" icon="trash"
                    class="!bg-red-600 hover:!bg-red-700 dark:!bg-red-500 dark:hover:!bg-red-600 !text-white"
                    onclick="return confirm('{{ __('Apakah Anda yakin ingin membersihkan log file ini?') }}')">
                    {{ __('Clear') }}
                </flux:button>
                @endif
            </div>
        </div>

        <flux:card>
            <div class="mb-4 space-y-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <flux:select wire:model.live="selectedFile" name="selectedFile"
                        placeholder="{{ __('Pilih Log File') }}">
                        <option value="">{{ __('Pilih Log File') }}</option>
                        @foreach($this->logFiles as $file)
                        <option value="{{ $file['name'] }}">
                            {{ $file['name'] }} ({{ number_format($file['size'] / 1024, 2) }} KB)
                        </option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model.live="filterLevel" name="filterLevel" placeholder="{{ __('Semua Level') }}">
                        <option value="">{{ __('Semua Level') }}</option>
                        @foreach($this->logLevels as $level)
                        <option value="{{ $level }}">{{ strtoupper($level) }}</option>
                        @endforeach
                    </flux:select>

                    <flux:input wire:model.live.debounce.500ms="search" name="search" type="search"
                        icon="magnifying-glass" placeholder="{{ __('Cari dalam log...') }}" class="w-full" />
                </div>

                @if($selectedFile)
                <div class="flex items-center gap-2">
                    <flux:checkbox wire:model.live="autoRefresh" name="autoRefresh" />
                    <label class="text-sm text-gray-700 dark:text-gray-300">{{ __('Auto Refresh') }}</label>
                    <flux:input wire:model="refreshInterval" name="refreshInterval" type="number" min="1" max="60"
                        class="w-20" />
                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('detik') }}</span>
                </div>
                @endif
            </div>

            @if($selectedFile)
            <div class="space-y-2">
                @forelse($this->logEntries['entries'] as $entry)
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="mb-2 flex items-center gap-2">
                                <span
                                    class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium {{ $this->getLevelColor($entry['level'] ?? '') }}">
                                    {{ strtoupper($entry['level'] ?? 'UNKNOWN') }}
                                </span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $entry['timestamp'] ?? '' }}
                                </span>
                                @if(isset($entry['environment']))
                                <span class="text-xs text-gray-500 dark:text-gray-500">
                                    [{{ $entry['environment'] }}]
                                </span>
                                @endif
                            </div>
                            <div class="mb-2 text-sm text-gray-900 dark:text-white">
                                {{ $entry['message'] ?? '' }}
                            </div>
                            @if(!empty($entry['context']))
                            <div
                                class="mt-2 rounded bg-gray-50 p-2 text-xs text-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <pre class="whitespace-pre-wrap">{{ trim($entry['context']) }}</pre>
                            </div>
                            @endif
                            @if(!empty($entry['stack_trace']))
                            <details class="mt-2">
                                <summary
                                    class="cursor-pointer text-xs text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                                    {{ __('Stack Trace') }}
                                </summary>
                                <div
                                    class="mt-2 rounded bg-red-50 p-2 text-xs text-red-700 dark:bg-red-900/20 dark:text-red-300">
                                    <pre class="whitespace-pre-wrap font-mono">{{ trim($entry['stack_trace']) }}</pre>
                                </div>
                            </details>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div
                    class="rounded-lg border border-gray-200 bg-white p-8 text-center dark:border-gray-700 dark:bg-gray-800">
                    <p class="text-gray-500 dark:text-gray-400">{{ __('Tidak ada log entries ditemukan.') }}</p>
                </div>
                @endforelse
            </div>

            <div class="mt-4">
                @if($this->logEntries['last_page'] > 1)
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Menampilkan') }} {{ ($this->logEntries['current_page'] - 1) *
                        $this->logEntries['per_page'] + 1 }}
                        {{ __('sampai') }} {{ min($this->logEntries['current_page'] * $this->logEntries['per_page'],
                        $this->logEntries['total']) }}
                        {{ __('dari') }} {{ $this->logEntries['total'] }} {{ __('entries') }}
                    </div>
                    <div class="flex gap-2">
                        @if($this->getPage() > 1)
                        <flux:button wire:click="previousPage" variant="ghost" size="sm">
                            {{ __('Sebelumnya') }}
                        </flux:button>
                        @endif
                        @if($this->getPage() < $this->logEntries['last_page'])
                            <flux:button wire:click="nextPage" variant="ghost" size="sm">
                                {{ __('Selanjutnya') }}
                            </flux:button>
                            @endif
                    </div>
                </div>
                @endif
            </div>
            @else
            <div
                class="rounded-lg border border-gray-200 bg-white p-8 text-center dark:border-gray-700 dark:bg-gray-800">
                <p class="text-gray-500 dark:text-gray-400">{{ __('Pilih log file untuk melihat isinya.') }}</p>
            </div>
            @endif
        </flux:card>
    </div>

    @if($autoRefresh && $selectedFile)
    <script>
        setInterval(function() {
            @this.call('$refresh');
        }, {{ $refreshInterval * 1000 }});
    </script>
    @endif
</div>