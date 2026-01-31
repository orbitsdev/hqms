<div class="min-h-screen bg-gradient-to-b from-zinc-50 to-white dark:from-zinc-900 dark:to-zinc-800 -m-6 lg:-m-8 p-4 sm:p-6 lg:p-8">
    <div class="mx-auto sm:max-w-2xl">

        {{-- Header --}}
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">{{ __('My Appointments') }}</h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Track and manage your visits') }}</p>
            </div>
            <a href="{{ route('patient.appointments.book') }}"
               class="flex items-center gap-1.5 rounded-lg bg-primary px-3 py-2 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90"
               wire:navigate>
                <flux:icon name="plus" class="h-4 w-4" />
                <span class="hidden sm:inline">{{ __('Book') }}</span>
            </a>
        </div>

        {{-- Filter Tabs --}}
        <div class="mb-4 flex gap-2 overflow-x-auto pb-2">
            <button wire:click="$set('filter', 'upcoming')"
                    class="flex-shrink-0 rounded-full px-4 py-2 text-sm font-medium transition {{ $filter === 'upcoming' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700' }}">
                {{ __('Upcoming') }}
            </button>
            <button wire:click="$set('filter', 'past')"
                    class="flex-shrink-0 rounded-full px-4 py-2 text-sm font-medium transition {{ $filter === 'past' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700' }}">
                {{ __('Past') }}
            </button>
            <button wire:click="$set('filter', 'all')"
                    class="flex-shrink-0 rounded-full px-4 py-2 text-sm font-medium transition {{ $filter === 'all' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700' }}">
                {{ __('All') }}
            </button>
        </div>

        {{-- Search --}}
        <div class="mb-6">
            <div class="relative">
                <flux:icon name="magnifying-glass" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" />
                <input type="search"
                       wire:model.live.debounce.400ms="search"
                       placeholder="{{ __('Search appointments...') }}"
                       class="w-full rounded-xl border border-zinc-200 bg-white py-2.5 pl-10 pr-4 text-sm text-zinc-900 placeholder-zinc-400 focus:border-primary focus:ring-primary dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500" />
            </div>
        </div>

        {{-- Appointments List --}}
        @if($appointments->count() > 0)
            <div class="space-y-3">
                @foreach($appointments as $appointment)
                    @php
                        $statusConfig = match ($appointment->status) {
                            'approved' => ['bg' => 'bg-success/10', 'text' => 'text-success', 'dot' => 'bg-success'],
                            'completed' => ['bg' => 'bg-success/10', 'text' => 'text-success', 'dot' => 'bg-success'],
                            'checked_in', 'in_progress' => ['bg' => 'bg-primary/10', 'text' => 'text-primary', 'dot' => 'bg-primary'],
                            'cancelled', 'no_show' => ['bg' => 'bg-destructive/10', 'text' => 'text-destructive', 'dot' => 'bg-destructive'],
                            'pending' => ['bg' => 'bg-warning/10', 'text' => 'text-warning', 'dot' => 'bg-warning'],
                            default => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400', 'dot' => 'bg-zinc-400'],
                        };
                        $statusLabel = str_replace('_', ' ', ucfirst($appointment->status));
                    @endphp

                    <a href="{{ route('patient.appointments.show', $appointment) }}"
                       class="block rounded-xl border border-zinc-200 bg-white p-4 shadow-sm transition hover:border-zinc-300 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600"
                       wire:navigate
                       wire:key="appointment-{{ $appointment->id }}">
                        <div class="flex items-start gap-4">
                            {{-- Date Badge --}}
                            <div class="flex h-14 w-14 flex-shrink-0 flex-col items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-700">
                                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ $appointment->appointment_date?->format('M') }}</span>
                                <span class="text-xl font-bold text-zinc-900 dark:text-white">{{ $appointment->appointment_date?->format('d') }}</span>
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0 flex-1">
                                        <p class="font-semibold text-zinc-900 dark:text-white truncate">
                                            {{ $appointment->consultationType?->name ?? __('Consultation') }}
                                        </p>
                                        <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">
                                            @if($appointment->appointment_time)
                                                {{ $appointment->appointment_time->format('h:i A') }}
                                            @else
                                                {{ __('Time TBA') }}
                                            @endif
                                            @if($appointment->doctor)
                                                &middot; {{ $appointment->doctor->name }}
                                            @endif
                                        </p>
                                    </div>
                                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-medium {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $statusConfig['dot'] }}"></span>
                                        {{ $statusLabel }}
                                    </span>
                                </div>

                                <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                                    <flux:icon name="user" class="inline h-3 w-3" />
                                    {{ $appointment->patient_first_name }} {{ $appointment->patient_last_name }}
                                    @if($appointment->relationship_to_account !== 'self')
                                        ({{ ucfirst($appointment->relationship_to_account) }})
                                    @endif
                                </p>

                                @if($appointment->queue)
                                    <p class="mt-1 text-xs font-medium text-primary">
                                        <flux:icon name="ticket" class="inline h-3 w-3" />
                                        {{ __('Queue') }}: {{ $appointment->queue->formatted_number }}
                                    </p>
                                @endif
                            </div>

                            <flux:icon name="chevron-right" class="h-5 w-5 flex-shrink-0 text-zinc-400" />
                        </div>
                    </a>

                    {{-- Cancel Modal for Pending --}}
                    @if($appointment->status === 'pending')
                        <flux:modal name="cancel-appointment-{{ $appointment->id }}" focusable class="max-w-sm">
                            <div class="p-4 space-y-4">
                                <div class="text-center">
                                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-destructive/10">
                                        <flux:icon name="exclamation-triangle" class="h-6 w-6 text-destructive" />
                                    </div>
                                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ __('Cancel Appointment?') }}</h3>
                                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ __('This action cannot be undone. You can book a new appointment anytime.') }}
                                    </p>
                                </div>
                                <div class="flex gap-3">
                                    <flux:modal.close class="flex-1">
                                        <flux:button variant="outline" class="w-full">{{ __('Keep') }}</flux:button>
                                    </flux:modal.close>
                                    <flux:modal.close class="flex-1">
                                        <flux:button variant="danger" class="w-full" wire:click="cancelAppointment({{ $appointment->id }})">
                                            {{ __('Cancel') }}
                                        </flux:button>
                                    </flux:modal.close>
                                </div>
                            </div>
                        </flux:modal>
                    @endif
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $appointments->links() }}
            </div>
        @else
            {{-- Empty State --}}
            <div class="rounded-xl border border-dashed border-zinc-300 bg-zinc-50 p-8 text-center dark:border-zinc-700 dark:bg-zinc-800/50">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-zinc-200 dark:bg-zinc-700">
                    <flux:icon name="calendar" class="h-8 w-8 text-zinc-400" />
                </div>
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ __('No appointments found') }}</h3>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    @if($search)
                        {{ __('Try adjusting your search terms.') }}
                    @elseif($filter === 'upcoming')
                        {{ __('You have no upcoming appointments.') }}
                    @elseif($filter === 'past')
                        {{ __('No past appointments found.') }}
                    @else
                        {{ __('Book your first appointment to get started.') }}
                    @endif
                </p>
                @unless($search)
                    <a href="{{ route('patient.appointments.book') }}"
                       class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                       wire:navigate>
                        <flux:icon name="plus" class="h-4 w-4" />
                        {{ __('Book Appointment') }}
                    </a>
                @endunless
            </div>
        @endif

        {{-- Bottom spacing for mobile nav --}}
        <div class="h-20 lg:hidden"></div>
    </div>
</div>
