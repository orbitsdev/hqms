<section class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-1">
            <flux:heading size="xl" level="1">{{ __('Medical Records') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('Search, view, and manage patient medical records.') }}
            </flux:text>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/20">
                    <flux:icon name="calendar" class="h-5 w-5 text-primary" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['today'] }}</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Today') }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-info/20">
                    <flux:icon name="chart-bar" class="h-5 w-5 text-info" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['this_month'] }}</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('This Month') }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-warning/20">
                    <flux:icon name="clock" class="h-5 w-5 text-warning" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['in_progress'] }}</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('In Progress') }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-success/20">
                    <flux:icon name="banknotes" class="h-5 w-5 text-success" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['for_billing'] }}</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('For Billing') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="space-y-4">
        <!-- Search Bar -->
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-1 gap-2">
                <div class="w-full lg:max-w-md">
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        type="search"
                        placeholder="{{ __('Search by record #, patient name...') }}"
                        icon="magnifying-glass"
                    />
                </div>
                <flux:button wire:click="toggleFilters" :variant="$showFilters ? 'filled' : 'ghost'" icon="funnel">
                    <span class="hidden sm:inline">{{ __('Filters') }}</span>
                </flux:button>
                @if($search || $consultationTypeFilter || $doctorFilter || $statusFilter || $visitTypeFilter)
                    <flux:button wire:click="clearFilters" variant="ghost" icon="x-mark">
                        <span class="hidden sm:inline">{{ __('Clear') }}</span>
                    </flux:button>
                @endif
            </div>
            <div class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                <flux:icon name="calendar" class="h-4 w-4" />
                <span>{{ \Carbon\Carbon::parse($dateFrom)->format('M d') }} - {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}</span>
            </div>
        </div>

        <!-- Advanced Filters -->
        @if($showFilters)
            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                    <flux:field>
                        <flux:label>{{ __('Date From') }}</flux:label>
                        <flux:input type="date" wire:model.live="dateFrom" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Date To') }}</flux:label>
                        <flux:input type="date" wire:model.live="dateTo" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Consultation Type') }}</flux:label>
                        <flux:select wire:model.live="consultationTypeFilter">
                            <flux:select.option value="">{{ __('All Types') }}</flux:select.option>
                            @foreach($consultationTypes as $type)
                                <flux:select.option value="{{ $type->id }}">{{ $type->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Doctor') }}</flux:label>
                        <flux:select wire:model.live="doctorFilter">
                            <flux:select.option value="">{{ __('All Doctors') }}</flux:select.option>
                            @foreach($doctors as $doctor)
                                <flux:select.option value="{{ $doctor->id }}">{{ $doctor->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Status') }}</flux:label>
                        <flux:select wire:model.live="statusFilter">
                            <flux:select.option value="">{{ __('All Status') }}</flux:select.option>
                            @foreach($statusOptions as $value => $label)
                                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Visit Type') }}</flux:label>
                        <flux:select wire:model.live="visitTypeFilter">
                            <flux:select.option value="">{{ __('All Visits') }}</flux:select.option>
                            @foreach($visitTypeOptions as $value => $label)
                                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                </div>
            </div>
        @endif
    </div>

    <!-- Records Table -->
    @if($records->isNotEmpty())
        <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            <th class="px-4 py-3 text-left">
                                <button wire:click="sortBy('record_number')" class="flex items-center gap-1 text-xs font-medium uppercase tracking-wider text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                                    {{ __('Record #') }}
                                    @if($sortField === 'record_number')
                                        <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="h-3 w-3" />
                                    @endif
                                </button>
                            </th>
                            <th class="px-4 py-3 text-left">
                                <button wire:click="sortBy('patient_last_name')" class="flex items-center gap-1 text-xs font-medium uppercase tracking-wider text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                                    {{ __('Patient') }}
                                    @if($sortField === 'patient_last_name')
                                        <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="h-3 w-3" />
                                    @endif
                                </button>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                {{ __('Type') }}
                            </th>
                            <th class="px-4 py-3 text-left">
                                <button wire:click="sortBy('visit_date')" class="flex items-center gap-1 text-xs font-medium uppercase tracking-wider text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                                    {{ __('Visit Date') }}
                                    @if($sortField === 'visit_date')
                                        <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="h-3 w-3" />
                                    @endif
                                </button>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                {{ __('Doctor') }}
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                {{ __('Status') }}
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                {{ __('Actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach($records as $record)
                            <tr wire:key="record-{{ $record->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="whitespace-nowrap px-4 py-4">
                                    <span class="font-mono text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ $record->record_number }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ $record->patient_full_name }}
                                    </div>
                                    @if($record->patient_age_at_visit_short || $record->patient_age)
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $record->patient_age_at_visit_short ?? $record->patient_age . 'y' }} &bull; {{ ucfirst($record->patient_gender ?? '-') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-4">
                                    <span class="inline-flex items-center rounded bg-zinc-100 px-2 py-1 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                        {{ $record->consultationType?->short_name ?? '-' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-zinc-600 dark:text-zinc-300">
                                    {{ $record->visit_date->format('M d, Y') }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-zinc-600 dark:text-zinc-300">
                                    {{ $record->doctor?->name ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-4">
                                    @switch($record->status)
                                        @case('in_progress')
                                            <span class="inline-flex items-center rounded bg-warning/20 px-2 py-0.5 text-xs font-medium text-warning">
                                                {{ __('In Progress') }}
                                            </span>
                                            @break
                                        @case('for_billing')
                                            <span class="inline-flex items-center rounded bg-primary/20 px-2 py-0.5 text-xs font-medium text-primary">
                                                {{ __('For Billing') }}
                                            </span>
                                            @break
                                        @case('for_admission')
                                            <span class="inline-flex items-center rounded bg-info/20 px-2 py-0.5 text-xs font-medium text-info">
                                                {{ __('For Admission') }}
                                            </span>
                                            @break
                                        @case('completed')
                                            <span class="inline-flex items-center rounded bg-success/20 px-2 py-0.5 text-xs font-medium text-success">
                                                {{ __('Completed') }}
                                            </span>
                                            @break
                                    @endswitch
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <flux:button wire:click="viewRecord({{ $record->id }})" size="xs" variant="ghost" icon="eye">
                                            {{ __('View') }}
                                        </flux:button>
                                        <flux:button wire:click="editRecord({{ $record->id }})" size="xs" variant="ghost" icon="pencil">
                                            {{ __('Edit') }}
                                        </flux:button>
                                        <flux:button wire:click="downloadPdf({{ $record->id }})" size="xs" variant="ghost" icon="arrow-down-tray">
                                            {{ __('PDF') }}
                                        </flux:button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $records->links() }}
        </div>
    @else
        <div class="rounded-lg border border-zinc-200 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                <flux:icon name="clipboard-document-list" class="h-6 w-6 text-zinc-400" />
            </div>
            <h3 class="mt-4 text-sm font-medium text-zinc-900 dark:text-white">{{ __('No medical records found') }}</h3>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('Try adjusting your search or filter criteria.') }}
            </p>
        </div>
    @endif

    <!-- View Record Modal -->
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
                        <flux:button wire:click="editRecord({{ $record->id }})" size="sm" variant="primary" icon="pencil">
                            {{ __('Edit') }}
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
                            <!-- Personal Information -->
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
                                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Marital Status') }}</dt>
                                        <dd class="text-sm font-medium text-zinc-900 dark:text-white">{{ ucfirst($record->patient_marital_status ?? '-') }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Contact') }}</dt>
                                        <dd class="text-sm font-medium text-zinc-900 dark:text-white">{{ $record->patient_contact_number ?? '-' }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Occupation') }}</dt>
                                        <dd class="text-sm font-medium text-zinc-900 dark:text-white">{{ $record->patient_occupation ?? '-' }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Religion') }}</dt>
                                        <dd class="text-sm font-medium text-zinc-900 dark:text-white">{{ $record->patient_religion ?? '-' }}</dd>
                                    </div>
                                </dl>

                                <h4 class="pt-4 text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Address') }}</h4>
                                <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ $record->patient_full_address ?: '-' }}</p>
                            </div>

                            <!-- Companion & Emergency -->
                            <div class="space-y-4">
                                <h4 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Companion/Watcher') }}</h4>
                                <dl class="space-y-2">
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Name') }}</dt>
                                        <dd class="text-sm font-medium text-zinc-900 dark:text-white">{{ $record->companion_name ?? '-' }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Contact') }}</dt>
                                        <dd class="text-sm font-medium text-zinc-900 dark:text-white">{{ $record->companion_contact ?? '-' }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Relationship') }}</dt>
                                        <dd class="text-sm font-medium text-zinc-900 dark:text-white">{{ $record->companion_relationship ?? '-' }}</dd>
                                    </div>
                                </dl>

                                <h4 class="pt-4 text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Emergency Contact') }}</h4>
                                <dl class="space-y-2">
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Name') }}</dt>
                                        <dd class="text-sm font-medium text-zinc-900 dark:text-white">{{ $record->emergency_contact_name ?? '-' }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Phone') }}</dt>
                                        <dd class="text-sm font-medium text-zinc-900 dark:text-white">{{ $record->emergency_contact_phone ?? '-' }}</dd>
                                    </div>
                                </dl>

                                <h4 class="pt-4 text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Medical Background') }}</h4>
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
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Service Type') }}</dt>
                                        <dd class="text-sm font-medium text-zinc-900 dark:text-white">{{ ucfirst($record->service_type ?? '-') }}</dd>
                                    </div>
                                    @if($record->ob_type)
                                        <div class="flex justify-between">
                                            <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('OB Type') }}</dt>
                                            <dd class="text-sm font-medium text-zinc-900 dark:text-white">{{ ucfirst($record->ob_type) }}</dd>
                                        </div>
                                    @endif
                                    @if($record->service_category)
                                        <div class="flex justify-between">
                                            <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Category') }}</dt>
                                            <dd class="text-sm font-medium text-zinc-900 dark:text-white">{{ ucfirst($record->service_category) }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                            <div class="space-y-4">
                                <h4 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Staff') }}</h4>
                                <dl class="space-y-2">
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
                                        {{ $record->effective_chief_complaints ?? __('No complaints recorded.') }}
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
                                @if($record->consultationType?->short_name === 'P')
                                    <h4 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Pediatric Measurements') }}</h4>
                                    <dl class="grid grid-cols-2 gap-4">
                                        <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800">
                                            <dt class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Head Circumference') }}</dt>
                                            <dd class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $record->head_circumference ? $record->head_circumference . ' cm' : '-' }}</dd>
                                        </div>
                                        <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800">
                                            <dt class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Chest Circumference') }}</dt>
                                            <dd class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $record->chest_circumference ? $record->chest_circumference . ' cm' : '-' }}</dd>
                                        </div>
                                    </dl>
                                @endif

                                @if($record->consultationType?->short_name === 'O')
                                    <h4 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('OB Measurements') }}</h4>
                                    <dl class="grid grid-cols-2 gap-4">
                                        <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800">
                                            <dt class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Fetal Heart Tone') }}</dt>
                                            <dd class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $record->fetal_heart_tone ? $record->fetal_heart_tone . ' bpm' : '-' }}</dd>
                                        </div>
                                        <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800">
                                            <dt class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Fundal Height') }}</dt>
                                            <dd class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $record->fundal_height ? $record->fundal_height . ' cm' : '-' }}</dd>
                                        </div>
                                        <div class="col-span-2 rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800">
                                            <dt class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Last Menstrual Period') }}</dt>
                                            <dd class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $record->last_menstrual_period?->format('M d, Y') ?? '-' }}</dd>
                                        </div>
                                    </dl>
                                @endif

                                @if($record->vital_signs_recorded_at)
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ __('Recorded at') }}: {{ $record->vital_signs_recorded_at->format('M d, Y h:i A') }}
                                    </p>
                                @else
                                    <div class="rounded-lg border border-warning/30 bg-warning/10 p-3 dark:bg-warning/20">
                                        <p class="text-sm text-warning-foreground dark:text-warning">{{ __('Vital signs not yet recorded.') }}</p>
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
                                    @if($record->procedures_done)
                                        <div>
                                            <h4 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Procedures Done') }}</h4>
                                            <div class="mt-2 rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800">
                                                <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ $record->procedures_done }}</p>
                                            </div>
                                        </div>
                                    @endif
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
                                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('No diagnosis recorded yet. The doctor will fill this in during consultation.') }}</p>
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

    <!-- Edit Record Modal -->
    <flux:modal wire:model="showEditModal" class="max-w-3xl">
        <div class="space-y-6">
            <flux:heading size="lg">{{ __('Edit Medical Record') }}</flux:heading>

            <!-- Step Navigation -->
            <div class="flex items-center justify-between border-b border-zinc-200 pb-4 dark:border-zinc-700">
                <nav class="flex flex-wrap gap-2">
                    @foreach(['patient' => __('Patient'), 'address' => __('Address'), 'companion' => __('Companion'), 'medical' => __('Medical'), 'visit' => __('Visit'), 'vitals' => __('Vitals')] as $step => $label)
                        <button
                            wire:click="setEditStep('{{ $step }}')"
                            type="button"
                            class="rounded-lg px-3 py-1.5 text-sm font-medium transition {{ $editStep === $step ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700' }}"
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </nav>
            </div>

            <!-- Step Content -->
            <div class="min-h-[350px]">
                @if($editStep === 'patient')
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <flux:field>
                            <flux:label>{{ __('First Name') }} *</flux:label>
                            <flux:input wire:model="patientFirstName" required />
                            <flux:error name="patientFirstName" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('Middle Name') }}</flux:label>
                            <flux:input wire:model="patientMiddleName" />
                            <flux:error name="patientMiddleName" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('Last Name') }} *</flux:label>
                            <flux:input wire:model="patientLastName" required />
                            <flux:error name="patientLastName" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('Date of Birth') }}</flux:label>
                            <flux:input type="date" wire:model="patientDateOfBirth" max="{{ now()->format('Y-m-d') }}" />
                            <flux:error name="patientDateOfBirth" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('Gender') }}</flux:label>
                            <flux:select wire:model="patientGender">
                                <flux:select.option value="">{{ __('Select...') }}</flux:select.option>
                                @foreach($genderOptions as $value => $label)
                                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="patientGender" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('Marital Status') }}</flux:label>
                            <flux:select wire:model="patientMaritalStatus">
                                <flux:select.option value="">{{ __('Select...') }}</flux:select.option>
                                @foreach($maritalStatusOptions as $value => $label)
                                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="patientMaritalStatus" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('Contact Number') }}</flux:label>
                            <flux:input wire:model="patientContactNumber" placeholder="09XX XXX XXXX" />
                            <flux:error name="patientContactNumber" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('Occupation') }}</flux:label>
                            <flux:input wire:model="patientOccupation" />
                            <flux:error name="patientOccupation" />
                        </flux:field>
                        <flux:field class="sm:col-span-2">
                            <flux:label>{{ __('Religion') }}</flux:label>
                            <flux:input wire:model="patientReligion" />
                            <flux:error name="patientReligion" />
                        </flux:field>
                    </div>
                @elseif($editStep === 'address')
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <flux:field>
                            <flux:label>{{ __('Province') }}</flux:label>
                            <flux:input wire:model="patientProvince" />
                            <flux:error name="patientProvince" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('Municipality/City') }}</flux:label>
                            <flux:input wire:model="patientMunicipality" />
                            <flux:error name="patientMunicipality" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('Barangay') }}</flux:label>
                            <flux:input wire:model="patientBarangay" />
                            <flux:error name="patientBarangay" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('Street/House No.') }}</flux:label>
                            <flux:input wire:model="patientStreet" />
                            <flux:error name="patientStreet" />
                        </flux:field>
                    </div>
                @elseif($editStep === 'companion')
                    <div class="space-y-6">
                        <div>
                            <h4 class="mb-4 text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Companion/Watcher') }}</h4>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <flux:field>
                                    <flux:label>{{ __('Name') }}</flux:label>
                                    <flux:input wire:model="companionName" />
                                    <flux:error name="companionName" />
                                </flux:field>
                                <flux:field>
                                    <flux:label>{{ __('Contact') }}</flux:label>
                                    <flux:input wire:model="companionContact" />
                                    <flux:error name="companionContact" />
                                </flux:field>
                                <flux:field>
                                    <flux:label>{{ __('Relationship') }}</flux:label>
                                    <flux:input wire:model="companionRelationship" placeholder="{{ __('e.g., Parent, Spouse') }}" />
                                    <flux:error name="companionRelationship" />
                                </flux:field>
                            </div>
                        </div>
                        <div>
                            <h4 class="mb-4 text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Emergency Contact') }}</h4>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <flux:field>
                                    <flux:label>{{ __('Name') }}</flux:label>
                                    <flux:input wire:model="emergencyContactName" />
                                    <flux:error name="emergencyContactName" />
                                </flux:field>
                                <flux:field>
                                    <flux:label>{{ __('Phone') }}</flux:label>
                                    <flux:input wire:model="emergencyContactPhone" />
                                    <flux:error name="emergencyContactPhone" />
                                </flux:field>
                            </div>
                        </div>
                    </div>
                @elseif($editStep === 'medical')
                    <div class="grid grid-cols-1 gap-4">
                        <flux:field>
                            <flux:label>{{ __('Blood Type') }}</flux:label>
                            <flux:select wire:model="patientBloodType">
                                <flux:select.option value="">{{ __('Unknown') }}</flux:select.option>
                                @foreach($bloodTypeOptions as $value => $label)
                                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="patientBloodType" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('Known Allergies') }}</flux:label>
                            <flux:textarea wire:model="patientAllergies" rows="3" placeholder="{{ __('List any known allergies (food, medication, etc.)') }}" />
                            <flux:error name="patientAllergies" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('Chronic Conditions') }}</flux:label>
                            <flux:textarea wire:model="patientChronicConditions" rows="3" placeholder="{{ __('List any chronic conditions (diabetes, hypertension, asthma, etc.)') }}" />
                            <flux:error name="patientChronicConditions" />
                        </flux:field>
                    </div>
                @elseif($editStep === 'visit')
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <flux:field>
                            <flux:label>{{ __('Visit Type') }}</flux:label>
                            <flux:select wire:model="visitType">
                                @foreach($visitTypeOptions as $value => $label)
                                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="visitType" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('Service Type') }}</flux:label>
                            <flux:select wire:model="serviceType">
                                @foreach($serviceTypeOptions as $value => $label)
                                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="serviceType" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('OB Type') }}</flux:label>
                            <flux:select wire:model="obType">
                                <flux:select.option value="">{{ __('N/A') }}</flux:select.option>
                                @foreach($obTypeOptions as $value => $label)
                                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="obType" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('Service Category') }}</flux:label>
                            <flux:select wire:model="serviceCategory">
                                <flux:select.option value="">{{ __('N/A') }}</flux:select.option>
                                @foreach($serviceCategoryOptions as $value => $label)
                                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="serviceCategory" />
                        </flux:field>
                        <flux:field class="sm:col-span-2">
                            <flux:label>{{ __('Chief Complaints (Initial)') }}</flux:label>
                            <flux:textarea wire:model="chiefComplaintsInitial" rows="3" />
                            <flux:error name="chiefComplaintsInitial" />
                        </flux:field>
                        <flux:field class="sm:col-span-2">
                            <flux:label>{{ __('Chief Complaints (Updated)') }}</flux:label>
                            <flux:textarea wire:model="chiefComplaintsUpdated" rows="3" placeholder="{{ __('Add any additional symptoms or updates...') }}" />
                            <flux:error name="chiefComplaintsUpdated" />
                        </flux:field>
                    </div>
                @elseif($editStep === 'vitals')
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
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
                    </div>
                @endif
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-between border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <div>
                    @if($editStep !== 'patient')
                        <flux:button wire:click="previousStep" variant="ghost" icon="arrow-left">
                            {{ __('Previous') }}
                        </flux:button>
                    @endif
                </div>
                <div class="flex gap-2">
                    <flux:button wire:click="closeEditModal" variant="ghost">
                        {{ __('Cancel') }}
                    </flux:button>
                    @if($editStep !== 'vitals')
                        <flux:button wire:click="nextStep" variant="filled" icon-trailing="arrow-right">
                            {{ __('Next') }}
                        </flux:button>
                    @endif
                    <flux:button wire:click="saveRecord" variant="primary" icon="check">
                        {{ __('Save') }}
                    </flux:button>
                </div>
            </div>
        </div>
    </flux:modal>
</section>
