<section class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-1">
            <flux:heading size="xl" level="1">{{ __('Doctor Schedules') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('Manage when doctors are available for consultations.') }}
            </flux:text>
        </div>
        <div class="flex gap-2">
            @if($viewMode === 'weekly')
                <flux:button wire:click="openAddScheduleModal" variant="primary" icon="plus">
                    {{ __('Add Schedule') }}
                </flux:button>
            @else
                <flux:button wire:click="openAddExceptionModal" variant="primary" icon="plus">
                    {{ __('Add Exception') }}
                </flux:button>
            @endif
        </div>
    </div>

    {{-- View Mode Tabs --}}
    <div class="flex flex-wrap items-center gap-2 border-b border-zinc-200 pb-4 dark:border-zinc-700">
        <flux:button wire:click="setViewMode('weekly')" :variant="$viewMode === 'weekly' ? 'filled' : 'ghost'" size="sm" icon="calendar-days">
            {{ __('Weekly Schedule') }}
            @if($counts['weekly'] > 0)
                <flux:badge size="sm" color="zinc" class="ml-1">{{ $counts['weekly'] }}</flux:badge>
            @endif
        </flux:button>
        <flux:button wire:click="setViewMode('exceptions')" :variant="$viewMode === 'exceptions' ? 'filled' : 'ghost'" size="sm" icon="calendar">
            {{ __('Exceptions') }}
            @if($counts['exceptions'] > 0)
                <flux:badge size="sm" color="amber" class="ml-1">{{ $counts['exceptions'] }}</flux:badge>
            @endif
        </flux:button>
    </div>

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
    </div>

    {{-- Weekly Schedule View --}}
    @if($viewMode === 'weekly')
        @if(count($weeklySchedules) > 0)
            <div class="space-y-4">
                @foreach($weeklySchedules as $doctorData)
                    @php
                        $doctor = $doctorData['doctor'];
                    @endphp
                    <div wire:key="doctor-{{ $doctor->id }}" class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                        {{-- Doctor Header --}}
                        <div class="border-b border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
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
                                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="space-y-2">
                                            {{-- Consultation Type Badge --}}
                                            <flux:badge size="sm" color="blue">{{ $consultationType->name }}</flux:badge>

                                            {{-- Days Grid --}}
                                            <div class="flex flex-wrap gap-1.5">
                                                @foreach($dayNames as $dayNum => $dayName)
                                                    @php
                                                        $isActive = isset($days[$dayNum]);
                                                        $shortName = substr($dayName, 0, 3);
                                                    @endphp
                                                    <span class="inline-flex h-8 w-10 items-center justify-center rounded text-xs font-medium transition
                                                        {{ $isActive
                                                            ? 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-400'
                                                            : 'bg-zinc-100 text-zinc-400 dark:bg-zinc-800 dark:text-zinc-500' }}">
                                                        {{ $shortName }}
                                                    </span>
                                                @endforeach
                                            </div>

                                            {{-- Time Range --}}
                                            @if($timeRange)
                                                <flux:text class="flex items-center gap-1 text-xs text-zinc-500 dark:text-zinc-400">
                                                    <flux:icon name="clock" class="h-3.5 w-3.5" />
                                                    {{ $timeRange }}
                                                </flux:text>
                                            @endif
                                        </div>

                                        {{-- Actions --}}
                                        <div class="flex gap-2">
                                            <flux:button wire:click="openEditScheduleModal({{ $doctor->id }}, {{ $typeId }})" size="xs" variant="ghost" icon="pencil">
                                                {{ __('Edit') }}
                                            </flux:button>
                                            <flux:button wire:click="confirmDeleteSchedule({{ $doctor->id }}, {{ $typeId }})" size="xs" variant="ghost" icon="trash" class="text-red-600 hover:text-red-700 dark:text-red-400">
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
            <div class="rounded-lg border border-zinc-200 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon name="calendar-days" class="h-6 w-6 text-zinc-400" />
                </div>
                <h3 class="mt-4 text-sm font-medium text-zinc-900 dark:text-white">{{ __('No schedules found') }}</h3>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Add a schedule to show when doctors are available.') }}
                </p>
                <div class="mt-4">
                    <flux:button wire:click="openAddScheduleModal" variant="primary" icon="plus">
                        {{ __('Add Schedule') }}
                    </flux:button>
                </div>
            </div>
        @endif
    @endif

    {{-- Exceptions View --}}
    @if($viewMode === 'exceptions')
        @if($exceptions->count() > 0)
            <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
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
                                @endphp
                                <tr wire:key="exception-{{ $exception->id }}" class="{{ $isPast ? 'opacity-50' : '' }} {{ $isToday ? 'bg-amber-50/50 dark:bg-amber-900/10' : '' }} hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <div class="font-medium text-zinc-900 dark:text-white">
                                            {{ $exception->date->format('M d, Y') }}
                                        </div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $exception->date->format('l') }}
                                            @if($isToday)
                                                <flux:badge size="sm" color="amber" class="ml-1">{{ __('Today') }}</flux:badge>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <div class="font-medium text-zinc-900 dark:text-white">{{ $exception->doctor?->name ?? '-' }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <flux:badge size="sm" color="zinc">{{ $exception->consultationType?->name ?? '-' }}</flux:badge>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        @if($exception->is_available)
                                            <flux:badge size="sm" color="green">{{ __('Extra Day') }}</flux:badge>
                                        @else
                                            <flux:badge size="sm" color="red">{{ __('Not Available') }}</flux:badge>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                        @if($exception->is_available && $exception->start_time && $exception->end_time)
                                            {{ \Carbon\Carbon::parse($exception->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($exception->end_time)->format('g:i A') }}
                                        @elseif($exception->is_available)
                                            {{ __('Full Day') }}
                                        @else
                                            <span class="text-zinc-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                        {{ $exception->reason ?? '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <flux:button wire:click="openEditExceptionModal({{ $exception->id }})" size="xs" variant="ghost" icon="pencil">
                                                {{ __('Edit') }}
                                            </flux:button>
                                            <flux:button wire:click="confirmDeleteException({{ $exception->id }})" size="xs" variant="ghost" icon="trash" class="text-red-600 hover:text-red-700 dark:text-red-400">
                                                {{ __('Delete') }}
                                            </flux:button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="rounded-lg border border-zinc-200 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon name="calendar" class="h-6 w-6 text-zinc-400" />
                </div>
                <h3 class="mt-4 text-sm font-medium text-zinc-900 dark:text-white">{{ __('No exceptions found') }}</h3>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Add exceptions for leaves, holidays, or extra clinic days.') }}
                </p>
                <div class="mt-4">
                    <flux:button wire:click="openAddExceptionModal" variant="primary" icon="plus">
                        {{ __('Add Exception') }}
                    </flux:button>
                </div>
            </div>
        @endif

        {{-- Legend --}}
        <div class="flex flex-wrap items-center gap-4 rounded-lg border border-zinc-200 bg-zinc-50 p-3 text-sm dark:border-zinc-700 dark:bg-zinc-800">
            <span class="font-medium text-zinc-600 dark:text-zinc-300">{{ __('Legend:') }}</span>
            <div class="flex items-center gap-2">
                <flux:badge size="sm" color="red">{{ __('Not Available') }}</flux:badge>
                <span class="text-zinc-500 dark:text-zinc-400">{{ __('Leave, holiday, or day off') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <flux:badge size="sm" color="green">{{ __('Extra Day') }}</flux:badge>
                <span class="text-zinc-500 dark:text-zinc-400">{{ __('Additional clinic day') }}</span>
            </div>
        </div>
    @endif

    {{-- Add/Edit Schedule Modal --}}
    <flux:modal wire:model="showScheduleModal" class="max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editScheduleId ? __('Edit Schedule') : __('Add Weekly Schedule') }}</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    {{ __('Set which days a doctor is available for consultations.') }}
                </flux:text>
            </div>

            <div class="space-y-4">
                {{-- Doctor Selection --}}
                <flux:field>
                    <flux:label>{{ __('Doctor') }} *</flux:label>
                    <flux:select wire:model="scheduleDoctor" placeholder="{{ __('Select doctor') }}" :disabled="(bool) $editScheduleId">
                        <flux:select.option value="">{{ __('Select doctor') }}</flux:select.option>
                        @foreach($doctors as $doctor)
                            <flux:select.option value="{{ $doctor->id }}">{{ $doctor->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="scheduleDoctor" />
                </flux:field>

                {{-- Consultation Type Selection --}}
                <flux:field>
                    <flux:label>{{ __('Consultation Type') }} *</flux:label>
                    <flux:select wire:model="scheduleConsultationType" placeholder="{{ __('Select type') }}" :disabled="(bool) $editScheduleId">
                        <flux:select.option value="">{{ __('Select type') }}</flux:select.option>
                        @foreach($consultationTypes as $type)
                            <flux:select.option value="{{ $type->id }}">{{ $type->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="scheduleConsultationType" />
                </flux:field>

                {{-- Days Selection --}}
                <flux:field>
                    <flux:label>{{ __('Available Days') }} *</flux:label>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach($dayNames as $dayNum => $dayName)
                            @php
                                $isChecked = in_array($dayNum, $scheduleDays);
                            @endphp
                            <label wire:key="day-{{ $dayNum }}" class="relative cursor-pointer">
                                <input
                                    type="checkbox"
                                    wire:model="scheduleDays"
                                    value="{{ $dayNum }}"
                                    class="peer sr-only"
                                >
                                <span class="inline-flex h-10 w-14 items-center justify-center rounded-lg border-2 text-sm font-medium transition
                                    peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:text-green-700
                                    dark:peer-checked:border-green-600 dark:peer-checked:bg-green-900/30 dark:peer-checked:text-green-400
                                    {{ !$isChecked ? 'border-zinc-200 bg-white text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:border-zinc-500' : '' }}">
                                    {{ substr($dayName, 0, 3) }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <flux:description class="mt-2">{{ __('Click to toggle which days the doctor works.') }}</flux:description>
                    <flux:error name="scheduleDays" />
                </flux:field>

                {{-- Time Range --}}
                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>{{ __('Start Time') }}</flux:label>
                        <flux:input type="time" wire:model="scheduleStartTime" />
                        <flux:error name="scheduleStartTime" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('End Time') }}</flux:label>
                        <flux:input type="time" wire:model="scheduleEndTime" />
                        <flux:error name="scheduleEndTime" />
                    </flux:field>
                </div>
                <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                    {{ __('Leave blank to use default clinic hours.') }}
                </flux:text>
            </div>

            <div class="flex justify-end gap-3">
                <flux:button wire:click="closeScheduleModal" variant="ghost">{{ __('Cancel') }}</flux:button>
                <flux:button wire:click="saveSchedule" variant="primary" icon="check">
                    {{ __('Save Schedule') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Add/Edit Exception Modal --}}
    <flux:modal wire:model="showExceptionModal" class="max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editExceptionId ? __('Edit Exception') : __('Add Exception') }}</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    {{ __('Add a leave, holiday, or extra clinic day.') }}
                </flux:text>
            </div>

            <div class="space-y-4">
                {{-- Doctor Selection --}}
                <flux:field>
                    <flux:label>{{ __('Doctor') }} *</flux:label>
                    <flux:select wire:model="exceptionDoctor" placeholder="{{ __('Select doctor') }}">
                        <flux:select.option value="">{{ __('Select doctor') }}</flux:select.option>
                        @foreach($doctors as $doctor)
                            <flux:select.option value="{{ $doctor->id }}">{{ $doctor->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="exceptionDoctor" />
                </flux:field>

                {{-- Consultation Type Selection --}}
                <flux:field>
                    <flux:label>{{ __('Consultation Type') }} *</flux:label>
                    <flux:select wire:model="exceptionConsultationType" placeholder="{{ __('Select type') }}">
                        <flux:select.option value="">{{ __('Select type') }}</flux:select.option>
                        @foreach($consultationTypes as $type)
                            <flux:select.option value="{{ $type->id }}">{{ $type->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="exceptionConsultationType" />
                </flux:field>

                {{-- Date --}}
                <flux:field>
                    <flux:label>{{ __('Date') }} *</flux:label>
                    <flux:input type="date" wire:model="exceptionDate" />
                    <flux:error name="exceptionDate" />
                </flux:field>

                {{-- Availability Toggle --}}
                <flux:field>
                    <div class="flex items-center justify-between rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                        <div>
                            <flux:label>{{ __('Will the doctor be available?') }}</flux:label>
                            <flux:description>
                                {{ $exceptionIsAvailable ? __('Doctor will work on this day (extra clinic day)') : __('Doctor will NOT be available (leave, holiday)') }}
                            </flux:description>
                        </div>
                        <flux:switch wire:model.live="exceptionIsAvailable" />
                    </div>
                </flux:field>

                {{-- Time Range (only if available) --}}
                @if($exceptionIsAvailable)
                    <div class="grid grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>{{ __('Start Time') }}</flux:label>
                            <flux:input type="time" wire:model="exceptionStartTime" />
                            <flux:error name="exceptionStartTime" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('End Time') }}</flux:label>
                            <flux:input type="time" wire:model="exceptionEndTime" />
                            <flux:error name="exceptionEndTime" />
                        </flux:field>
                    </div>
                @endif

                {{-- Reason --}}
                <flux:field>
                    <flux:label>{{ __('Reason') }}</flux:label>
                    <flux:input wire:model="exceptionReason" placeholder="{{ $exceptionIsAvailable ? __('e.g., Special clinic day') : __('e.g., Annual leave, Holiday') }}" />
                    <flux:error name="exceptionReason" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-3">
                <flux:button wire:click="closeExceptionModal" variant="ghost">{{ __('Cancel') }}</flux:button>
                <flux:button wire:click="saveException" variant="primary" icon="check">
                    {{ __('Save Exception') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Delete Confirmation Modal --}}
    <flux:modal wire:model="showDeleteModal" class="max-w-md">
        <div class="space-y-6">
            <div class="flex items-start gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                    <flux:icon name="trash" class="h-5 w-5 text-red-600 dark:text-red-400" />
                </div>
                <div>
                    <flux:heading size="lg">{{ __('Confirm Delete') }}</flux:heading>
                    <flux:text variant="subtle" class="mt-1">
                        @if(str_starts_with($deleteType, 'schedule:'))
                            {{ __('Are you sure you want to delete this weekly schedule? This action cannot be undone.') }}
                        @else
                            {{ __('Are you sure you want to delete this exception? This action cannot be undone.') }}
                        @endif
                    </flux:text>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <flux:button wire:click="closeDeleteModal" variant="ghost">{{ __('Cancel') }}</flux:button>
                <flux:button wire:click="deleteConfirmed" variant="danger" icon="trash">
                    {{ __('Delete') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</section>
