<section class="space-y-6">
    {{-- ==================== HEADER ==================== --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-1">
            <flux:heading size="xl" level="1">{{ __('Doctor Schedules') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('Manage doctor availability, working hours, and time-off requests.') }}
            </flux:text>
        </div>
        <div class="flex flex-wrap gap-2">
            <flux:button wire:click="openCopyModal" variant="ghost" icon="document-duplicate" size="sm">
                {{ __('Copy Schedule') }}
            </flux:button>
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
                    <flux:menu.item wire:click="openAddExceptionModal('holiday')" icon="star">
                        {{ __('Holiday') }}
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
            {{ __('Weekly Schedules') }}
            @if($stats['weekly_schedules'] > 0)
                <flux:badge size="sm" color="zinc" class="ml-1">{{ $stats['weekly_schedules'] }}</flux:badge>
            @endif
        </flux:button>
        <flux:button
            wire:click="setViewMode('exceptions')"
            :variant="$viewMode === 'exceptions' ? 'filled' : 'ghost'"
            size="sm"
            icon="calendar"
        >
            {{ __('Exceptions') }}
            @if($stats['total_exceptions'] > 0)
                <flux:badge size="sm" color="amber" class="ml-1">{{ $stats['total_exceptions'] }}</flux:badge>
            @endif
        </flux:button>
    </div>

    {{-- ==================== OVERVIEW VIEW ==================== --}}
    @if($viewMode === 'overview')
        {{-- Stats Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Total Doctors --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                        <flux:icon name="users" class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <p class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $stats['total_doctors'] }}</p>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Total Doctors') }}</p>
                    </div>
                </div>
            </div>

            {{-- Doctors with Schedules --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                        <flux:icon name="check-circle" class="h-5 w-5 text-green-600 dark:text-green-400" />
                    </div>
                    <div>
                        <p class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $stats['doctors_with_schedule'] }}</p>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('With Schedules') }}</p>
                    </div>
                </div>
                @if($stats['doctors_without_schedule'] > 0)
                    <p class="mt-2 text-xs text-amber-600 dark:text-amber-400">
                        <flux:icon name="exclamation-triangle" class="inline h-3 w-3" />
                        {{ $stats['doctors_without_schedule'] }} {{ __('doctor(s) need schedules') }}
                    </p>
                @endif
            </div>

            {{-- Upcoming Leaves --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/30">
                        <flux:icon name="clock" class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                    </div>
                    <div>
                        <p class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $stats['upcoming_leaves'] }}</p>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Upcoming Leaves') }}</p>
                    </div>
                </div>
                <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">{{ __('Next 30 days') }}</p>
            </div>

            {{-- Today's Changes --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg {{ $stats['today_exceptions'] > 0 ? 'bg-red-100 dark:bg-red-900/30' : 'bg-zinc-100 dark:bg-zinc-800' }}">
                        <flux:icon name="calendar" class="h-5 w-5 {{ $stats['today_exceptions'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-zinc-600 dark:text-zinc-400' }}" />
                    </div>
                    <div>
                        <p class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $stats['today_exceptions'] }}</p>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Today\'s Exceptions') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- This Week's Calendar --}}
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="sm">{{ __('This Week\'s Schedule') }}</flux:heading>
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ now()->startOfWeek(\Carbon\Carbon::MONDAY)->format('M d') }} - {{ now()->endOfWeek(\Carbon\Carbon::SUNDAY)->format('M d, Y') }}
                        </flux:text>
                    </div>
                    <flux:button wire:click="setViewMode('weekly')" size="xs" variant="ghost" icon-trailing="arrow-right">
                        {{ __('View All') }}
                    </flux:button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <div class="inline-flex min-w-full gap-0 divide-x divide-zinc-200 dark:divide-zinc-700">
                    @foreach($weekCalendar as $day)
                        <div wire:key="cal-{{ $day['date']->format('Y-m-d') }}"
                             class="flex min-w-[140px] flex-1 flex-col {{ $day['isPast'] ? 'opacity-50' : '' }} {{ $day['isToday'] ? 'bg-blue-50/50 dark:bg-blue-900/10' : '' }}">
                            {{-- Day Header --}}
                            <div class="border-b border-zinc-200 px-3 py-2 text-center dark:border-zinc-700 {{ $day['isToday'] ? 'bg-blue-100/50 dark:bg-blue-900/20' : 'bg-zinc-50 dark:bg-zinc-800' }}">
                                <p class="text-xs font-medium {{ $day['isToday'] ? 'text-blue-600 dark:text-blue-400' : 'text-zinc-500 dark:text-zinc-400' }}">
                                    {{ $day['dayShort'] }}
                                </p>
                                <p class="text-lg font-semibold {{ $day['isToday'] ? 'text-blue-700 dark:text-blue-300' : 'text-zinc-900 dark:text-white' }}">
                                    {{ $day['date']->format('d') }}
                                </p>
                                @if($day['isToday'])
                                    <flux:badge size="sm" color="blue" class="mt-1">{{ __('Today') }}</flux:badge>
                                @endif
                            </div>

                            {{-- Doctors for this day --}}
                            <div class="flex-1 space-y-1 p-2">
                                @forelse($day['doctors'] as $doctorData)
                                    @php $doctor = $doctorData['doctor']; @endphp
                                    <div wire:key="cal-doc-{{ $day['date']->format('Y-m-d') }}-{{ $doctor->id }}"
                                         class="rounded-lg border border-zinc-200 bg-white p-2 text-xs dark:border-zinc-700 dark:bg-zinc-800">
                                        <p class="truncate font-medium text-zinc-900 dark:text-white" title="{{ $doctor->name }}">
                                            {{ $doctor->name }}
                                        </p>
                                        @foreach($doctorData['types'] as $typeData)
                                            <div class="mt-1 flex items-center gap-1">
                                                @if($typeData['has_exception'])
                                                    @if($typeData['is_available'])
                                                        @if($typeData['is_extra'] ?? false)
                                                            <span class="h-2 w-2 rounded-full bg-purple-500" title="{{ __('Extra Day') }}"></span>
                                                        @else
                                                            <span class="h-2 w-2 rounded-full bg-amber-500" title="{{ __('Modified') }}"></span>
                                                        @endif
                                                    @else
                                                        <span class="h-2 w-2 rounded-full bg-red-500" title="{{ __('Not Available') }}"></span>
                                                    @endif
                                                @else
                                                    <span class="h-2 w-2 rounded-full bg-green-500" title="{{ __('Available') }}"></span>
                                                @endif
                                                <span class="truncate text-zinc-600 dark:text-zinc-300" title="{{ $typeData['type']->name }}">
                                                    {{ Str::limit($typeData['type']->name, 12) }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                @empty
                                    <div class="flex h-16 items-center justify-center text-zinc-400 dark:text-zinc-500">
                                        <p class="text-xs">{{ __('No doctors') }}</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            {{-- Legend --}}
            <div class="flex flex-wrap items-center gap-4 border-t border-zinc-200 px-4 py-2 text-xs dark:border-zinc-700">
                <span class="font-medium text-zinc-600 dark:text-zinc-400">{{ __('Legend:') }}</span>
                <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-green-500"></span> {{ __('Available') }}</span>
                <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-amber-500"></span> {{ __('Modified Hours') }}</span>
                <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-purple-500"></span> {{ __('Extra Day') }}</span>
                <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-red-500"></span> {{ __('Not Available') }}</span>
            </div>
        </div>

        {{-- Upcoming Exceptions --}}
        @if($upcomingExceptions->isNotEmpty())
            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    <div class="flex items-center justify-between">
                        <flux:heading size="sm">{{ __('Upcoming Schedule Changes') }}</flux:heading>
                        <flux:button wire:click="setViewMode('exceptions')" size="xs" variant="ghost" icon-trailing="arrow-right">
                            {{ __('View All') }}
                        </flux:button>
                    </div>
                </div>
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach($upcomingExceptions as $exception)
                        <div wire:key="upcoming-{{ $exception->id }}" class="flex items-center justify-between px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 flex-col items-center justify-center rounded-lg {{ $exception->is_available ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30' }}">
                                    <span class="text-xs font-medium {{ $exception->is_available ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ $exception->date->format('d') }}
                                    </span>
                                    <span class="text-[10px] {{ $exception->is_available ? 'text-green-500 dark:text-green-500' : 'text-red-500 dark:text-red-500' }}">
                                        {{ $exception->date->format('M') }}
                                    </span>
                                </div>
                                <div>
                                    <p class="font-medium text-zinc-900 dark:text-white">{{ $exception->doctor?->name }}</p>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $exception->reason ?: ($exception->is_available ? __('Extra Clinic Day') : __('Not Available')) }}
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
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    @endif

    {{-- ==================== WEEKLY SCHEDULE VIEW ==================== --}}
    @if($viewMode === 'weekly')
        {{-- Doctors Without Schedules Alert --}}
        @if($doctorsWithoutSchedule->isNotEmpty())
            <div class="rounded-xl border border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/20">
                <div class="border-b border-amber-200 px-4 py-3 dark:border-amber-800">
                    <div class="flex items-center gap-2">
                        <flux:icon name="exclamation-triangle" class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                        <flux:heading size="sm" class="text-amber-800 dark:text-amber-200">
                            {{ trans_choice('{1} :count Doctor Without Schedule|[2,*] :count Doctors Without Schedules', $doctorsWithoutSchedule->count(), ['count' => $doctorsWithoutSchedule->count()]) }}
                        </flux:heading>
                    </div>
                    <p class="mt-1 text-sm text-amber-700 dark:text-amber-300">
                        {{ __('These doctors cannot receive appointments until their schedules are set up.') }}
                    </p>
                </div>
                <div class="divide-y divide-amber-200 dark:divide-amber-800">
                    @foreach($doctorsWithoutSchedule as $doctor)
                        <div wire:key="no-schedule-{{ $doctor->id }}" class="flex items-center justify-between px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/40">
                                    <flux:icon name="user" class="h-4 w-4 text-amber-600 dark:text-amber-400" />
                                </div>
                                <div>
                                    <p class="font-medium text-zinc-900 dark:text-white">{{ $doctor->name }}</p>
                                    @if($doctor->consultationTypes->isNotEmpty())
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $doctor->consultationTypes->pluck('name')->join(', ') }}
                                        </p>
                                    @else
                                        <p class="text-xs text-red-500 dark:text-red-400">
                                            {{ __('No consultation types assigned') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <flux:button wire:click="openAddScheduleModal({{ $doctor->id }})" size="sm" variant="filled" icon="plus">
                                {{ __('Add Schedule') }}
                            </flux:button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Filters --}}
        <div class="flex flex-wrap items-end gap-3">
            <div class="w-full sm:w-48">
                <flux:select wire:model.live="doctorFilter" placeholder="{{ __('All doctors') }}">
                    <flux:select.option value="">{{ __('All doctors') }}</flux:select.option>
                    @foreach($doctors as $doctor)
                        <flux:select.option value="{{ $doctor->id }}">{{ $doctor->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            <div class="w-full sm:w-48">
                <flux:select wire:model.live="consultationTypeFilter" placeholder="{{ __('All types') }}">
                    <flux:select.option value="">{{ __('All types') }}</flux:select.option>
                    @foreach($consultationTypes as $type)
                        <flux:select.option value="{{ $type->id }}">{{ $type->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            @if($doctorFilter || $consultationTypeFilter)
                <flux:button wire:click="$set('doctorFilter', ''); $set('consultationTypeFilter', '')" size="sm" variant="ghost" icon="x-mark">
                    {{ __('Clear Filters') }}
                </flux:button>
            @endif
        </div>

        {{-- Schedule Cards --}}
        @if(count($weeklySchedules) > 0)
            <div class="space-y-4">
                @foreach($weeklySchedules as $doctorData)
                    @php $doctor = $doctorData['doctor']; @endphp
                    <div wire:key="doctor-{{ $doctor->id }}" class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                        {{-- Doctor Header --}}
                        <div class="border-b border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30">
                                        <flux:icon name="user" class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <div>
                                        <flux:heading size="sm">{{ $doctor->name }}</flux:heading>
                                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                            @php
                                                $types = collect($doctorData['schedules'])->pluck('consultation_type.name')->unique()->join(', ');
                                            @endphp
                                            {{ $types }}
                                        </flux:text>
                                    </div>
                                </div>
                                <flux:button wire:click="openAddExceptionModal(null, {{ $doctor->id }})" size="xs" variant="ghost" icon="plus">
                                    {{ __('Add Exception') }}
                                </flux:button>
                            </div>
                        </div>

                        {{-- Schedule Cards per Consultation Type --}}
                        <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($doctorData['schedules'] as $typeId => $scheduleData)
                                @php
                                    $consultationType = $scheduleData['consultation_type'];
                                    $days = $scheduleData['days'];
                                    $firstSchedule = collect($days)->first();
                                    $timeRange = '';
                                    if ($firstSchedule) {
                                        $start = $firstSchedule->start_time ? \Carbon\Carbon::parse($firstSchedule->start_time)->format('g:i A') : null;
                                        $end = $firstSchedule->end_time ? \Carbon\Carbon::parse($firstSchedule->end_time)->format('g:i A') : null;
                                        if ($start && $end) {
                                            $timeRange = $start . ' - ' . $end;
                                        }
                                    }
                                @endphp
                                <div wire:key="schedule-{{ $doctor->id }}-{{ $typeId }}" class="p-4">
                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                        <div class="space-y-3">
                                            {{-- Consultation Type --}}
                                            <div class="flex items-center gap-2">
                                                <flux:badge size="sm" color="blue">{{ $consultationType->name }}</flux:badge>
                                                @if($timeRange)
                                                    <span class="flex items-center gap-1 text-xs text-zinc-500 dark:text-zinc-400">
                                                        <flux:icon name="clock" class="h-3.5 w-3.5" />
                                                        {{ $timeRange }}
                                                    </span>
                                                @endif
                                            </div>

                                            {{-- Days Visual Grid --}}
                                            <div class="flex flex-wrap gap-1.5">
                                                @foreach($dayNames as $dayNum => $dayName)
                                                    @php
                                                        $isActive = isset($days[$dayNum]);
                                                        $shortName = substr($dayName, 0, 3);
                                                    @endphp
                                                    <div class="flex flex-col items-center">
                                                        <span class="mb-1 text-[10px] font-medium text-zinc-400 dark:text-zinc-500">
                                                            {{ $shortName }}
                                                        </span>
                                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg text-xs font-semibold transition
                                                            {{ $isActive
                                                                ? 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-400'
                                                                : 'bg-zinc-100 text-zinc-300 dark:bg-zinc-800 dark:text-zinc-600' }}">
                                                            {{ $isActive ? '✓' : '–' }}
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>

                                            {{-- Summary Text --}}
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                                @php
                                                    $activeDays = collect($dayNames)->filter(fn($name, $num) => isset($days[$num]))->values();
                                                @endphp
                                                {{ __('Available on') }}: {{ $activeDays->map(fn($name) => substr($name, 0, 3))->join(', ') }}
                                            </p>
                                        </div>

                                        {{-- Actions --}}
                                        <div class="flex gap-2">
                                            <flux:button wire:click="openEditScheduleModal({{ $doctor->id }}, {{ $typeId }})" size="sm" variant="ghost" icon="pencil">
                                                {{ __('Edit') }}
                                            </flux:button>
                                            <flux:button wire:click="confirmDeleteSchedule({{ $doctor->id }}, {{ $typeId }})" size="sm" variant="ghost" icon="trash" class="text-red-600 hover:text-red-700 dark:text-red-400">
                                                {{ __('Delete') }}
                                            </flux:button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- Empty State --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon name="calendar-days" class="h-8 w-8 text-zinc-400" />
                </div>
                <h3 class="mt-4 text-lg font-medium text-zinc-900 dark:text-white">{{ __('No schedules found') }}</h3>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Start by adding a weekly schedule to define when doctors are available for consultations.') }}
                </p>
                <div class="mt-6">
                    <flux:button wire:click="openAddScheduleModal" variant="primary" icon="plus">
                        {{ __('Add Weekly Schedule') }}
                    </flux:button>
                </div>
            </div>
        @endif
    @endif

    {{-- ==================== EXCEPTIONS VIEW ==================== --}}
    @if($viewMode === 'exceptions')
        {{-- Filters --}}
        <div class="flex flex-wrap items-end gap-3">
            <div class="w-full sm:w-48">
                <flux:select wire:model.live="doctorFilter" placeholder="{{ __('All doctors') }}">
                    <flux:select.option value="">{{ __('All doctors') }}</flux:select.option>
                    @foreach($doctors as $doctor)
                        <flux:select.option value="{{ $doctor->id }}">{{ $doctor->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            <div class="w-full sm:w-48">
                <flux:select wire:model.live="consultationTypeFilter" placeholder="{{ __('All types') }}">
                    <flux:select.option value="">{{ __('All types') }}</flux:select.option>
                    @foreach($consultationTypes as $type)
                        <flux:select.option value="{{ $type->id }}">{{ $type->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            @if($doctorFilter || $consultationTypeFilter)
                <flux:button wire:click="$set('doctorFilter', ''); $set('consultationTypeFilter', '')" size="sm" variant="ghost" icon="x-mark">
                    {{ __('Clear Filters') }}
                </flux:button>
            @endif
        </div>

        {{-- Quick Add Buttons --}}
        <div class="flex flex-wrap gap-2">
            <flux:button wire:click="openAddExceptionModal('annual_leave')" size="sm" variant="ghost" icon="briefcase">
                {{ __('Annual Leave') }}
            </flux:button>
            <flux:button wire:click="openAddExceptionModal('sick_leave')" size="sm" variant="ghost" icon="heart">
                {{ __('Sick Leave') }}
            </flux:button>
            <flux:button wire:click="openAddExceptionModal('holiday')" size="sm" variant="ghost" icon="star">
                {{ __('Holiday') }}
            </flux:button>
            <flux:button wire:click="openAddExceptionModal('extra_clinic')" size="sm" variant="ghost" icon="plus-circle">
                {{ __('Extra Clinic') }}
            </flux:button>
            <flux:button wire:click="openAddExceptionModal" size="sm" variant="ghost" icon="adjustments-horizontal">
                {{ __('Custom') }}
            </flux:button>
        </div>

        @if($exceptions->count() > 0)
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Date') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Doctor') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Type') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Time') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Reason') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($exceptions as $exception)
                                @php
                                    $isPast = $exception->date->isPast() && !$exception->date->isToday();
                                    $isToday = $exception->date->isToday();
                                    $isTomorrow = $exception->date->isTomorrow();
                                @endphp
                                <tr wire:key="exception-{{ $exception->id }}"
                                    class="{{ $isPast ? 'opacity-50' : '' }} {{ $isToday ? 'bg-amber-50/50 dark:bg-amber-900/10' : '' }} hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-10 w-10 flex-col items-center justify-center rounded-lg {{ $exception->is_available ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30' }}">
                                                <span class="text-sm font-semibold {{ $exception->is_available ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400' }}">
                                                    {{ $exception->date->format('d') }}
                                                </span>
                                                <span class="text-[10px] {{ $exception->is_available ? 'text-green-600 dark:text-green-500' : 'text-red-600 dark:text-red-500' }}">
                                                    {{ $exception->date->format('M') }}
                                                </span>
                                            </div>
                                            <div>
                                                <p class="font-medium text-zinc-900 dark:text-white">{{ $exception->date->format('l') }}</p>
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                                    @if($isToday)
                                                        <flux:badge size="sm" color="amber">{{ __('Today') }}</flux:badge>
                                                    @elseif($isTomorrow)
                                                        <flux:badge size="sm" color="blue">{{ __('Tomorrow') }}</flux:badge>
                                                    @else
                                                        {{ $exception->date->format('Y') }}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <p class="font-medium text-zinc-900 dark:text-white">{{ $exception->doctor?->name ?? '-' }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <flux:badge size="sm" color="zinc">{{ $exception->consultationType?->name ?? '-' }}</flux:badge>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        @if($exception->is_available)
                                            <flux:badge size="sm" color="green" icon="check-circle">{{ __('Available') }}</flux:badge>
                                        @else
                                            <flux:badge size="sm" color="red" icon="x-circle">{{ __('Not Available') }}</flux:badge>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                        @if($exception->is_available && $exception->start_time && $exception->end_time)
                                            <span class="flex items-center gap-1">
                                                <flux:icon name="clock" class="h-4 w-4 text-zinc-400" />
                                                {{ \Carbon\Carbon::parse($exception->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($exception->end_time)->format('g:i A') }}
                                            </span>
                                        @elseif($exception->is_available)
                                            <span class="text-zinc-400">{{ __('Full Day') }}</span>
                                        @else
                                            <span class="text-zinc-400">–</span>
                                        @endif
                                    </td>
                                    <td class="max-w-[200px] truncate px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300" title="{{ $exception->reason }}">
                                        {{ $exception->reason ?? '–' }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <flux:button wire:click="openEditExceptionModal({{ $exception->id }})" size="xs" variant="ghost" icon="pencil" />
                                            <flux:button wire:click="confirmDeleteException({{ $exception->id }})" size="xs" variant="ghost" icon="trash" class="text-red-600 hover:text-red-700 dark:text-red-400" />
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Legend --}}
            <div class="flex flex-wrap items-center gap-4 rounded-xl border border-zinc-200 bg-zinc-50 p-3 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                <span class="font-medium text-zinc-600 dark:text-zinc-300">{{ __('Status Guide:') }}</span>
                <div class="flex items-center gap-2">
                    <flux:badge size="sm" color="red" icon="x-circle">{{ __('Not Available') }}</flux:badge>
                    <span class="text-zinc-500 dark:text-zinc-400">{{ __('Doctor is off (leave, holiday)') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <flux:badge size="sm" color="green" icon="check-circle">{{ __('Available') }}</flux:badge>
                    <span class="text-zinc-500 dark:text-zinc-400">{{ __('Extra clinic day or modified hours') }}</span>
                </div>
            </div>
        @else
            {{-- Empty State --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon name="calendar" class="h-8 w-8 text-zinc-400" />
                </div>
                <h3 class="mt-4 text-lg font-medium text-zinc-900 dark:text-white">{{ __('No exceptions found') }}</h3>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Use exceptions to record leaves, holidays, or extra clinic days that differ from the regular schedule.') }}
                </p>
                <div class="mt-6 flex flex-wrap justify-center gap-2">
                    <flux:button wire:click="openAddExceptionModal('annual_leave')" variant="ghost" icon="briefcase">
                        {{ __('Record Leave') }}
                    </flux:button>
                    <flux:button wire:click="openAddExceptionModal('extra_clinic')" variant="primary" icon="plus">
                        {{ __('Add Extra Day') }}
                    </flux:button>
                </div>
            </div>
        @endif
    @endif

    {{-- ==================== ADD/EDIT SCHEDULE MODAL ==================== --}}
    <flux:modal wire:model="showScheduleModal" class="max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editScheduleId ? __('Edit Weekly Schedule') : __('Add Weekly Schedule') }}</flux:heading>
                <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                    {{ __('Define which days and hours a doctor is available for consultations.') }}
                </flux:text>
            </div>

            <div class="space-y-4">
                {{-- Doctor Selection --}}
                <flux:field>
                    <flux:label>{{ __('Doctor') }} <span class="text-red-500">*</span></flux:label>
                    <flux:select wire:model="scheduleDoctor" placeholder="{{ __('Select a doctor') }}" :disabled="(bool) $editScheduleId">
                        <flux:select.option value="">{{ __('Select a doctor') }}</flux:select.option>
                        @foreach($doctors as $doctor)
                            <flux:select.option value="{{ $doctor->id }}">{{ $doctor->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="scheduleDoctor" />
                </flux:field>

                {{-- Consultation Type Selection --}}
                <flux:field>
                    <flux:label>{{ __('Consultation Type') }} <span class="text-red-500">*</span></flux:label>
                    <flux:select wire:model="scheduleConsultationType" placeholder="{{ __('Select consultation type') }}" :disabled="(bool) $editScheduleId">
                        <flux:select.option value="">{{ __('Select consultation type') }}</flux:select.option>
                        @foreach($consultationTypes as $type)
                            <flux:select.option value="{{ $type->id }}">{{ $type->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="scheduleConsultationType" />
                </flux:field>

                {{-- Days Selection --}}
                <flux:field>
                    <flux:label>{{ __('Working Days') }} <span class="text-red-500">*</span></flux:label>
                    <p class="mb-2 text-xs text-zinc-500 dark:text-zinc-400">{{ __('Click to select the days when the doctor works.') }}</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($dayNames as $dayNum => $dayName)
                            @php $isChecked = in_array($dayNum, $scheduleDays); @endphp
                            <label wire:key="day-{{ $dayNum }}" class="cursor-pointer">
                                <input type="checkbox" wire:model="scheduleDays" value="{{ $dayNum }}" class="peer sr-only">
                                <span class="inline-flex h-12 w-14 flex-col items-center justify-center rounded-xl border-2 text-sm font-medium transition-all
                                    peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:text-green-700
                                    peer-focus:ring-2 peer-focus:ring-green-500 peer-focus:ring-offset-2
                                    dark:peer-checked:border-green-600 dark:peer-checked:bg-green-900/30 dark:peer-checked:text-green-400
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
                    {{ $editScheduleId ? __('Update Schedule') : __('Save Schedule') }}
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
                        <flux:label>{{ __('Exception Type') }}</flux:label>
                        <flux:select wire:model.live="exceptionPreset" placeholder="{{ __('Select type or choose custom') }}">
                            <flux:select.option value="">{{ __('Select type...') }}</flux:select.option>
                            <flux:select.option value="annual_leave">{{ __('Annual Leave') }}</flux:select.option>
                            <flux:select.option value="sick_leave">{{ __('Sick Leave') }}</flux:select.option>
                            <flux:select.option value="holiday">{{ __('Holiday') }}</flux:select.option>
                            <flux:select.option value="training">{{ __('Training/Seminar') }}</flux:select.option>
                            <flux:select.option value="emergency_leave">{{ __('Emergency Leave') }}</flux:select.option>
                            <flux:select.option value="half_day_am">{{ __('Half Day (Morning Off)') }}</flux:select.option>
                            <flux:select.option value="half_day_pm">{{ __('Half Day (Afternoon Off)') }}</flux:select.option>
                            <flux:select.option value="extra_clinic">{{ __('Extra Clinic Day') }}</flux:select.option>
                            <flux:select.option value="custom">{{ __('Custom') }}</flux:select.option>
                        </flux:select>
                    </flux:field>
                @endunless

                {{-- Doctor Selection --}}
                <flux:field>
                    <flux:label>{{ __('Doctor') }} <span class="text-red-500">*</span></flux:label>
                    <flux:select wire:model="exceptionDoctor" placeholder="{{ __('Select a doctor') }}">
                        <flux:select.option value="">{{ __('Select a doctor') }}</flux:select.option>
                        @foreach($doctors as $doctor)
                            <flux:select.option value="{{ $doctor->id }}">{{ $doctor->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="exceptionDoctor" />
                </flux:field>

                {{-- Consultation Type Selection --}}
                <flux:field>
                    <flux:label>{{ __('Consultation Type') }} <span class="text-red-500">*</span></flux:label>
                    <flux:select wire:model="exceptionConsultationType" placeholder="{{ __('Select consultation type') }}">
                        <flux:select.option value="">{{ __('Select consultation type') }}</flux:select.option>
                        @foreach($consultationTypes as $type)
                            <flux:select.option value="{{ $type->id }}">{{ $type->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="exceptionConsultationType" />
                </flux:field>

                {{-- Date Mode Toggle (only for new exceptions) --}}
                @unless($editExceptionId)
                    <div class="flex items-center justify-between rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                        <div>
                            <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Multiple Days (Date Range)') }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $exceptionUseDateRange ? __('Select start and end dates for vacation/leave period') : __('Enable to add exceptions for multiple days at once') }}
                            </p>
                        </div>
                        <flux:switch wire:model.live="exceptionUseDateRange" />
                    </div>
                @endunless

                {{-- Date Fields --}}
                @if($exceptionUseDateRange && !$editExceptionId)
                    {{-- Date Range Mode --}}
                    <div class="grid grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>{{ __('From Date') }} <span class="text-red-500">*</span></flux:label>
                            <flux:input type="date" wire:model.live="exceptionDate" min="{{ now()->format('Y-m-d') }}" />
                            <flux:error name="exceptionDate" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('To Date') }} <span class="text-red-500">*</span></flux:label>
                            <flux:input type="date" wire:model.live="exceptionDateEnd" min="{{ $exceptionDate ?: now()->format('Y-m-d') }}" />
                            <flux:error name="exceptionDateEnd" />
                        </flux:field>
                    </div>
                    @if($dateRangeDaysCount > 0)
                        <div class="rounded-lg border border-blue-200 bg-blue-50 p-3 dark:border-blue-800 dark:bg-blue-900/20">
                            <p class="flex items-center gap-2 text-sm text-blue-700 dark:text-blue-300">
                                <flux:icon name="information-circle" class="h-5 w-5" />
                                <span>
                                    {{ trans_choice('{1} This will create :count exception.|[2,*] This will create :count exceptions.', $dateRangeDaysCount, ['count' => $dateRangeDaysCount]) }}
                                </span>
                            </p>
                            @if($dateRangeDaysCount > 14)
                                <p class="mt-1 text-xs text-blue-600 dark:text-blue-400">
                                    {{ __('Tip: For long periods, consider if all dates need exceptions or just weekdays.') }}
                                </p>
                            @endif
                        </div>
                    @endif
                @else
                    {{-- Single Date Mode --}}
                    <flux:field>
                        <flux:label>{{ __('Date') }} <span class="text-red-500">*</span></flux:label>
                        <flux:input type="date" wire:model="exceptionDate" min="{{ now()->format('Y-m-d') }}" />
                        <flux:error name="exceptionDate" />
                    </flux:field>
                @endif

                {{-- Availability Toggle --}}
                <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <flux:label>{{ __('Availability Status') }}</flux:label>
                            <p class="mt-1 text-sm {{ $exceptionIsAvailable ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                @if($exceptionIsAvailable)
                                    <flux:icon name="check-circle" class="inline h-4 w-4" />
                                    {{ __('Doctor will be AVAILABLE on this day') }}
                                @else
                                    <flux:icon name="x-circle" class="inline h-4 w-4" />
                                    {{ __('Doctor will NOT be available on this day') }}
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
                    @elseif($exceptionUseDateRange && $dateRangeDaysCount > 1)
                        {{ __('Create :count Exceptions', ['count' => $dateRangeDaysCount]) }}
                    @else
                        {{ __('Save Exception') }}
                    @endif
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ==================== COPY SCHEDULE MODAL ==================== --}}
    <flux:modal wire:model="showCopyModal" class="max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Copy Schedule') }}</flux:heading>
                <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                    {{ __('Copy a weekly schedule from one doctor to another. This will replace any existing schedule for the target doctor.') }}
                </flux:text>
            </div>

            <div class="space-y-4">
                {{-- Source Doctor --}}
                <flux:field>
                    <flux:label>{{ __('Copy From (Source Doctor)') }} <span class="text-red-500">*</span></flux:label>
                    <flux:select wire:model="copyFromDoctor" placeholder="{{ __('Select source doctor') }}">
                        <flux:select.option value="">{{ __('Select source doctor') }}</flux:select.option>
                        @foreach($doctors as $doctor)
                            <flux:select.option value="{{ $doctor->id }}">{{ $doctor->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="copyFromDoctor" />
                </flux:field>

                {{-- Arrow indicator --}}
                <div class="flex justify-center">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                        <flux:icon name="arrow-down" class="h-4 w-4 text-zinc-500" />
                    </div>
                </div>

                {{-- Target Doctor --}}
                <flux:field>
                    <flux:label>{{ __('Copy To (Target Doctor)') }} <span class="text-red-500">*</span></flux:label>
                    <flux:select wire:model="copyToDoctor" placeholder="{{ __('Select target doctor') }}">
                        <flux:select.option value="">{{ __('Select target doctor') }}</flux:select.option>
                        @foreach($doctors as $doctor)
                            <flux:select.option value="{{ $doctor->id }}">{{ $doctor->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="copyToDoctor" />
                </flux:field>

                {{-- Consultation Type --}}
                <flux:field>
                    <flux:label>{{ __('Consultation Type') }} <span class="text-red-500">*</span></flux:label>
                    <flux:select wire:model="copyConsultationType" placeholder="{{ __('Select consultation type') }}">
                        <flux:select.option value="">{{ __('Select consultation type') }}</flux:select.option>
                        @foreach($consultationTypes as $type)
                            <flux:select.option value="{{ $type->id }}">{{ $type->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="copyConsultationType" />
                </flux:field>

                {{-- Warning --}}
                <flux:callout color="amber" icon="exclamation-triangle">
                    <flux:callout.heading>{{ __('Warning') }}</flux:callout.heading>
                    <flux:callout.text>
                        {{ __('This will replace any existing schedule for the selected consultation type on the target doctor.') }}
                    </flux:callout.text>
                </flux:callout>
            </div>

            <div class="flex justify-end gap-3 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <flux:button wire:click="closeCopyModal" variant="ghost">{{ __('Cancel') }}</flux:button>
                <flux:button wire:click="copySchedule" variant="primary" icon="document-duplicate">
                    {{ __('Copy Schedule') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ==================== DELETE CONFIRMATION MODAL ==================== --}}
    <flux:modal wire:model="showDeleteModal" class="max-w-md">
        <div class="space-y-6">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                    <flux:icon name="trash" class="h-6 w-6 text-red-600 dark:text-red-400" />
                </div>
                <div>
                    <flux:heading size="lg">{{ __('Delete Confirmation') }}</flux:heading>
                    <flux:text class="mt-2 text-zinc-500 dark:text-zinc-400">
                        @if(str_starts_with($deleteType, 'schedule:'))
                            {{ __('Are you sure you want to delete this weekly schedule? This will remove all the working days for this doctor and consultation type.') }}
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
