<div class="min-h-screen bg-gradient-to-b from-zinc-50 to-white dark:from-zinc-900 dark:to-zinc-800 -m-6 lg:-m-8 p-4 sm:p-6 lg:p-8">
    <div class="mx-auto sm:max-w-2xl">

        {{-- Header --}}
        <div class="mb-6">
            <div class="flex items-center gap-3">
                <a href="{{ route('patient.dashboard') }}" class="rounded-lg p-2 text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800" wire:navigate>
                    <flux:icon name="arrow-left" class="h-5 w-5" />
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">{{ __('Queue Status') }}</h1>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ now()->format('l, F j, Y') }}</p>
                </div>
            </div>
        </div>

        @if($activeQueue)
            {{-- Active Queue Card --}}
            <div class="mb-6 overflow-hidden rounded-2xl bg-gradient-to-br from-primary to-primary/80 p-6 text-white shadow-lg">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-white/80">{{ __('Your Queue Number') }}</p>
                        <p class="mt-1 text-5xl font-bold">{{ $activeQueue->formatted_number }}</p>
                    </div>
                    <div class="rounded-full bg-white/20 p-3">
                        <flux:icon name="ticket" class="h-8 w-8" />
                    </div>
                </div>

                {{-- Status Badge --}}
                <div class="mt-4">
                    @if($activeQueue->status === 'serving')
                        <div class="inline-flex items-center rounded-full bg-success/20 px-4 py-2 text-sm font-semibold">
                            <span class="mr-2 h-3 w-3 rounded-full bg-success animate-pulse"></span>
                            {{ __('Now Being Served') }}
                        </div>
                    @elseif($activeQueue->status === 'called')
                        <div class="inline-flex items-center rounded-full bg-warning/20 px-4 py-2 text-sm font-semibold">
                            <span class="mr-2 h-3 w-3 rounded-full bg-warning animate-pulse"></span>
                            {{ __('Please Proceed to Nurse Station') }}
                        </div>
                    @else
                        <div class="inline-flex items-center rounded-full bg-white/20 px-4 py-2 text-sm font-semibold">
                            <span class="mr-2 h-3 w-3 rounded-full bg-white/60"></span>
                            {{ __('Waiting in Queue') }}
                        </div>
                    @endif
                </div>

                {{-- Service Info --}}
                <div class="mt-4 rounded-lg bg-white/10 p-3">
                    <p class="text-sm text-white/80">{{ $activeQueue->consultationType?->name }}</p>
                    @if($activeQueue->appointment)
                        <p class="mt-1 text-sm text-white/70">
                            {{ __('Patient:') }} {{ $activeQueue->appointment->patient_first_name }} {{ $activeQueue->appointment->patient_last_name }}
                        </p>
                    @endif
                </div>
            </div>

            {{-- Queue Position (only for waiting) --}}
            @if($activeQueue->status === 'waiting' && $queuePosition)
                <div class="mb-6 grid grid-cols-2 gap-4">
                    <div class="rounded-xl border border-zinc-200 bg-white p-4 text-center dark:border-zinc-700 dark:bg-zinc-800">
                        <p class="text-3xl font-bold text-primary">{{ $queuePosition }}</p>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Position in Line') }}</p>
                    </div>
                    <div class="rounded-xl border border-zinc-200 bg-white p-4 text-center dark:border-zinc-700 dark:bg-zinc-800">
                        <p class="text-3xl font-bold text-zinc-900 dark:text-white">
                            @if($estimatedWaitMinutes)
                                ~{{ $estimatedWaitMinutes }}
                            @else
                                {{ __('Soon') }}
                            @endif
                        </p>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Est. Minutes') }}</p>
                    </div>
                </div>
            @endif

            {{-- Currently Serving (for context) --}}
            @if($currentlyServing && $currentlyServing->count() > 0 && $activeQueue->status === 'waiting')
                <div class="mb-6 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                    <h2 class="mb-3 text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Currently Serving') }}</h2>
                    <div class="space-y-2">
                        @foreach($currentlyServing as $serving)
                            <div class="flex items-center justify-between rounded-lg bg-zinc-50 p-3 dark:bg-zinc-700/50">
                                <span class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $serving->formatted_number }}</span>
                                <flux:badge size="sm" variant="success">{{ __('Serving') }}</flux:badge>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Being Served Details --}}
            @if($activeQueue->status === 'serving' && $activeQueue->servedBy)
                <div class="mb-6 rounded-xl border border-success/30 bg-success/5 p-4 dark:border-success/50 dark:bg-success/10">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-success/20">
                            <flux:icon name="user" class="h-5 w-5 text-success" />
                        </div>
                        <div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Being attended by') }}</p>
                            <p class="font-medium text-zinc-900 dark:text-white">
                                {{ $activeQueue->servedBy->personalInformation?->full_name ?? $activeQueue->servedBy->name }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Priority Badge --}}
            @if($activeQueue->priority !== 'normal')
                <div class="mb-6">
                    @if($activeQueue->priority === 'emergency')
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-900 dark:bg-red-900/20">
                            <div class="flex items-center gap-2">
                                <flux:icon name="exclamation-triangle" class="h-5 w-5 text-red-600 dark:text-red-400" />
                                <span class="font-semibold text-red-700 dark:text-red-400">{{ __('Emergency Priority') }}</span>
                            </div>
                        </div>
                    @elseif($activeQueue->priority === 'urgent')
                        <div class="rounded-xl border border-orange-200 bg-orange-50 p-4 dark:border-orange-900 dark:bg-orange-900/20">
                            <div class="flex items-center gap-2">
                                <flux:icon name="clock" class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                                <span class="font-semibold text-orange-700 dark:text-orange-400">{{ __('Urgent Priority') }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

        @else
            {{-- No Active Queue --}}
            <div class="mb-6 rounded-xl border border-dashed border-zinc-300 bg-zinc-50 p-8 text-center dark:border-zinc-700 dark:bg-zinc-800/50">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-zinc-200 dark:bg-zinc-700">
                    <flux:icon name="ticket" class="h-8 w-8 text-zinc-400" />
                </div>
                <h3 class="text-lg font-semibold text-zinc-700 dark:text-zinc-300">{{ __('No Active Queue') }}</h3>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('You don\'t have an active queue number today.') }}
                </p>
                <a href="{{ route('patient.appointments.book') }}"
                   class="mt-4 inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/90"
                   wire:navigate>
                    <flux:icon name="calendar-days" class="h-4 w-4" />
                    {{ __('Book Appointment') }}
                </a>
            </div>
        @endif

        {{-- Recent Queue History --}}
        @if($recentHistory->count() > 0)
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <h2 class="mb-3 text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Recent Queue History') }}</h2>
                <div class="space-y-2">
                    @foreach($recentHistory as $history)
                        <div class="flex items-center justify-between rounded-lg bg-zinc-50 p-3 dark:bg-zinc-700/50"
                             wire:key="history-{{ $history->id }}">
                            <div>
                                <p class="font-medium text-zinc-900 dark:text-white">{{ $history->formatted_number }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $history->queue_date?->format('M d, Y') }} &middot; {{ $history->consultationType?->name }}
                                </p>
                            </div>
                            @php
                                $statusVariant = match($history->status) {
                                    'completed' => 'success',
                                    'skipped' => 'warning',
                                    'cancelled' => 'danger',
                                    default => 'default'
                                };
                            @endphp
                            <flux:badge size="sm" :variant="$statusVariant">{{ ucfirst($history->status) }}</flux:badge>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Auto-refresh notice --}}
        @if($activeQueue)
            <p class="mt-6 text-center text-xs text-zinc-400 dark:text-zinc-500">
                <flux:icon name="arrow-path" class="inline h-3 w-3" />
                {{ __('This page updates automatically') }}
            </p>
        @endif

        {{-- Bottom spacing for mobile nav --}}
        <div class="h-20 lg:hidden"></div>
    </div>
</div>
