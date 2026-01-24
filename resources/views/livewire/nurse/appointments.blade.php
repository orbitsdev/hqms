<section class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-1">
            <flux:heading size="xl" level="1">{{ __('Appointment Requests') }}</flux:heading>
            <flux:text variant="subtle" class="text-sm">
                {{ __('Review, approve, or cancel patient appointment requests.') }}
            </flux:text>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <flux:button
            wire:click="setStatus('pending')"
            :variant="$status === 'pending' ? 'primary' : 'ghost'"
            size="sm"
        >
            {{ __('Pending') }}
            @if($statusCounts['pending'] > 0)
                <flux:badge color="yellow" size="sm" class="ml-1">{{ $statusCounts['pending'] }}</flux:badge>
            @endif
        </flux:button>
        <flux:button
            wire:click="setStatus('approved')"
            :variant="$status === 'approved' ? 'primary' : 'ghost'"
            size="sm"
        >
            {{ __('Approved') }}
            @if($statusCounts['approved'] > 0)
                <flux:badge color="green" size="sm" class="ml-1">{{ $statusCounts['approved'] }}</flux:badge>
            @endif
        </flux:button>
        <flux:button
            wire:click="setStatus('today')"
            :variant="$status === 'today' ? 'primary' : 'ghost'"
            size="sm"
        >
            {{ __('Today') }}
            @if($statusCounts['today'] > 0)
                <flux:badge color="blue" size="sm" class="ml-1">{{ $statusCounts['today'] }}</flux:badge>
            @endif
        </flux:button>
        <flux:button
            wire:click="setStatus('cancelled')"
            :variant="$status === 'cancelled' ? 'primary' : 'ghost'"
            size="sm"
        >
            {{ __('Cancelled') }}
        </flux:button>
        <flux:button
            wire:click="setStatus('all')"
            :variant="$status === 'all' ? 'primary' : 'ghost'"
            size="sm"
        >
            {{ __('All') }}
            <flux:badge size="sm" class="ml-1">{{ $statusCounts['all'] }}</flux:badge>
        </flux:button>
    </div>

    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="w-full sm:w-64">
                <flux:input
                    type="search"
                    wire:model.live.debounce.400ms="search"
                    placeholder="{{ __('Search patients, phone, complaints...') }}"
                    icon="magnifying-glass"
                />
            </div>
            <div class="w-full sm:w-48">
                <flux:select wire:model.live="consultationTypeFilter" placeholder="{{ __('All types') }}">
                    <flux:select.option value="">{{ __('All types') }}</flux:select.option>
                    @foreach($consultationTypes as $type)
                        <flux:select.option value="{{ $type->id }}">{{ $type->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            <div class="w-full sm:w-44">
                <flux:input
                    type="date"
                    wire:model.live="dateFilter"
                />
            </div>
            @if($search || $consultationTypeFilter || $dateFilter)
                <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                    {{ __('Clear') }}
                </flux:button>
            @endif
        </div>

        <div class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
            <span>{{ __('Sort by:') }}</span>
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
        <ul role="list" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
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
                    $patientName = trim($appointment->patient_first_name . ' ' . $appointment->patient_last_name);
                    $accountOwner = $appointment->user;
                    $accountOwnerName = $accountOwner?->name ?? __('Unknown');
                    $consultationType = $appointment->consultationType;
                @endphp

                <li
                    wire:key="appointment-{{ $appointment->id }}"
                    class="col-span-1 divide-y divide-gray-200 rounded-lg bg-white shadow dark:divide-white/10 dark:bg-zinc-800/50 dark:shadow-none dark:outline dark:outline-1 dark:-outline-offset-1 dark:outline-white/10"
                >
                    <div class="flex w-full items-start justify-between space-x-4 p-5">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="truncate text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $patientName }}
                                </h3>
                                <span @class([
                                    'inline-flex shrink-0 items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset',
                                    'bg-yellow-50 text-yellow-700 ring-yellow-600/20 dark:bg-yellow-500/10 dark:text-yellow-500 dark:ring-yellow-500/20' => $appointment->status === 'pending',
                                    'bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-500/10 dark:text-green-500 dark:ring-green-500/20' => in_array($appointment->status, ['approved', 'completed']),
                                    'bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-500/10 dark:text-blue-500 dark:ring-blue-500/20' => in_array($appointment->status, ['checked_in', 'in_progress']),
                                    'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-500/10 dark:text-red-500 dark:ring-red-500/20' => in_array($appointment->status, ['cancelled', 'no_show']),
                                ])>
                                    {{ $statusLabel }}
                                </span>
                            </div>

                            <div class="mt-2 space-y-1 text-sm text-gray-500 dark:text-gray-400">
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
                                @if($appointment->relationship_to_account !== 'self')
                                    <div class="flex items-center gap-2">
                                        <flux:icon name="user-group" class="h-4 w-4 shrink-0" />
                                        <span class="truncate text-xs">
                                            {{ __('Booked by') }}: {{ $accountOwnerName }} ({{ ucfirst($appointment->relationship_to_account) }})
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
                                <p class="mt-2 line-clamp-2 text-xs text-gray-600 dark:text-gray-300">
                                    {{ $appointment->chief_complaints }}
                                </p>
                            @endif

                            <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                                {{ __('Requested') }} {{ $appointment->created_at->diffForHumans() }}
                            </p>
                        </div>

                        <div class="shrink-0">
                            @php
                                $shortName = $consultationType?->short_name ?? '?';
                                $bgColors = [
                                    'O' => 'bg-pink-100 text-pink-700 dark:bg-pink-900/30 dark:text-pink-400',
                                    'P' => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-400',
                                    'G' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                ];
                                $bgClass = $bgColors[$shortName] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
                            @endphp
                            <div class="flex h-10 w-10 items-center justify-center rounded-full {{ $bgClass }} text-sm font-bold">
                                {{ $shortName }}
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="-mt-px flex divide-x divide-gray-200 dark:divide-white/10">
                            <div class="flex w-0 flex-1">
                                <a
                                    href="{{ route('nurse.appointments.show', $appointment) }}"
                                    wire:navigate
                                    class="relative -mr-px inline-flex w-0 flex-1 items-center justify-center gap-x-2 rounded-bl-lg border border-transparent py-3 text-sm font-semibold text-gray-900 hover:bg-gray-50 dark:text-white dark:hover:bg-white/5"
                                >
                                    <flux:icon name="eye" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                                    {{ __('View') }}
                                </a>
                            </div>
                            @if($appointment->patient_phone)
                                <div class="-ml-px flex w-0 flex-1">
                                    <a
                                        href="tel:{{ $appointment->patient_phone }}"
                                        class="relative inline-flex w-0 flex-1 items-center justify-center gap-x-2 rounded-br-lg border border-transparent py-3 text-sm font-semibold text-gray-900 hover:bg-gray-50 dark:text-white dark:hover:bg-white/5"
                                    >
                                        <flux:icon name="phone" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                                        {{ __('Call') }}
                                    </a>
                                </div>
                            @elseif($accountOwner?->email)
                                <div class="-ml-px flex w-0 flex-1">
                                    <a
                                        href="mailto:{{ $accountOwner->email }}"
                                        class="relative inline-flex w-0 flex-1 items-center justify-center gap-x-2 rounded-br-lg border border-transparent py-3 text-sm font-semibold text-gray-900 hover:bg-gray-50 dark:text-white dark:hover:bg-white/5"
                                    >
                                        <flux:icon name="envelope" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                                        {{ __('Email') }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>

        <div class="mt-6">
            {{ $appointments->links() }}
        </div>
    @else
        <div class="rounded-xl border border-zinc-200/70 bg-white p-8 text-center shadow-sm dark:border-zinc-800 dark:bg-zinc-900 space-y-4">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                <flux:icon name="calendar-days" class="h-8 w-8 text-zinc-400" />
            </div>
            <flux:heading size="lg" level="2">{{ __('No appointments found') }}</flux:heading>
            <flux:text variant="subtle" class="text-sm">
                @if($search || $consultationTypeFilter || $dateFilter)
                    {{ __('Try adjusting your search terms or filters.') }}
                @elseif($status === 'pending')
                    {{ __('No pending appointment requests at this time.') }}
                @elseif($status === 'today')
                    {{ __('No appointments scheduled for today.') }}
                @else
                    {{ __('No appointments match the selected criteria.') }}
                @endif
            </flux:text>

            @if($search || $consultationTypeFilter || $dateFilter)
                <flux:button wire:click="clearFilters" variant="primary">
                    {{ __('Clear filters') }}
                </flux:button>
            @endif
        </div>
    @endif
</section>
