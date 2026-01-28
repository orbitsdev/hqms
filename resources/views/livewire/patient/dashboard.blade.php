<div class="min-h-screen bg-gradient-to-b from-zinc-50 to-white dark:from-zinc-900 dark:to-zinc-800">
    <div class="mx-auto max-w-2xl px-4 py-6 sm:px-6 lg:px-8">

        {{-- Welcome Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                {{ __('Hello') }}, {{ $this->user->personalInformation?->first_name ?? $this->user->first_name }}
            </h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ now()->format('l, F j, Y') }}
            </p>
        </div>

        {{-- Active Queue Card (Priority) --}}
        @if($this->activeQueue)
            <div class="mb-6 overflow-hidden rounded-2xl bg-gradient-to-br from-primary to-primary/80 p-5 text-white shadow-lg">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-white/80">{{ __('Your Queue Number') }}</p>
                        <p class="mt-1 text-4xl font-bold">{{ $this->activeQueue->formatted_number }}</p>
                    </div>
                    <div class="rounded-full bg-white/20 p-3">
                        <flux:icon name="ticket" class="h-6 w-6" />
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-2">
                    <span class="inline-flex items-center rounded-full bg-white/20 px-3 py-1 text-xs font-medium">
                        @if($this->activeQueue->status === 'serving')
                            <span class="mr-1.5 h-2 w-2 rounded-full bg-success animate-pulse"></span>
                            {{ __('Now Serving') }}
                        @elseif($this->activeQueue->status === 'called')
                            <span class="mr-1.5 h-2 w-2 rounded-full bg-warning animate-pulse"></span>
                            {{ __('Please Proceed') }}
                        @else
                            <span class="mr-1.5 h-2 w-2 rounded-full bg-white/60"></span>
                            {{ __('Waiting') }}
                        @endif
                    </span>
                    <span class="text-sm text-white/80">
                        {{ $this->activeQueue->consultationType?->name }}
                    </span>
                </div>
                <a href="{{ route('patient.appointments') }}" class="mt-4 flex items-center gap-1 text-sm font-medium text-white hover:text-white/80" wire:navigate>
                    {{ __('View Details') }}
                    <flux:icon name="chevron-right" class="h-4 w-4" />
                </a>
            </div>
        @endif

        {{-- Quick Actions --}}
        <div class="mb-6 grid grid-cols-2 gap-3">
            <a href="{{ route('patient.appointments.book') }}"
               class="flex flex-col items-center justify-center gap-2 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm transition hover:border-primary/30 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-primary/50"
               wire:navigate>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-primary">
                    <flux:icon name="calendar-days" class="h-6 w-6" />
                </div>
                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Book Visit') }}</span>
            </a>
            <a href="{{ route('patient.appointments') }}"
               class="flex flex-col items-center justify-center gap-2 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm transition hover:border-success/30 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-success/50"
               wire:navigate>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-success/10 text-success">
                    <flux:icon name="clipboard-document-list" class="h-6 w-6" />
                </div>
                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('My Bookings') }}</span>
            </a>
        </div>

        {{-- Stats Cards --}}
        <div class="mb-6 grid grid-cols-3 gap-3">
            <div class="rounded-xl border border-zinc-200 bg-white p-4 text-center dark:border-zinc-700 dark:bg-zinc-800">
                <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $this->stats['total_visits'] }}</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Total Visits') }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 text-center dark:border-zinc-700 dark:bg-zinc-800">
                <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $this->stats['upcoming_appointments'] }}</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Upcoming') }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 text-center dark:border-zinc-700 dark:bg-zinc-800">
                <p class="text-2xl font-bold text-warning">{{ $this->stats['pending_appointments'] }}</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Pending') }}</p>
            </div>
        </div>

        {{-- Upcoming Appointments --}}
        <div class="mb-6">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ __('Upcoming Appointments') }}</h2>
                @if($this->upcomingAppointments->count() > 0)
                    <a href="{{ route('patient.appointments') }}" class="text-sm font-medium text-primary hover:text-primary/80" wire:navigate>
                        {{ __('See all') }}
                    </a>
                @endif
            </div>

            @forelse($this->upcomingAppointments as $appointment)
                <a href="{{ route('patient.appointments.show', $appointment) }}"
                   class="mb-3 block rounded-xl border border-zinc-200 bg-white p-4 shadow-sm transition hover:border-zinc-300 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600"
                   wire:navigate
                   wire:key="appt-{{ $appointment->id }}">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 flex-shrink-0 flex-col items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-700">
                            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ $appointment->appointment_date?->format('M') }}</span>
                            <span class="text-lg font-bold text-zinc-900 dark:text-white">{{ $appointment->appointment_date?->format('d') }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="font-medium text-zinc-900 dark:text-white truncate">
                                    {{ $appointment->consultationType?->name }}
                                </p>
                                @php
                                    $statusVariant = match ($appointment->status) {
                                        'approved' => 'success',
                                        'pending' => 'warning',
                                        default => 'default'
                                    };
                                @endphp
                                <flux:badge size="sm" :variant="$statusVariant">
                                    {{ ucfirst($appointment->status) }}
                                </flux:badge>
                            </div>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                @if($appointment->appointment_time)
                                    {{ $appointment->appointment_time->format('h:i A') }}
                                @else
                                    {{ __('Time TBA') }}
                                @endif
                                @if($appointment->doctor)
                                    &middot; {{ $appointment->doctor->name }}
                                @endif
                            </p>
                            <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">
                                {{ $appointment->patient_first_name }} {{ $appointment->patient_last_name }}
                                @if($appointment->relationship_to_account !== 'self')
                                    ({{ ucfirst($appointment->relationship_to_account) }})
                                @endif
                            </p>
                        </div>
                        <flux:icon name="chevron-right" class="h-5 w-5 text-zinc-400" />
                    </div>
                </a>
            @empty
                <div class="rounded-xl border border-dashed border-zinc-300 bg-zinc-50 p-6 text-center dark:border-zinc-700 dark:bg-zinc-800/50">
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-zinc-200 dark:bg-zinc-700">
                        <flux:icon name="calendar" class="h-6 w-6 text-zinc-400" />
                    </div>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('No upcoming appointments') }}</p>
                    <a href="{{ route('patient.appointments.book') }}" class="mt-2 inline-block text-sm font-medium text-primary hover:text-primary/80" wire:navigate>
                        {{ __('Book your first visit') }}
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Recent Records --}}
        @if($this->recentRecords->count() > 0)
            <div>
                <h2 class="mb-3 text-lg font-semibold text-zinc-900 dark:text-white">{{ __('Recent Visits') }}</h2>
                @foreach($this->recentRecords as $record)
                    <div class="mb-3 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800"
                         wire:key="record-{{ $record->id }}">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-zinc-900 dark:text-white">
                                    {{ $record->consultationType?->name }}
                                </p>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $record->visit_date?->format('M d, Y') }}
                                    @if($record->doctor)
                                        &middot; Dr. {{ $record->doctor->personalInformation?->last_name ?? $record->doctor->last_name }}
                                    @endif
                                </p>
                            </div>
                            <flux:badge size="sm" variant="success">{{ __('Completed') }}</flux:badge>
                        </div>
                        @if($record->diagnosis)
                            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300 line-clamp-2">
                                {{ $record->diagnosis }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Bottom spacing for mobile nav --}}
        <div class="h-20 lg:hidden"></div>
    </div>
</div>
