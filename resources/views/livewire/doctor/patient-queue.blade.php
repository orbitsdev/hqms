<section class="space-y-4">
    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" level="1">{{ __('Patient Queue') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ now()->format('l, F j, Y') }}</flux:text>
        </div>
        @if($this->statusCounts['waiting'] > 0)
            <flux:button wire:click="startNextPatient" variant="primary" icon="play">
                {{ __('Start Next Patient') }}
            </flux:button>
        @endif
    </div>

    {{-- Status Tabs --}}
    <div class="flex flex-wrap gap-2">
        <button
            wire:click="setStatus('waiting')"
            class="flex items-center gap-2 rounded-lg border px-4 py-2 transition {{ $status === 'waiting' ? 'border-primary bg-primary/10' : 'border-zinc-200 bg-white hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700' }}"
        >
            <span class="h-2 w-2 rounded-full bg-primary"></span>
            <span class="font-medium">{{ $this->statusCounts['waiting'] }}</span>
            <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Waiting') }}</span>
        </button>

        <button
            wire:click="setStatus('examining')"
            class="flex items-center gap-2 rounded-lg border px-4 py-2 transition {{ $status === 'examining' ? 'border-success bg-success/10' : 'border-zinc-200 bg-white hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700' }}"
        >
            <span class="h-2 w-2 animate-pulse rounded-full bg-success"></span>
            <span class="font-medium">{{ $this->statusCounts['examining'] }}</span>
            <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Examining') }}</span>
        </button>

        <button
            wire:click="setStatus('completed')"
            class="flex items-center gap-2 rounded-lg border px-4 py-2 transition {{ $status === 'completed' ? 'border-zinc-500 bg-zinc-100 dark:border-zinc-400 dark:bg-zinc-700' : 'border-zinc-200 bg-white hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700' }}"
        >
            <span class="h-2 w-2 rounded-full bg-zinc-400"></span>
            <span class="font-medium">{{ $this->statusCounts['completed'] }}</span>
            <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Completed') }}</span>
        </button>
    </div>

    {{-- Consultation Type Filter --}}
    @if($consultationTypes->count() > 1)
        <div class="flex flex-wrap gap-2">
            <button
                wire:click="setConsultationType('')"
                class="rounded-full px-3 py-1 text-xs font-medium transition {{ $consultationTypeFilter === '' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300' }}"
            >
                {{ __('All') }}
            </button>
            @foreach($consultationTypes as $type)
                <button
                    wire:click="setConsultationType('{{ $type->id }}')"
                    class="rounded-full px-3 py-1 text-xs font-medium transition {{ $consultationTypeFilter == $type->id ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300' }}"
                >
                    {{ $type->short_name }}
                </button>
            @endforeach
        </div>
    @endif

    {{-- Main Content --}}
    <div class="grid gap-4 lg:grid-cols-3">
        {{-- Queue List --}}
        <div class="space-y-2 lg:col-span-2">
            @forelse($queues as $queue)
                @php
                    $record = $queue->medicalRecord;
                    $patientName = $record?->patient_full_name ?? 'Unknown';
                    $isSelected = $selectedQueueId === $queue->id;
                @endphp
                <button
                    wire:click="selectQueue({{ $queue->id }})"
                    wire:key="queue-{{ $queue->id }}"
                    class="w-full rounded-lg border p-4 text-left transition {{ $isSelected ? 'border-primary bg-primary/10 ring-2 ring-primary' : 'border-zinc-200 bg-white hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:bg-zinc-800' }}"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-3">
                            {{-- Queue Number --}}
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg {{ $queue->priority === 'emergency' ? 'bg-destructive/20' : ($queue->priority === 'urgent' ? 'bg-warning/20' : 'bg-primary/20') }}">
                                <span class="text-sm font-bold {{ $queue->priority === 'emergency' ? 'text-destructive' : ($queue->priority === 'urgent' ? 'text-warning' : 'text-primary') }}">
                                    {{ $queue->formatted_number }}
                                </span>
                            </div>

                            <div>
                                <div class="flex items-center gap-2">
                                    <p class="font-semibold text-zinc-900 dark:text-white">{{ $patientName }}</p>
                                    @if($queue->priority === 'emergency')
                                        <flux:badge size="sm" color="red">{{ __('EMERGENCY') }}</flux:badge>
                                    @elseif($queue->priority === 'urgent')
                                        <flux:badge size="sm" color="yellow">{{ __('URGENT') }}</flux:badge>
                                    @endif
                                </div>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ $queue->consultationType?->name }}
                                    @if($record?->patient_age)
                                        &bull; {{ $record->patient_age }} {{ __('yrs') }}
                                    @endif
                                    @if($record?->patient_gender)
                                        &bull; {{ ucfirst($record->patient_gender) }}
                                    @endif
                                </p>
                                @if($record?->effective_chief_complaints)
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                        <span class="font-medium">{{ __('CC:') }}</span>
                                        {{ Str::limit($record->effective_chief_complaints, 60) }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- Status Badge --}}
                        @if($status === 'examining' && $record?->examined_at)
                            <div class="text-right">
                                <flux:badge size="sm" color="green">{{ __('Examining') }}</flux:badge>
                                <p class="mt-1 text-xs text-zinc-500">{{ $record->examined_at->diffForHumans() }}</p>
                            </div>
                        @elseif($status === 'completed')
                            <flux:badge size="sm" :color="$record?->status === 'for_billing' ? 'yellow' : ($record?->status === 'for_admission' ? 'blue' : 'zinc')">
                                {{ str_replace('_', ' ', ucfirst($record?->status ?? '')) }}
                            </flux:badge>
                        @endif
                    </div>

                    {{-- Vital Signs Preview --}}
                    @if($record && $status === 'waiting')
                        <div class="mt-3 flex flex-wrap gap-3 border-t border-zinc-100 pt-3 text-xs text-zinc-600 dark:border-zinc-800 dark:text-zinc-400">
                            @if($record->temperature)
                                <span class="{{ $record->temperature >= 38 ? 'text-destructive font-medium' : '' }}">
                                    T: {{ $record->temperature }}°C
                                </span>
                            @endif
                            @if($record->blood_pressure)
                                <span>BP: {{ $record->blood_pressure }}</span>
                            @endif
                            @if($record->cardiac_rate)
                                <span>HR: {{ $record->cardiac_rate }}</span>
                            @endif
                            @if($record->respiratory_rate)
                                <span>RR: {{ $record->respiratory_rate }}</span>
                            @endif
                        </div>
                    @endif
                </button>
            @empty
                <div class="rounded-lg border border-dashed border-zinc-300 p-8 text-center dark:border-zinc-600">
                    <img src="{{ asset('images/illustrations/empty-queue.svg') }}" alt="" class="mx-auto h-32 w-32 opacity-60" />
                    <p class="mt-4 font-medium text-zinc-600 dark:text-zinc-400">
                        @if($status === 'waiting')
                            {{ __('No patients waiting') }}
                        @elseif($status === 'examining')
                            {{ __('No patients being examined') }}
                        @else
                            {{ __('No completed consultations') }}
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

        {{-- Selected Patient Panel --}}
        <div class="lg:col-span-1">
            @if($this->selectedQueue)
                @php
                    $sq = $this->selectedQueue;
                    $sr = $sq->medicalRecord;
                @endphp
                <div class="sticky top-4 rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    {{-- Header --}}
                    <div class="border-b border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xl font-bold text-zinc-900 dark:text-white">
                                    {{ $sq->formatted_number }}
                                </p>
                                <p class="text-sm text-zinc-500">{{ $sq->consultationType?->name }}</p>
                            </div>
                            @if($sq->priority !== 'normal')
                                <flux:badge size="sm" :color="$sq->priority === 'emergency' ? 'red' : 'yellow'">
                                    {{ strtoupper($sq->priority) }}
                                </flux:badge>
                            @endif
                        </div>
                    </div>

                    @if($sr)
                        {{-- Patient Info --}}
                        <div class="border-b border-zinc-200 p-4 dark:border-zinc-700">
                            <p class="mb-2 text-xs font-medium uppercase text-zinc-500">{{ __('Patient') }}</p>
                            <p class="font-semibold text-zinc-900 dark:text-white">{{ $sr->patient_full_name }}</p>
                            <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                @if($sr->patient_age)
                                    {{ $sr->patient_age }} {{ __('years old') }}
                                @endif
                                @if($sr->patient_gender)
                                    &bull; {{ ucfirst($sr->patient_gender) }}
                                @endif
                            </div>
                        </div>

                        {{-- Vital Signs --}}
                        <div class="border-b border-zinc-200 p-4 dark:border-zinc-700">
                            <p class="mb-2 text-xs font-medium uppercase text-zinc-500">{{ __('Vital Signs') }}</p>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                @if($sr->temperature)
                                    <div class="{{ $sr->temperature >= 38 ? 'text-destructive' : 'text-zinc-700 dark:text-zinc-300' }}">
                                        <span class="text-zinc-500 dark:text-zinc-400">T:</span> {{ $sr->temperature }}°C
                                    </div>
                                @endif
                                @if($sr->blood_pressure)
                                    <div class="text-zinc-700 dark:text-zinc-300">
                                        <span class="text-zinc-500 dark:text-zinc-400">BP:</span> {{ $sr->blood_pressure }}
                                    </div>
                                @endif
                                @if($sr->cardiac_rate)
                                    <div class="text-zinc-700 dark:text-zinc-300">
                                        <span class="text-zinc-500 dark:text-zinc-400">HR:</span> {{ $sr->cardiac_rate }} bpm
                                    </div>
                                @endif
                                @if($sr->respiratory_rate)
                                    <div class="text-zinc-700 dark:text-zinc-300">
                                        <span class="text-zinc-500 dark:text-zinc-400">RR:</span> {{ $sr->respiratory_rate }}/min
                                    </div>
                                @endif
                                @if($sr->weight)
                                    <div class="text-zinc-700 dark:text-zinc-300">
                                        <span class="text-zinc-500 dark:text-zinc-400">Wt:</span> {{ $sr->weight }} kg
                                    </div>
                                @endif
                                @if($sr->height)
                                    <div class="text-zinc-700 dark:text-zinc-300">
                                        <span class="text-zinc-500 dark:text-zinc-400">Ht:</span> {{ $sr->height }} cm
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Chief Complaints --}}
                        @if($sr->effective_chief_complaints)
                            <div class="border-b border-zinc-200 p-4 dark:border-zinc-700">
                                <p class="mb-2 text-xs font-medium uppercase text-zinc-500">{{ __('Chief Complaints') }}</p>
                                <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ $sr->effective_chief_complaints }}</p>
                            </div>
                        @endif

                        {{-- Medical Background --}}
                        @if($sr->patient_allergies || $sr->patient_chronic_conditions)
                            <div class="border-b border-zinc-200 p-4 dark:border-zinc-700">
                                <p class="mb-2 text-xs font-medium uppercase text-zinc-500">{{ __('Medical Background') }}</p>
                                @if($sr->patient_allergies)
                                    <div class="mb-2 rounded bg-destructive/10 p-2 text-xs">
                                        <span class="font-medium text-destructive">{{ __('Allergies:') }}</span>
                                        <span class="text-destructive/80">{{ $sr->patient_allergies }}</span>
                                    </div>
                                @endif
                                @if($sr->patient_chronic_conditions)
                                    <div class="text-xs text-zinc-600 dark:text-zinc-400">
                                        <span class="font-medium">{{ __('Conditions:') }}</span>
                                        {{ $sr->patient_chronic_conditions }}
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endif

                    {{-- Actions --}}
                    <div class="p-4">
                        @if($status === 'waiting')
                            <flux:button wire:click="startExamination" class="w-full" variant="primary" icon="play">
                                {{ __('Start Examination') }}
                            </flux:button>
                        @elseif($status === 'examining' && $sr)
                            <flux:button href="{{ route('doctor.examine', $sr) }}" wire:navigate class="w-full" variant="primary" icon="arrow-right">
                                {{ __('Continue Examination') }}
                            </flux:button>
                        @elseif($status === 'completed' && $sr)
                            <flux:button href="{{ route('doctor.examine', $sr) }}" wire:navigate class="w-full" variant="filled" icon="eye">
                                {{ __('View Details') }}
                            </flux:button>
                        @endif
                    </div>
                </div>
            @else
                <div class="rounded-xl border border-dashed border-zinc-300 p-8 text-center dark:border-zinc-600">
                    <flux:icon name="cursor-arrow-rays" class="mx-auto h-10 w-10 text-zinc-400" />
                    <p class="mt-3 text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Select a patient') }}</p>
                    <p class="mt-1 text-xs text-zinc-500">{{ __('Click on a patient to view details') }}</p>
                </div>
            @endif
        </div>
    </div>
</section>
