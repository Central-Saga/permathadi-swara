<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head', ['title' => $title ?? null])
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">
    <flux:sidebar sticky collapsible
        class="bg-orange-50/50 dark:bg-orange-950/30 border-r border-orange-200 dark:border-orange-800/50">
        <flux:sidebar.header>
            <flux:sidebar.brand :href="route('dashboard')" :name="config('app.name', 'Permathadi Swara')" wire:navigate>
                <x-app-logo-icon class="size-6 fill-current text-orange-600 dark:text-orange-400" />
            </flux:sidebar.brand>
            <flux:sidebar.collapse
                class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
        </flux:sidebar.header>

        <flux:sidebar.nav>
            <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                wire:navigate>
                {{ __('Dashboard') }}
            </flux:sidebar.item>
            @can('melihat user')
            <flux:sidebar.item icon="users" :href="route('godmode.users.index')"
                :current="request()->routeIs('godmode.users.*')" wire:navigate>
                {{ __('User') }}
            </flux:sidebar.item>
            @endcan
            @can('melihat role')
            <flux:sidebar.item icon="shield-check" :href="route('godmode.roles.index')"
                :current="request()->routeIs('godmode.roles.*')" wire:navigate>
                {{ __('Role') }}
            </flux:sidebar.item>
            @endcan
        </flux:sidebar.nav>

        <flux:sidebar.spacer />

        <flux:dropdown position="top" align="start" class="max-lg:hidden">
            <flux:sidebar.profile :name="auth()->user()?->name ?? ''" :initials="auth()->user()?->initials() ?? ''" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-200">
                                    {{ auth()->user()?->initials() ?? '' }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()?->name ?? '' }}</span>
                                <span class="truncate text-xs">{{ auth()->user()?->email ?? '' }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full"
                        data-test="logout-button">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <!-- Mobile Header -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()?->initials() ?? ''" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-200">
                                    {{ auth()->user()?->initials() ?? '' }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()?->name ?? '' }}</span>
                                <span class="truncate text-xs">{{ auth()->user()?->email ?? '' }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full"
                        data-test="logout-button">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    @persist('toast')
    <flux:toast position="top end" />
    @endpersist

    @if (session('success'))
    <script>
        (function() {
                function showToast() {
                    if (window.Flux && typeof window.Flux.toast === 'function') {
                        window.Flux.toast({
                            variant: 'success',
                            text: {!! json_encode(session('success')) !!}
                        });
                    } else {
                        // Retry after a short delay if Flux is not yet loaded
                        setTimeout(showToast, 100);
                    }
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', showToast);
                } else {
                    showToast();
                }

                // Also listen for Livewire navigation events
                document.addEventListener('livewire:navigated', showToast);
            })();
    </script>
    @endif

    @fluxScripts
</body>

</html>