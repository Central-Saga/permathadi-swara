@props(['layanans'])

<div class="bg-white py-24 sm:py-32 dark:bg-gray-900" data-gsap="program-section" data-lazy-section="program">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="mx-auto max-w-2xl lg:text-center" data-gsap="program-heading">
            <h2 class="text-base/7 font-semibold text-orange-600 dark:text-orange-400">Program Unggulan</h2>
            <p
                class="mt-2 text-4xl font-semibold tracking-tight text-pretty text-gray-900 sm:text-5xl lg:text-balance dark:text-white">
                Pilih Program yang Tepat untuk Anda
            </p>
            <p class="mt-6 text-lg/8 text-gray-600 dark:text-gray-300">
                Kami menawarkan berbagai program pembelajaran untuk semua kalangan, dari pemula hingga tingkat lanjutan.
                Setiap program dirancang dengan kurikulum terstruktur dan instruktur berpengalaman.
            </p>
        </div>
        <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-none">
            <div class="grid max-w-xl grid-cols-1 gap-8 lg:max-w-none lg:grid-cols-3">
                @forelse ($layanans as $index => $layanan)
                <div class="group flex flex-col overflow-hidden rounded-2xl bg-gray-50 shadow-sm ring-1 ring-gray-900/5 transition-all hover:shadow-xl dark:bg-white/5 dark:ring-white/10"
                    data-gsap="program-card">
                    @if ($layanan->getFirstMediaUrl('layanan_cover'))
                    <div class="aspect-[16/9] w-full overflow-hidden">
                        <img src="{{ $layanan->getFirstMediaUrl('layanan_cover') }}" alt="{{ $layanan->name }}"
                            class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-110" />
                    </div>
                    @else
                    <div
                        class="aspect-[16/9] w-full bg-gradient-to-br from-orange-400 to-red-500 flex items-center justify-center">
                        <svg viewBox="0 0 20 20" fill="currentColor" class="size-12 text-white/50">
                            <path fill-rule="evenodd"
                                d="M1 5.25A2.25 2.25 0 013.25 3h13.5A2.25 2.25 0 0119 5.25v9.5A2.25 2.25 0 0116.75 17H3.25A2.25 2.25 0 011 14.75v-9.5zm1.5 0v9.5a.75.75 0 00.75.75h13.5a.75.75 0 00.75-.75v-9.5a.75.75 0 00-.75-.75H3.25a.75.75 0 00-.75.75zm16.5 0A2.25 2.25 0 0016.75 3H3.25A2.25 2.25 0 001 5.25v9.5A2.25 2.25 0 003.25 17h13.5A2.25 2.25 0 0019 14.75v-9.5z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    @endif

                    <div class="flex flex-1 flex-col p-6">
                        <div class="flex items-start justify-between gap-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $layanan->name }}
                            </h3>
                            @if ($layanan->is_active)
                            <span
                                class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                Aktif
                            </span>
                            @endif
                        </div>

                        <p class="mt-3 flex-1 text-sm/6 text-gray-600 dark:text-gray-400 line-clamp-3">
                            {{ $layanan->description }}
                        </p>

                        <div
                            class="mt-6 flex items-center justify-between border-t border-gray-200 pt-4 dark:border-gray-700">
                            <div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                    Rp {{ number_format($layanan->price, 0, ',', '.') }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    @if ($layanan->duration >= 365)
                                    {{ round($layanan->duration / 365) }} Tahun
                                    @elseif ($layanan->duration >= 30)
                                    {{ round($layanan->duration / 30) }} Bulan
                                    @else
                                    {{ $layanan->duration }} Hari
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center text-orange-600 dark:text-orange-400 program-card-arrow">
                                <svg viewBox="0 0 20 20" fill="currentColor" class="size-5">
                                    <path fill-rule="evenodd"
                                        d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>

                        @if ($layanan->slug)
                        <div class="mt-4">
                            <a href="{{ route('landing.program-detail', $layanan->slug) }}"
                                class="block w-full rounded-lg bg-orange-600 px-4 py-2.5 text-center text-sm font-semibold text-white shadow-sm hover:bg-orange-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-600 transition-colors">
                                Lihat Detail
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
                @empty
                <div class="col-span-full text-center py-12">
                    <svg viewBox="0 0 20 20" fill="currentColor" class="mx-auto size-12 text-gray-400">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z"
                            clip-rule="evenodd" />
                    </svg>
                    <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">Belum ada program yang tersedia saat ini.
                    </p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>