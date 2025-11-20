<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="bg-white dark:bg-gray-900">
    <x-landing.header />

    <main>
        {{ $slot }}
    </main>

    <x-layouts.landing.footer />

    @fluxScripts
    @vite('resources/js/landing-animations.js')
</body>

</html>
