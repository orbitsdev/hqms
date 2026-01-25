<section class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-1">
            <flux:heading size="xl" level="1">{{ __('Appointments') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('Review and manage patient appointment requests.') }}
            </flux:text>
        </div>
        <flux:button href="{{ route('nurse.walk-in') }}" wire:navigate variant="primary" icon="plus">
            {{ __('Walk-in') }}
        </flux:button>
    </div>

    {{-- Status Tabs --}}
    <div class="flex flex-wrap items-center gap-2 border-b border-zinc-200 pb-4 dark:border-zinc-700">
        <flux:button wire:click="setStatus('pending')" :variant="$status === 'pending' ? 'filled' : 'ghost'" size="sm">
            {{ __('Pending') }}
            @if($statusCounts['pending'] > 0)
                <flux:badge size="sm" color="zinc" class="ml-1">{{ $statusCounts['pending'] }}</flux:badge>
            @endif
        </flux:button>
        <flux:button wire:click="setStatus('approved')" :variant="$status === 'approved' ? 'filled' : 'ghost'" size="sm">
            {{ __('Approved') }}
            @if($statusCounts['approved'] > 0)
                <flux:badge size="sm" color="zinc" class="ml-1">{{ $statusCounts['approved'] }}</flux:badge>
            @endif
        </flux:button>
        <flux:button wire:click="setStatus('today')" :variant="$status === 'today' ? 'filled' : 'ghost'" size="sm">
            {{ __('Today') }}
            @if($statusCounts['today'] > 0)
                <flux:badge size="sm" color="zinc" class="ml-1">{{ $statusCounts['today'] }}</flux:badge>
            @endif
        </flux:button>
        <flux:button wire:click="setStatus('cancelled')" :variant="$status === 'cancelled' ? 'filled' : 'ghost'" size="sm">
            {{ __('Cancelled') }}
        </flux:button>
        <flux:button wire:click="setStatus('all')" :variant="$status === 'all' ? 'filled' : 'ghost'" size="sm">
            {{ __('All') }}
            <flux:badge size="sm" color="zinc" class="ml-1">{{ $statusCounts['all'] }}</flux:badge>
        </flux:button>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap items-end gap-3">
        <div class="w-full sm:w-64">
            <flux:input
                type="search"
                wire:model.live.debounce.400ms="search"
                placeholder="{{ __('Search patients...') }}"
                icon="magnifying-glass"
            />
        </div>
        <div class="w-full sm:w-40">
            <flux:select wire:model.live="consultationTypeFilter" placeholder="{{ __('All types') }}">
                <flux:select.option value="">{{ __('All types') }}</flux:select.option>
                @foreach($consultationTypes as $type)
                    <flux:select.option value="{{ $type->id }}">{{ $type->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
        <div class="w-full sm:w-32">
            <flux:select wire:model.live="sourceFilter" placeholder="{{ __('All sources') }}">
                <flux:select.option value="">{{ __('All sources') }}</flux:select.option>
                <flux:select.option value="online">{{ __('Online') }}</flux:select.option>
                <flux:select.option value="walk-in">{{ __('Walk-in') }}</flux:select.option>
            </flux:select>
        </div>
        <div class="w-full sm:w-40">
            <flux:input type="date" wire:model.live="dateFilter" />
        </div>
        @if($search || $consultationTypeFilter || $dateFilter || $sourceFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                {{ __('Clear') }}
            </flux:button>
        @endif
    </div>

    {{-- Table --}}
    @if($appointments->count() > 0)
        <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Patient') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Type') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Date') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Source') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Queue') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach($appointments as $appointment)
                            @php
                                $statusColor = match($appointment->status) {
                                    'pending' => 'yellow',
                                    'approved', 'checked_in' => 'green',
                                    'in_progress' => 'blue',
                                    'cancelled', 'no_show' => 'red',
                                    'completed' => 'zinc',
                                    default => 'zinc',
                                };
                                $patientName = trim($appointment->patient_first_name . ' ' . $appointment->patient_last_name);
                            @endphp

                            <tr wire:key="appointment-{{ $appointment->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="whitespace-nowrap px-4 py-3">
                                    <div>
                                        <div class="font-medium text-zinc-900 dark:text-white">{{ $patientName }}</div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $appointment->patient_phone ?? '-' }}</div>
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-4 py-3">
                                    <flux:badge size="sm" color="zinc">{{ $appointment->consultationType?->name ?? '-' }}</flux:badge>
                                </td>

                                <td class="whitespace-nowrap px-4 py-3">
                                    <div class="text-sm text-zinc-900 dark:text-white">{{ $appointment->appointment_date?->format('M d, Y') }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $appointment->created_at->diffForHumans() }}</div>
                                </td>

                                <td class="whitespace-nowrap px-4 py-3">
                                    @if($appointment->source === 'walk-in')
                                        <flux:badge size="sm" color="zinc">{{ __('Walk-in') }}</flux:badge>
                                    @else
                                        <flux:badge size="sm" color="blue">{{ __('Online') }}</flux:badge>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap px-4 py-3">
                                    <flux:badge size="sm" color="{{ $statusColor }}">
                                        {{ str_replace('_', ' ', ucfirst($appointment->status)) }}
                                    </flux:badge>
                                </td>

                                <td class="whitespace-nowrap px-4 py-3">
                                    @if($appointment->queue)
                                        <span class="font-bold text-zinc-900 dark:text-white">{{ $appointment->queue->formatted_number }}</span>
                                    @else
                                        <span class="text-zinc-400">-</span>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <flux:button wire:click="viewAppointment({{ $appointment->id }})" size="xs" variant="ghost" icon="eye">
                                            {{ __('View') }}
                                        </flux:button>

                                        @if($appointment->status === 'pending')
                                            <flux:button wire:click="openApproveModal({{ $appointment->id }})" size="xs" variant="primary" icon="check">
                                                {{ __('Approve') }}
                                            </flux:button>
                                            <flux:button wire:click="openCancelModal({{ $appointment->id }})" size="xs" variant="danger" icon="x-mark">
                                                {{ __('Cancel') }}
                                            </flux:button>
                                        @elseif($appointment->status === 'approved')
                                            <flux:button wire:click="openCancelModal({{ $appointment->id }})" size="xs" variant="danger" icon="x-mark">
                                                {{ __('Cancel') }}
                                            </flux:button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $appointments->links() }}
        </div>
    @else
        <div class="rounded-lg border border-zinc-200 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                <flux:icon name="calendar-days" class="h-6 w-6 text-zinc-400" />
            </div>
            <h3 class="mt-4 text-sm font-medium text-zinc-900 dark:text-white">{{ __('No appointments found') }}</h3>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                @if($search || $consultationTypeFilter || $dateFilter || $sourceFilter)
                    {{ __('Try adjusting your filters.') }}
                @elseif($status === 'pending')
                    {{ __('No pending requests at this time.') }}
                @else
                    {{ __('No appointments match the criteria.') }}
                @endif
            </p>
            <div class="mt-4 flex justify-center gap-2">
                @if($search || $consultationTypeFilter || $dateFilter || $sourceFilter)
                    <flux:button wire:click="clearFilters" variant="ghost">{{ __('Clear filters') }}</flux:button>
                @endif
                <flux:button href="{{ route('nurse.walk-in') }}" wire:navigate variant="primary" icon="plus">
                    {{ __('Register Walk-in') }}
                </flux:button>
            </div>
        </div>
    @endif

    {{-- View Modal --}}
    <flux:modal wire:model="showViewModal" class="max-w-2xl">
        @if($selectedAppointment)
            @php
                $apt = $selectedAppointment;
                $statusColor = match($apt->status) {
                    'pending' => 'yellow',
                    'approved', 'checked_in' => 'green',
                    'in_progress' => 'blue',
                    'cancelled', 'no_show' => 'red',
                    'completed' => 'zinc',
                    default => 'zinc',
                };
            @endphp

            <div class="space-y-6">
                <div class="flex items-start justify-between">
                    <div>
                        <flux:heading size="lg">{{ __('Appointment Details') }}</flux:heading>
                        <div class="mt-1 flex items-center gap-2">
                            <flux:badge color="{{ $statusColor }}">{{ str_replace('_', ' ', ucfirst($apt->status)) }}</flux:badge>
                            @if($apt->source === 'walk-in')
                                <flux:badge color="zinc">{{ __('Walk-in') }}</flux:badge>
                            @endif
                            @if($apt->queue)
                                <flux:badge color="zinc">{{ __('Queue') }}: {{ $apt->queue->formatted_number }}</flux:badge>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Patient Info --}}
                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <flux:heading size="sm" class="mb-3">{{ __('Patient Information') }}</flux:heading>
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Name') }}</dt>
                            <dd class="font-medium text-zinc-900 dark:text-white">
                                {{ $apt->patient_first_name }} {{ $apt->patient_middle_name }} {{ $apt->patient_last_name }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Date of Birth') }}</dt>
                            <dd class="text-zinc-900 dark:text-white">
                                {{ $apt->patient_date_of_birth?->format('M d, Y') ?? '-' }}
                                @if($apt->patient_date_of_birth)
                                    <span class="text-zinc-500">({{ $apt->patient_date_of_birth->age }} {{ __('yrs') }})</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Gender') }}</dt>
                            <dd class="text-zinc-900 dark:text-white">{{ ucfirst($apt->patient_gender ?? '-') }}</dd>
                        </div>
                        <div>
                            <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Phone') }}</dt>
                            <dd class="text-zinc-900 dark:text-white">{{ $apt->patient_phone ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Appointment Info --}}
                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <flux:heading size="sm" class="mb-3">{{ __('Appointment Information') }}</flux:heading>
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Consultation Type') }}</dt>
                            <dd class="font-medium text-zinc-900 dark:text-white">{{ $apt->consultationType?->name ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Appointment Date') }}</dt>
                            <dd class="text-zinc-900 dark:text-white">{{ $apt->appointment_date?->format('M d, Y') }}</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Chief Complaints') }}</dt>
                            <dd class="mt-1 text-zinc-900 dark:text-white whitespace-pre-wrap">{{ $apt->chief_complaints ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Account Owner --}}
                @if($apt->user)
                    <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                        <flux:heading size="sm" class="mb-3">{{ __('Account Owner') }}</flux:heading>
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                <flux:icon name="user" class="h-5 w-5 text-zinc-500" />
                            </div>
                            <div>
                                <div class="font-medium text-zinc-900 dark:text-white">{{ $apt->user->name }}</div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $apt->user->email }}</div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Cancellation Reason --}}
                @if($apt->status === 'cancelled' && $apt->cancellation_reason)
                    <flux:callout variant="danger" icon="x-circle" :heading="__('Cancellation Reason')">
                        {{ $apt->cancellation_reason }}
                    </flux:callout>
                @endif

                {{-- Actions --}}
                <div class="flex items-center justify-between border-t border-zinc-200 pt-4 dark:border-zinc-700">
                    <flux:button wire:click="closeViewModal" variant="ghost">{{ __('Close') }}</flux:button>

                    <div class="flex gap-2">
                        @if($apt->status === 'pending')
                            <flux:button wire:click="openApproveModal({{ $apt->id }})" variant="primary" icon="check-circle">
                                {{ __('Approve') }}
                            </flux:button>
                            <flux:button wire:click="openCancelModal({{ $apt->id }})" variant="danger" icon="x-circle">
                                {{ __('Cancel') }}
                            </flux:button>
                        @elseif($apt->status === 'approved')
                            <flux:button wire:click="openCancelModal({{ $apt->id }})" variant="danger" icon="x-circle">
                                {{ __('Cancel') }}
                            </flux:button>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </flux:modal>

    {{-- Approve Modal --}}
    <flux:modal wire:model="showApproveModal" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Approve Appointment') }}</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    {{ __('A queue number will be assigned automatically.') }}
                </flux:text>
            </div>

            <flux:field>
                <flux:label>{{ __('Notes (Optional)') }}</flux:label>
                <flux:textarea wire:model="notes" rows="2" placeholder="{{ __('Add any notes...') }}" />
                <flux:error name="notes" />
            </flux:field>

            <div class="flex justify-end gap-3">
                <flux:button wire:click="closeApproveModal" variant="ghost">{{ __('Cancel') }}</flux:button>
                <flux:button wire:click="approveAppointment" variant="primary" icon="check-circle">
                    {{ __('Approve') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Cancel Modal --}}
    <flux:modal wire:model="showCancelModal" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Cancel Appointment') }}</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    {{ __('The patient will be notified about the cancellation.') }}
                </flux:text>
            </div>

            <flux:field>
                <flux:label>{{ __('Reason for Cancellation') }} *</flux:label>
                <flux:textarea wire:model="cancelReason" rows="3" placeholder="{{ __('Please provide a clear reason...') }}" />
                <flux:description>{{ __('Minimum 10 characters') }}</flux:description>
                <flux:error name="cancelReason" />
            </flux:field>

            <div class="flex justify-end gap-3">
                <flux:button wire:click="closeCancelModal" variant="ghost">{{ __('Keep') }}</flux:button>
                <flux:button wire:click="cancelAppointment" variant="danger" icon="x-circle">
                    {{ __('Cancel Appointment') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</section>
