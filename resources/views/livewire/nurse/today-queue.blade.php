<section class="space-y-4">
    <!-- Header -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" level="1">{{ __("Today's Queue") }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ now()->format('l, F j, Y') }}</flux:text>
        </div>
        <flux:button href="{{ route('nurse.walk-in') }}" wire:navigate variant="primary" icon="plus">
            {{ __('Walk-in') }}
        </flux:button>
    </div>

    <!-- Quick Stats -->
    <div class="flex flex-wrap gap-2">
        <div class="flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 dark:border-amber-800 dark:bg-amber-900/20">
            <flux:icon name="clock" class="h-4 w-4 text-amber-600 dark:text-amber-400" />
            <span class="text-sm font-semibold text-amber-800 dark:text-amber-200">{{ $pendingCheckIns->count() }}</span>
            <span class="text-xs text-amber-700 dark:text-amber-300">{{ __('Check-ins') }}</span>
        </div>
        <button wire:click="setStatus('waiting')" class="flex items-center gap-2 rounded-lg border px-3 py-2 transition {{ $status === 'waiting' ? 'border-blue-500 bg-blue-50 dark:border-blue-400 dark:bg-blue-900/30' : 'border-zinc-200 bg-white hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700' }}">
            <span class="h-2 w-2 rounded-full bg-blue-500"></span>
            <span class="text-sm font-semibold">{{ $statusCounts['waiting'] + $statusCounts['called'] }}</span>
            <span class="text-xs text-zinc-600 dark:text-zinc-400">{{ __('In Queue') }}</span>
            @if($statusCounts['called'] > 0)
                <span class="ml-1 flex items-center gap-1 rounded bg-purple-100 px-1.5 py-0.5 text-[10px] font-medium text-purple-700 dark:bg-purple-900/50 dark:text-purple-300">
                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-purple-500"></span>
                    {{ $statusCounts['called'] }} {{ __('called') }}
                </span>
            @endif
        </button>
        <button wire:click="setStatus('serving')" class="flex items-center gap-2 rounded-lg border px-3 py-2 transition {{ $status === 'serving' ? 'border-emerald-500 bg-emerald-50 dark:border-emerald-400 dark:bg-emerald-900/30' : 'border-zinc-200 bg-white hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700' }}">
            <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"></span>
            <span class="text-sm font-semibold">{{ $statusCounts['serving'] }}</span>
            <span class="text-xs text-zinc-600 dark:text-zinc-400">{{ __('Serving') }}</span>
        </button>
        <button wire:click="setStatus('completed')" class="flex items-center gap-2 rounded-lg border px-3 py-2 transition {{ $status === 'completed' ? 'border-zinc-500 bg-zinc-100 dark:border-zinc-400 dark:bg-zinc-700' : 'border-zinc-200 bg-white hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700' }}">
            <span class="h-2 w-2 rounded-full bg-zinc-400"></span>
            <span class="text-sm font-semibold">{{ $statusCounts['completed'] }}</span>
            <span class="text-xs text-zinc-600 dark:text-zinc-400">{{ __('Done') }}</span>
        </button>
        @if($statusCounts['skipped'] > 0)
            <button wire:click="setStatus('skipped')" class="flex items-center gap-2 rounded-lg border px-3 py-2 transition {{ $status === 'skipped' ? 'border-zinc-500 bg-zinc-100 dark:border-zinc-400 dark:bg-zinc-700' : 'border-zinc-200 bg-white hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700' }}">
                <span class="h-2 w-2 rounded-full bg-zinc-300"></span>
                <span class="text-sm font-semibold">{{ $statusCounts['skipped'] }}</span>
                <span class="text-xs text-zinc-600 dark:text-zinc-400">{{ __('Skipped') }}</span>
            </button>
        @endif
    </div>

    <!-- Pending Check-ins -->
    @if($pendingCheckIns->isNotEmpty())
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-800 dark:bg-amber-900/20">
            <p class="mb-2 text-xs font-medium text-amber-700 dark:text-amber-300">{{ __('Click to check in:') }}</p>
            <div class="flex flex-wrap gap-2">
                @foreach($pendingCheckIns as $appointment)
                    <button
                        wire:click="openCheckInModal({{ $appointment->id }})"
                        class="inline-flex items-center gap-1.5 rounded-md border border-amber-300 bg-white px-2 py-1 text-xs font-medium text-amber-800 transition hover:bg-amber-100 dark:border-amber-700 dark:bg-amber-900/30 dark:text-amber-200"
                    >
                        <span class="font-bold">{{ $appointment->consultationType?->short_name }}</span>
                        {{ $appointment->patient_first_name }}
                        @if($appointment->appointment_time)
                            <span class="opacity-60">{{ $appointment->appointment_time->format('h:i A') }}</span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Main Split View -->
    <div class="grid gap-4 lg:grid-cols-3">
        <!-- Left Panel: Queue Grid -->
        <div class="lg:col-span-2 space-y-3">
            <!-- Consultation Type Filter -->
            <div class="flex flex-wrap items-center gap-2">
                <button
                    wire:click="setConsultationType('')"
                    class="rounded-full px-3 py-1 text-xs font-medium transition {{ $consultationTypeFilter === '' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300' }}"
                >
                    {{ __('All') }} ({{ $typeCounts['all'] ?? 0 }})
                </button>
                @foreach($consultationTypes as $type)
                    @if(isset($typeCounts[$type->id]) && $typeCounts[$type->id] > 0)
                        <button
                            wire:click="setConsultationType('{{ $type->id }}')"
                            class="rounded-full px-3 py-1 text-xs font-medium transition {{ $consultationTypeFilter == $type->id ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300' }}"
                        >
                            {{ $type->short_name }} ({{ $typeCounts[$type->id] }})
                        </button>
                    @endif
                @endforeach

                @if($statusCounts['waiting'] > 0 || $statusCounts['called'] > 0)
                    <div class="ml-auto">
                        <flux:button wire:click="serveNextAvailable" size="sm" variant="primary" icon="play">
                            {{ __('Serve Next') }}
                        </flux:button>
                    </div>
                @endif
            </div>

            <!-- Queue Cards Grid -->
            @if($queues->isNotEmpty())
                <div class="grid grid-cols-3 gap-2 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-4 xl:grid-cols-5">
                    @foreach($queues as $queue)
                        @php
                            $isSelected = $selectedQueueId === $queue->id;
                            $hasVitals = $queue->medicalRecord?->vital_signs_recorded_at !== null;
                            $patientName = $queue->appointment
                                ? $queue->appointment->patient_first_name
                                : __('Walk-in');
                        @endphp
                        <button
                            wire:click="selectQueue({{ $queue->id }})"
                            wire:key="queue-card-{{ $queue->id }}"
                            class="relative flex flex-col items-center justify-center rounded-lg border-2 p-3 text-center transition
                                @if($isSelected) ring-2 ring-offset-2 dark:ring-offset-zinc-900
                                    @if($queue->status === 'serving') ring-emerald-500 border-emerald-500 bg-emerald-500 text-white
                                    @elseif($queue->status === 'waiting') ring-blue-500 border-blue-500 bg-blue-500 text-white
                                    @elseif($queue->status === 'called') ring-purple-500 border-purple-500 bg-purple-500 text-white
                                    @else ring-zinc-500 border-zinc-400 bg-zinc-400 text-white @endif
                                @else
                                    @if($queue->status === 'serving') border-emerald-400 bg-emerald-500 text-white hover:bg-emerald-600
                                    @elseif($queue->status === 'waiting') border-blue-300 bg-blue-500 text-white hover:bg-blue-600
                                    @elseif($queue->status === 'called') border-purple-300 bg-purple-500 text-white hover:bg-purple-600
                                    @elseif($queue->status === 'skipped') border-zinc-300 bg-zinc-200 text-zinc-600 hover:bg-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-300
                                    @else border-zinc-300 bg-zinc-100 text-zinc-500 hover:bg-zinc-200 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 @endif
                                @endif"
                        >
                            <!-- Priority Badge -->
                            @if($queue->priority === 'emergency')
                                <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[8px] font-bold text-white">!</span>
                            @elseif($queue->priority === 'urgent')
                                <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-amber-500 text-[8px] font-bold text-white">U</span>
                            @endif

                            <!-- Vitals Indicator -->
                            @if($queue->status === 'serving' && $hasVitals)
                                <span class="absolute -bottom-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-white text-emerald-600">
                                    <flux:icon name="check-circle" class="h-3 w-3" />
                                </span>
                            @endif

                            <!-- Queue Number -->
                            <span class="text-xl font-bold leading-none">{{ $queue->formatted_number }}</span>

                            <!-- Patient Name (truncated) -->
                            <span class="mt-1 w-full truncate text-[10px] opacity-90">{{ $patientName }}</span>

                            <!-- Time -->
                            @if($queue->status === 'serving' && $queue->serving_started_at)
                                <span class="mt-0.5 text-[9px] opacity-75">{{ $queue->serving_started_at->diffForHumans(short: true) }}</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            @else
                <div class="rounded-lg border border-dashed border-zinc-300 p-8 text-center dark:border-zinc-600">
                    <flux:icon name="queue-list" class="mx-auto h-8 w-8 text-zinc-400" />
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('No patients in this queue') }}</p>
                </div>
            @endif
        </div>

        <!-- Right Panel: Selected Queue Detail -->
        <div class="lg:col-span-1">
            @if($selectedQueue)
                @php
                    $hasVitals = $selectedQueue->medicalRecord?->vital_signs_recorded_at !== null;
                    $patientName = $selectedQueue->appointment
                        ? $selectedQueue->appointment->patient_first_name . ' ' . $selectedQueue->appointment->patient_last_name
                        : __('Walk-in Patient');
                @endphp
                <div class="sticky top-4 rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <!-- Header -->
                    <div class="flex items-start justify-between border-b border-zinc-200 p-4 dark:border-zinc-700">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-2xl font-bold
                                    @if($selectedQueue->status === 'serving') text-emerald-600 dark:text-emerald-400
                                    @elseif($selectedQueue->status === 'waiting') text-blue-600 dark:text-blue-400
                                    @elseif($selectedQueue->status === 'called') text-purple-600 dark:text-purple-400
                                    @else text-zinc-600 dark:text-zinc-400 @endif">
                                    {{ $selectedQueue->formatted_number }}
                                </span>
                                @if($selectedQueue->priority === 'emergency')
                                    <span class="rounded bg-red-100 px-1.5 py-0.5 text-xs font-bold text-red-700 dark:bg-red-900/50 dark:text-red-300">{{ __('EMERGENCY') }}</span>
                                @elseif($selectedQueue->priority === 'urgent')
                                    <span class="rounded bg-amber-100 px-1.5 py-0.5 text-xs font-bold text-amber-700 dark:bg-amber-900/50 dark:text-amber-300">{{ __('URGENT') }}</span>
                                @endif
                            </div>
                            <p class="mt-1 text-sm font-medium text-zinc-900 dark:text-white">{{ $patientName }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $selectedQueue->consultationType?->name }}</p>
                        </div>
                        <button wire:click="clearSelection" class="rounded p-1 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                            <flux:icon name="x-mark" class="h-5 w-5" />
                        </button>
                    </div>

                    <!-- Status -->
                    <div class="border-b border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</span>
                            @if($selectedQueue->status === 'waiting')
                                <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">
                                    <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                                    {{ __('Waiting') }}
                                </span>
                            @elseif($selectedQueue->status === 'called')
                                <span class="inline-flex items-center gap-1 rounded-full bg-purple-100 px-2.5 py-1 text-xs font-semibold text-purple-800 dark:bg-purple-900/50 dark:text-purple-300">
                                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-purple-500"></span>
                                    {{ __('Called') }}
                                </span>
                            @elseif($selectedQueue->status === 'serving')
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
                                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-500"></span>
                                    {{ __('Being Served') }}
                                </span>
                            @elseif($selectedQueue->status === 'skipped')
                                <span class="inline-flex items-center gap-1 rounded-full bg-zinc-200 px-2.5 py-1 text-xs font-semibold text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400">
                                    {{ __('Skipped') }}
                                </span>
                            @elseif($selectedQueue->status === 'completed')
                                <span class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                    <flux:icon name="check" class="h-3 w-3" />
                                    {{ __('Forwarded') }}
                                </span>
                            @endif
                        </div>

                        @if($selectedQueue->status === 'serving')
                            <div class="mt-3 flex items-center gap-2">
                                @if($hasVitals)
                                    <span class="inline-flex items-center gap-1 rounded bg-emerald-100 px-2 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">
                                        <flux:icon name="check-circle" class="h-3.5 w-3.5" />
                                        {{ __('Vitals Recorded') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded bg-amber-100 px-2 py-1 text-xs font-medium text-amber-700 dark:bg-amber-900/50 dark:text-amber-300">
                                        <flux:icon name="exclamation-circle" class="h-3.5 w-3.5" />
                                        {{ __('Needs Interview & Vitals') }}
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Patient Info Preview -->
                    @if($selectedQueue->appointment)
                        <div class="border-b border-zinc-200 p-4 dark:border-zinc-700">
                            <p class="mb-2 text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">{{ __('Patient Info') }}</p>
                            <dl class="space-y-1 text-sm">
                                @if($selectedQueue->appointment->patient_date_of_birth)
                                    <div class="flex justify-between">
                                        <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Age') }}</dt>
                                        <dd class="font-medium text-zinc-900 dark:text-white">{{ $selectedQueue->appointment->patient_date_of_birth->age }} {{ __('yrs') }}</dd>
                                    </div>
                                @endif
                                @if($selectedQueue->appointment->patient_gender)
                                    <div class="flex justify-between">
                                        <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Gender') }}</dt>
                                        <dd class="font-medium text-zinc-900 dark:text-white">{{ ucfirst($selectedQueue->appointment->patient_gender) }}</dd>
                                    </div>
                                @endif
                                @if($selectedQueue->appointment->chief_complaints)
                                    <div class="mt-2">
                                        <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Chief Complaint') }}</dt>
                                        <dd class="mt-1 text-xs text-zinc-700 dark:text-zinc-300">{{ Str::limit($selectedQueue->appointment->chief_complaints, 100) }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="space-y-2 p-4">
                        @if($selectedQueue->status === 'waiting')
                            <flux:button wire:click="callPatient({{ $selectedQueue->id }})" class="w-full" icon="megaphone">
                                {{ __('Call Patient') }}
                            </flux:button>
                            <flux:button wire:click="startServing({{ $selectedQueue->id }})" class="w-full" variant="primary" icon="play">
                                {{ __('Start Serving') }}
                            </flux:button>
                            <flux:button wire:click="openSkipModal({{ $selectedQueue->id }})" class="w-full" variant="ghost" icon="forward">
                                {{ __('Skip') }}
                            </flux:button>

                        @elseif($selectedQueue->status === 'called')
                            <flux:button wire:click="startServing({{ $selectedQueue->id }})" class="w-full" variant="primary" icon="play">
                                {{ __('Start Serving') }}
                            </flux:button>
                            <flux:button wire:click="openSkipModal({{ $selectedQueue->id }})" class="w-full" variant="ghost" icon="forward">
                                {{ __('Skip') }}
                            </flux:button>

                        @elseif($selectedQueue->status === 'serving')
                            <flux:button wire:click="openInterviewModal({{ $selectedQueue->id }})" class="w-full" variant="{{ $hasVitals ? 'filled' : 'primary' }}" icon="clipboard-document-list">
                                {{ __('Patient Interview') }}
                            </flux:button>
                            @if($hasVitals)
                                <flux:button wire:click="forwardToDoctor({{ $selectedQueue->id }})" class="w-full" variant="primary" icon="arrow-right">
                                    {{ __('Forward to Doctor') }}
                                </flux:button>
                            @endif
                            <flux:button wire:click="openStopServingModal({{ $selectedQueue->id }})" class="w-full" variant="ghost" icon="x-mark">
                                {{ __('Stop Serving') }}
                            </flux:button>

                        @elseif($selectedQueue->status === 'skipped')
                            <flux:button wire:click="openRequeueModal({{ $selectedQueue->id }})" class="w-full" variant="primary" icon="arrow-path">
                                {{ __('Requeue Patient') }}
                            </flux:button>

                        @elseif($selectedQueue->status === 'completed')
                            <div class="rounded-lg bg-zinc-100 p-3 text-center text-sm text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                <flux:icon name="check-circle" class="mx-auto h-8 w-8 text-emerald-500" />
                                <p class="mt-2">{{ __('Patient has been forwarded to the doctor.') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <!-- Empty State -->
                <div class="rounded-lg border border-dashed border-zinc-300 bg-zinc-50 p-8 text-center dark:border-zinc-600 dark:bg-zinc-800/50">
                    <flux:icon name="cursor-arrow-rays" class="mx-auto h-10 w-10 text-zinc-400" />
                    <p class="mt-3 text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Select a queue') }}</p>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-500">{{ __('Click on a queue card to view details and take action') }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Check-in Modal -->
    <flux:modal wire:model="showCheckInModal" class="max-w-sm">
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('Confirm Check-in') }}</flux:heading>

            @if($checkInAppointmentId)
                @php $checkInAppt = $pendingCheckIns->firstWhere('id', $checkInAppointmentId); @endphp
                @if($checkInAppt)
                    <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800">
                        <p class="font-medium text-zinc-900 dark:text-white">{{ $checkInAppt->patient_first_name }} {{ $checkInAppt->patient_last_name }}</p>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $checkInAppt->consultationType?->name }}</p>
                        @if($checkInAppt->queue)
                            <p class="mt-2 text-lg font-bold text-zinc-900 dark:text-white">{{ __('Queue') }}: {{ $checkInAppt->queue->formatted_number }}</p>
                        @endif
                    </div>
                @endif
            @endif

            <div class="flex justify-end gap-2">
                <flux:button wire:click="closeCheckInModal" variant="ghost">{{ __('Cancel') }}</flux:button>
                <flux:button wire:click="confirmCheckIn" variant="primary" icon="check">{{ __('Check In') }}</flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- Patient Interview Modal -->
    <flux:modal wire:model="showInterviewModal" class="max-w-5xl">
        <div class="flex gap-6">
            {{-- Main Interview Form --}}
            <div class="flex-1 space-y-6">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">{{ __('Patient Interview') }}</flux:heading>
                    @if($this->interviewQueue)
                        <span class="rounded-lg bg-emerald-100 px-3 py-1 text-sm font-bold text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
                            {{ $this->interviewQueue->formatted_number }}
                        </span>
                    @endif
                </div>

                {{-- Validation Errors at Top --}}
                @if($errors->any())
                    <div class="rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-800 dark:bg-red-900/20">
                        <div class="flex items-start gap-2">
                            <flux:icon name="exclamation-circle" class="h-5 w-5 shrink-0 text-red-600 dark:text-red-400" />
                            <div>
                                <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ __('Please fix the following errors:') }}</p>
                                <ul class="mt-1 list-inside list-disc text-xs text-red-700 dark:text-red-300">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

            <!-- Step Indicator -->
            <div class="flex items-center justify-between border-b border-zinc-200 pb-4 dark:border-zinc-700">
                @php
                    $steps = [
                        'patient' => ['icon' => 'user', 'label' => __('Patient')],
                        'address' => ['icon' => 'map-pin', 'label' => __('Address')],
                        'companion' => ['icon' => 'users', 'label' => __('Companion')],
                        'medical' => ['icon' => 'heart', 'label' => __('Medical')],
                        'vitals' => ['icon' => 'clipboard-document-list', 'label' => __('Vitals')],
                    ];
                    $stepKeys = array_keys($steps);
                    $currentIndex = array_search($interviewStep, $stepKeys);
                @endphp
                @foreach($steps as $key => $step)
                    @php
                        $stepIndex = array_search($key, $stepKeys);
                        $isActive = $key === $interviewStep;
                        $isCompleted = $stepIndex < $currentIndex;
                    @endphp
                    <button wire:click="setInterviewStep('{{ $key }}')" class="flex flex-col items-center gap-1 {{ $isActive ? 'text-zinc-900 dark:text-white' : ($isCompleted ? 'text-emerald-600' : 'text-zinc-400') }}">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full {{ $isActive ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : ($isCompleted ? 'bg-emerald-100 dark:bg-emerald-900/50' : 'bg-zinc-100 dark:bg-zinc-800') }}">
                            @if($isCompleted)
                                <flux:icon name="check" class="h-4 w-4" />
                            @else
                                <flux:icon :name="$step['icon']" class="h-4 w-4" />
                            @endif
                        </div>
                        <span class="hidden text-xs font-medium sm:block">{{ $step['label'] }}</span>
                    </button>
                    @if(!$loop->last)
                        <div class="h-0.5 flex-1 {{ $isCompleted || $isActive ? 'bg-emerald-300 dark:bg-emerald-700' : 'bg-zinc-200 dark:bg-zinc-700' }}"></div>
                    @endif
                @endforeach
            </div>

            <!-- Step Content -->
            <div class="min-h-[280px]">
                @if($interviewStep === 'patient')
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <flux:field>
                            <flux:label>{{ __('First Name') }} *</flux:label>
                            <flux:input wire:model="patientFirstName" />
                            <flux:error name="patientFirstName" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('Middle Name') }}</flux:label>
                            <flux:input wire:model="patientMiddleName" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('Last Name') }} *</flux:label>
                            <flux:input wire:model="patientLastName" />
                            <flux:error name="patientLastName" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('Date of Birth') }}</flux:label>
                            <flux:input type="date" wire:model="patientDateOfBirth" max="{{ now()->format('Y-m-d') }}" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('Gender') }}</flux:label>
                            <flux:select wire:model="patientGender">
                                <flux:select.option value="">{{ __('Select...') }}</flux:select.option>
                                <flux:select.option value="male">{{ __('Male') }}</flux:select.option>
                                <flux:select.option value="female">{{ __('Female') }}</flux:select.option>
                            </flux:select>
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('Contact Number') }}</flux:label>
                            <flux:input wire:model="patientContactNumber" placeholder="09XX XXX XXXX" />
                        </flux:field>
                    </div>

                @elseif($interviewStep === 'address')
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <flux:field>
                            <flux:label>{{ __('Province') }}</flux:label>
                            <flux:input wire:model="patientProvince" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('Municipality/City') }}</flux:label>
                            <flux:input wire:model="patientMunicipality" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('Barangay') }}</flux:label>
                            <flux:input wire:model="patientBarangay" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('Zip Code') }}</flux:label>
                            <flux:input wire:model="patientZipCode" />
                        </flux:field>
                        <flux:field class="sm:col-span-2">
                            <flux:label>{{ __('Street Address') }}</flux:label>
                            <flux:input wire:model="patientStreet" />
                        </flux:field>
                    </div>

                @elseif($interviewStep === 'companion')
                    <div class="space-y-6">
                        {{-- Companion Section --}}
                        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                            <div class="mb-3 flex items-center gap-2">
                                <flux:icon name="user" class="h-4 w-4 text-zinc-500" />
                                <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ __('Companion') }}</p>
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('(Person accompanying the patient today)') }}</span>
                            </div>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                <flux:field>
                                    <flux:label>{{ __('Companion Name') }}</flux:label>
                                    <flux:input wire:model="companionName" placeholder="{{ __('Full name') }}" />
                                </flux:field>
                                <flux:field>
                                    <flux:label>{{ __('Companion Contact') }}</flux:label>
                                    <flux:input wire:model="companionContact" placeholder="{{ __('Phone number') }}" />
                                </flux:field>
                                <flux:field>
                                    <flux:label>{{ __('Relationship to Patient') }}</flux:label>
                                    <flux:input wire:model="companionRelationship" placeholder="{{ __('e.g., Mother, Spouse') }}" />
                                </flux:field>
                            </div>
                        </div>

                        {{-- Emergency Contact Section --}}
                        <div class="rounded-lg border border-amber-200 bg-amber-50/50 p-4 dark:border-amber-800 dark:bg-amber-900/10">
                            <div class="mb-3 flex items-center gap-2">
                                <flux:icon name="phone" class="h-4 w-4 text-amber-600 dark:text-amber-400" />
                                <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ __('Emergency Contact') }}</p>
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('(Person to contact in case of emergency)') }}</span>
                            </div>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                <flux:field>
                                    <flux:label>{{ __('Emergency Contact Name') }}</flux:label>
                                    <flux:input wire:model="emergencyContactName" placeholder="{{ __('Full name') }}" />
                                </flux:field>
                                <flux:field>
                                    <flux:label>{{ __('Emergency Contact Number') }}</flux:label>
                                    <flux:input wire:model="emergencyContactNumber" placeholder="{{ __('Phone number') }}" />
                                </flux:field>
                                <flux:field>
                                    <flux:label>{{ __('Relationship to Patient') }}</flux:label>
                                    <flux:input wire:model="emergencyContactRelationship" placeholder="{{ __('e.g., Father, Sibling') }}" />
                                </flux:field>
                            </div>
                        </div>
                    </div>

                @elseif($interviewStep === 'medical')
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <flux:field>
                            <flux:label>{{ __('Blood Type') }}</flux:label>
                            <flux:select wire:model="patientBloodType">
                                <flux:select.option value="">{{ __('Unknown') }}</flux:select.option>
                                @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bt)
                                    <flux:select.option value="{{ $bt }}">{{ $bt }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('Allergies') }}</flux:label>
                            <flux:input wire:model="patientAllergies" placeholder="{{ __('e.g., Penicillin') }}" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('Chronic Conditions') }}</flux:label>
                            <flux:input wire:model="patientChronicConditions" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('Current Medications') }}</flux:label>
                            <flux:input wire:model="patientCurrentMedications" />
                        </flux:field>
                        <flux:field class="sm:col-span-2">
                            <flux:label>{{ __('Past Medical History') }}</flux:label>
                            <flux:textarea wire:model="patientPastMedicalHistory" rows="2" />
                        </flux:field>
                    </div>

                @elseif($interviewStep === 'vitals')
                    @php
                        $consultationType = $this->interviewQueue?->consultationType;
                        $isOb = $consultationType?->short_name === 'O';
                        $isPedia = $consultationType?->short_name === 'P';
                        $vitalAlerts = $this->vitalAlerts;
                    @endphp

                    {{-- Vital Signs Alerts Banner --}}
                    @if(count($vitalAlerts) > 0)
                        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-800 dark:bg-amber-900/20">
                            <div class="flex items-start gap-2">
                                <flux:icon name="exclamation-triangle" class="h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400" />
                                <div>
                                    <p class="text-sm font-medium text-amber-800 dark:text-amber-200">{{ __('Abnormal Vital Signs Detected') }}</p>
                                    <ul class="mt-1 list-inside list-disc text-xs text-amber-700 dark:text-amber-300">
                                        @foreach($vitalAlerts as $field => $alert)
                                            <li>{{ $alert['message'] }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                            <flux:field>
                                <flux:label class="flex items-center gap-2">
                                    {{ __('Temperature') }} (°C)
                                    @if(isset($vitalAlerts['temperature']))
                                        <span class="rounded px-1.5 py-0.5 text-[10px] font-bold {{ $vitalAlerts['temperature']['level'] === 'danger' ? 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300' }}">
                                            {{ $vitalAlerts['temperature']['message'] }}
                                        </span>
                                    @endif
                                </flux:label>
                                <flux:input type="number" wire:model.live.debounce.500ms="temperature" step="0.1" placeholder="36.5" class="{{ isset($vitalAlerts['temperature']) ? ($vitalAlerts['temperature']['level'] === 'danger' ? 'border-red-500! ring-red-500!' : 'border-amber-500! ring-amber-500!') : '' }}" />
                                <flux:error name="temperature" />
                            </flux:field>
                            <flux:field>
                                <flux:label class="flex items-center gap-2">
                                    {{ __('Blood Pressure') }}
                                    @if(isset($vitalAlerts['bloodPressure']))
                                        <span class="rounded px-1.5 py-0.5 text-[10px] font-bold {{ $vitalAlerts['bloodPressure']['level'] === 'danger' ? 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300' }}">
                                            {{ $vitalAlerts['bloodPressure']['message'] }}
                                        </span>
                                    @endif
                                </flux:label>
                                <flux:input wire:model.live.debounce.500ms="bloodPressure" placeholder="120/80" class="{{ isset($vitalAlerts['bloodPressure']) ? ($vitalAlerts['bloodPressure']['level'] === 'danger' ? 'border-red-500! ring-red-500!' : 'border-amber-500! ring-amber-500!') : '' }}" />
                                <flux:error name="bloodPressure" />
                            </flux:field>
                            <flux:field>
                                <flux:label class="flex items-center gap-2">
                                    {{ __('Cardiac Rate') }}
                                    @if(isset($vitalAlerts['cardiacRate']))
                                        <span class="rounded px-1.5 py-0.5 text-[10px] font-bold {{ $vitalAlerts['cardiacRate']['level'] === 'danger' ? 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300' }}">
                                            {{ $vitalAlerts['cardiacRate']['message'] }}
                                        </span>
                                    @endif
                                </flux:label>
                                <flux:input type="number" wire:model.live.debounce.500ms="cardiacRate" placeholder="72" class="{{ isset($vitalAlerts['cardiacRate']) ? ($vitalAlerts['cardiacRate']['level'] === 'danger' ? 'border-red-500! ring-red-500!' : 'border-amber-500! ring-amber-500!') : '' }}" />
                            </flux:field>
                            <flux:field>
                                <flux:label class="flex items-center gap-2">
                                    {{ __('Respiratory Rate') }}
                                    @if(isset($vitalAlerts['respiratoryRate']))
                                        <span class="rounded px-1.5 py-0.5 text-[10px] font-bold {{ $vitalAlerts['respiratoryRate']['level'] === 'danger' ? 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300' }}">
                                            {{ $vitalAlerts['respiratoryRate']['message'] }}
                                        </span>
                                    @endif
                                </flux:label>
                                <flux:input type="number" wire:model.live.debounce.500ms="respiratoryRate" placeholder="16" class="{{ isset($vitalAlerts['respiratoryRate']) ? ($vitalAlerts['respiratoryRate']['level'] === 'danger' ? 'border-red-500! ring-red-500!' : 'border-amber-500! ring-amber-500!') : '' }}" />
                            </flux:field>
                            <flux:field>
                                <flux:label>{{ __('Weight') }} (kg)</flux:label>
                                <flux:input type="number" wire:model="weight" step="0.1" />
                            </flux:field>
                            <flux:field>
                                <flux:label>{{ __('Height') }} (cm)</flux:label>
                                <flux:input type="number" wire:model="height" step="0.1" />
                            </flux:field>
                            @if($isPedia)
                                <flux:field>
                                    <flux:label>{{ __('Head Circ.') }} (cm)</flux:label>
                                    <flux:input type="number" wire:model="headCircumference" step="0.1" />
                                </flux:field>
                            @endif
                            @if($isOb)
                                <flux:field>
                                    <flux:label class="flex items-center gap-2">
                                        {{ __('Fetal Heart Tone') }}
                                        @if(isset($vitalAlerts['fetalHeartTone']))
                                            <span class="rounded px-1.5 py-0.5 text-[10px] font-bold {{ $vitalAlerts['fetalHeartTone']['level'] === 'danger' ? 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300' }}">
                                                {{ $vitalAlerts['fetalHeartTone']['message'] }}
                                            </span>
                                        @endif
                                    </flux:label>
                                    <flux:input type="number" wire:model.live.debounce.500ms="fetalHeartTone" class="{{ isset($vitalAlerts['fetalHeartTone']) ? ($vitalAlerts['fetalHeartTone']['level'] === 'danger' ? 'border-red-500! ring-red-500!' : 'border-amber-500! ring-amber-500!') : '' }}" />
                                </flux:field>
                                <flux:field>
                                    <flux:label>{{ __('Fundal Height') }} (cm)</flux:label>
                                    <flux:input type="number" wire:model="fundalHeight" step="0.1" />
                                </flux:field>
                                <flux:field>
                                    <flux:label>{{ __('LMP') }}</flux:label>
                                    <flux:input type="date" wire:model="lastMenstrualPeriod" />
                                </flux:field>
                            @endif
                        </div>
                        <flux:field>
                            <flux:label>{{ __('Updated Chief Complaints') }}</flux:label>
                            <flux:textarea wire:model="chiefComplaintsUpdated" rows="2" />
                        </flux:field>
                    </div>
                @endif
            </div>

            <!-- Footer -->
                <div class="flex items-center justify-between border-t border-zinc-200 pt-4 dark:border-zinc-700">
                    <div>
                        @if($interviewStep !== 'patient')
                            <flux:button wire:click="previousInterviewStep" variant="ghost" icon="arrow-left">{{ __('Back') }}</flux:button>
                        @endif
                    </div>
                    <div class="flex gap-2">
                        <flux:button wire:click="closeInterviewModal" variant="ghost">{{ __('Cancel') }}</flux:button>
                        @if($interviewStep !== 'vitals')
                            <flux:button wire:click="nextInterviewStep" variant="primary" icon-trailing="arrow-right">{{ __('Next') }}</flux:button>
                        @else
                            <flux:button wire:click="saveInterview" wire:loading.attr="disabled" variant="primary" icon="check">
                                <span wire:loading.remove wire:target="saveInterview">{{ __('Save') }}</span>
                                <span wire:loading wire:target="saveInterview">{{ __('Saving...') }}</span>
                            </flux:button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Patient History Sidebar --}}
            @if($this->patientHistory->isNotEmpty())
                <div class="hidden w-64 shrink-0 border-l border-zinc-200 pl-6 dark:border-zinc-700 lg:block">
                    <h3 class="mb-3 text-sm font-medium text-zinc-900 dark:text-white">{{ __('Previous Visits') }}</h3>
                    <div class="space-y-3">
                        @foreach($this->patientHistory as $record)
                            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-2.5 text-xs dark:border-zinc-700 dark:bg-zinc-800">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ $record->visit_date?->format('M d, Y') }}</span>
                                    <span class="rounded bg-zinc-200 px-1.5 py-0.5 text-[10px] font-bold text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">
                                        {{ $record->consultationType?->short_name }}
                                    </span>
                                </div>
                                @if($record->chief_complaints_initial || $record->chief_complaints_updated)
                                    <p class="mt-1.5 text-zinc-600 dark:text-zinc-400">
                                        {{ Str::limit($record->chief_complaints_updated ?? $record->chief_complaints_initial, 60) }}
                                    </p>
                                @endif
                                <div class="mt-2 grid grid-cols-2 gap-1 text-[10px] text-zinc-500 dark:text-zinc-400">
                                    @if($record->temperature)
                                        <div>T: {{ $record->temperature }}°C</div>
                                    @endif
                                    @if($record->blood_pressure)
                                        <div>BP: {{ $record->blood_pressure }}</div>
                                    @endif
                                    @if($record->cardiac_rate)
                                        <div>HR: {{ $record->cardiac_rate }}</div>
                                    @endif
                                    @if($record->respiratory_rate)
                                        <div>RR: {{ $record->respiratory_rate }}</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </flux:modal>

    <!-- Skip Modal -->
    <flux:modal wire:model="showSkipModal" class="max-w-sm">
        <div class="space-y-4">
            @if(!$skipConfirmed)
                <flux:heading size="lg">{{ __('Skip Patient?') }}</flux:heading>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Patient will be moved to skipped list. You can requeue them later.') }}</p>
                <div class="flex justify-end gap-2">
                    <flux:button wire:click="closeSkipModal" variant="ghost">{{ __('Cancel') }}</flux:button>
                    <flux:button wire:click="confirmSkip" icon="forward">{{ __('Skip') }}</flux:button>
                </div>
            @else
                <div class="text-center">
                    <flux:icon name="check-circle" class="mx-auto h-10 w-10 text-zinc-400" />
                    <p class="mt-2 font-medium text-zinc-900 dark:text-white">{{ __('Patient Skipped') }}</p>
                </div>
                <div class="flex justify-center gap-2">
                    <flux:button wire:click="closeSkipModal" variant="ghost">{{ __('Close') }}</flux:button>
                    <flux:button wire:click="requeueFromSkipModal" variant="primary" icon="arrow-path">{{ __('Requeue') }}</flux:button>
                </div>
            @endif
        </div>
    </flux:modal>

    <!-- Requeue Modal -->
    <flux:modal wire:model="showRequeueModal" class="max-w-sm">
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('Requeue Patient?') }}</flux:heading>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Patient will return to waiting queue with the same number.') }}</p>
            <div class="flex justify-end gap-2">
                <flux:button wire:click="closeRequeueModal" variant="ghost">{{ __('Cancel') }}</flux:button>
                <flux:button wire:click="confirmRequeue" variant="primary" icon="arrow-path">{{ __('Requeue') }}</flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- Stop Serving Modal -->
    <flux:modal wire:model="showStopServingModal" class="max-w-sm">
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('Stop Serving?') }}</flux:heading>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Patient will return to waiting queue. Unsaved data will be lost.') }}</p>
            <div class="flex justify-end gap-2">
                <flux:button wire:click="closeStopServingModal" variant="ghost">{{ __('Cancel') }}</flux:button>
                <flux:button wire:click="confirmStopServing" variant="danger" icon="x-mark">{{ __('Stop') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</section>
