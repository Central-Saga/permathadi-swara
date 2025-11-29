@props(['layanan'])

<div class="relative isolate pt-14">
    <div aria-hidden="true" class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80" data-gsap="program-detail-hero-blur-1">
        <div style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"
            class="relative left-[calc(50%-11rem)] aspect-1155/678 w-144.5 -translate-x-1/2 rotate-30 bg-gradient-to-tr from-orange-400 to-red-500 opacity-30 sm:left-[calc(50%-30rem)] sm:w-288.75 dark:opacity-20">
        </div>
    </div>
    <div class="py-24 sm:py-32 lg:pb-40">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-4xl">
                @if ($layanan->getFirstMediaUrl('layanan_cover'))
                <div class="mb-8 overflow-hidden rounded-2xl shadow-2xl ring-1 ring-gray-900/5 dark:ring-white/10" data-gsap="program-detail-hero-image">
                    <img src="{{ $layanan->getFirstMediaUrl('layanan_cover') }}" alt="{{ $layanan->name }}"
                        class="h-full w-full object-cover" />
                </div>
                @endif
                <div class="text-center">
                    <h1
                        class="text-5xl font-semibold tracking-tight text-balance text-gray-900 sm:text-7xl dark:text-white"
                        data-gsap="program-detail-hero-title">
                        {{ $layanan->name }}
                    </h1>
                    @if ($layanan->description)
                    <p class="mt-8 text-lg font-medium text-pretty text-gray-500 sm:text-xl/8 dark:text-gray-400" data-gsap="program-detail-hero-description">
                        {{ $layanan->description }}
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div aria-hidden="true"
        class="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]"
        data-gsap="program-detail-hero-blur-2">
        <div style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"
            class="relative left-[calc(50%+3rem)] aspect-1155/678 w-144.5 -translate-x-1/2 bg-gradient-to-tr from-orange-400 to-red-500 opacity-30 sm:left-[calc(50%+36rem)] sm:w-288.75 dark:opacity-20">
        </div>
    </div>
</div>

