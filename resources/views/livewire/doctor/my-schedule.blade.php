<section class="space-y-6">
    {{-- ==================== HEADER ==================== --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-1">
            <flux:heading size="xl" level="1">{{ __('My Schedule') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('Manage your clinic schedules and view upcoming appointments.') }}
            </flux:text>
        </div>
        <div class="flex flex-wrap gap-2">
            <flux:dropdown>
                <flux:button variant="primary" icon="plus" icon-trailing="chevron-down">
                    {{ __('Add New') }}
                </flux:button>
                <flux:menu>
                    <flux:menu.item wire:click="openAddScheduleModal" icon="calendar-days">
                        {{ __('Weekly Schedule') }}
                    </flux:menu.item>
                    <flux:menu.separator />
                    <flux:menu.heading>{{ __('Quick Exception') }}</flux:menu.heading>
                    <flux:menu.item wire:click="openAddExceptionModal('annual_leave')" icon="briefcase">
                        {{ __('Annual Leave') }}
                    </flux:menu.item>
                    <flux:menu.item wire:click="openAddExceptionModal('sick_leave')" icon="heart">
                        {{ __('Sick Leave') }}
                    </flux:menu.item>
                    <flux:menu.item wire:click="openAddExceptionModal('extra_clinic')" icon="plus-circle">
                        {{ __('Extra Clinic Day') }}
                    </flux:menu.item>
                    <flux:menu.separator />
                    <flux:menu.item wire:click="openAddExceptionModal" icon="adjustments-horizontal">
                        {{ __('Custom Exception') }}
                    </flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </div>
    </div>

    {{-- ==================== VIEW MODE TABS ==================== --}}
    <div class="flex flex-wrap items-center gap-2 border-b border-zinc-200 pb-4 dark:border-zinc-700">
        <flux:button
            wire:click="setViewMode('overview')"
            :variant="$viewMode === 'overview' ? 'filled' : 'ghost'"
            size="sm"
            icon="squares-2x2"
        >
            {{ __('Overview') }}
        </flux:button>
        <flux:button
            wire:click="setViewMode('weekly')"
            :variant="$viewMode === 'weekly' ? 'filled' : 'ghost'"
            size="sm"
            icon="calendar-days"
        >
            {{ __('Weekly Schedule') }}
        </flux:button>
        <flux:button
            wire:click="setViewMode('appointments')"
            :variant="$viewMode === 'appointments' ? 'filled' : 'ghost'"
            size="sm"
            icon="clipboard-document-list"
        >
            {{ __('Upcoming Appointments') }}
            @if($this->upcomingAppointments->count() > 0)
                <flux:badge size="sm" color="blue" class="ml-1">{{ $this->upcomingAppointments->count() }}</flux:badge>
            @endif
        </flux:button>
    </div>

    {{-- ==================== OVERVIEW VIEW ==================== --}}
    @if($viewMode === 'overview')
        {{-- Stats Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Today's Status --}}
            @php
                $todaySchedule = $this->getScheduleForDay(now());
                $hasScheduleToday = $todaySchedule['regular']->isNotEmpty() && !$todaySchedule['isDayOff'];
            @endphp
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg {{ $hasScheduleToday ? 'bg-success/20' : 'bg-zinc-100 dark:bg-zinc-800' }}">
                        <flux:icon name="calendar" class="h-5 w-5 {{ $hasScheduleToday ? 'text-success' : 'text-zinc-600 dark:text-zinc-400' }}" />
                    </div>
                    <div>
                        <p class="text-2xl font-semibold text-zinc-900 dark:text-white">
                            {{ $hasScheduleToday ? __('On Duty') : __('Off') }}
                        </p>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Today') }}</p>
                    </div>
                </div>
                @if($todaySchedule['isDayOff'] && $todaySchedule['dayOffReason'])
                    <p class="mt-2 text-xs text-warning">
                        <flux:icon name="information-circle" class="inline h-3 w-3" />
                        {{ $todaySchedule['dayOffReason'] }}
                    </p>
                @endif
            </div>

            {{-- Weekly Schedule Count --}}
            @php
                $weeklyScheduleCount = $this->schedules->where('schedule_type', 'regular')->count();
            @endphp
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/20">
                        <flux:icon name="calendar-days" class="h-5 w-5 text-primary" />
                    </div>
                    <div>
                        <p class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $weeklyScheduleCount }}</p>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Weekly Slots') }}</p>
                    </div>
                </div>
            </div>

            {{-- Upcoming Appointments --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-warning/20">
                        <flux:icon name="users" class="h-5 w-5 text-warning" />
                    </div>
                    <div>
                        <p class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $this->upcomingAppointments->count() }}</p>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Upcoming') }}</p>
                    </div>
                </div>
                <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">{{ __('Approved appointments') }}</p>
            </div>

            {{-- Exceptions This Week --}}
            @php
                $exceptionsThisWeek = $this->schedules->where('schedule_type', 'exception')->count();
            @endphp
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg {{ $exceptionsThisWeek > 0 ? 'bg-info/20 dark:bg-info/30' : 'bg-zinc-100 dark:bg-zinc-800' }}">
                        <flux:icon name="adjustments-horizontal" class="h-5 w-5 {{ $exceptionsThisWeek > 0 ? 'text-info' : 'text-zinc-600 dark:text-zinc-400' }}" />
                    </div>
                    <div>
                        <p class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $exceptionsThisWeek }}</p>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Exceptions') }}</p>
                    </div>
                </div>
                <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">{{ __('This week') }}</p>
            </div>
        </div>

        {{-- This Week's Calendar --}}
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="sm">{{ __('This Week\'s Schedule') }}</flux:heading>
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ Carbon\Carbon::parse($weekStart)->format('M d') }} - {{ Carbon\Carbon::parse($weekStart)->addDays(6)->format('M d, Y') }}
                        </flux:text>
                    </div>
                    <flux:button wire:click="setViewMode('weekly')" size="xs" variant="ghost" icon-trailing="arrow-right">
                        {{ __('View Details') }}
                    </flux:button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <div class="inline-flex min-w-full gap-0 divide-x divide-zinc-200 dark:divide-zinc-700">
                    @foreach($weekDays as $day)
                        @php
                            $daySchedule = $this->getScheduleForDay($day);
                            $isToday = $day->isToday();
                            $isPast = $day->isPast() && !$isToday;
                        @endphp
                        <div wire:key="cal-{{ $day->format('Y-m-d') }}"
                             class="flex min-w-[120px] flex-1 flex-col {{ $isPast ? 'opacity-50' : '' }} {{ $isToday ? 'bg-primary/5' : '' }}">
                            {{-- Day Header --}}
                            <div class="border-b border-zinc-200 px-3 py-2 text-center dark:border-zinc-700 {{ $isToday ? 'bg-primary/10' : 'bg-zinc-50 dark:bg-zinc-800' }}">
                                <p class="text-xs font-medium {{ $isToday ? 'text-primary' : 'text-zinc-500 dark:text-zinc-400' }}">
                                    {{ $day->format('D') }}
                                </p>
                                <p class="text-lg font-semibold {{ $isToday ? 'text-primary' : 'text-zinc-900 dark:text-white' }}">
                                    {{ $day->format('d') }}
                                </p>
                                @if($isToday)
                                    <flux:badge size="sm" color="blue" class="mt-1">{{ __('Today') }}</flux:badge>
                                @endif
                            </div>

                            {{-- Day Content --}}
                            <div class="flex-1 space-y-1 p-2">
                                @if($daySchedule['isDayOff'])
                                    <div class="rounded-lg border border-destructive/30 bg-destructive/10 p-2 text-center dark:bg-destructive/20">
                                        <flux:icon name="x-circle" class="mx-auto h-4 w-4 text-destructive" />
                                        <p class="mt-1 text-xs font-medium text-destructive">{{ __('Day Off') }}</p>
                                        @if($daySchedule['dayOffReason'])
                                            <p class="mt-0.5 text-[10px] text-destructive">{{ Str::limit($daySchedule['dayOffReason'], 20) }}</p>
                                        @endif
                                    </div>
                                @elseif($daySchedule['regular']->isNotEmpty())
                                    @foreach($daySchedule['regular'] as $schedule)
                                        <div class="rounded-lg border border-success/30 bg-success/10 p-2 dark:bg-success/20">
                                            <div class="flex items-center gap-1">
                                                <span class="h-2 w-2 rounded-full bg-success"></span>
                                                <span class="truncate text-xs font-medium text-success" title="{{ $schedule->consultationType?->name }}">
                                                    {{ $schedule->consultationType?->short_name }}
                                                </span>
                                            </div>
                                            @if($schedule->start_time && $schedule->end_time)
                                                <p class="mt-1 text-[10px] text-success">
                                                    {{ Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }}
                                                </p>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <div class="flex h-16 items-center justify-center text-zinc-400 dark:text-zinc-500">
                                        <p class="text-xs">{{ __('No schedule') }}</p>
                                    </div>
                                @endif

                                {{-- Exceptions (available) --}}
                                @foreach($daySchedule['exceptions']->where('is_available', true) as $exception)
                                    <div class="rounded-lg border border-info/30 bg-info/10 p-2 dark:bg-info/20">
                                        <div class="flex items-center gap-1">
                                            <span class="h-2 w-2 rounded-full bg-info"></span>
                                            <span class="truncate text-xs font-medium text-info">
                                                {{ $exception->reason ?: __('Extra') }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            {{-- Legend --}}
            <div class="flex flex-wrap items-center gap-4 border-t border-zinc-200 px-4 py-2 text-xs dark:border-zinc-700">
                <span class="font-medium text-zinc-600 dark:text-zinc-400">{{ __('Legend:') }}</span>
                <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-success"></span> {{ __('Available') }}</span>
                <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-info"></span> {{ __('Extra/Modified') }}</span>
                <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-destructive"></span> {{ __('Day Off') }}</span>
            </div>
        </div>

        {{-- My Weekly Schedules --}}
        @if($this->weeklySchedules->isNotEmpty())
            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    <flux:heading size="sm">{{ __('My Weekly Schedules') }}</flux:heading>
                </div>
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach($this->weeklySchedules as $typeId => $schedules)
                        @php
                            $consultationType = $schedules->first()->consultationType;
                            $activeDays = $schedules->pluck('day_of_week')->map(fn($d) => substr($dayNames[(int)$d], 0, 3))->join(', ');
                            $firstSchedule = $schedules->first();
                            $timeRange = '';
                            if ($firstSchedule->start_time && $firstSchedule->end_time) {
                                $timeRange = Carbon\Carbon::parse($firstSchedule->start_time)->format('g:i A') . ' - ' . Carbon\Carbon::parse($firstSchedule->end_time)->format('g:i A');
                            }
                        @endphp
                        <div wire:key="my-schedule-{{ $typeId }}" class="flex items-center justify-between px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-success/20">
                                    <flux:icon name="calendar-days" class="h-5 w-5 text-success" />
                                </div>
                                <div>
                                    <p class="font-medium text-zinc-900 dark:text-white">{{ $consultationType?->name }}</p>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $activeDays }}
                                        @if($timeRange)
                                            &bull; {{ $timeRange }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="flex gap-1">
                                <flux:button wire:click="openEditScheduleModal({{ $typeId }})" size="xs" variant="ghost" icon="pencil" />
                                <flux:button wire:click="confirmDeleteSchedule({{ $typeId }})" size="xs" variant="ghost" icon="trash" class="text-destructive hover:text-destructive/80" />
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="rounded-xl border border-dashed border-warning/50 bg-warning/10 p-4 dark:bg-warning/20">
                <div class="flex items-start gap-3">
                    <flux:icon name="exclamation-triangle" class="h-5 w-5 text-warning" />
                    <div>
                        <p class="font-medium text-warning-foreground dark:text-warning">{{ __('No Weekly Schedule Set') }}</p>
                        <p class="mt-1 text-sm text-warning">
                            {{ __('You haven\'t set up your weekly schedule yet. Add a schedule to receive patient appointments.') }}
                        </p>
                        <flux:button wire:click="openAddScheduleModal" size="sm" variant="filled" class="mt-3" icon="plus">
                            {{ __('Add Weekly Schedule') }}
                        </flux:button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Upcoming Exceptions --}}
        @if($this->exceptions->isNotEmpty())
            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    <flux:heading size="sm">{{ __('Upcoming Exceptions') }}</flux:heading>
                </div>
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach($this->exceptions->take(5) as $exception)
                        <div wire:key="exception-{{ $exception->id }}" class="flex items-center justify-between px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 flex-col items-center justify-center rounded-lg {{ $exception->is_available ? 'bg-success/20' : 'bg-destructive/20' }}">
                                    <span class="text-xs font-semibold {{ $exception->is_available ? 'text-success' : 'text-destructive' }}">
                                        {{ $exception->date->format('d') }}
                                    </span>
                                    <span class="text-[10px] {{ $exception->is_available ? 'text-success' : 'text-destructive' }}">
                                        {{ $exception->date->format('M') }}
                                    </span>
                                </div>
                                <div>
                                    <p class="font-medium text-zinc-900 dark:text-white">
                                        {{ $exception->reason ?: ($exception->is_available ? __('Extra Clinic Day') : __('Day Off')) }}
                                    </p>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $exception->consultationType?->name }}
                                        &bull; {{ $exception->date->format('l') }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($exception->is_available)
                                    <flux:badge size="sm" color="green">{{ __('Available') }}</flux:badge>
                                @else
                                    <flux:badge size="sm" color="red">{{ __('Off') }}</flux:badge>
                                @endif
                                <flux:button wire:click="openEditExceptionModal({{ $exception->id }})" size="xs" variant="ghost" icon="pencil" />
                                <flux:button wire:click="confirmDeleteException({{ $exception->id }})" size="xs" variant="ghost" icon="trash" class="text-destructive" />
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Upcoming Appointments Preview --}}
        @if($this->upcomingAppointments->isNotEmpty())
            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    <div class="flex items-center justify-between">
                        <flux:heading size="sm">{{ __('Upcoming Appointments') }}</flux:heading>
                        <flux:button wire:click="setViewMode('appointments')" size="xs" variant="ghost" icon-trailing="arrow-right">
                            {{ __('View All') }}
                        </flux:button>
                    </div>
                </div>
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach($this->upcomingAppointments->take(5) as $appointment)
                        <div wire:key="upcoming-{{ $appointment->id }}" class="flex items-center justify-between px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 flex-col items-center justify-center rounded-lg bg-primary/20">
                                    <span class="text-sm font-semibold text-primary">
                                        {{ $appointment->appointment_date->format('d') }}
                                    </span>
                                    <span class="text-[10px] text-primary">
                                        {{ $appointment->appointment_date->format('M') }}
                                    </span>
                                </div>
                                <div>
                                    <p class="font-medium text-zinc-900 dark:text-white">
                                        {{ $appointment->patient_first_name }} {{ $appointment->patient_last_name }}
                                    </p>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $appointment->consultationType?->name }}
                                        @if($appointment->appointment_time)
                                            &bull; {{ $appointment->appointment_time->format('g:i A') }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            @if($appointment->queue)
                                <flux:badge size="sm" color="blue">{{ $appointment->queue->formatted_number }}</flux:badge>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif

    {{-- ==================== WEEKLY SCHEDULE VIEW ==================== --}}
    @if($viewMode === 'weekly')
        {{-- Week Navigation --}}
        <div class="flex items-center justify-between rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:button wire:click="previousWeek" variant="ghost" icon="chevron-left" />
            <div class="text-center">
                <p class="font-medium text-zinc-900 dark:text-white">
                    {{ Carbon\Carbon::parse($weekStart)->format('M d') }} - {{ Carbon\Carbon::parse($weekStart)->addDays(6)->format('M d, Y') }}
                </p>
                @if(Carbon\Carbon::parse($weekStart)->isCurrentWeek())
                    <p class="text-xs text-success">{{ __('Current Week') }}</p>
                @endif
            </div>
            <div class="flex gap-2">
                <flux:button wire:click="goToToday" variant="ghost" size="sm">{{ __('Today') }}</flux:button>
                <flux:button wire:click="nextWeek" variant="ghost" icon="chevron-right" />
            </div>
        </div>

        {{-- Week Calendar Grid --}}
        <div class="grid gap-3 sm:grid-cols-7">
            @foreach($weekDays as $day)
                @php
                    $daySchedule = $this->getScheduleForDay($day);
                    $isToday = $day->isToday();
                    $isPast = $day->isPast() && !$isToday;
                @endphp
                <div wire:key="week-{{ $day->format('Y-m-d') }}"
                     class="rounded-xl border {{ $isToday ? 'border-primary ring-2 ring-primary/30 dark:border-primary dark:ring-primary/20' : 'border-zinc-200 dark:border-zinc-700' }} {{ $isPast ? 'opacity-50' : '' }} bg-white dark:bg-zinc-900">
                    {{-- Day Header --}}
                    <div class="border-b border-zinc-200 p-3 text-center dark:border-zinc-700 {{ $isToday ? 'bg-primary/10 dark:bg-primary/20' : '' }}">
                        <p class="text-xs font-medium uppercase {{ $isToday ? 'text-primary' : 'text-zinc-500 dark:text-zinc-400' }}">
                            {{ $day->format('D') }}
                        </p>
                        <p class="text-2xl font-bold {{ $isToday ? 'text-primary' : 'text-zinc-900 dark:text-white' }}">
                            {{ $day->format('d') }}
                        </p>
                        @if($isToday)
                            <flux:badge size="sm" color="blue">{{ __('Today') }}</flux:badge>
                        @endif
                    </div>

                    {{-- Day Content --}}
                    <div class="min-h-32 p-3">
                        @if($daySchedule['isDayOff'])
                            <div class="flex h-full flex-col items-center justify-center rounded-lg bg-destructive/10 p-3 text-center dark:bg-destructive/20">
                                <flux:icon name="x-circle" class="h-6 w-6 text-destructive" />
                                <p class="mt-2 text-sm font-medium text-destructive">{{ __('Day Off') }}</p>
                                @if($daySchedule['dayOffReason'])
                                    <p class="mt-1 text-xs text-destructive">{{ $daySchedule['dayOffReason'] }}</p>
                                @endif
                            </div>
                        @elseif($daySchedule['regular']->isNotEmpty())
                            <div class="space-y-2">
                                @foreach($daySchedule['regular'] as $schedule)
                                    <div class="rounded-lg bg-success/10 p-2 dark:bg-success/20">
                                        <p class="text-xs font-semibold text-success">
                                            {{ $schedule->consultationType?->name }}
                                        </p>
                                        @if($schedule->start_time && $schedule->end_time)
                                            <p class="mt-1 flex items-center gap-1 text-xs text-success">
                                                <flux:icon name="clock" class="h-3 w-3" />
                                                {{ Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }} -
                                                {{ Carbon\Carbon::parse($schedule->end_time)->format('g:i A') }}
                                            </p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="flex h-full flex-col items-center justify-center py-4 text-center">
                                <flux:icon name="calendar" class="h-6 w-6 text-zinc-300 dark:text-zinc-600" />
                                <p class="mt-2 text-xs text-zinc-400 dark:text-zinc-500">{{ __('No schedule') }}</p>
                            </div>
                        @endif

                        {{-- Exceptions (available) --}}
                        @foreach($daySchedule['exceptions']->where('is_available', true) as $exception)
                            <div class="mt-2 rounded-lg bg-warning/10 p-2 dark:bg-warning/20">
                                <p class="text-xs font-medium text-warning-foreground dark:text-warning">
                                    {{ $exception->reason ?: __('Extra Clinic') }}
                                </p>
                                @if($exception->start_time)
                                    <p class="mt-1 text-xs text-warning">
                                        {{ Carbon\Carbon::parse($exception->start_time)->format('g:i A') }} -
                                        {{ Carbon\Carbon::parse($exception->end_time)->format('g:i A') }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Legend --}}
        <div class="flex flex-wrap gap-4 text-xs text-zinc-500">
            <div class="flex items-center gap-2">
                <span class="h-3 w-3 rounded bg-success/20"></span>
                {{ __('Regular Schedule') }}
            </div>
            <div class="flex items-center gap-2">
                <span class="h-3 w-3 rounded bg-warning/20"></span>
                {{ __('Exception/Special') }}
            </div>
            <div class="flex items-center gap-2">
                <span class="h-3 w-3 rounded bg-destructive/10 dark:bg-destructive/20"></span>
                {{ __('Day Off') }}
            </div>
        </div>
    @endif

    {{-- ==================== APPOINTMENTS VIEW ==================== --}}
    @if($viewMode === 'appointments')
        @if($this->upcomingAppointments->isNotEmpty())
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Date') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Time') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Patient') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Type') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Queue') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($this->upcomingAppointments as $appointment)
                                @php
                                    $isToday = $appointment->appointment_date->isToday();
                                    $isTomorrow = $appointment->appointment_date->isTomorrow();
                                @endphp
                                <tr wire:key="appt-{{ $appointment->id }}"
                                    class="{{ $isToday ? 'bg-primary/5' : '' }} hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-10 w-10 flex-col items-center justify-center rounded-lg bg-primary/20">
                                                <span class="text-sm font-semibold text-primary">
                                                    {{ $appointment->appointment_date->format('d') }}
                                                </span>
                                                <span class="text-[10px] text-primary">
                                                    {{ $appointment->appointment_date->format('M') }}
                                                </span>
                                            </div>
                                            <div>
                                                <p class="font-medium text-zinc-900 dark:text-white">{{ $appointment->appointment_date->format('l') }}</p>
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                                    @if($isToday)
                                                        <flux:badge size="sm" color="blue">{{ __('Today') }}</flux:badge>
                                                    @elseif($isTomorrow)
                                                        <flux:badge size="sm" color="amber">{{ __('Tomorrow') }}</flux:badge>
                                                    @else
                                                        {{ $appointment->appointment_date->format('Y') }}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                        @if($appointment->appointment_time)
                                            <span class="flex items-center gap-1">
                                                <flux:icon name="clock" class="h-4 w-4 text-zinc-400" />
                                                {{ $appointment->appointment_time->format('g:i A') }}
                                            </span>
                                        @else
                                            <span class="text-zinc-400">{{ __('Not set') }}</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <p class="font-medium text-zinc-900 dark:text-white">
                                            {{ $appointment->patient_first_name }} {{ $appointment->patient_last_name }}
                                        </p>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <flux:badge size="sm" color="zinc">{{ $appointment->consultationType?->name }}</flux:badge>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        @if($appointment->queue)
                                            <flux:badge size="sm" color="blue">{{ $appointment->queue->formatted_number }}</flux:badge>
                                        @else
                                            <span class="text-xs text-zinc-400">{{ __('Not queued') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            {{-- Empty State --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon name="calendar-days" class="h-8 w-8 text-zinc-400" />
                </div>
                <h3 class="mt-4 text-lg font-medium text-zinc-900 dark:text-white">{{ __('No upcoming appointments') }}</h3>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('You don\'t have any approved appointments scheduled.') }}
                </p>
            </div>
        @endif
    @endif

    {{-- ==================== ADD/EDIT SCHEDULE MODAL ==================== --}}
    <flux:modal wire:model="showScheduleModal" class="max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editScheduleTypeId ? __('Edit Weekly Schedule') : __('Add Weekly Schedule') }}</flux:heading>
                <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                    {{ __('Define which days and hours you are available for consultations.') }}
                </flux:text>
            </div>

            <div class="space-y-4">
                {{-- Consultation Type Selection --}}
                <flux:field>
                    <flux:label>{{ __('Consultation Type') }} <span class="text-destructive">*</span></flux:label>
                    <flux:select wire:model="scheduleConsultationType" placeholder="{{ __('Select consultation type') }}" :disabled="(bool) $editScheduleTypeId">
                        <flux:select.option value="">{{ __('Select consultation type') }}</flux:select.option>
                        @foreach($this->consultationTypes as $type)
                            <flux:select.option value="{{ $type->id }}">{{ $type->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="scheduleConsultationType" />
                </flux:field>

                {{-- Days Selection --}}
                <flux:field>
                    <flux:label>{{ __('Working Days') }} <span class="text-destructive">*</span></flux:label>
                    <p class="mb-2 text-xs text-zinc-500 dark:text-zinc-400">{{ __('Click to select the days when you work.') }}</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($dayNames as $dayNum => $dayName)
                            @php $isChecked = in_array($dayNum, $scheduleDays); @endphp
                            <label wire:key="day-{{ $dayNum }}" class="cursor-pointer">
                                <input type="checkbox" wire:model="scheduleDays" value="{{ $dayNum }}" class="peer sr-only">
                                <span class="inline-flex h-12 w-14 flex-col items-center justify-center rounded-xl border-2 text-sm font-medium transition-all
                                    peer-checked:border-success peer-checked:bg-success/10 peer-checked:text-success
                                    peer-focus:ring-2 peer-focus:ring-success peer-focus:ring-offset-2
                                    dark:peer-checked:border-success dark:peer-checked:bg-success/20 dark:peer-checked:text-success
                                    {{ !$isChecked ? 'border-zinc-200 bg-white text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:border-zinc-500' : '' }}">
                                    <span class="text-xs">{{ substr($dayName, 0, 3) }}</span>
                                    @if($isChecked)
                                        <flux:icon name="check" class="mt-0.5 h-3 w-3" />
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <flux:error name="scheduleDays" />
                </flux:field>

                {{-- Time Range --}}
                <div>
                    <flux:label class="mb-2">{{ __('Working Hours') }}</flux:label>
                    <div class="grid grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label class="text-xs">{{ __('Start Time') }}</flux:label>
                            <flux:input type="time" wire:model="scheduleStartTime" />
                            <flux:error name="scheduleStartTime" />
                        </flux:field>
                        <flux:field>
                            <flux:label class="text-xs">{{ __('End Time') }}</flux:label>
                            <flux:input type="time" wire:model="scheduleEndTime" />
                            <flux:error name="scheduleEndTime" />
                        </flux:field>
                    </div>
                    <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                        {{ __('Leave empty to use the clinic\'s default hours.') }}
                    </p>
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <flux:button wire:click="closeScheduleModal" variant="ghost">{{ __('Cancel') }}</flux:button>
                <flux:button wire:click="saveSchedule" variant="primary" icon="check">
                    {{ $editScheduleTypeId ? __('Update Schedule') : __('Save Schedule') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ==================== ADD/EDIT EXCEPTION MODAL ==================== --}}
    <flux:modal wire:model="showExceptionModal" class="max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editExceptionId ? __('Edit Exception') : __('Add Schedule Exception') }}</flux:heading>
                <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                    {{ __('Record leave, time off, or additional clinic days.') }}
                </flux:text>
            </div>

            <div class="space-y-4">
                {{-- Quick Preset Selection --}}
                @unless($editExceptionId)
                    <flux:field>
                        <flux:label>{{ __('Quick Fill (Optional)') }}</flux:label>
                        <flux:select wire:model.live="exceptionPreset" placeholder="{{ __('Select to auto-fill or leave empty') }}">
                            <flux:select.option value="">{{ __('— Select to auto-fill —') }}</flux:select.option>
                            <flux:select.option value="annual_leave">{{ __('Annual Leave') }}</flux:select.option>
                            <flux:select.option value="sick_leave">{{ __('Sick Leave') }}</flux:select.option>
                            <flux:select.option value="holiday">{{ __('Holiday') }}</flux:select.option>
                            <flux:select.option value="training">{{ __('Training/Seminar') }}</flux:select.option>
                            <flux:select.option value="emergency_leave">{{ __('Emergency Leave') }}</flux:select.option>
                            <flux:select.option value="half_day_am">{{ __('Half Day (Morning Off)') }}</flux:select.option>
                            <flux:select.option value="half_day_pm">{{ __('Half Day (Afternoon Off)') }}</flux:select.option>
                            <flux:select.option value="extra_clinic">{{ __('Extra Clinic Day') }}</flux:select.option>
                        </flux:select>
                    </flux:field>
                @endunless

                {{-- Consultation Type Selection --}}
                <flux:field>
                    <flux:label>{{ __('Consultation Type') }} <span class="text-destructive">*</span></flux:label>
                    <flux:select wire:model="exceptionConsultationType" placeholder="{{ __('Select consultation type') }}">
                        <flux:select.option value="">{{ __('Select consultation type') }}</flux:select.option>
                        @foreach($this->consultationTypes as $type)
                            <flux:select.option value="{{ $type->id }}">{{ $type->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="exceptionConsultationType" />
                </flux:field>

                {{-- Date Mode Toggle --}}
                @unless($editExceptionId)
                    <div class="flex items-center justify-between rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                        <div>
                            <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Multiple Days (Date Range)') }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $exceptionUseDateRange ? __('Select start and end dates') : __('Enable for vacation/leave period') }}
                            </p>
                        </div>
                        <flux:switch wire:model.live="exceptionUseDateRange" />
                    </div>
                @endunless

                {{-- Date Fields --}}
                @if($exceptionUseDateRange && !$editExceptionId)
                    <div class="grid grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>{{ __('From Date') }} <span class="text-destructive">*</span></flux:label>
                            <flux:input type="date" wire:model.live="exceptionDate" min="{{ now()->format('Y-m-d') }}" />
                            <flux:error name="exceptionDate" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('To Date') }} <span class="text-destructive">*</span></flux:label>
                            <flux:input type="date" wire:model.live="exceptionDateEnd" min="{{ $exceptionDate ?: now()->format('Y-m-d') }}" />
                            <flux:error name="exceptionDateEnd" />
                        </flux:field>
                    </div>
                    @if($this->dateRangeDaysCount > 0)
                        <div class="rounded-lg border border-primary/30 bg-primary/10 p-3 dark:bg-primary/20">
                            <p class="flex items-center gap-2 text-sm text-primary">
                                <flux:icon name="information-circle" class="h-5 w-5" />
                                {{ trans_choice('{1} This will create :count exception.|[2,*] This will create :count exceptions.', $this->dateRangeDaysCount, ['count' => $this->dateRangeDaysCount]) }}
                            </p>
                        </div>
                    @endif
                @else
                    <flux:field>
                        <flux:label>{{ __('Date') }} <span class="text-destructive">*</span></flux:label>
                        <flux:input type="date" wire:model="exceptionDate" min="{{ now()->format('Y-m-d') }}" />
                        <flux:error name="exceptionDate" />
                    </flux:field>
                @endif

                {{-- Availability Toggle --}}
                <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <flux:label>{{ __('Availability Status') }}</flux:label>
                            <p class="mt-1 text-sm {{ $exceptionIsAvailable ? 'text-success' : 'text-destructive' }}">
                                @if($exceptionIsAvailable)
                                    <flux:icon name="check-circle" class="inline h-4 w-4" />
                                    {{ __('I will be AVAILABLE on this day') }}
                                @else
                                    <flux:icon name="x-circle" class="inline h-4 w-4" />
                                    {{ __('I will NOT be available on this day') }}
                                @endif
                            </p>
                        </div>
                        <flux:switch wire:model.live="exceptionIsAvailable" />
                    </div>
                </div>

                {{-- Time Range (only if available) --}}
                @if($exceptionIsAvailable)
                    <div>
                        <flux:label class="mb-2">{{ __('Working Hours for This Day') }}</flux:label>
                        <div class="grid grid-cols-2 gap-4">
                            <flux:field>
                                <flux:label class="text-xs">{{ __('Start Time') }}</flux:label>
                                <flux:input type="time" wire:model="exceptionStartTime" />
                                <flux:error name="exceptionStartTime" />
                            </flux:field>
                            <flux:field>
                                <flux:label class="text-xs">{{ __('End Time') }}</flux:label>
                                <flux:input type="time" wire:model="exceptionEndTime" />
                                <flux:error name="exceptionEndTime" />
                            </flux:field>
                        </div>
                    </div>
                @endif

                {{-- Reason --}}
                <flux:field>
                    <flux:label>{{ __('Reason / Notes') }}</flux:label>
                    <flux:input wire:model="exceptionReason" placeholder="{{ $exceptionIsAvailable ? __('e.g., Special weekend clinic') : __('e.g., Annual leave, Medical appointment') }}" />
                    <flux:error name="exceptionReason" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-3 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <flux:button wire:click="closeExceptionModal" variant="ghost">{{ __('Cancel') }}</flux:button>
                <flux:button wire:click="saveException" variant="primary" icon="check">
                    @if($editExceptionId)
                        {{ __('Update Exception') }}
                    @elseif($exceptionUseDateRange && $this->dateRangeDaysCount > 1)
                        {{ __('Create :count Exceptions', ['count' => $this->dateRangeDaysCount]) }}
                    @else
                        {{ __('Save Exception') }}
                    @endif
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ==================== DELETE CONFIRMATION MODAL ==================== --}}
    <flux:modal wire:model="showDeleteModal" class="max-w-md">
        <div class="space-y-6">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-destructive/20">
                    <flux:icon name="trash" class="h-6 w-6 text-destructive" />
                </div>
                <div>
                    <flux:heading size="lg">{{ __('Delete Confirmation') }}</flux:heading>
                    <flux:text class="mt-2 text-zinc-500 dark:text-zinc-400">
                        @if(str_starts_with($deleteType, 'schedule:'))
                            {{ __('Are you sure you want to delete this weekly schedule? This will remove all the working days for this consultation type.') }}
                        @else
                            {{ __('Are you sure you want to delete this exception? This action cannot be undone.') }}
                        @endif
                    </flux:text>
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <flux:button wire:click="closeDeleteModal" variant="ghost">{{ __('Cancel') }}</flux:button>
                <flux:button wire:click="deleteConfirmed" variant="danger" icon="trash">
                    {{ __('Yes, Delete') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</section>
