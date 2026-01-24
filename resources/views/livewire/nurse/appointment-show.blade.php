<section class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-1">
            <flux:heading size="xl" level="1">{{ __('Appointment Details') }}</flux:heading>
            <flux:text variant="subtle" class="text-sm">
                {{ __('Review patient information and manage this appointment.') }}
            </flux:text>
        </div>
        <flux:button :href="route('nurse.appointments')" variant="ghost" icon="arrow-left" size="sm" wire:navigate>
            {{ __('Back to list') }}
        </flux:button>
    </div>

    @php
        $statusColor = match ($appointment->status) {
            'approved', 'completed' => 'green',
            'checked_in', 'in_progress' => 'blue',
            'cancelled', 'no_show' => 'red',
            'pending' => 'yellow',
            default => 'gray',
        };
        $statusLabel = str_replace('_', ' ', ucfirst($appointment->status));
        $dateLabel = $appointment->appointment_date?->format('M d, Y');
        $timeLabel = $appointment->appointment_time?->format('h:i A');
        $consultationType = $appointment->consultationType;
        $accountOwner = $appointment->user;
        $accountInfo = $accountOwner?->personalInformation;
    @endphp

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-xl border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-200/70 p-5 dark:border-zinc-800">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex flex-wrap items-center gap-3">
                            <flux:heading size="lg" level="2">
                                {{ $consultationType?->name ?? __('Consultation') }}
                            </flux:heading>
                            <flux:badge color="{{ $statusColor }}">{{ $statusLabel }}</flux:badge>
                            @if($appointment->source === 'walk-in')
                                <flux:badge color="gray">{{ __('Walk-in') }}</flux:badge>
                            @endif
                        </div>
                        @if($appointment->queue)
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Queue') }}:</span>
                                <span class="rounded-full bg-zinc-100 px-3 py-1 text-sm font-bold text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200">
                                    {{ $appointment->queue->formatted_number }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="p-5 space-y-6">
                    <div>
                        <flux:heading size="sm" level="3" class="mb-3 flex items-center gap-2">
                            <flux:icon name="user" class="h-4 w-4 text-zinc-400" />
                            {{ __('Patient Information') }}
                        </flux:heading>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <flux:text class="text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                    {{ __('Full Name') }}
                                </flux:text>
                                <flux:text class="text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ $appointment->patient_first_name }}
                                    {{ $appointment->patient_middle_name }}
                                    {{ $appointment->patient_last_name }}
                                </flux:text>
                            </div>
                            <div>
                                <flux:text class="text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                    {{ __('Date of Birth') }}
                                </flux:text>
                                <flux:text class="text-sm text-zinc-700 dark:text-zinc-200">
                                    {{ $appointment->patient_date_of_birth?->format('M d, Y') ?? __('N/A') }}
                                    @if($patientAge)
                                        <span class="text-zinc-500">({{ $patientAge }})</span>
                                    @endif
                                </flux:text>
                            </div>
                            <div>
                                <flux:text class="text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                    {{ __('Gender') }}
                                </flux:text>
                                <flux:text class="text-sm text-zinc-700 dark:text-zinc-200">
                                    {{ ucfirst($appointment->patient_gender ?? __('N/A')) }}
                                </flux:text>
                            </div>
                            <div>
                                <flux:text class="text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                    {{ __('Phone') }}
                                </flux:text>
                                <flux:text class="text-sm text-zinc-700 dark:text-zinc-200">
                                    @if($appointment->patient_phone)
                                        <a href="tel:{{ $appointment->patient_phone }}" class="text-blue-600 hover:underline dark:text-blue-400">
                                            {{ $appointment->patient_phone }}
                                        </a>
                                    @else
                                        {{ __('Not provided') }}
                                    @endif
                                </flux:text>
                            </div>
                            @if($patientAddress)
                                <div class="sm:col-span-2">
                                    <flux:text class="text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                        {{ __('Address') }}
                                    </flux:text>
                                    <flux:text class="text-sm text-zinc-700 dark:text-zinc-200">
                                        {{ $patientAddress }}
                                    </flux:text>
                                </div>
                            @endif
                            <div>
                                <flux:text class="text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                    {{ __('Relationship to Account') }}
                                </flux:text>
                                <flux:text class="text-sm text-zinc-700 dark:text-zinc-200">
                                    {{ ucfirst($appointment->relationship_to_account) }}
                                </flux:text>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-zinc-200/70 pt-5 dark:border-zinc-800">
                        <flux:heading size="sm" level="3" class="mb-3 flex items-center gap-2">
                            <flux:icon name="calendar-days" class="h-4 w-4 text-zinc-400" />
                            {{ __('Appointment Details') }}
                        </flux:heading>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <flux:text class="text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                    {{ __('Requested Date') }}
                                </flux:text>
                                <flux:text class="text-sm text-zinc-700 dark:text-zinc-200">
                                    {{ $dateLabel }}
                                </flux:text>
                            </div>
                            <div>
                                <flux:text class="text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                    {{ __('Time') }}
                                </flux:text>
                                <flux:text class="text-sm text-zinc-700 dark:text-zinc-200">
                                    {{ $timeLabel ?? __('To be assigned') }}
                                </flux:text>
                            </div>
                            <div>
                                <flux:text class="text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                    {{ __('Doctor') }}
                                </flux:text>
                                <flux:text class="text-sm text-zinc-700 dark:text-zinc-200">
                                    {{ $appointment->doctor?->name ?? __('To be assigned') }}
                                </flux:text>
                            </div>
                            <div>
                                <flux:text class="text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                    {{ __('Request Submitted') }}
                                </flux:text>
                                <flux:text class="text-sm text-zinc-700 dark:text-zinc-200">
                                    {{ $appointment->created_at->format('M d, Y h:i A') }}
                                </flux:text>
                            </div>
                        </div>
                    </div>

                    @if(filled($appointment->chief_complaints))
                        <div class="border-t border-zinc-200/70 pt-5 dark:border-zinc-800">
                            <flux:heading size="sm" level="3" class="mb-3 flex items-center gap-2">
                                <flux:icon name="document-text" class="h-4 w-4 text-zinc-400" />
                                {{ __('Chief Complaints') }}
                            </flux:heading>
                            <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800/50">
                                <flux:text class="text-sm text-zinc-700 dark:text-zinc-200 whitespace-pre-wrap">
                                    {{ $appointment->chief_complaints }}
                                </flux:text>
                            </div>
                        </div>
                    @endif

                    @if(filled($appointment->notes))
                        <div class="border-t border-zinc-200/70 pt-5 dark:border-zinc-800">
                            <flux:heading size="sm" level="3" class="mb-3 flex items-center gap-2">
                                <flux:icon name="chat-bubble-left-right" class="h-4 w-4 text-zinc-400" />
                                {{ __('Notes') }}
                            </flux:heading>
                            <flux:text class="text-sm text-zinc-700 dark:text-zinc-200">
                                {{ $appointment->notes }}
                            </flux:text>
                        </div>
                    @endif
                </div>
            </div>

            @if($appointment->status !== 'pending' && ($appointment->approvedBy || $appointment->approved_at))
                <div class="rounded-xl border border-zinc-200/70 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <flux:heading size="sm" level="3" class="mb-3 flex items-center gap-2">
                        <flux:icon name="clipboard-document-check" class="h-4 w-4 text-zinc-400" />
                        {{ __('Processing Information') }}
                    </flux:heading>
                    <div class="grid gap-4 sm:grid-cols-2">
                        @if($appointment->approvedBy)
                            <div>
                                <flux:text class="text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                    {{ __('Processed By') }}
                                </flux:text>
                                <flux:text class="text-sm text-zinc-700 dark:text-zinc-200">
                                    {{ $appointment->approvedBy->name }}
                                </flux:text>
                            </div>
                        @endif
                        @if($appointment->approved_at)
                            <div>
                                <flux:text class="text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                    {{ __('Processed At') }}
                                </flux:text>
                                <flux:text class="text-sm text-zinc-700 dark:text-zinc-200">
                                    {{ $appointment->approved_at->format('M d, Y h:i A') }}
                                </flux:text>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            @if(filled($appointment->cancellation_reason))
                <flux:callout variant="danger" icon="x-circle" :heading="__('Cancellation Reason')">
                    <flux:text class="text-sm">
                        {{ $appointment->cancellation_reason }}
                    </flux:text>
                </flux:callout>
            @endif

            @if(filled($appointment->decline_reason) || $appointment->suggested_date)
                <flux:callout variant="warning" icon="exclamation-circle" :heading="__('Scheduling Updates')">
                    <div class="space-y-1">
                        @if(filled($appointment->decline_reason))
                            <flux:text class="text-sm">{{ $appointment->decline_reason }}</flux:text>
                        @endif
                        @if($appointment->suggested_date)
                            <flux:text class="text-sm">
                                {{ __('Suggested date') }}: {{ $appointment->suggested_date?->format('M d, Y') }}
                            </flux:text>
                        @endif
                    </div>
                </flux:callout>
            @endif
        </div>

        <div class="space-y-6">
            <div class="rounded-xl border border-zinc-200/70 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <flux:heading size="sm" level="3" class="mb-4 flex items-center gap-2">
                    <flux:icon name="identification" class="h-4 w-4 text-zinc-400" />
                    {{ __('Account Owner') }}
                </flux:heading>
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                            <flux:icon name="user" class="h-5 w-5 text-zinc-500" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $accountOwner?->name ?? __('Unknown') }}
                            </p>
                            @if($accountOwner?->email)
                                <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $accountOwner->email }}
                                </p>
                            @endif
                        </div>
                    </div>

                    @if($accountInfo)
                        <div class="space-y-2 border-t border-zinc-200/70 pt-3 text-sm dark:border-zinc-800">
                            @if($accountInfo->phone)
                                <div class="flex items-center gap-2 text-zinc-600 dark:text-zinc-300">
                                    <flux:icon name="phone" class="h-4 w-4 text-zinc-400" />
                                    <a href="tel:{{ $accountInfo->phone }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                                        {{ $accountInfo->phone }}
                                    </a>
                                </div>
                            @endif
                            @if($accountOwner?->email)
                                <div class="flex items-center gap-2 text-zinc-600 dark:text-zinc-300">
                                    <flux:icon name="envelope" class="h-4 w-4 text-zinc-400" />
                                    <a href="mailto:{{ $accountOwner->email }}" class="truncate hover:text-blue-600 dark:hover:text-blue-400">
                                        {{ $accountOwner->email }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            @if($appointment->queue)
                <div class="rounded-xl border border-zinc-200/70 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <flux:heading size="sm" level="3" class="mb-4 flex items-center gap-2">
                        <flux:icon name="queue-list" class="h-4 w-4 text-zinc-400" />
                        {{ __('Queue Information') }}
                    </flux:heading>
                    <div class="space-y-3">
                        <div class="flex items-center justify-center">
                            <span class="rounded-xl bg-zinc-100 px-6 py-3 text-2xl font-bold text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200">
                                {{ $appointment->queue->formatted_number }}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</p>
                                <p class="font-medium text-zinc-700 dark:text-zinc-200">
                                    {{ ucfirst($appointment->queue->status) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Date') }}</p>
                                <p class="font-medium text-zinc-700 dark:text-zinc-200">
                                    {{ $appointment->queue->queue_date?->format('M d') }}
                                </p>
                            </div>
                            @if($appointment->queue->estimated_time)
                                <div class="col-span-2">
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Estimated Time') }}</p>
                                    <p class="font-medium text-zinc-700 dark:text-zinc-200">
                                        {{ $appointment->queue->estimated_time?->format('h:i A') }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <div class="rounded-xl border border-zinc-200/70 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <flux:heading size="sm" level="3" class="mb-4">{{ __('Actions') }}</flux:heading>
                <div class="space-y-3">
                    @if($appointment->status === 'pending')
                        <flux:button
                            wire:click="openApproveModal"
                            variant="primary"
                            class="w-full justify-center"
                            icon="check-circle"
                        >
                            {{ __('Approve Appointment') }}
                        </flux:button>
                        <flux:button
                            wire:click="openCancelModal"
                            variant="danger"
                            class="w-full justify-center"
                            icon="x-circle"
                        >
                            {{ __('Cancel Appointment') }}
                        </flux:button>
                    @elseif($appointment->status === 'approved')
                        <flux:button
                            wire:click="openCancelModal"
                            variant="danger"
                            class="w-full justify-center"
                            icon="x-circle"
                        >
                            {{ __('Cancel Appointment') }}
                        </flux:button>
                    @endif

                    @if($appointment->patient_phone)
                        <flux:button
                            href="tel:{{ $appointment->patient_phone }}"
                            variant="outline"
                            class="w-full justify-center"
                            icon="phone"
                        >
                            {{ __('Call Patient') }}
                        </flux:button>
                    @endif

                    @if($accountOwner?->email)
                        <flux:button
                            href="mailto:{{ $accountOwner->email }}"
                            variant="outline"
                            class="w-full justify-center"
                            icon="envelope"
                        >
                            {{ __('Email Account Owner') }}
                        </flux:button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <flux:modal wire:model="showApproveModal" class="max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Approve Appointment') }}</flux:heading>
                <flux:text variant="subtle" class="mt-1 text-sm">
                    {{ __('Confirm approval for :patient on :date. A queue number will be assigned automatically.', [
                        'patient' => $appointment->patient_first_name . ' ' . $appointment->patient_last_name,
                        'date' => $appointment->appointment_date?->format('M d, Y'),
                    ]) }}
                </flux:text>
            </div>

            <div class="space-y-4">
                <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800/50">
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Consultation Type') }}</p>
                            <p class="font-medium text-zinc-900 dark:text-white">{{ $consultationType?->name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Date') }}</p>
                            <p class="font-medium text-zinc-900 dark:text-white">{{ $dateLabel }}</p>
                        </div>
                    </div>
                </div>

                <flux:field>
                    <flux:label>{{ __('Appointment Time (Optional)') }}</flux:label>
                    <flux:input
                        type="time"
                        wire:model="appointmentTime"
                    />
                    <flux:description>{{ __('Leave empty if time is flexible') }}</flux:description>
                    <flux:error name="appointmentTime" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Notes (Optional)') }}</flux:label>
                    <flux:textarea
                        wire:model="notes"
                        rows="2"
                        placeholder="{{ __('Add any notes for the patient or staff...') }}"
                    />
                    <flux:error name="notes" />
                </flux:field>
            </div>

            <div class="flex items-center justify-end gap-3">
                <flux:button wire:click="closeApproveModal" variant="ghost">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button
                    wire:click="approveAppointment"
                    variant="primary"
                    icon="check-circle"
                >
                    {{ __('Confirm Approval') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal wire:model="showCancelModal" class="max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Cancel Appointment') }}</flux:heading>
                <flux:text variant="subtle" class="mt-1 text-sm">
                    {{ __('This will cancel the appointment for :patient scheduled on :date. The patient will be notified.', [
                        'patient' => $appointment->patient_first_name . ' ' . $appointment->patient_last_name,
                        'date' => $appointment->appointment_date?->format('M d, Y'),
                    ]) }}
                </flux:text>
            </div>

            <flux:field>
                <flux:label>{{ __('Reason for Cancellation') }} <span class="text-red-500">*</span></flux:label>
                <flux:textarea
                    wire:model="cancelReason"
                    rows="3"
                    placeholder="{{ __('Please provide a clear reason for the cancellation...') }}"
                    required
                />
                <flux:description>{{ __('Minimum 10 characters. This will be visible to the patient.') }}</flux:description>
                <flux:error name="cancelReason" />
            </flux:field>

            <div class="flex items-center justify-end gap-3">
                <flux:button wire:click="closeCancelModal" variant="ghost">
                    {{ __('Keep Appointment') }}
                </flux:button>
                <flux:button
                    wire:click="cancelAppointment"
                    variant="danger"
                    icon="x-circle"
                >
                    {{ __('Confirm Cancellation') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</section>
