<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-gray-900">
        <div class="relative isolate flex min-h-svh flex-col items-center justify-center gap-6 overflow-hidden p-6 md:p-10">
            <!-- Futuristic Background Blur Effects -->
            <div aria-hidden="true" class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80">
                <div style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"
                    class="relative left-[calc(50%-11rem)] aspect-1155/678 w-144.5 -translate-x-1/2 rotate-30 bg-gradient-to-tr from-orange-400 to-red-500 opacity-30 sm:left-[calc(50%-30rem)] sm:w-288.75 dark:opacity-20">
                </div>
            </div>
            <div aria-hidden="true" class="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]">
                <div style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"
                    class="relative left-[calc(50%+3rem)] aspect-1155/678 w-144.5 -translate-x-1/2 bg-gradient-to-tr from-orange-400 to-red-500 opacity-30 sm:left-[calc(50%+36rem)] sm:w-288.75 dark:opacity-20">
                </div>
            </div>
            
            <!-- Grid Pattern Background -->
            <div class="absolute inset-0 -z-10 bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] bg-[size:24px_24px] dark:bg-[linear-gradient(to_right,#8080800a_1px,transparent_1px),linear-gradient(to_bottom,#8080800a_1px,transparent_1px)]"></div>

            <div class="flex w-full max-w-md flex-col gap-6">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-3 font-medium group" wire:navigate>
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-orange-500 to-red-500 shadow-lg shadow-orange-500/50 transition-transform duration-300 group-hover:scale-110 group-hover:shadow-xl group-hover:shadow-orange-500/60">
                        <x-app-logo-icon class="size-7 fill-current text-white" />
                    </span>
                    <span class="text-lg font-semibold text-gray-900 dark:text-white">Permathadi Swara</span>
                    <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                </a>

                <div class="flex flex-col gap-6">
                    <div class="group relative rounded-2xl border border-gray-200/50 bg-white/80 backdrop-blur-xl dark:bg-gray-900/80 dark:border-gray-700/50 text-gray-900 dark:text-white shadow-2xl shadow-orange-500/10 ring-1 ring-gray-900/5 dark:ring-white/10 transition-all duration-300 hover:shadow-orange-500/20">
                        <!-- Glow effect -->
                        <div class="absolute -inset-0.5 rounded-2xl bg-gradient-to-r from-orange-500/20 via-red-500/20 to-orange-500/20 opacity-0 blur-xl transition-opacity duration-500 group-hover:opacity-100 -z-10"></div>
                        <div class="relative px-8 py-10">{{ $slot }}</div>
                    </div>
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
