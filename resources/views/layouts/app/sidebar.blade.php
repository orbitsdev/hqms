<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        @php
            $user = auth()->user();
            $isPatient = $user?->isPatient() ?? false;
            $portalLabel = $isPatient ? __('Patient Portal') : __('Platform');

            if (! $isPatient && $user) {
                if ($user->isDoctor()) {
                    $portalLabel = __('Doctor Station');
                } elseif ($user->isNurse()) {
                    $portalLabel = __('Nurse Station');
                } elseif ($user->isAdmin()) {
                    $portalLabel = __('Admin Console');
                } elseif ($user->isCashier()) {
                    $portalLabel = __('Cashier Desk');
                } else {
                    $portalLabel = __('Staff Portal');
                }
            }
        @endphp

        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <div class="grid gap-1">
                    <x-app-logo :sidebar="true" href="{{ $isPatient ? route('patient.dashboard') : route('dashboard') }}" wire:navigate />
                    <span class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                        {{ $portalLabel }}
                    </span>
                </div>
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                @if($isPatient)
                    <flux:sidebar.group :heading="__('Patient Portal')" class="grid">
                        <flux:sidebar.item icon="home" :href="route('patient.dashboard')" :current="request()->routeIs('patient.dashboard')" wire:navigate>
                            {{ __('Dashboard') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="calendar" :href="route('patient.appointments')" :current="request()->routeIs('patient.appointments*')" wire:navigate>
                            {{ __('My Appointments') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="plus-circle" :href="route('patient.appointments.book')" :current="request()->routeIs('patient.appointments.book')" wire:navigate>
                            {{ __('Book Appointment') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="queue-list" :href="route('patient.queue')" :current="request()->routeIs('patient.queue')" wire:navigate>
                            {{ __('Queue Status') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="document-text" :href="route('patient.records')" :current="request()->routeIs('patient.records*')" wire:navigate>
                            {{ __('Medical Records') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="user" :href="route('patient.profile')" :current="request()->routeIs('patient.profile')" wire:navigate>
                            {{ __('Profile') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="cog" :href="route('profile.edit')" :current="request()->routeIs('profile.edit')" wire:navigate>
                            {{ __('Settings') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @else
                    <flux:sidebar.group :heading="$portalLabel" class="grid">
                        <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                            {{ __('Dashboard') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="cog" :href="route('profile.edit')" :current="request()->routeIs('profile.edit')" wire:navigate>
                            {{ __('Settings') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            @unless($isPatient)
                <flux:sidebar.nav>
                    <flux:sidebar.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                        {{ __('Repository') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                        {{ __('Documentation') }}
                    </flux:sidebar.item>
                </flux:sidebar.nav>
            @endunless

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        @if($isPatient)
            <div class="lg:hidden fixed bottom-0 left-0 right-0 bg-white/95 dark:bg-zinc-900/95 border-t border-zinc-200 dark:border-zinc-700 z-50 backdrop-blur">
                <nav class="grid grid-cols-5 h-16">
                    <a href="{{ route('patient.dashboard') }}"
                       class="flex flex-col items-center justify-center text-sm transition {{ request()->routeIs('patient.dashboard') ? 'text-zinc-900 dark:text-zinc-100 bg-zinc-100/70 dark:bg-zinc-800/60' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100/60 dark:hover:bg-zinc-800/60' }}"
                       wire:navigate>
                        <flux:icon name="home" class="w-5 h-5" />
                        <span class="text-xs mt-1">Home</span>
                    </a>
                    <a href="{{ route('patient.appointments') }}"
                       class="flex flex-col items-center justify-center text-sm transition {{ request()->routeIs('patient.appointments*') ? 'text-zinc-900 dark:text-zinc-100 bg-zinc-100/70 dark:bg-zinc-800/60' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100/60 dark:hover:bg-zinc-800/60' }}"
                       wire:navigate>
                        <flux:icon name="calendar" class="w-5 h-5" />
                        <span class="text-xs mt-1">Visits</span>
                    </a>
                    <a href="{{ route('patient.appointments.book') }}"
                       class="flex flex-col items-center justify-center text-sm transition {{ request()->routeIs('patient.appointments.book') ? 'text-zinc-900 dark:text-zinc-100 bg-zinc-100/70 dark:bg-zinc-800/60' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100/60 dark:hover:bg-zinc-800/60' }}"
                       wire:navigate>
                        <flux:icon name="plus-circle" class="w-5 h-5" />
                        <span class="text-xs mt-1">Book</span>
                    </a>
                    <a href="{{ route('patient.queue') }}"
                       class="flex flex-col items-center justify-center text-sm transition {{ request()->routeIs('patient.queue') ? 'text-zinc-900 dark:text-zinc-100 bg-zinc-100/70 dark:bg-zinc-800/60' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100/60 dark:hover:bg-zinc-800/60' }}"
                       wire:navigate>
                        <flux:icon name="queue-list" class="w-5 h-5" />
                        <span class="text-xs mt-1">Queue</span>
                    </a>
                    <a href="{{ route('patient.records') }}"
                       class="flex flex-col items-center justify-center text-sm transition {{ request()->routeIs('patient.records*') ? 'text-zinc-900 dark:text-zinc-100 bg-zinc-100/70 dark:bg-zinc-800/60' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100/60 dark:hover:bg-zinc-800/60' }}"
                       wire:navigate>
                        <flux:icon name="document-text" class="w-5 h-5" />
                        <span class="text-xs mt-1">Records</span>
                    </a>
                </nav>
            </div>
        @endif

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                <flux:menu.radio.group>
                    @if($isPatient)
                        <flux:menu.item :href="route('patient.profile')" icon="user" wire:navigate>
                            {{ __('Profile') }}
                        </flux:menu.item>
                    @endif
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                        {{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
