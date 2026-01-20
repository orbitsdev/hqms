<div class="space-y-6">
    <h1 class="text-2xl font-bold">Queue Status</h1>

    @if($activeQueue)
        <flux:card class="border-2 border-blue-200 dark:border-blue-800">
            <flux:card.content>
                <div class="text-center space-y-6">
                    <!-- Queue Number Display -->
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-6 text-white">
                        <p class="text-lg font-medium mb-2">Your Queue Number</p>
                        <p class="text-4xl font-bold">{{ $activeQueue->formatted_number }}</p>
                        <p class="text-blue-100 mt-2">{{ $consultationType->name }}</p>
                    </div>

                    <!-- Queue Status Information -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-900 rounded-lg">
                            <flux:icon name="clock" class="w-8 h-8 mx-auto mb-2 text-blue-600" />
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">Estimated Time</p>
                            <p class="font-medium">{{ $estimatedTime->format('h:i A') }}</p>
                        </div>
                        
                        <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-900 rounded-lg">
                            <flux:icon name="users" class="w-8 h-8 mx-auto mb-2 text-orange-600" />
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">Ahead of You</p>
                            <p class="font-medium">{{ $patientsAhead }} patients</p>
                        </div>
                        
                        <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-900 rounded-lg">
                            <flux:icon name="hashtag" class="w-8 h-8 mx-auto mb-2 text-green-600" />
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">Your Position</p>
                            <p class="font-medium">#{{ $this->getQueuePosition() }}</p>
                        </div>
                    </div>

                    <!-- Current Serving -->
                    @if($currentServing)
                        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                            <p class="text-sm text-green-800 dark:text-green-200 font-medium">
                                Now Serving: {{ $currentServing->formatted_number }}
                            </p>
                        </div>
                    @endif

                    <!-- Status Alert -->
                    <div class="bg-{{ $statusColor }}-50 dark:bg-{{ $statusColor }}-900/20 border border-{{ $statusColor }}-200 dark:border-{{ $statusColor }}-800 rounded-lg p-4">
                        @if($patientsAhead <= 2)
                            <div class="flex items-center gap-2 text-red-800 dark:text-red-200">
                                <flux:icon name="exclamation-triangle" class="w-5 h-5" />
                                <span class="font-medium">Get Ready!</span>
                            </div>
                            <p class="text-sm text-red-700 dark:text-red-300 mt-1">
                                Your turn is coming up soon. Please wait in the designated area.
                            </p>
                        @elseif($patientsAhead <= 5)
                            <div class="flex items-center gap-2 text-yellow-800 dark:text-yellow-200">
                                <flux:icon name="clock" class="w-5 h-5" />
                                <span class="font-medium">Getting Close</span>
                            </div>
                            <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                                You're in the next group. Stay nearby and listen for your number.
                            </p>
                        @else
                            <div class="flex items-center gap-2 text-blue-800 dark:text-blue-200">
                                <flux:icon name="information-circle" class="w-5 h-5" />
                                <span class="font-medium">Waiting</span>
                            </div>
                            <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                                Please wait comfortably. We'll send you SMS updates when you're getting closer.
                            </p>
                        @endif
                    </div>

                    <!-- SMS Notification Status -->
                    <div class="flex items-center justify-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                        <flux:icon name="bell" class="w-4 h-4" />
                        <span>SMS alerts enabled</span>
                    </div>
                </div>
            </flux:card.content>
        </flux:card>
    @else
        <flux:card>
            <flux:card.content class="text-center py-8">
                <flux:icon name="queue" class="w-12 h-12 mx-auto text-zinc-400 mb-4" />
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
            </flux:card.content>
        </flux:card>
    @endif

    <!-- Auto-refresh notice -->
    <flux:callout type="info">
        This page automatically refreshes to show the latest queue status.
    </flux:callout>
</div>
