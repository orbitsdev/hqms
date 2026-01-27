<section class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-1">
            <flux:heading size="xl" level="1">{{ __('Patient History') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('View patient visit history. Use search to filter by name or contact number.') }}
            </flux:text>
        </div>
        @if($selectedPatient)
            <flux:button wire:click="clearSelection" variant="ghost" icon="arrow-left">
                {{ __('Back to Search') }}
            </flux:button>
        @endif
    </div>

    @if(!$selectedPatient)
        <!-- Search Section -->
        <div class="space-y-4">
            <div class="max-w-xl">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    type="search"
                    placeholder="{{ __('Search by patient or account holder name...') }}"
                    icon="magnifying-glass"
                    autofocus
                />
                @if(strlen($search) > 0 && strlen($search) < 2)
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Type at least 2 characters to search...') }}
                    </p>
                @endif
            </div>

            <!-- Patient Results -->
            @if($patients->isNotEmpty())
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($patients as $patient)
                        <button
                            wire:key="patient-{{ $patient['key'] }}"
                            wire:click="selectPatient('{{ $patient['key'] }}')"
                            type="button"
                            class="group rounded-lg border border-zinc-200 bg-white p-4 text-left transition hover:border-zinc-300 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-600"
                        >
                            <div class="flex items-start gap-3">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                    <flux:icon name="user" class="h-6 w-6 text-zinc-500 dark:text-zinc-400" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-semibold text-zinc-900 group-hover:text-zinc-700 dark:text-white dark:group-hover:text-zinc-200">
                                        {{ $patient['full_name'] }}
                                    </p>
                                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                                        @if($patient['age'])
                                            <span>{{ $patient['age'] }} {{ __('yrs') }}</span>
                                            <span>&bull;</span>
                                        @endif
                                        @if($patient['gender'])
                                            <span>{{ ucfirst($patient['gender']) }}</span>
                                            <span>&bull;</span>
                                        @endif
                                        <span>{{ $patient['visit_count'] }} {{ __('visits') }}</span>
                                    </div>
                                    @if($patient['contact_number'])
                                        <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">
                                            {{ $patient['contact_number'] }}
                                        </p>
                                    @endif
                                </div>
                                <flux:icon name="chevron-right" class="h-5 w-5 text-zinc-400 transition group-hover:text-zinc-600 dark:group-hover:text-zinc-300" />
                            </div>
                            <div class="mt-3 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-zinc-500 dark:text-zinc-400">{{ __('Last visit') }}</span>
                                    <span class="font-medium text-zinc-700 dark:text-zinc-300">
                                        {{ \Carbon\Carbon::parse($patient['last_visit'])->format('M d, Y') }}
                                    </span>
                                </div>
                            </div>
                        </button>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($patients->hasPages())
                    <div class="mt-6">
                        {{ $patients->links() }}
                    </div>
                @endif
            @elseif(strlen($search) >= 2)
                <!-- No search results -->
                <div class="rounded-lg border border-zinc-200 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-900">
                    <img
                        src="{{ asset('images/undraw_file-search_cbur.svg') }}"
                        alt="No results"
                        class="mx-auto h-40 w-40"
                    />
                    <h3 class="mt-4 text-sm font-medium text-zinc-900 dark:text-white">{{ __('No patients found') }}</h3>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('No patients match your search. Try a different name or contact number.') }}
                    </p>
                </div>
            @else
                <!-- No patients in database -->
                <div class="rounded-lg border border-zinc-200 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-900">
                    <img
                        src="{{ asset('images/undraw_my-documents_ltqk.svg') }}"
                        alt="No patients"
                        class="mx-auto h-40 w-40"
                    />
                    <h3 class="mt-4 text-sm font-medium text-zinc-900 dark:text-white">{{ __('No patient records yet') }}</h3>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Patient history will appear here once medical records are created.') }}
                    </p>
                </div>
            @endif
        </div>
    @else
        <!-- Patient Profile & History -->
        <div class="space-y-6">
            <!-- Patient Info Card -->
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                            <flux:icon name="user" class="h-8 w-8 text-zinc-500 dark:text-zinc-400" />
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-zinc-900 dark:text-white">
                                {{ $selectedPatient['full_name'] }}
                            </h2>
                            <div class="mt-1 flex flex-wrap items-center gap-3 text-sm text-zinc-500 dark:text-zinc-400">
                                @if($selectedPatient['age'])
                                    <span class="flex items-center gap-1">
                                        <flux:icon name="cake" class="h-4 w-4" />
                                        {{ $selectedPatient['age'] }} {{ __('years old') }}
                                    </span>
                                @endif
                                @if($selectedPatient['gender'])
                                    <span class="flex items-center gap-1">
                                        <flux:icon name="user" class="h-4 w-4" />
                                        {{ ucfirst($selectedPatient['gender']) }}
                                    </span>
                                @endif
                                @if($selectedPatient['contact_number'])
                                    <span class="flex items-center gap-1">
                                        <flux:icon name="phone" class="h-4 w-4" />
                                        {{ $selectedPatient['contact_number'] }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 text-sm">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $selectedPatient['visit_count'] }}</p>
                            <p class="text-zinc-500 dark:text-zinc-400">{{ __('Total Visits') }}</p>
                        </div>
                        <div class="h-12 w-px bg-zinc-200 dark:bg-zinc-700"></div>
                        <div class="text-center">
                            <p class="text-sm font-semibold text-zinc-900 dark:text-white">
                                {{ \Carbon\Carbon::parse($selectedPatient['first_visit'])->format('M Y') }}
                            </p>
                            <p class="text-zinc-500 dark:text-zinc-400">{{ __('First Visit') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visit History Timeline -->
            <div>
                <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">{{ __('Visit History') }}</h3>

                @if($patientRecords->isNotEmpty())
                    <div class="space-y-4">
                        @foreach($patientRecords as $record)
                            @php
                                $bgClass = match($record->status) {
                                    'completed' => 'bg-emerald-100 dark:bg-emerald-900/30',
                                    'in_progress' => 'bg-amber-100 dark:bg-amber-900/30',
                                    'for_billing' => 'bg-blue-100 dark:bg-blue-900/30',
                                    default => 'bg-zinc-100 dark:bg-zinc-800',
                                };
                                $iconClass = match($record->status) {
                                    'completed' => 'text-emerald-600 dark:text-emerald-400',
                                    'in_progress' => 'text-amber-600 dark:text-amber-400',
                                    'for_billing' => 'text-blue-600 dark:text-blue-400',
                                    default => 'text-zinc-500 dark:text-zinc-400',
                                };
                            @endphp
                            <div
                                wire:key="record-{{ $record->id }}"
                                class="rounded-lg border border-zinc-200 bg-white p-4 transition hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-600"
                            >
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="flex items-start gap-4">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $bgClass }}">
                                            <flux:icon name="clipboard-document-list" class="h-5 w-5 {{ $iconClass }}" />
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-mono text-sm font-medium text-zinc-900 dark:text-white">
                                                    {{ $record->record_number }}
                                                </span>
                                                <span class="rounded bg-zinc-100 px-2 py-0.5 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                                    {{ $record->consultationType?->short_name ?? '-' }}
                                                </span>
                                            </div>
                                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                                                {{ $record->visit_date->format('F d, Y') }}
                                                @if($record->time_in)
                                                    <span class="text-zinc-400">&bull; {{ $record->time_in->format('h:i A') }}</span>
                                                @endif
                                            </p>
                                            @if($record->doctor)
                                                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                                    {{ __('Dr.') }} {{ $record->doctor->personalInformation?->full_name ?? $record->doctor->name }}
                                                </p>
                                            @endif
                                            @if($record->user)
                                                <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">
                                                    <span class="inline-flex items-center gap-1">
                                                        <flux:icon name="user-circle" class="h-3 w-3" />
                                                        {{ __('Account:') }} {{ $record->user->personalInformation?->full_name ?? $record->user->name }}
                                                    </span>
                                                </p>
                                            @endif
                                            @if($record->diagnosis)
                                                <p class="mt-2 line-clamp-2 text-sm text-zinc-600 dark:text-zinc-300">
                                                    <span class="font-medium">{{ __('Dx:') }}</span> {{ $record->diagnosis }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @switch($record->status)
                                            @case('completed')
                                                <span class="inline-flex items-center rounded bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                                                    {{ __('Completed') }}
                                                </span>
                                                @break
                                            @case('in_progress')
                                                <span class="inline-flex items-center rounded bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                                                    {{ __('In Progress') }}
                                                </span>
                                                @break
                                            @case('for_billing')
                                                <span class="inline-flex items-center rounded bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                                    {{ __('For Billing') }}
                                                </span>
                                                @break
                                        @endswitch
                                        <flux:button wire:click="viewRecord({{ $record->id }})" size="xs" variant="ghost" icon="eye">
                                            {{ __('View') }}
                                        </flux:button>
                                        <flux:button wire:click="downloadPdf({{ $record->id }})" size="xs" variant="ghost" icon="arrow-down-tray">
                                            {{ __('PDF') }}
                                        </flux:button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-lg border border-zinc-200 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-900">
                        <flux:icon name="clipboard-document-list" class="mx-auto h-12 w-12 text-zinc-400" />
                        <h3 class="mt-4 text-sm font-medium text-zinc-900 dark:text-white">{{ __('No records found') }}</h3>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ __('This patient has no medical records yet.') }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- View Record Modal (same as MedicalRecords) -->
    <flux:modal wire:model="showViewModal" class="max-w-4xl">
        @if($this->viewingRecord)
            @php $record = $this->viewingRecord; @endphp
            <div class="space-y-6">
                <!-- Modal Header -->
                <div class="flex items-start justify-between">
                    <div>
                        <flux:heading size="lg">{{ __('Medical Record') }}</flux:heading>
                        <p class="mt-1 font-mono text-sm text-zinc-500 dark:text-zinc-400">{{ $record->record_number }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <flux:button wire:click="downloadPdf({{ $record->id }})" size="sm" variant="ghost" icon="arrow-down-tray">
                            {{ __('Download PDF') }}
                        </flux:button>
                    </div>
                </div>

                <!-- Tab Navigation -->
                <div class="border-b border-zinc-200 dark:border-zinc-700">
                    <nav class="-mb-px flex gap-4">
                        @foreach(['patient' => __('Patient Info'), 'visit' => __('Visit Details'), 'vitals' => __('Vital Signs'), 'diagnosis' => __('Diagnosis')] as $tab => $label)
                            <button
                                wire:click="setViewTab('{{ $tab }}')"
                                type="button"
                                class="border-b-2 px-1 py-2 text-sm font-medium transition {{ $viewTab === $tab ? 'border-zinc-900 text-zinc-900 dark:border-white dark:text-white' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400' }}"
                            >
                                {{ $label }}
                            </button>
                        @endforeach
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="min-h-[300px]">
                    @if($viewTab === 'patient')
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="space-y-4">
                                <h4 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Personal Information') }}</h4>
                                <dl class="space-y-2">
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Full Name') }}</dt>
                                        <dd class="text-sm font-medium text-zinc-900 dark:text-white">{{ $record->patient_full_name }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Date of Birth') }}</dt>
                                        <dd class="text-sm font-medium text-zinc-900 dark:text-white">{{ $record->patient_date_of_birth?->format('M d, Y') ?? '-' }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Age') }}</dt>
                                        <dd class="text-sm font-medium text-zinc-900 dark:text-white">{{ $record->patient_age ? $record->patient_age . ' ' . __('years') : '-' }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Gender') }}</dt>
                                        <dd class="text-sm font-medium text-zinc-900 dark:text-white">{{ ucfirst($record->patient_gender ?? '-') }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Contact') }}</dt>
                                        <dd class="text-sm font-medium text-zinc-900 dark:text-white">{{ $record->patient_contact_number ?? '-' }}</dd>
                                    </div>
                                </dl>
                            </div>
                            <div class="space-y-4">
                                <h4 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Medical Background') }}</h4>
                                <dl class="space-y-2">
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Blood Type') }}</dt>
                                        <dd class="text-sm font-medium text-zinc-900 dark:text-white">{{ $record->patient_blood_type ?? '-' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Allergies') }}</dt>
                                        <dd class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $record->patient_allergies ?? '-' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Chronic Conditions') }}</dt>
                                        <dd class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $record->patient_chronic_conditions ?? '-' }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    @elseif($viewTab === 'visit')
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="space-y-4">
                                <h4 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Visit Information') }}</h4>
                                <dl class="space-y-2">
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Visit Date') }}</dt>
                                        <dd class="text-sm font-medium text-zinc-900 dark:text-white">{{ $record->visit_date->format('M d, Y') }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Time In') }}</dt>
                                        <dd class="text-sm font-medium text-zinc-900 dark:text-white">{{ $record->time_in?->format('h:i A') ?? '-' }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Consultation Type') }}</dt>
                                        <dd class="text-sm font-medium text-zinc-900 dark:text-white">{{ $record->consultationType?->name ?? '-' }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Visit Type') }}</dt>
                                        <dd class="text-sm font-medium text-zinc-900 dark:text-white">{{ ucfirst($record->visit_type ?? '-') }}</dd>
                                    </div>
                                </dl>
                            </div>
                            <div class="space-y-4">
                                <h4 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Staff & Account') }}</h4>
                                <dl class="space-y-2">
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Account Holder') }}</dt>
                                        <dd class="text-sm font-medium text-zinc-900 dark:text-white">{{ $record->user?->personalInformation?->full_name ?? $record->user?->name ?? '-' }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Nurse') }}</dt>
                                        <dd class="text-sm font-medium text-zinc-900 dark:text-white">{{ $record->nurse?->name ?? '-' }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Doctor') }}</dt>
                                        <dd class="text-sm font-medium text-zinc-900 dark:text-white">{{ $record->doctor?->name ?? '-' }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</dt>
                                        <dd class="text-sm font-medium text-zinc-900 dark:text-white">{{ ucfirst(str_replace('_', ' ', $record->status)) }}</dd>
                                    </div>
                                </dl>

                                <h4 class="pt-4 text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Chief Complaints') }}</h4>
                                <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800">
                                    <p class="text-sm text-zinc-700 dark:text-zinc-300">
                                        {{ $record->chief_complaints_updated ?? $record->chief_complaints_initial ?? __('No complaints recorded.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @elseif($viewTab === 'vitals')
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="space-y-4">
                                <h4 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('General Vitals') }}</h4>
                                <dl class="grid grid-cols-2 gap-4">
                                    <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800">
                                        <dt class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Temperature') }}</dt>
                                        <dd class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $record->temperature ? $record->temperature . ' °C' : '-' }}</dd>
                                    </div>
                                    <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800">
                                        <dt class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Blood Pressure') }}</dt>
                                        <dd class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $record->blood_pressure ?? '-' }}</dd>
                                    </div>
                                    <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800">
                                        <dt class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Cardiac Rate') }}</dt>
                                        <dd class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $record->cardiac_rate ? $record->cardiac_rate . ' bpm' : '-' }}</dd>
                                    </div>
                                    <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800">
                                        <dt class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Respiratory Rate') }}</dt>
                                        <dd class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $record->respiratory_rate ? $record->respiratory_rate . '/min' : '-' }}</dd>
                                    </div>
                                    <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800">
                                        <dt class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Weight') }}</dt>
                                        <dd class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $record->weight ? $record->weight . ' kg' : '-' }}</dd>
                                    </div>
                                    <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800">
                                        <dt class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Height') }}</dt>
                                        <dd class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $record->height ? $record->height . ' cm' : '-' }}</dd>
                                    </div>
                                </dl>
                            </div>
                            <div class="space-y-4">
                                @if($record->vital_signs_recorded_at)
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ __('Recorded at') }}: {{ $record->vital_signs_recorded_at->format('M d, Y h:i A') }}
                                    </p>
                                @else
                                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-800 dark:bg-amber-900/20">
                                        <p class="text-sm text-amber-800 dark:text-amber-200">{{ __('Vital signs not yet recorded.') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @elseif($viewTab === 'diagnosis')
                        <div class="space-y-6">
                            @if($record->diagnosis || $record->pertinent_hpi_pe || $record->plan)
                                <div class="space-y-4">
                                    <div>
                                        <h4 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Pertinent HPI/PE') }}</h4>
                                        <div class="mt-2 rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800">
                                            <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ $record->pertinent_hpi_pe ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Diagnosis') }}</h4>
                                        <div class="mt-2 rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800">
                                            <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ $record->diagnosis ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Plan') }}</h4>
                                        <div class="mt-2 rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800">
                                            <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ $record->plan ?? '-' }}</p>
                                        </div>
                                    </div>
                                    @if($record->prescription_notes)
                                        <div>
                                            <h4 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Prescription Notes') }}</h4>
                                            <div class="mt-2 rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800">
                                                <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ $record->prescription_notes }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-6 text-center dark:border-zinc-700 dark:bg-zinc-800">
                                    <flux:icon name="clipboard-document" class="mx-auto h-10 w-10 text-zinc-400" />
                                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('No diagnosis recorded yet.') }}</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="flex justify-end pt-4">
                    <flux:button wire:click="closeViewModal" variant="ghost">
                        {{ __('Close') }}
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</section>
