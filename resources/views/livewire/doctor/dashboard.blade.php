<section class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" level="1">{{ __('Doctor Dashboard') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ now()->format('l, F j, Y') }}
            </flux:text>
        </div>
        <flux:button href="{{ route('doctor.queue') }}" wire:navigate variant="primary" icon="play">
            {{ __('Start Consultations') }}
        </flux:button>
    </div>

    {{-- Quick Stats --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon name="clock" class="h-5 w-5 text-zinc-600 dark:text-zinc-400" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $waitingCount }}</p>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Waiting') }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon name="user" class="h-5 w-5 text-zinc-600 dark:text-zinc-400" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $examiningCount }}</p>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Examining') }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon name="check-circle" class="h-5 w-5 text-zinc-600 dark:text-zinc-400" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $completedCount }}</p>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Completed') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Currently Examining --}}
        @if($currentlyExamining)
            <div class="rounded-xl border-2 border-zinc-900 bg-zinc-50 p-4 dark:border-white dark:bg-zinc-800">
                <div class="mb-3 flex items-center gap-2">
                    <span class="relative flex h-3 w-3">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-zinc-400 opacity-75"></span>
                        <span class="relative inline-flex h-3 w-3 rounded-full bg-zinc-900 dark:bg-white"></span>
                    </span>
                    <flux:heading size="sm">{{ __('Currently Examining') }}</flux:heading>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-lg font-bold text-zinc-900 dark:text-white">
                            {{ $currentlyExamining->patient_full_name }}
                        </p>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                            {{ $currentlyExamining->consultationType?->name }} &bull;
                            {{ $currentlyExamining->queue?->formatted_number }}
                        </p>
                        <p class="mt-1 text-xs text-zinc-500">
                            {{ __('Started') }} {{ $currentlyExamining->examined_at?->diffForHumans() }}
                        </p>
                    </div>
                    <flux:button href="{{ route('doctor.examine', $currentlyExamining) }}" wire:navigate variant="primary" icon="arrow-right">
                        {{ __('Continue') }}
                    </flux:button>
                </div>
            </div>
        @endif

        {{-- Waiting Patients --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900 {{ $currentlyExamining ? '' : 'lg:col-span-2' }}">
            <div class="mb-4 flex items-center justify-between">
                <flux:heading size="sm">{{ __('Waiting Patients') }}</flux:heading>
                @if($waitingPatients->isNotEmpty())
                    <flux:button href="{{ route('doctor.queue') }}" wire:navigate variant="ghost" size="sm" icon-trailing="arrow-right">
                        {{ __('View All') }}
                    </flux:button>
                @endif
            </div>

            @if($waitingPatients->isNotEmpty())
                <div class="space-y-2">
                    @foreach($waitingPatients as $queue)
                        @php
                            $record = $queue->medicalRecord;
                            $patientName = $record?->patient_full_name ?? ($queue->appointment?->patient_first_name . ' ' . $queue->appointment?->patient_last_name);
                        @endphp
                        <div class="flex items-center justify-between rounded-lg border border-zinc-100 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                            <div class="flex items-center gap-3">
                                {{-- Queue Number --}}
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-200 text-xs font-bold text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">
                                    {{ $queue->formatted_number }}
                                </span>
                                <div>
                                    <p class="font-medium text-zinc-900 dark:text-white">{{ $patientName }}</p>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ $queue->consultationType?->name }}
                                        @if($record?->effective_chief_complaints)
                                            &bull; {{ Str::limit($record->effective_chief_complaints, 30) }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            @if($queue->priority !== 'normal')
                                <flux:badge size="sm" color="zinc">
                                    {{ strtoupper($queue->priority) }}
                                </flux:badge>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center">
                    <img src="{{ asset('images/illustrations/empty-queue.svg') }}" alt="" class="mx-auto h-32 w-32 opacity-60" />
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('No patients waiting') }}</p>
                </div>
            @endif
        </div>

        {{-- Recent Completed --}}
        @if($recentCompleted->isNotEmpty())
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="sm" class="mb-4">{{ __('Recently Completed') }}</flux:heading>
                <div class="space-y-2">
                    @foreach($recentCompleted as $record)
                        <div class="flex items-center justify-between rounded-lg p-2">
                            <div>
                                <p class="font-medium text-zinc-900 dark:text-white">{{ $record->patient_full_name }}</p>
                                <p class="text-xs text-zinc-500">
                                    {{ $record->consultationType?->name }} &bull;
                                    {{ $record->examination_ended_at?->format('h:i A') }}
                                </p>
                            </div>
                            <flux:badge size="sm" color="zinc">
                                {{ str_replace('_', ' ', ucfirst($record->status)) }}
                            </flux:badge>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
