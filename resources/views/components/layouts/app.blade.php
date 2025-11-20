@php
// Determine layout based on route name pattern and auth status
$isLandingRoute = request()->routeIs('landing.*', 'home');
$isAdminRoute = request()->routeIs('godmode.*', 'dashboard', 'settings.*', 'profile.*', 'user-password.*',
'appearance.*');

// Route-based layout selection
$useAdminLayout = $isAdminRoute || (auth()->check() && !$isLandingRoute);
@endphp

@if($isLandingRoute || !$useAdminLayout)
<x-layouts.landing :title="$title ?? null">
    {{ $slot }}
</x-layouts.landing>
@else
<x-layouts.admin :title="$title ?? null">
    {{ $slot }}
</x-layouts.admin>
@endif