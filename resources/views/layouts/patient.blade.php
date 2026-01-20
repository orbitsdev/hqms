<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <!-- Mobile Bottom Navigation -->
        <div class="lg:hidden fixed bottom-0 left-0 right-0 bg-white dark:bg-zinc-900 border-t border-zinc-200 dark:border-zinc-700 z-50">
            <nav class="grid grid-cols-5 h-16">
                <a href="{{ route('patient.dashboard') }}"
                   class="flex flex-col items-center justify-center text-sm {{ request()->routeIs('patient.dashboard') ? 'text-blue-600 dark:text-blue-400' : 'text-zinc-600 dark:text-zinc-400' }}"
                   wire:navigate>
                    <flux:icon name="home" class="w-5 h-5" />
                    <span class="text-xs mt-1">Home</span>
                </a>
                <a href="{{ route('patient.appointments') }}"
                   class="flex flex-col items-center justify-center text-sm {{ request()->routeIs('patient.appointments*') ? 'text-blue-600 dark:text-blue-400' : 'text-zinc-600 dark:text-zinc-400' }}"
                   wire:navigate>
                    <flux:icon name="calendar" class="w-5 h-5" />
                    <span class="text-xs mt-1">Visits</span>
                </a>
                <a href="{{ route('patient.appointments.book') }}"
                   class="flex flex-col items-center justify-center text-sm {{ request()->routeIs('patient.appointments.book') ? 'text-blue-600 dark:text-blue-400' : 'text-zinc-600 dark:text-zinc-400' }}"
                   wire:navigate>
                    <flux:icon name="plus-circle" class="w-5 h-5" />
                    <span class="text-xs mt-1">Book</span>
                </a>
                <a href="{{ route('patient.queue') }}"
                   class="flex flex-col items-center justify-center text-sm {{ request()->routeIs('patient.queue') ? 'text-blue-600 dark:text-blue-400' : 'text-zinc-600 dark:text-zinc-400' }}"
                   wire:navigate>
                    <flux:icon name="queue" class="w-5 h-5" />
                    <span class="text-xs mt-1">Queue</span>
                </a>
                <a href="{{ route('patient.records') }}"
                   class="flex flex-col items-center justify-center text-sm {{ request()->routeIs('patient.records*') ? 'text-blue-600 dark:text-blue-400' : 'text-zinc-600 dark:text-zinc-400' }}"
                   wire:navigate>
                    <flux:icon name="document-text" class="w-5 h-5" />
                    <span class="text-xs mt-1">Records</span>
                </a>
            </nav>
        </div>

        <!-- Desktop Sidebar -->
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 lg:block hidden">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('patient.dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="Patient Portal" class="grid">
                    <flux:sidebar.item icon="home" :href="route('patient.dashboard')" :current="request()->routeIs('patient.dashboard')" wire:navigate>
                        Dashboard
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="calendar" :href="route('patient.appointments')" :current="request()->routeIs('patient.appointments*')" wire:navigate>
                        My Appointments
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="plus-circle" :href="route('patient.appointments.book')" :current="request()->routeIs('patient.appointments.book')" wire:navigate>
                        Book Appointment
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="queue" :href="route('patient.queue')" :current="request()->routeIs('patient.queue')" wire:navigate>
                        Queue Status
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="document-text" :href="route('patient.records')" :current="request()->routeIs('patient.records*')" wire:navigate>
                        Medical Records
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="user" :href="route('patient.profile')" :current="request()->routeIs('patient.profile')" wire:navigate>
                        Profile
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="arrow-right-start-on-rectangle" href="{{ route('logout') }}" method="post">
                    Logout
                </flux:sidebar.item>
            </flux:sidebar.nav>
        </flux:sidebar>

        <!-- Mobile Header -->
        <flux:header class="lg:hidden pb-16">
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
                        <flux:menu.item :href="route('patient.profile')" icon="user" wire:navigate>
                            Profile
                        </flux:menu.item>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            Settings
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
                        >
                            Logout
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        <!-- Main Content -->
        <main class="lg:pl-64 pb-16 lg:pb-0">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
                {{ $slot }}
            </div>
        </main>

        @fluxScripts
    </body>
</html>
