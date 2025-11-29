<?php

use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{layout, computed};

layout('components.layouts.landing');

$subscriptions = computed(function () {
    $user = Auth::user();
    $anggota = $user->anggota;

    if (!$anggota) {
        return collect();
    }

    return Subscription::where('anggota_id', $anggota->id)
        ->with(['layanan', 'payments'])
        ->orderBy('created_at', 'desc')
        ->get();
});

?>

<div class="bg-white dark:bg-gray-900">
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
        <div class="mx-auto max-w-2xl">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl mb-8">
                History Langganan
            </h1>

            @if ($this->subscriptions->isEmpty())
            <div
                class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-4 text-sm font-semibold text-gray-900 dark:text-white">Belum ada langganan</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Anda belum memiliki langganan aktif.</p>
                <div class="mt-6">
                    <a href="{{ route('landing.program') }}"
                        class="inline-flex items-center rounded-md bg-orange-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-500">
                        Lihat Program
                    </a>
                </div>
            </div>
            @else
            <div class="space-y-4">
                @foreach ($this->subscriptions as $subscription)
                <div
                    class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                    <div class="p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ $subscription->layanan->name }}
                                </h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $subscription->layanan->description }}
                                </p>
                                <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Mulai:</span>
                                        <span class="ml-2 font-medium text-gray-900 dark:text-white">
                                            {{ $subscription->start_date->format('d/m/Y') }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Berakhir:</span>
                                        <span class="ml-2 font-medium text-gray-900 dark:text-white">
                                            {{ $subscription->end_date ? $subscription->end_date->format('d/m/Y') : '-'
                                            }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Harga:</span>
                                        <span class="ml-2 font-medium text-gray-900 dark:text-white">
                                            Rp {{ number_format($subscription->layanan->price, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Status:</span>
                                        <span
                                            class="ml-2 inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $subscription->status_badge_color }}">
                                            {{ ucfirst($subscription->status) }}
                                        </span>
                                    </div>
                                </div>
                                @if ($subscription->payments->count() > 0)
                                <div class="mt-4">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $subscription->payments->count() }} pembayaran
                                    </p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>