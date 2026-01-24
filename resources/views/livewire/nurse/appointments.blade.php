<section class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-1">
            <flux:heading size="xl" level="1">{{ __('Appointments') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('Review and manage patient appointment requests.') }}
            </flux:text>
        </div>
        <flux:button href="{{ route('nurse.walk-in') }}" wire:navigate variant="primary" icon="plus">
            {{ __('Walk-in') }}
        </flux:button>
    </div>

    <div class="flex flex-wrap items-center gap-2 border-b border-zinc-200 pb-4 dark:border-zinc-700">
        <flux:button
            wire:click="setStatus('pending')"
            :variant="$status === 'pending' ? 'filled' : 'ghost'"
            size="sm"
        >
            {{ __('Pending') }}
            @if($statusCounts['pending'] > 0)
                <span class="ml-1 rounded-full bg-zinc-200 px-1.5 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">{{ $statusCounts['pending'] }}</span>
            @endif
        </flux:button>
        <flux:button
            wire:click="setStatus('approved')"
            :variant="$status === 'approved' ? 'filled' : 'ghost'"
            size="sm"
        >
            {{ __('Approved') }}
            @if($statusCounts['approved'] > 0)
                <span class="ml-1 rounded-full bg-zinc-200 px-1.5 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">{{ $statusCounts['approved'] }}</span>
            @endif
        </flux:button>
        <flux:button
            wire:click="setStatus('today')"
            :variant="$status === 'today' ? 'filled' : 'ghost'"
            size="sm"
        >
            {{ __('Today') }}
            @if($statusCounts['today'] > 0)
                <span class="ml-1 rounded-full bg-zinc-200 px-1.5 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">{{ $statusCounts['today'] }}</span>
            @endif
        </flux:button>
        <flux:button
            wire:click="setStatus('cancelled')"
            :variant="$status === 'cancelled' ? 'filled' : 'ghost'"
            size="sm"
        >
            {{ __('Cancelled') }}
        </flux:button>
        <flux:button
            wire:click="setStatus('all')"
            :variant="$status === 'all' ? 'filled' : 'ghost'"
            size="sm"
        >
            {{ __('All') }}
            <span class="ml-1 rounded-full bg-zinc-200 px-1.5 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">{{ $statusCounts['all'] }}</span>
        </flux:button>
    </div>

    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="w-full sm:w-64">
                <flux:input
                    type="search"
                    wire:model.live.debounce.400ms="search"
                    placeholder="{{ __('Search patients...') }}"
                    icon="magnifying-glass"
                />
            </div>
            <div class="w-full sm:w-40">
                <flux:select wire:model.live="consultationTypeFilter" placeholder="{{ __('All types') }}">
                    <flux:select.option value="">{{ __('All types') }}</flux:select.option>
                    @foreach($consultationTypes as $type)
                        <flux:select.option value="{{ $type->id }}">{{ $type->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            <div class="w-full sm:w-32">
                <flux:select wire:model.live="sourceFilter" placeholder="{{ __('All sources') }}">
                    <flux:select.option value="">{{ __('All sources') }}</flux:select.option>
                    <flux:select.option value="online">{{ __('Online') }}</flux:select.option>
                    <flux:select.option value="walk-in">{{ __('Walk-in') }}</flux:select.option>
                </flux:select>
            </div>
            <div class="w-full sm:w-40">
                <flux:input
                    type="date"
                    wire:model.live="dateFilter"
                />
            </div>
            @if($search || $consultationTypeFilter || $dateFilter || $sourceFilter)
                <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                    {{ __('Clear') }}
                </flux:button>
            @endif
        </div>

        <div class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
            <span>{{ __('Sort:') }}</span>
            <flux:button
                wire:click="sort('appointment_date')"
                variant="ghost"
                size="xs"
                class="{{ $sortBy === 'appointment_date' ? 'text-zinc-900 dark:text-white' : '' }}"
            >
                {{ __('Date') }}
                @if($sortBy === 'appointment_date')
                    <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="h-3 w-3" />
                @endif
            </flux:button>
            <flux:button
                wire:click="sort('created_at')"
                variant="ghost"
                size="xs"
                class="{{ $sortBy === 'created_at' ? 'text-zinc-900 dark:text-white' : '' }}"
            >
                {{ __('Requested') }}
                @if($sortBy === 'created_at')
                    <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="h-3 w-3" />
                @endif
            </flux:button>
        </div>
    </div>

    @if($appointments->count() > 0)
        <ul role="list" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($appointments as $appointment)
                @php
                    $statusLabel = str_replace('_', ' ', ucfirst($appointment->status));
                    $dateLabel = $appointment->appointment_date?->format('M d, Y');
                    $timeLabel = $appointment->appointment_time?->format('h:i A');
                    $patientName = trim($appointment->patient_first_name . ' ' . $appointment->patient_last_name);
                    $accountOwner = $appointment->user;
                    $consultationType = $appointment->consultationType;
                    $isWalkIn = $appointment->source === 'walk-in';
                @endphp

                <li
                    wire:key="appointment-{{ $appointment->id }}"
                    class="col-span-1 rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900"
                >
                    <div class="p-4">
                        <div class="flex items-start justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="truncate text-sm font-semibold text-zinc-900 dark:text-white">
                                        {{ $patientName }}
                                    </h3>
                                    <span class="inline-flex items-center rounded px-1.5 py-0.5 text-xs font-medium {{ $appointment->status === 'pending' ? 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' : ($appointment->status === 'cancelled' ? 'bg-zinc-100 text-zinc-500 line-through dark:bg-zinc-800 dark:text-zinc-500' : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300') }}">
                                        {{ $statusLabel }}
                                    </span>
                                </div>

                                <div class="mt-2 space-y-1 text-sm text-zinc-500 dark:text-zinc-400">
                                    <div class="flex items-center gap-2">
                                        <flux:icon name="calendar-days" class="h-4 w-4 shrink-0" />
                                        <span class="truncate">
                                            {{ $dateLabel }}
                                            @if($timeLabel)
                                                {{ __('at') }} {{ $timeLabel }}
                                            @endif
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <flux:icon name="clipboard-document-list" class="h-4 w-4 shrink-0" />
                                        <span class="truncate">{{ $consultationType?->name ?? __('N/A') }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <flux:icon name="{{ $isWalkIn ? 'user' : 'globe-alt' }}" class="h-4 w-4 shrink-0" />
                                        <span class="truncate text-xs">
                                            {{ $isWalkIn ? __('Walk-in') : __('Online') }}
                                        </span>
                                    </div>
                                    @if($appointment->relationship_to_account !== 'self')
                                        <div class="flex items-center gap-2">
                                            <flux:icon name="user-group" class="h-4 w-4 shrink-0" />
                                            <span class="truncate text-xs">
                                                {{ __('By') }}: {{ $accountOwner?->name ?? '-' }}
                                            </span>
                                        </div>
                                    @endif
                                    @if($appointment->queue)
                                        <div class="flex items-center gap-2">
                                            <flux:icon name="queue-list" class="h-4 w-4 shrink-0" />
                                            <span class="font-medium text-zinc-700 dark:text-zinc-200">
                                                {{ __('Queue') }}: {{ $appointment->queue->formatted_number }}
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                @if(filled($appointment->chief_complaints))
                                    <p class="mt-2 line-clamp-2 text-xs text-zinc-600 dark:text-zinc-400">
                                        {{ $appointment->chief_complaints }}
                                    </p>
                                @endif

                                <p class="mt-2 text-xs text-zinc-400 dark:text-zinc-500">
                                    {{ $appointment->created_at->diffForHumans() }}
                                </p>
                            </div>

                            <div class="ml-4 shrink-0">
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-zinc-100 text-xs font-bold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                    {{ $consultationType?->short_name ?? '?' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-zinc-200 dark:border-zinc-700">
                        <a
                            href="{{ route('nurse.appointments.show', $appointment) }}"
                            wire:navigate
                            class="flex items-center justify-center gap-2 py-3 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800"
                        >
                            <flux:icon name="eye" class="h-4 w-4" />
                            {{ __('View Details') }}
                        </a>
                    </div>
                </li>
            @endforeach
        </ul>

        <div class="mt-6">
            {{ $appointments->links() }}
        </div>
    @else
        <div class="rounded-lg border border-zinc-200 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                <flux:icon name="calendar-days" class="h-6 w-6 text-zinc-400" />
            </div>
            <h3 class="mt-4 text-sm font-medium text-zinc-900 dark:text-white">{{ __('No appointments found') }}</h3>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                @if($search || $consultationTypeFilter || $dateFilter || $sourceFilter)
                    {{ __('Try adjusting your filters.') }}
                @elseif($status === 'pending')
                    {{ __('No pending requests at this time.') }}
                @else
                    {{ __('No appointments match the criteria.') }}
                @endif
            </p>

            <div class="mt-4 flex justify-center gap-2">
                @if($search || $consultationTypeFilter || $dateFilter || $sourceFilter)
                    <flux:button wire:click="clearFilters" variant="ghost">
                        {{ __('Clear filters') }}
                    </flux:button>
                @endif
                <flux:button href="{{ route('nurse.walk-in') }}" wire:navigate variant="primary" icon="plus">
                    {{ __('Register Walk-in') }}
                </flux:button>
            </div>
        </div>
    @endif
</section>
