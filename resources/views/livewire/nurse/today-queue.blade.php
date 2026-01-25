<section class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-1">
            <flux:heading size="xl" level="1">{{ __("Today's Queue") }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('Manage patient queue, vital signs, and forward to doctors.') }}
            </flux:text>
        </div>
        <flux:button href="{{ route('nurse.walk-in') }}" wire:navigate variant="primary" icon="plus">
            {{ __('Walk-in') }}
        </flux:button>
    </div>

    <!-- Pending Check-ins Alert -->
    @if($pendingCheckIns->isNotEmpty())
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
            <div class="flex items-center gap-2 text-sm font-medium text-amber-800 dark:text-amber-200">
                <flux:icon name="clock" class="h-5 w-5" />
                {{ __(':count patient(s) waiting to check in', ['count' => $pendingCheckIns->count()]) }}
            </div>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach($pendingCheckIns as $appointment)
                    <button
                        wire:click="openCheckInModal({{ $appointment->id }})"
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg border border-amber-300 bg-white px-3 py-2 text-sm font-medium text-amber-800 transition hover:bg-amber-100 dark:border-amber-700 dark:bg-amber-900/30 dark:text-amber-200 dark:hover:bg-amber-900/50"
                    >
                        <span class="font-semibold">{{ $appointment->consultationType?->short_name }}</span>
                        <span>{{ $appointment->patient_first_name }} {{ $appointment->patient_last_name }}</span>
                        @if($appointment->appointment_time)
                            <span class="text-xs opacity-75">{{ $appointment->appointment_time->format('h:i A') }}</span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Consultation Type Tabs -->
    <div class="border-b border-zinc-200 dark:border-zinc-700">
        <nav class="-mb-px flex gap-1 overflow-x-auto" aria-label="Consultation Types">
            <button
                wire:click="setConsultationType('')"
                type="button"
                class="inline-flex items-center gap-2 whitespace-nowrap border-b-2 px-4 py-3 text-sm font-medium transition {{ $consultationTypeFilter === '' ? 'border-zinc-900 text-zinc-900 dark:border-white dark:text-white' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-300' }}"
            >
                {{ __('All Queues') }}
                <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                    {{ $typeCounts['all'] ?? 0 }}
                </span>
            </button>
            @foreach($consultationTypes as $type)
                <button
                    wire:click="setConsultationType('{{ $type->id }}')"
                    type="button"
                    class="inline-flex items-center gap-2 whitespace-nowrap border-b-2 px-4 py-3 text-sm font-medium transition {{ $consultationTypeFilter == $type->id ? 'border-zinc-900 text-zinc-900 dark:border-white dark:text-white' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-300' }}"
                >
                    <span class="font-bold">{{ $type->short_name }}</span>
                    <span class="hidden sm:inline">{{ $type->name }}</span>
                    @if(isset($typeCounts[$type->id]) && $typeCounts[$type->id] > 0)
                        <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                            {{ $typeCounts[$type->id] }}
                        </span>
                    @endif
                </button>
            @endforeach
        </nav>
    </div>

    <!-- Currently Serving Display -->
    @if($currentServing->isNotEmpty())
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-800 dark:bg-emerald-900/20">
            <div class="flex items-center gap-2 text-sm font-medium text-emerald-800 dark:text-emerald-200">
                <flux:icon name="play-circle" class="h-5 w-5" />
                {{ __('Currently Serving') }}
            </div>
            <div class="mt-3 flex flex-wrap gap-3">
                @foreach($currentServing as $typeId => $queues)
                    @foreach($queues as $queue)
                        <div class="inline-flex items-center gap-2 rounded-lg border border-emerald-300 bg-white px-4 py-2 dark:border-emerald-700 dark:bg-emerald-900/30">
                            <span class="text-lg font-bold text-emerald-800 dark:text-emerald-200">
                                {{ $queue->formatted_number }}
                            </span>
                            <span class="text-xs text-emerald-600 dark:text-emerald-400">
                                {{ $queue->consultationType?->short_name }}
                            </span>
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>
    @endif

    <!-- Search & Status Filter -->
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <!-- Search -->
        <div class="w-full lg:w-64">
            <flux:input
                wire:model.live.debounce.300ms="search"
                type="search"
                placeholder="{{ __('Search queue # or patient...') }}"
                icon="magnifying-glass"
            />
        </div>

        <!-- Status Filter Pills -->
        <div class="flex flex-wrap items-center gap-2">
            <flux:button
                wire:click="setStatus('waiting')"
                :variant="$status === 'waiting' ? 'filled' : 'ghost'"
                size="sm"
            >
                {{ __('Waiting') }}
                @if($statusCounts['waiting'] > 0)
                    <span class="ml-1 rounded-full bg-zinc-200 px-1.5 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-600 dark:text-zinc-200">{{ $statusCounts['waiting'] }}</span>
                @endif
            </flux:button>
            <flux:button
                wire:click="setStatus('called')"
                :variant="$status === 'called' ? 'filled' : 'ghost'"
                size="sm"
            >
                {{ __('Called') }}
                @if($statusCounts['called'] > 0)
                    <span class="ml-1 rounded-full bg-zinc-200 px-1.5 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-600 dark:text-zinc-200">{{ $statusCounts['called'] }}</span>
                @endif
            </flux:button>
            <flux:button
                wire:click="setStatus('serving')"
                :variant="$status === 'serving' ? 'filled' : 'ghost'"
                size="sm"
            >
                {{ __('Serving') }}
                @if($statusCounts['serving'] > 0)
                    <span class="ml-1 rounded-full bg-zinc-200 px-1.5 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-600 dark:text-zinc-200">{{ $statusCounts['serving'] }}</span>
                @endif
            </flux:button>
            <flux:button
                wire:click="setStatus('skipped')"
                :variant="$status === 'skipped' ? 'filled' : 'ghost'"
                size="sm"
            >
                {{ __('Skipped') }}
                @if($statusCounts['skipped'] > 0)
                    <span class="ml-1 rounded-full bg-zinc-200 px-1.5 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-600 dark:text-zinc-200">{{ $statusCounts['skipped'] }}</span>
                @endif
            </flux:button>
            <flux:button
                wire:click="setStatus('completed')"
                :variant="$status === 'completed' ? 'filled' : 'ghost'"
                size="sm"
            >
                {{ __('Completed') }}
                @if($statusCounts['completed'] > 0)
                    <span class="ml-1 rounded-full bg-zinc-200 px-1.5 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-600 dark:text-zinc-200">{{ $statusCounts['completed'] }}</span>
                @endif
            </flux:button>
            <flux:button
                wire:click="setStatus('all')"
                :variant="$status === 'all' ? 'filled' : 'ghost'"
                size="sm"
            >
                {{ __('All') }}
                <span class="ml-1 rounded-full bg-zinc-200 px-1.5 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-600 dark:text-zinc-200">{{ $statusCounts['all'] }}</span>
            </flux:button>

            @if($statusCounts['waiting'] > 0 || $statusCounts['called'] > 0)
                <flux:button wire:click="serveNextAvailable" variant="primary" icon="play">
                    {{ __('Serve Next') }}
                </flux:button>
            @endif
        </div>
    </div>

    <!-- Queue Table -->
    @if($queues->isNotEmpty())
        <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Queue #') }}
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Patient') }}
                        </th>
                        @if($consultationTypeFilter === '')
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                {{ __('Type') }}
                            </th>
                        @endif
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Priority') }}
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Status') }}
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Vitals') }}
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach($queues as $queue)
                        @php
                            $patientName = $queue->appointment
                                ? $queue->appointment->patient_first_name . ' ' . $queue->appointment->patient_last_name
                                : __('Walk-in');
                            $hasVitals = $queue->medicalRecord?->vital_signs_recorded_at !== null;
                        @endphp
                        <tr wire:key="queue-{{ $queue->id }}" class="@if($queue->status === 'serving') bg-emerald-50 dark:bg-emerald-900/10 @elseif($queue->status === 'skipped') bg-zinc-100 dark:bg-zinc-800/50 @endif">
                            <td class="whitespace-nowrap px-4 py-4">
                                <span class="text-lg font-bold text-zinc-900 dark:text-white">
                                    {{ $queue->formatted_number }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ $patientName }}
                                </div>
                                @if($queue->source === 'walk-in')
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Walk-in') }}</span>
                                @endif
                            </td>
                            @if($consultationTypeFilter === '')
                                <td class="whitespace-nowrap px-4 py-4">
                                    <span class="inline-flex items-center rounded bg-zinc-100 px-2 py-1 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                        {{ $queue->consultationType?->short_name ?? '-' }}
                                    </span>
                                </td>
                            @endif
                            <td class="whitespace-nowrap px-4 py-4">
                                @if($queue->priority === 'emergency')
                                    <span class="inline-flex items-center rounded bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                        {{ __('Emergency') }}
                                    </span>
                                @elseif($queue->priority === 'urgent')
                                    <span class="inline-flex items-center rounded bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                                        {{ __('Urgent') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                        {{ __('Normal') }}
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-4">
                                @if($queue->status === 'waiting')
                                    <span class="inline-flex items-center rounded bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                        {{ __('Waiting') }}
                                    </span>
                                @elseif($queue->status === 'called')
                                    <span class="inline-flex items-center rounded bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                                        {{ __('Called') }}
                                    </span>
                                @elseif($queue->status === 'serving')
                                    <span class="inline-flex items-center rounded bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                                        {{ __('Serving') }}
                                    </span>
                                @elseif($queue->status === 'skipped')
                                    <span class="inline-flex items-center rounded bg-zinc-200 px-2 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400">
                                        {{ __('Skipped') }}
                                    </span>
                                @elseif($queue->status === 'completed')
                                    <span class="inline-flex items-center rounded bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                        {{ __('Completed') }}
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-4">
                                @if($hasVitals)
                                    <flux:icon name="check-circle" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                                @elseif($queue->status === 'serving')
                                    <flux:icon name="exclamation-circle" class="h-5 w-5 text-amber-500" />
                                @else
                                    <flux:icon name="minus-circle" class="h-5 w-5 text-zinc-300 dark:text-zinc-600" />
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($queue->status === 'waiting')
                                        <flux:button wire:click="callPatient({{ $queue->id }})" size="xs" variant="ghost" icon="megaphone">
                                            {{ __('Call') }}
                                        </flux:button>
                                        <flux:button wire:click="startServing({{ $queue->id }})" size="xs" variant="primary" icon="play">
                                            {{ __('Serve') }}
                                        </flux:button>
                                        <flux:button wire:click="openSkipModal({{ $queue->id }})" size="xs" variant="ghost" icon="forward">
                                            {{ __('Skip') }}
                                        </flux:button>
                                    @elseif($queue->status === 'called')
                                        <flux:button wire:click="startServing({{ $queue->id }})" size="xs" variant="primary" icon="play">
                                            {{ __('Serve') }}
                                        </flux:button>
                                        <flux:button wire:click="openSkipModal({{ $queue->id }})" size="xs" variant="ghost" icon="forward">
                                            {{ __('Skip') }}
                                        </flux:button>
                                    @elseif($queue->status === 'serving')
                                        <flux:button wire:click="openStopServingModal({{ $queue->id }})" size="xs" variant="ghost" icon="x-mark">
                                            {{ __('Stop') }}
                                        </flux:button>
                                        <flux:button wire:click="openVitalSignsModal({{ $queue->id }})" size="xs" variant="{{ $hasVitals ? 'ghost' : 'primary' }}" icon="heart">
                                            {{ __('Vitals') }}
                                        </flux:button>
                                        @if($hasVitals)
                                            <flux:button wire:click="forwardToDoctor({{ $queue->id }})" size="xs" variant="primary" icon="arrow-right">
                                                {{ __('Forward') }}
                                            </flux:button>
                                        @endif
                                    @elseif($queue->status === 'skipped')
                                        <flux:button wire:click="openRequeueModal({{ $queue->id }})" size="xs" variant="ghost" icon="arrow-path">
                                            {{ __('Requeue') }}
                                        </flux:button>
                                    @elseif($queue->status === 'completed')
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ __('Forwarded') }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="rounded-lg border border-zinc-200 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                <flux:icon name="queue-list" class="h-6 w-6 text-zinc-400" />
            </div>
            <h3 class="mt-4 text-sm font-medium text-zinc-900 dark:text-white">{{ __('No patients in queue') }}</h3>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                @if($status === 'waiting')
                    {{ __('No patients are currently waiting.') }}
                @elseif($status === 'serving')
                    {{ __('No patients are currently being served.') }}
                @elseif($status === 'skipped')
                    {{ __('No skipped patients.') }}
                @else
                    {{ __('The queue is empty for this filter.') }}
                @endif
            </p>
            <div class="mt-4">
                <flux:button href="{{ route('nurse.walk-in') }}" wire:navigate variant="primary" icon="plus">
                    {{ __('Register Walk-in') }}
                </flux:button>
            </div>
        </div>
    @endif

    <!-- Check-in Modal -->
    <flux:modal wire:model="showCheckInModal" class="max-w-md">
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('Confirm Check-in') }}</flux:heading>

            @if($checkInAppointmentId)
                @php
                    $checkInAppt = $pendingCheckIns->firstWhere('id', $checkInAppointmentId);
                @endphp
                @if($checkInAppt)
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                        <dl class="space-y-2">
                            <div class="flex justify-between">
                                <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Patient') }}</dt>
                                <dd class="text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ $checkInAppt->patient_first_name }} {{ $checkInAppt->patient_last_name }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Type') }}</dt>
                                <dd class="text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ $checkInAppt->consultationType?->name }}
                                </dd>
                            </div>
                            @if($checkInAppt->appointment_time)
                                <div class="flex justify-between">
                                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Time') }}</dt>
                                    <dd class="text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ $checkInAppt->appointment_time->format('h:i A') }}
                                    </dd>
                                </div>
                            @endif
                            @if($checkInAppt->queue)
                                <div class="flex justify-between">
                                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Queue #') }}</dt>
                                    <dd class="text-sm font-bold text-zinc-900 dark:text-white">
                                        {{ $checkInAppt->queue->formatted_number }}
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                @endif
            @endif

            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('Confirm that this patient has arrived.') }}
            </p>

            <div class="flex justify-end gap-3">
                <flux:button wire:click="closeCheckInModal" variant="ghost">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button wire:click="confirmCheckIn" variant="primary" icon="check">
                    {{ __('Check In') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- Vital Signs Modal -->
    <flux:modal wire:model="showVitalSignsModal" class="max-w-2xl">
        <div class="space-y-6">
            <flux:heading size="lg">{{ __('Record Vital Signs') }}</flux:heading>

            @if($vitalSignsQueueId)
                @php
                    $vsQueue = $queues->firstWhere('id', $vitalSignsQueueId);
                    $consultationType = $vsQueue?->consultationType;
                    $isOb = $consultationType?->short_name === 'O';
                    $isPedia = $consultationType?->short_name === 'P';
                @endphp

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <!-- Common Vitals -->
                    <flux:field>
                        <flux:label>{{ __('Temperature') }} (°C)</flux:label>
                        <flux:input type="number" wire:model="temperature" step="0.1" min="30" max="45" placeholder="36.5" />
                        <flux:error name="temperature" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Blood Pressure') }} (mmHg)</flux:label>
                        <flux:input wire:model="bloodPressure" placeholder="120/80" />
                        <flux:error name="bloodPressure" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Cardiac Rate') }} (bpm)</flux:label>
                        <flux:input type="number" wire:model="cardiacRate" min="30" max="250" placeholder="72" />
                        <flux:error name="cardiacRate" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Respiratory Rate') }} (/min)</flux:label>
                        <flux:input type="number" wire:model="respiratoryRate" min="5" max="60" placeholder="16" />
                        <flux:error name="respiratoryRate" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Weight') }} (kg)</flux:label>
                        <flux:input type="number" wire:model="weight" step="0.01" min="0.1" max="500" placeholder="60.5" />
                        <flux:error name="weight" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Height') }} (cm)</flux:label>
                        <flux:input type="number" wire:model="height" step="0.1" min="10" max="300" placeholder="165" />
                        <flux:error name="height" />
                    </flux:field>

                    @if($isPedia)
                        <!-- Pediatric Specific -->
                        <flux:field>
                            <flux:label>{{ __('Head Circumference') }} (cm)</flux:label>
                            <flux:input type="number" wire:model="headCircumference" step="0.1" min="20" max="100" />
                            <flux:error name="headCircumference" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Chest Circumference') }} (cm)</flux:label>
                            <flux:input type="number" wire:model="chestCircumference" step="0.1" min="20" max="200" />
                            <flux:error name="chestCircumference" />
                        </flux:field>
                    @endif

                    @if($isOb)
                        <!-- OB Specific -->
                        <flux:field>
                            <flux:label>{{ __('Fetal Heart Tone') }} (bpm)</flux:label>
                            <flux:input type="number" wire:model="fetalHeartTone" min="60" max="200" />
                            <flux:error name="fetalHeartTone" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Fundal Height') }} (cm)</flux:label>
                            <flux:input type="number" wire:model="fundalHeight" step="0.1" min="5" max="50" />
                            <flux:error name="fundalHeight" />
                        </flux:field>

                        <flux:field class="sm:col-span-2">
                            <flux:label>{{ __('Last Menstrual Period') }}</flux:label>
                            <flux:input type="date" wire:model="lastMenstrualPeriod" max="{{ now()->format('Y-m-d') }}" />
                            <flux:error name="lastMenstrualPeriod" />
                        </flux:field>
                    @endif
                </div>

                <flux:field>
                    <flux:label>{{ __('Updated Chief Complaints') }}</flux:label>
                    <flux:textarea wire:model="chiefComplaintsUpdated" rows="2" placeholder="{{ __('Any additional symptoms...') }}" />
                    <flux:error name="chiefComplaintsUpdated" />
                </flux:field>
            @endif

            <div class="flex justify-end gap-3">
                <flux:button wire:click="closeVitalSignsModal" variant="ghost">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button wire:click="saveVitalSigns" variant="primary" icon="check">
                    {{ __('Save') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- Requeue Patient Modal -->
    <flux:modal wire:model="showRequeueModal" class="max-w-md">
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('Requeue Patient?') }}</flux:heading>

            @if($requeueQueueId)
                @php
                    $requeueQueue = $queues->firstWhere('id', $requeueQueueId);
                @endphp
                @if($requeueQueue)
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl font-bold text-zinc-900 dark:text-white">
                                {{ $requeueQueue->formatted_number }}
                            </span>
                            <div>
                                <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ $requeueQueue->appointment?->patient_first_name }} {{ $requeueQueue->appointment?->patient_last_name }}
                                </div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $requeueQueue->consultationType?->name }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('This patient will be returned to the waiting queue with the same queue number.') }}
            </p>

            <div class="flex justify-end gap-3">
                <flux:button wire:click="closeRequeueModal" variant="ghost">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button wire:click="confirmRequeue" variant="primary" icon="arrow-path">
                    {{ __('Requeue Patient') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- Skip Patient Modal -->
    <flux:modal wire:model="showSkipModal" class="max-w-md">
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('Skip Patient?') }}</flux:heading>

            @if($skipQueueId)
                @php
                    $skipQueue = $queues->firstWhere('id', $skipQueueId);
                @endphp
                @if($skipQueue)
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl font-bold text-zinc-900 dark:text-white">
                                {{ $skipQueue->formatted_number }}
                            </span>
                            <div>
                                <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ $skipQueue->appointment?->patient_first_name }} {{ $skipQueue->appointment?->patient_last_name }}
                                </div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $skipQueue->consultationType?->name }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('This patient will be moved to the skipped list. You can requeue them later when they arrive.') }}
            </p>

            <div class="flex justify-end gap-3">
                <flux:button wire:click="closeSkipModal" variant="ghost">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button wire:click="confirmSkip" variant="filled" icon="forward">
                    {{ __('Skip Patient') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- Stop Serving Modal -->
    <flux:modal wire:model="showStopServingModal" class="max-w-md">
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('Stop Serving Patient?') }}</flux:heading>

            @if($stopServingQueueId)
                @php
                    $stopQueue = $queues->firstWhere('id', $stopServingQueueId);
                @endphp
                @if($stopQueue)
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl font-bold text-zinc-900 dark:text-white">
                                {{ $stopQueue->formatted_number }}
                            </span>
                            <div>
                                <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ $stopQueue->appointment?->patient_first_name }} {{ $stopQueue->appointment?->patient_last_name }}
                                </div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $stopQueue->consultationType?->name }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('This patient will be returned to the waiting queue. Any unsaved vital signs will be discarded.') }}
            </p>

            <div class="flex justify-end gap-3">
                <flux:button wire:click="closeStopServingModal" variant="ghost">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button wire:click="confirmStopServing" variant="danger" icon="x-mark">
                    {{ __('Stop Serving') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</section>
