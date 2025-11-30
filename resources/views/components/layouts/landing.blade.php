<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="bg-white dark:bg-gray-900 landing-page flex flex-col min-h-screen">
    <x-landing.header />

    <main class="flex-1 flex flex-col">
        {{ $slot }}
    </main>

    <x-layouts.landing.footer />

    @fluxScripts
    @vite('resources/js/landing-animations.js')
    
    <script>
        // Prevent Flux dropdown from disabling body scroll on landing pages
        (function() {
            const body = document.body;
            
            function ensureScrollable() {
                if (body.classList.contains('landing-page')) {
                    body.style.setProperty('overflow-y', 'auto', 'important');
                    body.style.setProperty('overflow-x', 'hidden', 'important');
                }
            }
            
            // Set initial overflow
            ensureScrollable();
            
            // Watch for style changes (when Flux dropdown opens/closes)
            const observer = new MutationObserver(function() {
                ensureScrollable();
            });
            
            // Observe body for any attribute changes
            observer.observe(body, {
                attributes: true,
                attributeFilter: ['style', 'class'],
                subtree: false
            });
            
            // Also prevent on dropdown interactions
            // Jangan gunakan capture phase untuk menghindari konflik dengan navigasi
            document.addEventListener('click', function(e) {
                // Skip jika target adalah link atau button yang melakukan navigasi
                if (e.target.closest('a[href]') || e.target.closest('button[type="submit"]')) {
                    return;
                }
                
                if (e.target.closest('[data-flux-dropdown]') || e.target.closest('button[type="button"]')) {
                    setTimeout(ensureScrollable, 10);
                    setTimeout(ensureScrollable, 50);
                    setTimeout(ensureScrollable, 100);
                }
            }, false);
            
            // Periodic check to ensure scrollability (as fallback)
            setInterval(ensureScrollable, 200);
        })();
    </script>
</body>

</html>