<section class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-1">
            <flux:heading size="xl" level="1">{{ __('Dashboard') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('Welcome back! Here\'s what\'s happening today.') }}
            </flux:text>
        </div>
        <div class="flex gap-2">
            <flux:button href="{{ route('nurse.walk-in') }}" wire:navigate variant="primary" icon="plus">
                {{ __('Walk-in') }}
            </flux:button>
            <flux:button href="{{ route('nurse.queue') }}" wire:navigate variant="ghost" icon="queue-list">
                {{ __('Queue') }}
            </flux:button>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        <a href="{{ route('nurse.appointments', ['status' => 'pending']) }}" wire:navigate class="rounded-lg border border-zinc-200 bg-white p-4 transition hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-600">
            <div class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['pending_appointments'] }}</div>
            <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ __('Pending') }}</div>
        </a>

        <a href="{{ route('nurse.appointments', ['status' => 'today']) }}" wire:navigate class="rounded-lg border border-zinc-200 bg-white p-4 transition hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-600">
            <div class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['today_appointments'] }}</div>
            <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ __('Today') }}</div>
        </a>

        <a href="{{ route('nurse.queue') }}" wire:navigate class="rounded-lg border border-zinc-200 bg-white p-4 transition hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-600">
            <div class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['waiting_checkin'] }}</div>
            <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ __('Check-in') }}</div>
        </a>

        <a href="{{ route('nurse.queue', ['status' => 'waiting']) }}" wire:navigate class="rounded-lg border border-zinc-200 bg-white p-4 transition hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-600">
            <div class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['queue_waiting'] }}</div>
            <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ __('In Queue') }}</div>
        </a>

        <a href="{{ route('nurse.queue', ['status' => 'serving']) }}" wire:navigate class="rounded-lg border border-zinc-200 bg-white p-4 transition hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-600">
            <div class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['queue_serving'] }}</div>
            <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ __('Serving') }}</div>
        </a>

        <a href="{{ route('nurse.queue', ['status' => 'completed']) }}" wire:navigate class="rounded-lg border border-zinc-200 bg-white p-4 transition hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-600">
            <div class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['queue_completed'] }}</div>
            <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ __('Completed') }}</div>
        </a>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <!-- Currently Serving -->
        <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                <h2 class="text-sm font-medium text-zinc-900 dark:text-white">{{ __('Currently Serving') }}</h2>
            </div>
            <div class="p-4">
                @if($currentServing->isNotEmpty())
                    <div class="space-y-3">
                        @foreach($currentServing as $queue)
                            <div class="flex items-center justify-between rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800">
                                <div class="flex items-center gap-3">
                                    <span class="text-lg font-bold text-zinc-900 dark:text-white">
                                        {{ $queue->formatted_number }}
                                    </span>
                                    <div>
                                        <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                            {{ $queue->appointment?->patient_first_name }} {{ $queue->appointment?->patient_last_name }}
                                        </div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $queue->consultationType?->name }}
                                        </div>
                                    </div>
                                </div>
                                <flux:button href="{{ route('nurse.queue') }}" wire:navigate size="xs" variant="ghost">
                                    {{ __('Manage') }}
                                </flux:button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center">
                        <flux:icon name="user-group" class="mx-auto h-8 w-8 text-zinc-300 dark:text-zinc-600" />
                        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('No patients being served') }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Up Next -->
        <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                <h2 class="text-sm font-medium text-zinc-900 dark:text-white">{{ __('Up Next') }}</h2>
            </div>
            <div class="p-4">
                @if($recentQueue->isNotEmpty())
                    <div class="space-y-2">
                        @foreach($recentQueue as $queue)
                            <div class="flex items-center justify-between rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800">
                                <div class="flex items-center gap-3">
                                    <span class="font-bold text-zinc-700 dark:text-zinc-200">
                                        {{ $queue->formatted_number }}
                                    </span>
                                    <div>
                                        <div class="text-sm text-zinc-900 dark:text-white">
                                            {{ $queue->appointment?->patient_first_name }} {{ $queue->appointment?->patient_last_name }}
                                        </div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $queue->consultationType?->name }}
                                            @if($queue->priority !== 'normal')
                                                <span class="ml-1 font-medium">({{ ucfirst($queue->priority) }})</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <span class="rounded px-2 py-0.5 text-xs font-medium bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                    {{ ucfirst($queue->status) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 text-center">
                        <flux:button href="{{ route('nurse.queue') }}" wire:navigate variant="ghost" size="sm">
                            {{ __('View All') }}
                        </flux:button>
                    </div>
                @else
                    <div class="py-8 text-center">
                        <flux:icon name="queue-list" class="mx-auto h-8 w-8 text-zinc-300 dark:text-zinc-600" />
                        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('No patients in queue') }}</p>
                        <flux:button href="{{ route('nurse.walk-in') }}" wire:navigate variant="primary" size="sm" class="mt-3" icon="plus">
                            {{ __('Register Walk-in') }}
                        </flux:button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
        <h2 class="mb-4 text-sm font-medium text-zinc-900 dark:text-white">{{ __('Quick Actions') }}</h2>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <flux:button href="{{ route('nurse.walk-in') }}" wire:navigate variant="ghost" class="h-auto flex-col gap-2 py-4" icon="user-plus">
                <span>{{ __('Walk-in') }}</span>
            </flux:button>
            <flux:button href="{{ route('nurse.queue') }}" wire:navigate variant="ghost" class="h-auto flex-col gap-2 py-4" icon="queue-list">
                <span>{{ __('Queue') }}</span>
            </flux:button>
            <flux:button href="{{ route('nurse.appointments') }}" wire:navigate variant="ghost" class="h-auto flex-col gap-2 py-4" icon="calendar-days">
                <span>{{ __('Appointments') }}</span>
            </flux:button>
            <flux:button href="{{ route('nurse.appointments', ['status' => 'pending']) }}" wire:navigate variant="ghost" class="h-auto flex-col gap-2 py-4" icon="clipboard-document-check">
                <span>{{ __('Pending') }}</span>
            </flux:button>
        </div>
    </div>
</section>
