<section class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-1">
            <flux:heading size="xl" level="1">{{ __('Today\'s Queue') }}</flux:heading>
            <flux:text variant="subtle" class="text-sm">
                {{ __('Manage patient queue, vital signs, and forward to doctors.') }}
            </flux:text>
        </div>
        <flux:button href="{{ route('nurse.walk-in') }}" wire:navigate variant="primary" icon="plus">
            {{ __('Walk-in Patient') }}
        </flux:button>
    </div>

    <!-- Current Serving Display -->
    @if($currentServing->isNotEmpty())
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
            <div class="flex items-center gap-2 text-sm font-medium text-blue-800 dark:text-blue-200">
                <flux:icon name="play-circle" class="h-5 w-5" />
                {{ __('Currently Serving') }}
            </div>
            <div class="mt-2 flex flex-wrap gap-3">
                @foreach($currentServing as $typeId => $queues)
                    @foreach($queues as $queue)
                        <span class="inline-flex items-center gap-2 rounded-full bg-blue-100 px-4 py-2 text-sm font-bold text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                            {{ $queue->formatted_number }}
                        </span>
                    @endforeach
                @endforeach
            </div>
        </div>
    @endif

    <!-- Pending Check-ins Alert -->
    @if($pendingCheckIns->isNotEmpty())
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-sm font-medium text-amber-800 dark:text-amber-200">
                    <flux:icon name="clock" class="h-5 w-5" />
                    {{ __(':count patient(s) waiting to check in', ['count' => $pendingCheckIns->count()]) }}
                </div>
            </div>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach($pendingCheckIns as $appointment)
                    <button
                        wire:click="openCheckInModal({{ $appointment->id }})"
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg border border-amber-300 bg-white px-3 py-2 text-sm font-medium text-amber-800 shadow-sm transition hover:bg-amber-100 dark:border-amber-700 dark:bg-amber-900/40 dark:text-amber-200 dark:hover:bg-amber-900/60"
                    >
                        <span>{{ $appointment->patient_first_name }} {{ $appointment->patient_last_name }}</span>
                        <span class="text-xs text-amber-600 dark:text-amber-400">
                            @if($appointment->appointment_time)
                                {{ $appointment->appointment_time->format('h:i A') }}
                            @else
                                {{ $appointment->consultationType?->short_name }}
                            @endif
                        </span>
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Status Tabs -->
    <div class="flex flex-wrap items-center gap-2">
        <flux:button
            wire:click="setStatus('waiting')"
            :variant="$status === 'waiting' ? 'primary' : 'ghost'"
            size="sm"
        >
            {{ __('Waiting') }}
            @if($statusCounts['waiting'] > 0)
                <flux:badge color="yellow" size="sm" class="ml-1">{{ $statusCounts['waiting'] }}</flux:badge>
            @endif
        </flux:button>
        <flux:button
            wire:click="setStatus('called')"
            :variant="$status === 'called' ? 'primary' : 'ghost'"
            size="sm"
        >
            {{ __('Called') }}
            @if($statusCounts['called'] > 0)
                <flux:badge color="orange" size="sm" class="ml-1">{{ $statusCounts['called'] }}</flux:badge>
            @endif
        </flux:button>
        <flux:button
            wire:click="setStatus('serving')"
            :variant="$status === 'serving' ? 'primary' : 'ghost'"
            size="sm"
        >
            {{ __('Serving') }}
            @if($statusCounts['serving'] > 0)
                <flux:badge color="blue" size="sm" class="ml-1">{{ $statusCounts['serving'] }}</flux:badge>
            @endif
        </flux:button>
        <flux:button
            wire:click="setStatus('completed')"
            :variant="$status === 'completed' ? 'primary' : 'ghost'"
            size="sm"
        >
            {{ __('Completed') }}
            @if($statusCounts['completed'] > 0)
                <flux:badge color="green" size="sm" class="ml-1">{{ $statusCounts['completed'] }}</flux:badge>
            @endif
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

    <!-- Filter by Type -->
    <div class="flex items-center gap-3">
        <flux:select wire:model.live="consultationTypeFilter" class="w-48" placeholder="{{ __('All types') }}">
            <flux:select.option value="">{{ __('All types') }}</flux:select.option>
            @foreach($consultationTypes as $type)
                <flux:select.option value="{{ $type->id }}">{{ $type->name }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <!-- Queue Table -->
    @if($queues->isNotEmpty())
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Queue #') }}
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Patient') }}
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Type') }}
                        </th>
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
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @foreach($queues as $queue)
                        @php
                            $patientName = $queue->appointment
                                ? $queue->appointment->patient_first_name . ' ' . $queue->appointment->patient_last_name
                                : __('Walk-in');
                            $hasVitals = $queue->medicalRecord?->vital_signs_recorded_at !== null;
                        @endphp
                        <tr wire:key="queue-{{ $queue->id }}" class="@if($queue->status === 'serving') bg-blue-50 dark:bg-blue-900/10 @endif">
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
                            <td class="whitespace-nowrap px-4 py-4">
                                @php
                                    $shortName = $queue->consultationType?->short_name ?? '?';
                                    $typeColors = [
                                        'O' => 'bg-pink-100 text-pink-700 dark:bg-pink-900/30 dark:text-pink-400',
                                        'P' => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-400',
                                        'G' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                    ];
                                    $typeClass = $typeColors[$shortName] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $typeClass }}">
                                    {{ $queue->consultationType?->name ?? '-' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4">
                                @php
                                    $priorityColors = [
                                        'normal' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300',
                                        'urgent' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                                        'emergency' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                    ];
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $priorityColors[$queue->priority] ?? $priorityColors['normal'] }}">
                                    {{ ucfirst($queue->priority) }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4">
                                @php
                                    $statusColors = [
                                        'waiting' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                        'called' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                                        'serving' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                        'completed' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                        'skipped' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                        'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                    ];
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColors[$queue->status] ?? $statusColors['waiting'] }}">
                                    {{ ucfirst($queue->status) }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4">
                                @if($hasVitals)
                                    <flux:icon name="check-circle" class="h-5 w-5 text-green-500" />
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
                                    @elseif($queue->status === 'called')
                                        <flux:button wire:click="startServing({{ $queue->id }})" size="xs" variant="primary" icon="play">
                                            {{ __('Serve') }}
                                        </flux:button>
                                        <flux:button wire:click="skipPatient({{ $queue->id }})" size="xs" variant="ghost" icon="forward">
                                            {{ __('Skip') }}
                                        </flux:button>
                                    @elseif($queue->status === 'serving')
                                        <flux:button wire:click="openVitalSignsModal({{ $queue->id }})" size="xs" variant="{{ $hasVitals ? 'ghost' : 'primary' }}" icon="heart">
                                            {{ __('Vitals') }}
                                        </flux:button>
                                        @if($hasVitals)
                                            <flux:button wire:click="forwardToDoctor({{ $queue->id }})" size="xs" variant="primary" icon="arrow-right">
                                                {{ __('Forward') }}
                                            </flux:button>
                                        @endif
                                    @elseif($queue->status === 'skipped')
                                        <flux:button wire:click="requeuePatient({{ $queue->id }})" size="xs" variant="ghost" icon="arrow-path">
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
        <div class="rounded-xl border border-zinc-200/70 bg-white p-8 text-center shadow-sm dark:border-zinc-800 dark:bg-zinc-900 space-y-4">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                <flux:icon name="queue-list" class="h-8 w-8 text-zinc-400" />
            </div>
            <flux:heading size="lg" level="2">{{ __('No patients in queue') }}</flux:heading>
            <flux:text variant="subtle" class="text-sm">
                @if($status === 'waiting')
                    {{ __('No patients are currently waiting.') }}
                @elseif($status === 'serving')
                    {{ __('No patients are currently being served.') }}
                @else
                    {{ __('The queue is empty for this status.') }}
                @endif
            </flux:text>
            <flux:button href="{{ route('nurse.walk-in') }}" wire:navigate variant="primary" icon="plus">
                {{ __('Register Walk-in') }}
            </flux:button>
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
                    <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800">
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

            <flux:text variant="subtle" class="text-sm">
                {{ __('Confirm that this patient has arrived and is ready to be seen.') }}
            </flux:text>

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
                    <flux:textarea wire:model="chiefComplaintsUpdated" rows="2" placeholder="{{ __('Any additional symptoms or updates...') }}" />
                    <flux:error name="chiefComplaintsUpdated" />
                </flux:field>
            @endif

            <div class="flex justify-end gap-3">
                <flux:button wire:click="closeVitalSignsModal" variant="ghost">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button wire:click="saveVitalSigns" variant="primary" icon="check">
                    {{ __('Save Vital Signs') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</section>
