<section class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-1">
            <flux:heading size="xl" level="1">{{ __('Appointment Details') }}</flux:heading>
            <flux:text variant="subtle" class="text-sm">
                {{ __('Review appointment status, schedule, and visit information.') }}
            </flux:text>
        </div>
        <flux:button :href="route('patient.appointments')" variant="ghost" icon="arrow-left" size="sm" wire:navigate>
            {{ __('Back to appointments') }}
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
    @endphp

    <div class="space-y-4">
        <div class="rounded-xl border border-zinc-200/70 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-wrap items-center gap-2">
                <flux:heading size="lg" level="2">
                    {{ $appointment->consultationType?->name ?? __('Consultation') }}
                </flux:heading>
                <flux:badge color="{{ $statusColor }}">{{ $statusLabel }}</flux:badge>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <flux:text class="text-xs font-semibold uppercase tracking-wide text-zinc-500">
                        {{ __('Schedule') }}
                    </flux:text>
                    <flux:text class="text-sm text-zinc-700 dark:text-zinc-200">
                        {{ $dateLabel }}
                        @if($timeLabel)
                            {{ __('at') }} {{ $timeLabel }}
                        @else
                            <span class="text-zinc-500">({{ __('Time to be confirmed') }})</span>
                        @endif
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
                        {{ __('Patient') }}
                    </flux:text>
                    <flux:text class="text-sm text-zinc-700 dark:text-zinc-200">
                        {{ $appointment->patient_first_name }} {{ $appointment->patient_last_name }}
                        @if($appointment->relationship_to_account !== 'self')
                            <span class="text-zinc-500">({{ ucfirst($appointment->relationship_to_account) }})</span>
                        @endif
                    </flux:text>
                </div>
                <div>
                    <flux:text class="text-xs font-semibold uppercase tracking-wide text-zinc-500">
                        {{ __('Contact') }}
                    </flux:text>
                    <flux:text class="text-sm text-zinc-700 dark:text-zinc-200">
                        {{ $appointment->patient_phone ?? __('Not provided') }}
                    </flux:text>
                </div>
            </div>

            @if(filled($appointment->chief_complaints))
                <div class="mt-5 border-t border-zinc-200/70 pt-4 dark:border-zinc-800">
                    <flux:text class="text-xs font-semibold uppercase tracking-wide text-zinc-500">
                        {{ __('Chief complaints') }}
                    </flux:text>
                    <flux:text class="text-sm text-zinc-700 dark:text-zinc-200">
                        {{ $appointment->chief_complaints }}
                    </flux:text>
                </div>
            @endif
        </div>

        @if($appointment->queue)
            <div class="rounded-xl border border-zinc-200/70 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between gap-4">
                    <div class="space-y-1">
                        <flux:heading size="sm" level="3">{{ __('Queue Information') }}</flux:heading>
                        <flux:text variant="subtle" class="text-sm">
                            {{ __('Keep an eye on your queue number once you arrive.') }}
                        </flux:text>
                    </div>
                    <span class="rounded-full bg-zinc-100 px-3 py-1 text-sm font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                        {{ $appointment->queue->formatted_number }}
                    </span>
                </div>

                <div class="mt-3 text-sm text-zinc-600 dark:text-zinc-300">
                    {{ __('Queue date') }}: {{ $appointment->queue->queue_date?->format('M d, Y') }}
                </div>
            </div>
        @endif

        @if(filled($appointment->cancellation_reason))
            <div class="rounded-xl border border-red-200/70 bg-red-50 p-5 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-200">
                <div class="font-semibold">{{ __('Cancellation reason') }}</div>
                <div class="mt-1">{{ $appointment->cancellation_reason }}</div>
            </div>
        @endif

        @if(filled($appointment->decline_reason) || $appointment->suggested_date)
            <div class="rounded-xl border border-amber-200/70 bg-amber-50 p-5 text-sm text-amber-700 dark:border-amber-900/40 dark:bg-amber-950/40 dark:text-amber-200">
                <div class="font-semibold">{{ __('Scheduling updates') }}</div>
                @if(filled($appointment->decline_reason))
                    <div class="mt-1">{{ $appointment->decline_reason }}</div>
                @endif
                @if($appointment->suggested_date)
                    <div class="mt-2">
                        {{ __('Suggested date') }}: {{ $appointment->suggested_date?->format('M d, Y') }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</section>
