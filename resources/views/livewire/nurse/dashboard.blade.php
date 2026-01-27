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

    <!-- Alerts Section -->
    @if(count($alerts) > 0)
        <div class="space-y-2">
            @foreach($alerts as $category => $categoryAlerts)
                @foreach($categoryAlerts as $alert)
                    <a href="{{ $alert['action'] }}" wire:navigate class="flex items-center gap-3 rounded-lg border p-3 transition hover:shadow-sm
                        @if($alert['type'] === 'danger') border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20
                        @elseif($alert['type'] === 'warning') border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/20
                        @else border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/20 @endif">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full
                            @if($alert['type'] === 'danger') bg-red-100 dark:bg-red-900/50
                            @elseif($alert['type'] === 'warning') bg-amber-100 dark:bg-amber-900/50
                            @else bg-blue-100 dark:bg-blue-900/50 @endif">
                            @if($alert['type'] === 'danger')
                                <flux:icon name="exclamation-triangle" class="h-4 w-4 text-red-600 dark:text-red-400" />
                            @elseif($alert['type'] === 'warning')
                                <flux:icon name="exclamation-circle" class="h-4 w-4 text-amber-600 dark:text-amber-400" />
                            @else
                                <flux:icon name="information-circle" class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                            @endif
                        </div>
                        <span class="flex-1 text-sm font-medium
                            @if($alert['type'] === 'danger') text-red-800 dark:text-red-200
                            @elseif($alert['type'] === 'warning') text-amber-800 dark:text-amber-200
                            @else text-blue-800 dark:text-blue-200 @endif">
                            {{ $alert['message'] }}
                        </span>
                        <flux:icon name="chevron-right" class="h-4 w-4 text-zinc-400" />
                    </a>
                @endforeach
            @endforeach
        </div>
    @endif

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

    <!-- Main Grid -->
    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Left Column: Queue Status -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Currently Serving -->
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    <h2 class="text-sm font-medium text-zinc-900 dark:text-white">{{ __('Currently Serving') }}</h2>
                </div>
                <div class="p-4">
                    @if($currentServing->isNotEmpty())
                        <div class="space-y-3">
                            @foreach($currentServing as $queue)
                                <div class="flex items-center justify-between rounded-lg border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-800 dark:bg-emerald-900/20">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500 text-lg font-bold text-white">
                                            {{ $queue->formatted_number }}
                                        </span>
                                        <div>
                                            <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                                {{ $queue->appointment?->patient_first_name }} {{ $queue->appointment?->patient_last_name }}
                                            </div>
                                            <div class="flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                                                <span>{{ $queue->consultationType?->name }}</span>
                                                @if($queue->serving_started_at)
                                                    <span class="text-emerald-600 dark:text-emerald-400">{{ $queue->serving_started_at->diffForHumans(short: true) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if($queue->medicalRecord?->vital_signs_recorded_at)
                                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">
                                                {{ __('Vitals OK') }}
                                            </span>
                                        @else
                                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/50 dark:text-amber-300">
                                                {{ __('Needs Vitals') }}
                                            </span>
                                        @endif
                                        <flux:button href="{{ route('nurse.queue') }}" wire:navigate size="xs" variant="ghost">
                                            {{ __('Manage') }}
                                        </flux:button>
                                    </div>
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
                                        <span class="flex h-8 w-8 items-center justify-center rounded font-bold
                                            @if($queue->priority === 'emergency') bg-red-500 text-white
                                            @elseif($queue->priority === 'urgent') bg-amber-500 text-white
                                            @else bg-blue-500 text-white @endif">
                                            {{ $queue->formatted_number }}
                                        </span>
                                        <div>
                                            <div class="text-sm text-zinc-900 dark:text-white">
                                                {{ $queue->appointment?->patient_first_name }} {{ $queue->appointment?->patient_last_name }}
                                            </div>
                                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ $queue->consultationType?->name }}
                                                @if($queue->priority !== 'normal')
                                                    <span class="ml-1 font-medium
                                                        @if($queue->priority === 'emergency') text-red-600 dark:text-red-400
                                                        @else text-amber-600 dark:text-amber-400 @endif">
                                                        ({{ ucfirst($queue->priority) }})
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <span class="rounded px-2 py-0.5 text-xs font-medium
                                        @if($queue->status === 'called') bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300
                                        @else bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300 @endif">
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

        <!-- Right Column: Doctors & Wait Times -->
        <div class="space-y-6">
            <!-- Doctors Available Today -->
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    <h2 class="text-sm font-medium text-zinc-900 dark:text-white">{{ __('Doctors Today') }}</h2>
                </div>
                <div class="p-4">
                    @if($doctorsAvailable->isNotEmpty())
                        <div class="space-y-2">
                            @foreach($doctorsAvailable as $doctor)
                                <div class="flex items-center justify-between rounded-lg border border-zinc-200 bg-zinc-50 p-2.5 dark:border-zinc-700 dark:bg-zinc-800">
                                    <div class="flex items-center gap-2">
                                        <div class="relative">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-200 text-xs font-medium text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">
                                                {{ strtoupper(substr($doctor['name'], 0, 2)) }}
                                            </div>
                                            @if($doctor['is_serving'])
                                                <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-white bg-emerald-500 dark:border-zinc-800"></span>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $doctor['name'] }}</div>
                                            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $doctor['specialties'] ?: __('General') }}</div>
                                        </div>
                                    </div>
                                    @if($doctor['is_serving'])
                                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">
                                            {{ __('Busy') }}
                                        </span>
                                    @else
                                        <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400">
                                            {{ __('Available') }}
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-6 text-center">
                            <flux:icon name="user-circle" class="mx-auto h-8 w-8 text-zinc-300 dark:text-zinc-600" />
                            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('No doctors scheduled today') }}</p>
                            <flux:button href="{{ route('nurse.doctor-schedules') }}" wire:navigate variant="ghost" size="sm" class="mt-2">
                                {{ __('Manage Schedules') }}
                            </flux:button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Wait Times -->
            @if($waitTimes->isNotEmpty())
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                        <h2 class="text-sm font-medium text-zinc-900 dark:text-white">{{ __('Queue Status') }}</h2>
                    </div>
                    <div class="p-4">
                        <div class="space-y-3">
                            @foreach($waitTimes as $type)
                                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-zinc-900 dark:text-white">{{ $type['name'] }}</span>
                                        <span class="rounded bg-zinc-200 px-1.5 py-0.5 text-xs font-bold text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">
                                            {{ $type['short_name'] }}
                                        </span>
                                    </div>
                                    <div class="mt-2 flex items-center justify-between text-xs">
                                        <div class="flex items-center gap-3">
                                            <span class="text-zinc-500 dark:text-zinc-400">
                                                <span class="font-semibold text-zinc-900 dark:text-white">{{ $type['waiting'] }}</span> {{ __('waiting') }}
                                            </span>
                                            @if($type['serving'] > 0)
                                                <span class="text-emerald-600 dark:text-emerald-400">
                                                    <span class="font-semibold">{{ $type['serving'] }}</span> {{ __('serving') }}
                                                </span>
                                            @endif
                                        </div>
                                        @if($type['estimated_minutes'] > 0)
                                            <span class="text-zinc-500 dark:text-zinc-400">
                                                ~{{ $type['estimated_minutes'] }} {{ __('min') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
        <h2 class="mb-4 text-sm font-medium text-zinc-900 dark:text-white">{{ __('Quick Actions') }}</h2>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-6">
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
            <flux:button href="{{ route('nurse.medical-records') }}" wire:navigate variant="ghost" class="h-auto flex-col gap-2 py-4" icon="document-text">
                <span>{{ __('Records') }}</span>
            </flux:button>
            <flux:button href="{{ route('nurse.doctor-schedules') }}" wire:navigate variant="ghost" class="h-auto flex-col gap-2 py-4" icon="calendar">
                <span>{{ __('Schedules') }}</span>
            </flux:button>
        </div>
    </div>
</section>
