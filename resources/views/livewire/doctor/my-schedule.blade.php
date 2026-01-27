<section class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" level="1">{{ __('My Schedule') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('View your clinic schedules and appointments') }}
            </flux:text>
        </div>
    </div>

    {{-- Week Navigation --}}
    <div class="flex items-center justify-between rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:button wire:click="previousWeek" variant="ghost" icon="chevron-left" />
        <div class="text-center">
            <p class="font-medium text-zinc-900 dark:text-white">
                {{ Carbon\Carbon::parse($weekStart)->format('M d') }} - {{ Carbon\Carbon::parse($weekStart)->addDays(6)->format('M d, Y') }}
            </p>
            @if(Carbon\Carbon::parse($weekStart)->isCurrentWeek())
                <p class="text-xs text-emerald-600 dark:text-emerald-400">{{ __('Current Week') }}</p>
            @endif
        </div>
        <div class="flex gap-2">
            <flux:button wire:click="goToToday" variant="ghost" size="sm">{{ __('Today') }}</flux:button>
            <flux:button wire:click="nextWeek" variant="ghost" icon="chevron-right" />
        </div>
    </div>

    {{-- Week Calendar --}}
    <div class="grid gap-2 sm:grid-cols-7">
        @foreach($weekDays as $day)
            @php
                $daySchedule = $this->getScheduleForDay($day);
                $isToday = $day->isToday();
                $isPast = $day->isPast() && !$isToday;
            @endphp
            <div class="rounded-lg border {{ $isToday ? 'border-blue-500 bg-blue-50 dark:border-blue-400 dark:bg-blue-900/20' : 'border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900' }} {{ $isPast ? 'opacity-60' : '' }}">
                {{-- Day Header --}}
                <div class="border-b border-zinc-200 p-2 text-center dark:border-zinc-700 {{ $isToday ? 'border-blue-200 dark:border-blue-800' : '' }}">
                    <p class="text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">{{ $day->format('D') }}</p>
                    <p class="text-lg font-bold {{ $isToday ? 'text-blue-600 dark:text-blue-400' : 'text-zinc-900 dark:text-white' }}">
                        {{ $day->format('d') }}
                    </p>
                </div>

                {{-- Day Content --}}
                <div class="min-h-24 p-2">
                    @if($daySchedule['isDayOff'])
                        <div class="rounded bg-zinc-100 p-2 text-center text-xs text-zinc-500 dark:bg-zinc-800">
                            <flux:icon name="x-circle" class="mx-auto h-4 w-4" />
                            <p class="mt-1">{{ __('Day Off') }}</p>
                            @if($daySchedule['dayOffReason'])
                                <p class="text-[10px]">{{ $daySchedule['dayOffReason'] }}</p>
                            @endif
                        </div>
                    @elseif($daySchedule['regular']->isNotEmpty())
                        <div class="space-y-1">
                            @foreach($daySchedule['regular'] as $schedule)
                                <div class="rounded bg-emerald-100 p-1.5 text-xs dark:bg-emerald-900/30">
                                    <p class="font-medium text-emerald-800 dark:text-emerald-200">
                                        {{ $schedule->consultationType?->short_name }}
                                    </p>
                                    <p class="text-emerald-600 dark:text-emerald-400">
                                        {{ Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }} -
                                        {{ Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="py-4 text-center text-xs text-zinc-400">{{ __('No schedule') }}</p>
                    @endif

                    {{-- Exceptions --}}
                    @foreach($daySchedule['exceptions']->where('is_day_off', false) as $exception)
                        <div class="mt-1 rounded bg-amber-100 p-1.5 text-xs dark:bg-amber-900/30">
                            <p class="font-medium text-amber-800 dark:text-amber-200">{{ $exception->exception_reason }}</p>
                            @if($exception->start_time)
                                <p class="text-amber-600 dark:text-amber-400">
                                    {{ Carbon\Carbon::parse($exception->start_time)->format('h:i A') }} -
                                    {{ Carbon\Carbon::parse($exception->end_time)->format('h:i A') }}
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    {{-- Upcoming Appointments --}}
    @if($this->upcomingAppointments->isNotEmpty())
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="sm" class="mb-4">{{ __('Upcoming Appointments') }}</flux:heading>

            <div class="space-y-2">
                @foreach($this->upcomingAppointments as $appointment)
                    <div class="flex items-center justify-between rounded-lg border border-zinc-100 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 flex-col items-center justify-center rounded bg-zinc-200 text-xs dark:bg-zinc-700">
                                <span class="font-bold text-zinc-700 dark:text-zinc-300">{{ $appointment->appointment_date->format('d') }}</span>
                                <span class="text-[10px] text-zinc-500">{{ $appointment->appointment_date->format('M') }}</span>
                            </div>
                            <div>
                                <p class="font-medium text-zinc-900 dark:text-white">
                                    {{ $appointment->patient_first_name }} {{ $appointment->patient_last_name }}
                                </p>
                                <p class="text-xs text-zinc-500">
                                    {{ $appointment->consultationType?->name }}
                                    @if($appointment->appointment_time)
                                        &bull; {{ $appointment->appointment_time->format('h:i A') }}
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

    {{-- Legend --}}
    <div class="flex flex-wrap gap-4 text-xs text-zinc-500">
        <div class="flex items-center gap-2">
            <span class="h-3 w-3 rounded bg-emerald-100 dark:bg-emerald-900/30"></span>
            {{ __('Regular Schedule') }}
        </div>
        <div class="flex items-center gap-2">
            <span class="h-3 w-3 rounded bg-amber-100 dark:bg-amber-900/30"></span>
            {{ __('Exception/Special') }}
        </div>
        <div class="flex items-center gap-2">
            <span class="h-3 w-3 rounded bg-zinc-100 dark:bg-zinc-800"></span>
            {{ __('Day Off') }}
        </div>
    </div>
</section>
