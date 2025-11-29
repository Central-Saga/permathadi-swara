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

<div class="relative isolate bg-white dark:bg-gray-900 min-h-full">
    <!-- Background Blur Effects -->
    <div aria-hidden="true" class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80"
        data-gsap="history-blur-1">
        <div style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"
            class="relative left-[calc(50%-11rem)] aspect-1155/678 w-144.5 -translate-x-1/2 rotate-30 bg-gradient-to-tr from-orange-400 to-red-500 opacity-30 sm:left-[calc(50%-30rem)] sm:w-288.75 dark:opacity-20">
        </div>
    </div>

    <div class="relative mx-auto max-w-7xl px-4 pt-16 pb-12 sm:px-6 sm:pt-24 sm:pb-16 lg:px-8">
        <div class="mx-auto max-w-4xl">
            <div class="mb-8" data-gsap="history-title">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                    History Langganan
                </h1>
                <p class="mt-2 text-base text-gray-500 dark:text-gray-400">
                    Lihat riwayat langganan dan pembayaran Anda
                </p>
            </div>

            @if ($this->subscriptions->isEmpty())
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-12 text-center shadow-sm"
                data-gsap="history-empty">
                <svg class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">Belum ada langganan</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Anda belum memiliki langganan aktif.</p>
                <div class="mt-6">
                    <a href="{{ route('landing.program') }}"
                        class="inline-flex items-center rounded-md bg-orange-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-500 focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-colors">
                        Lihat Program
                    </a>
                </div>
            </div>
            @else
            <div class="space-y-6" data-gsap="history-list">
                @foreach ($this->subscriptions as $subscription)
                <div
                    class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm transition-shadow hover:shadow-md">
                    <div class="p-6">
                        <div class="flex items-start gap-4">
                            @if ($subscription->layanan->getFirstMedia('layanan_cover'))
                            <div class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-lg">
                                <x-optimized-image :model="$subscription->layanan" collection="layanan_cover" 
                                    :alt="$subscription->layanan->name"
                                    sizes="96px" loading="lazy" class="h-full w-full object-cover"
                                    :responsive="true" :placeholder="true" />
                            </div>
                            @endif
                            <div class="flex-1">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                            {{ $subscription->layanan->name }}
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
                                            {{ $subscription->layanan->description }}
                                        </p>
                                    </div>
                                    <span
                                        class="ml-4 inline-flex items-center rounded-md px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $subscription->status_badge_color }}">
                                        {{ ucfirst($subscription->status) }}
                                    </span>
                                </div>

                                <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Tanggal Mulai:</span>
                                        <span class="ml-2 font-medium text-gray-900 dark:text-white">
                                            {{ $subscription->start_date->format('d/m/Y') }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Tanggal Berakhir:</span>
                                        <span class="ml-2 font-medium text-gray-900 dark:text-white">
                                            {{ $subscription->end_date ? $subscription->end_date->format('d/m/Y') : '-' }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Harga:</span>
                                        <span class="ml-2 font-medium text-gray-900 dark:text-white">
                                            Rp {{ number_format($subscription->layanan->price, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Durasi:</span>
                                        <span class="ml-2 font-medium text-gray-900 dark:text-white">
                                            @if ($subscription->layanan->duration >= 365)
                                            {{ round($subscription->layanan->duration / 365) }} Tahun
                                            @elseif ($subscription->layanan->duration >= 30)
                                            {{ round($subscription->layanan->duration / 30) }} Bulan
                                            @else
                                            {{ $subscription->layanan->duration }} Hari
                                            @endif
                                        </span>
                                    </div>
                                </div>

                                @if ($subscription->notes)
                                <div class="mt-4 rounded-md bg-gray-50 dark:bg-gray-900/50 p-3">
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Catatan:</p>
                                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $subscription->notes }}</p>
                                </div>
                                @endif

                                @if ($subscription->payments->count() > 0)
                                <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                                        Riwayat Pembayaran ({{ $subscription->payments->count() }})
                                    </h4>
                                    <div class="space-y-3">
                                        @foreach ($subscription->payments as $payment)
                                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 p-4">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-3">
                                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                            {{ $payment->formatted_amount }}
                                                        </span>
                                                        <span
                                                            class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $payment->status_badge_color }}">
                                                            {{ ucfirst($payment->status) }}
                                                        </span>
                                                    </div>
                                                    <div class="mt-2 flex flex-wrap items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                                                        <span>
                                                            Metode: <span class="font-medium capitalize">{{ $payment->method }}</span>
                                                        </span>
                                                        @if ($payment->paid_at)
                                                        <span>
                                                            Dibayar: <span class="font-medium">{{ $payment->paid_at->format('d/m/Y H:i') }}</span>
                                                        </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                @if ($payment->getFirstMediaUrl('payment_proof'))
                                                <div class="ml-4">
                                                    <a href="{{ $payment->getFirstMediaUrl('payment_proof') }}" target="_blank"
                                                        class="inline-flex items-center gap-1 text-xs text-orange-600 dark:text-orange-400 hover:text-orange-700 dark:hover:text-orange-300">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                        Lihat Bukti
                                                    </a>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @else
                                <div class="mt-4 rounded-md bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 p-3">
                                    <p class="text-xs text-orange-700 dark:text-orange-300">
                                        Belum ada pembayaran untuk langganan ini.
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