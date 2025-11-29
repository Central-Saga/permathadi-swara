<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-gray-900">
        <div class="relative isolate flex min-h-svh flex-col items-center justify-center gap-6 overflow-hidden p-6 md:p-10">
            <!-- Futuristic Background Blur Effects -->
            <div aria-hidden="true" class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80" data-gsap="error-503-blur-1">
                <div style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"
                    class="relative left-[calc(50%-11rem)] aspect-1155/678 w-144.5 -translate-x-1/2 rotate-30 bg-gradient-to-tr from-orange-400 to-red-500 opacity-30 sm:left-[calc(50%-30rem)] sm:w-288.75 dark:opacity-20">
                </div>
            </div>
            <div aria-hidden="true" class="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]" data-gsap="error-503-blur-2">
                <div style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"
                    class="relative left-[calc(50%+3rem)] aspect-1155/678 w-144.5 -translate-x-1/2 bg-gradient-to-tr from-orange-400 to-red-500 opacity-30 sm:left-[calc(50%+36rem)] sm:w-288.75 dark:opacity-20">
                </div>
            </div>
            
            <!-- Grid Pattern Background -->
            <div class="absolute inset-0 -z-10 bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] bg-[size:24px_24px] dark:bg-[linear-gradient(to_right,#8080800a_1px,transparent_1px),linear-gradient(to_bottom,#8080800a_1px,transparent_1px)]"></div>

            <div class="flex w-full max-w-2xl flex-col items-center gap-6 text-center">
                <!-- Maintenance Icon -->
                <div class="flex justify-center" data-gsap="error-503-icon">
                    <div class="relative">
                        <div class="absolute inset-0 rounded-full bg-orange-500/20 blur-xl"></div>
                        <div class="relative flex h-20 w-20 items-center justify-center rounded-full bg-orange-500/10 ring-1 ring-orange-500/20">
                            <svg class="h-10 w-10 text-orange-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655-5.653a2.548 2.548 0 0 0-3.586 0L2.25 12.5m9.17 2.67L2.25 12.5m0 0L3.75 9.75m8.67 5.92L3.75 9.75" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Error Code -->
                <h1 class="text-7xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-9xl" data-gsap="error-503-title">
                    503
                </h1>

                <!-- Error Message -->
                <p class="text-3xl font-semibold leading-8 text-gray-900 dark:text-white sm:text-4xl" data-gsap="error-503-subtitle">
                    Layanan Tidak Tersedia
                </p>

                <!-- Explanation -->
                <p class="text-base leading-7 text-gray-600 dark:text-gray-400 sm:text-lg max-w-md" data-gsap="error-503-description">
                    Maaf, layanan sedang dalam pemeliharaan atau sementara tidak tersedia. Kami sedang bekerja untuk memperbaikinya. Silakan coba lagi nanti.
                </p>

                <!-- Action Buttons -->
                <div class="flex items-center justify-center gap-x-4 mt-4" data-gsap="error-503-actions">
                    <a href="{{ route('home') }}"
                        class="rounded-lg bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-500 hover:to-red-500 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-orange-500/50 hover:shadow-xl hover:shadow-orange-500/60 transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98]">
                        Kembali ke Beranda
                    </a>
                    <a href="javascript:location.reload()"
                        class="rounded-lg border border-gray-300 dark:border-white/20 bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl px-6 py-3 text-sm font-semibold text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-white/10 transition-colors">
                        Muat Ulang Halaman <span aria-hidden="true">↻</span>
                    </a>
                </div>
            </div>
        </div>
        @vite('resources/js/landing-animations.js')
    </body>
</html>

