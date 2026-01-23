<section class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-1">
            <flux:heading size="xl" level="1">{{ __('My Appointments') }}</flux:heading>
            <flux:text variant="subtle" class="text-sm">
                {{ __('Track upcoming visits, review past care, and manage your requests.') }}
            </flux:text>
        </div>
        <flux:button :href="route('patient.appointments.book')" variant="primary" icon="calendar-days" wire:navigate>
            {{ __('Book appointment') }}
        </flux:button>
    </div>

    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex flex-wrap gap-2">
            <flux:button
                wire:click="$set('filter', 'upcoming')"
                :variant="$filter === 'upcoming' ? 'primary' : 'ghost'"
                size="sm"
            >
                {{ __('Upcoming') }}
            </flux:button>
            <flux:button
                wire:click="$set('filter', 'past')"
                :variant="$filter === 'past' ? 'primary' : 'ghost'"
                size="sm"
            >
                {{ __('Past') }}
            </flux:button>
            <flux:button
                wire:click="$set('filter', 'all')"
                :variant="$filter === 'all' ? 'primary' : 'ghost'"
                size="sm"
            >
                {{ __('All') }}
            </flux:button>
        </div>

        <div class="w-full lg:w-72">
            <flux:input
                type="search"
                wire:model.live.debounce.400ms="search"
                placeholder="{{ __('Search appointments') }}"
            />
        </div>
    </div>

    @if($appointments->count() > 0)
        <div class="grid gap-4">
            @foreach($appointments as $appointment)
                @php
                    $statusColor = match ($appointment->status) {
                        'approved', 'completed' => 'green',
                        'checked_in', 'in_progress' => 'blue',
                        'cancelled', 'no_show' => 'red',
                        'pending' => 'yellow',
                        default => 'gray',
                    };
                    $statusLabel = str_replace('_', ' ', ucfirst($appointment->status));
                    $dateLabel = $appointment->appointment_date?->format('M d, Y');
                    $timeLabel = $appointment->appointment_time?->format('h:i A');
                @endphp

                <article
                    class="rounded-xl border border-zinc-200/70 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
                    wire:key="appointment-{{ $appointment->id }}"
                >
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="space-y-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <flux:heading size="lg" level="3">
                                    {{ $appointment->consultationType?->name ?? __('Consultation') }}
                                </flux:heading>
                                <flux:badge color="{{ $statusColor }}">{{ $statusLabel }}</flux:badge>
                            </div>

                            <div class="space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                                <div class="flex items-center gap-2">
                                    <flux:icon name="calendar-days" class="h-4 w-4 text-zinc-400" />
                                    <span>
                                        {{ $dateLabel }}
                                        @if($timeLabel)
                                            {{ __('at') }} {{ $timeLabel }}
                                        @else
                                            <span class="text-zinc-500">({{ __('Time to be confirmed') }})</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <flux:icon name="user" class="h-4 w-4 text-zinc-400" />
                                    <span>
                                        {{ $appointment->patient_first_name }} {{ $appointment->patient_last_name }}
                                        @if($appointment->relationship_to_account !== 'self')
                                            <span class="text-zinc-500">({{ ucfirst($appointment->relationship_to_account) }})</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <flux:icon name="user-circle" class="h-4 w-4 text-zinc-400" />
                                    <span>
                                        {{ $appointment->doctor?->name ?? __('Doctor assignment pending') }}
                                    </span>
                                </div>
                            </div>

                            @if(filled($appointment->chief_complaints))
                                <flux:text class="text-sm text-zinc-700 dark:text-zinc-300">
                                    {{ $appointment->chief_complaints }}
                                </flux:text>
                            @endif

                            @if($appointment->queue)
                                <div class="flex items-center gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-200">
                                    <span>{{ __('Queue') }}:</span>
                                    <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                                        {{ $appointment->queue->formatted_number }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <flux:button
                                :href="route('patient.appointments.show', $appointment)"
                                variant="outline"
                                size="sm"
                                wire:navigate
                            >
                                {{ __('View details') }}
                            </flux:button>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        {{ $appointments->links() }}
    @else
        <div class="rounded-xl border border-zinc-200/70 bg-white p-8 text-center shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <img
                src="{{ asset('images/undraw_booked_bb22.svg') }}"
                alt="{{ __('Appointments') }}"
                class="mx-auto mb-4 h-28 w-auto opacity-80"
            />
            <flux:heading size="lg" level="2">{{ __('No appointments found') }}</flux:heading>
            <flux:text variant="subtle" class="mt-2 text-sm">
                @if($search)
                    {{ __('Try adjusting your search terms or filters.') }}
                @elseif($filter === 'upcoming')
                    {{ __('You have no upcoming appointments right now.') }}
                @elseif($filter === 'past')
                    {{ __('No past appointments are on file yet.') }}
                @else
                    {{ __('Book your first appointment to get started.') }}
                @endif
            </flux:text>

            @if(! $search)
                <div class="mt-4">
                    <flux:button :href="route('patient.appointments.book')" variant="primary" wire:navigate>
                        {{ __('Book appointment') }}
                    </flux:button>
                </div>
            @endif
        </div>
    @endif
</section>
