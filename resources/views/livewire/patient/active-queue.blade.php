<div class="space-y-6">
    <h1 class="text-2xl font-bold">Queue Status</h1>

    @if($activeQueue)
        <div class="rounded-lg border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="p-4">
                <div class="text-center space-y-6">
                    <!-- Queue Number Display -->
                    <div class="bg-gradient-to-r from-zinc-900 to-zinc-800 rounded-lg p-6 text-white dark:from-zinc-100 dark:to-zinc-200 dark:text-zinc-900">
                        <p class="text-lg font-medium mb-2">Your Queue Number</p>
                        <p class="text-4xl font-bold">{{ $activeQueue->formatted_number }}</p>
                        <p class="text-zinc-200 dark:text-zinc-600 mt-2">{{ $consultationType->name }}</p>
                    </div>

                    <!-- Queue Status Information -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-900 rounded-lg">
                            <span aria-hidden="true" class="mx-auto mb-2 block h-8 w-8 rounded-full bg-zinc-100 dark:bg-zinc-800"></span>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">Estimated Time</p>
                            <p class="font-medium">{{ $estimatedTime->format('h:i A') }}</p>
                        </div>
                        
                        <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-900 rounded-lg">
                            <span aria-hidden="true" class="mx-auto mb-2 block h-8 w-8 rounded-full bg-zinc-100 dark:bg-zinc-800"></span>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">Ahead of You</p>
                            <p class="font-medium">{{ $patientsAhead }} patients</p>
                        </div>
                        
                        <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-900 rounded-lg">
                            <span aria-hidden="true" class="mx-auto mb-2 block h-8 w-8 rounded-full bg-zinc-100 dark:bg-zinc-800"></span>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">Your Position</p>
                            <p class="font-medium">#{{ $this->getQueuePosition() }}</p>
                        </div>
                    </div>

                    <!-- Current Serving -->
                    @if($currentServing)
                        <div class="bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">
                            <p class="text-sm text-zinc-900 dark:text-zinc-100 font-medium">
                                Now Serving: {{ $currentServing->formatted_number }}
                            </p>
                        </div>
                    @endif

                    <!-- Status Alert -->
                    <div class="bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">
                        @if($patientsAhead <= 2)
                            <div class="flex items-center gap-2 text-zinc-900 dark:text-zinc-100">
                                <span aria-hidden="true" class="h-3 w-3 rounded-full bg-zinc-400"></span>
                                <span class="font-medium">Get Ready!</span>
                            </div>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                                Your turn is coming up soon. Please wait in the designated area.
                            </p>
                        @elseif($patientsAhead <= 5)
                            <div class="flex items-center gap-2 text-zinc-900 dark:text-zinc-100">
                                <span aria-hidden="true" class="h-3 w-3 rounded-full bg-zinc-400"></span>
                                <span class="font-medium">Getting Close</span>
                            </div>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                                You're in the next group. Stay nearby and listen for your number.
                            </p>
                        @else
                            <div class="flex items-center gap-2 text-zinc-900 dark:text-zinc-100">
                                <span aria-hidden="true" class="h-3 w-3 rounded-full bg-zinc-400"></span>
                                <span class="font-medium">Waiting</span>
                            </div>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                                Please wait comfortably. We'll send you SMS updates when you're getting closer.
                            </p>
                        @endif
                    </div>

                    <!-- SMS Notification Status -->
                    <div class="flex items-center justify-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                        <span aria-hidden="true" class="h-2 w-2 rounded-full bg-zinc-400"></span>
                        <span>SMS alerts enabled</span>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="rounded-lg border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="px-4 py-8 text-center">
                <img
                    src="{{ asset('images/undraw_wait-in-line_fbdq.svg') }}"
                    alt="Waiting in line"
                    class="mx-auto mb-4 h-28 w-auto opacity-80"
                />
                <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">No Active Queue</h3>
                <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                    You don't have an active queue ticket. Book an appointment or check in at the hospital.
                </p>
                <div class="flex gap-4 justify-center">
                    <flux:button href="{{ route('patient.appointments.book') }}" variant="primary" wire:navigate>
                        Book Appointment
                    </flux:button>
                    <flux:button href="{{ route('patient.appointments') }}" variant="outline" wire:navigate>
                        My Appointments
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    <!-- Auto-refresh notice -->
    <flux:callout type="info">
        This page automatically refreshes to show the latest queue status.
    </flux:callout>
</div>
