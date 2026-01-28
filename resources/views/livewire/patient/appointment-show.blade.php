<div class="min-h-screen bg-gradient-to-b from-zinc-50 to-white dark:from-zinc-900 dark:to-zinc-800">
    <div class="mx-auto max-w-2xl px-4 py-6 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-6">
            <a href="{{ route('patient.appointments') }}"
               class="mb-3 inline-flex items-center gap-1 text-sm text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200"
               wire:navigate>
                <flux:icon name="arrow-left" class="h-4 w-4" />
                {{ __('Back to Appointments') }}
            </a>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">{{ __('Appointment Details') }}</h1>
        </div>

        @php
            $statusConfig = match ($appointment->status) {
                'approved' => ['bg' => 'bg-success/10', 'text' => 'text-success', 'dot' => 'bg-success', 'border' => 'border-success/30'],
                'completed' => ['bg' => 'bg-success/10', 'text' => 'text-success', 'dot' => 'bg-success', 'border' => 'border-success/30'],
                'checked_in', 'in_progress' => ['bg' => 'bg-primary/10', 'text' => 'text-primary', 'dot' => 'bg-primary', 'border' => 'border-primary/30'],
                'cancelled', 'no_show' => ['bg' => 'bg-destructive/10', 'text' => 'text-destructive', 'dot' => 'bg-destructive', 'border' => 'border-destructive/30'],
                'pending' => ['bg' => 'bg-warning/10', 'text' => 'text-warning', 'dot' => 'bg-warning', 'border' => 'border-warning/30'],
                default => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400', 'dot' => 'bg-zinc-400', 'border' => 'border-zinc-200 dark:border-zinc-700'],
            };
            $statusLabel = str_replace('_', ' ', ucfirst($appointment->status));
        @endphp

        <div class="space-y-4">
            {{-- Status Banner --}}
            <div class="rounded-xl border {{ $statusConfig['border'] }} {{ $statusConfig['bg'] }} p-4">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white/60 dark:bg-zinc-800/60">
                        <span class="h-3 w-3 rounded-full {{ $statusConfig['dot'] }}"></span>
                    </span>
                    <div>
                        <p class="font-semibold {{ $statusConfig['text'] }}">{{ $statusLabel }}</p>
                        <p class="text-sm {{ $statusConfig['text'] }} opacity-80">
                            @switch($appointment->status)
                                @case('pending')
                                    {{ __('Waiting for approval from the clinic') }}
                                    @break
                                @case('approved')
                                    {{ __('Your appointment has been confirmed') }}
                                    @break
                                @case('checked_in')
                                    {{ __('You have checked in at the clinic') }}
                                    @break
                                @case('in_progress')
                                    {{ __('Your consultation is in progress') }}
                                    @break
                                @case('completed')
                                    {{ __('Your visit has been completed') }}
                                    @break
                                @case('cancelled')
                                    {{ __('This appointment was cancelled') }}
                                    @break
                                @case('no_show')
                                    {{ __('Marked as no-show') }}
                                    @break
                                @default
                                    {{ __('Appointment status') }}
                            @endswitch
                        </p>
                    </div>
                </div>
            </div>

            {{-- Queue Card (if available) --}}
            @if($appointment->queue)
                <div class="overflow-hidden rounded-xl bg-gradient-to-br from-primary to-primary/80 p-5 text-white shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-white/80">{{ __('Your Queue Number') }}</p>
                            <p class="mt-1 text-4xl font-bold">{{ $appointment->queue->formatted_number }}</p>
                        </div>
                        <div class="rounded-full bg-white/20 p-3">
                            <flux:icon name="ticket" class="h-6 w-6" />
                        </div>
                    </div>
                    <p class="mt-3 text-sm text-white/80">
                        {{ __('Queue date') }}: {{ $appointment->queue->queue_date?->format('M d, Y') }}
                    </p>
                </div>
            @endif

            {{-- Appointment Info Card --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                <h2 class="mb-4 flex items-center gap-2 font-semibold text-zinc-900 dark:text-white">
                    <flux:icon name="calendar-days" class="h-5 w-5 text-zinc-400" />
                    {{ __('Appointment Information') }}
                </h2>

                <div class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Consultation Type') }}</p>
                            <p class="mt-1 font-medium text-zinc-900 dark:text-white">{{ $appointment->consultationType?->name ?? __('Consultation') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Schedule') }}</p>
                            <p class="mt-1 font-medium text-zinc-900 dark:text-white">
                                {{ $appointment->appointment_date?->format('M d, Y') }}
                                @if($appointment->appointment_time)
                                    {{ __('at') }} {{ $appointment->appointment_time->format('h:i A') }}
                                @else
                                    <span class="text-zinc-500">({{ __('Time TBA') }})</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Doctor') }}</p>
                            <p class="mt-1 font-medium text-zinc-900 dark:text-white">{{ $appointment->doctor?->name ?? __('To be assigned') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Contact') }}</p>
                            <p class="mt-1 font-medium text-zinc-900 dark:text-white">{{ $appointment->patient_phone ?? __('Not provided') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Patient Info Card --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                <h2 class="mb-4 flex items-center gap-2 font-semibold text-zinc-900 dark:text-white">
                    <flux:icon name="user" class="h-5 w-5 text-zinc-400" />
                    {{ __('Patient Information') }}
                </h2>

                <div class="space-y-3">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Patient Name') }}</p>
                        <p class="mt-1 font-medium text-zinc-900 dark:text-white">
                            {{ $appointment->patient_first_name }} {{ $appointment->patient_last_name }}
                            @if($appointment->relationship_to_account !== 'self')
                                <span class="ml-1 rounded-full bg-zinc-100 px-2 py-0.5 text-xs text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                    {{ ucfirst($appointment->relationship_to_account) }}
                                </span>
                            @endif
                        </p>
                    </div>

                    @if(filled($appointment->chief_complaints))
                        <div class="border-t border-zinc-100 pt-3 dark:border-zinc-700">
                            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Chief Complaints') }}</p>
                            <p class="mt-1 text-sm text-zinc-700 dark:text-zinc-300">{{ $appointment->chief_complaints }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Cancellation Reason --}}
            @if(filled($appointment->cancellation_reason))
                <div class="rounded-xl border border-destructive/30 bg-destructive/10 p-4">
                    <div class="flex gap-3">
                        <flux:icon name="x-circle" class="h-5 w-5 flex-shrink-0 text-destructive" />
                        <div>
                            <p class="font-medium text-destructive">{{ __('Cancellation Reason') }}</p>
                            <p class="mt-1 text-sm text-destructive/80">{{ $appointment->cancellation_reason }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Scheduling Updates --}}
            @if(filled($appointment->decline_reason) || $appointment->suggested_date)
                <div class="rounded-xl border border-warning/30 bg-warning/10 p-4">
                    <div class="flex gap-3">
                        <flux:icon name="exclamation-circle" class="h-5 w-5 flex-shrink-0 text-warning" />
                        <div>
                            <p class="font-medium text-warning-foreground dark:text-warning">{{ __('Scheduling Update') }}</p>
                            @if(filled($appointment->decline_reason))
                                <p class="mt-1 text-sm text-warning-foreground/80 dark:text-warning/80">{{ $appointment->decline_reason }}</p>
                            @endif
                            @if($appointment->suggested_date)
                                <p class="mt-1 text-sm text-warning-foreground/80 dark:text-warning/80">
                                    {{ __('Suggested date') }}: {{ $appointment->suggested_date->format('M d, Y') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Action Button for Pending --}}
            @if($appointment->status === 'pending')
                <div class="sticky bottom-20 lg:bottom-4">
                    <flux:modal.trigger name="cancel-appointment">
                        <button type="button"
                                class="w-full rounded-xl border border-destructive/30 bg-white px-4 py-3 text-center font-semibold text-destructive shadow-sm transition hover:bg-destructive/5 dark:bg-zinc-800 dark:hover:bg-destructive/10">
                            {{ __('Cancel Appointment') }}
                        </button>
                    </flux:modal.trigger>
                </div>

                <flux:modal name="cancel-appointment" focusable class="max-w-sm">
                    <div class="p-4 space-y-4">
                        <div class="text-center">
                            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-destructive/10">
                                <flux:icon name="exclamation-triangle" class="h-6 w-6 text-destructive" />
                            </div>
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ __('Cancel Appointment?') }}</h3>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ __('This action cannot be undone. You can book a new appointment anytime.') }}
                            </p>
                        </div>
                        <div class="flex gap-3">
                            <flux:modal.close class="flex-1">
                                <flux:button variant="outline" class="w-full">{{ __('Keep') }}</flux:button>
                            </flux:modal.close>
                            <flux:modal.close class="flex-1">
                                <flux:button variant="danger" class="w-full" wire:click="cancelAppointment">
                                    {{ __('Cancel') }}
                                </flux:button>
                            </flux:modal.close>
                        </div>
                    </div>
                </flux:modal>
            @endif
        </div>

        {{-- Bottom spacing for mobile nav --}}
        <div class="h-20 lg:hidden"></div>
    </div>
</div>
