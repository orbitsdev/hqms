<div class="space-y-6">
    <h1 class="text-2xl font-bold">Appointment Details</h1>

    @if($appointment)
        <flux:card>
            <flux:card.content>
                <div class="space-y-6">
                    <!-- Status Badge -->
                    <div class="flex items-center justify-between">
                        <flux:badge variant="{{ $statusColor }}" size="lg">
                            {{ str_replace('_', ' ', ucfirst($appointment->status)) }}
                        </flux:badge>
                        
                        @if($canCancel)
                            <flux:button 
                                wire:click="cancelAppointment"
                                variant="danger"
                                size="sm"
                            >
                                Cancel Appointment
                            </flux:button>
                        @endif
                    </div>

                    <!-- Basic Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-2">Consultation Type</h3>
                            <p class="font-medium">{{ $appointment->consultationType->name }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-2">Date & Time</h3>
                            <p class="font-medium">
                                {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y \a\t h:i A') }}
                            </p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-2">Queue Number</h3>
                            <p class="font-medium">
                                @if($appointment->queue)
                                    {{ $appointment->queue->formatted_number }}
                                @else
                                    Not assigned
                                @endif
                            </p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-2">Patient</h3>
                            <p class="font-medium">
                                {{ $appointment->user->personalInformation?->full_name ?? $appointment->user->email }}
                            </p>
                        </div>
                    </div>

                    <!-- Chief Complaints -->
                    <div>
                        <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-2">Chief Complaints</h3>
                        <div class="bg-zinc-50 dark:bg-zinc-900 p-4 rounded-lg">
                            <p class="whitespace-pre-wrap">{{ $appointment->chief_complaints }}</p>
                        </div>
                    </div>

                    <!-- Status Information -->
                    <div>
                        <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-2">Status Information</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span>Created:</span>
                                <span>{{ \Carbon\Carbon::parse($appointment->created_at)->format('M d, Y h:i A') }}</span>
                            </div>
                            @if($appointment->updated_at && $appointment->updated_at != $appointment->created_at)
                                <div class="flex justify-between">
                                    <span>Last Updated:</span>
                                    <span>{{ \Carbon\Carbon::parse($appointment->updated_at)->format('M d, Y h:i A') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </flux:card.content>
        </flux:card>

        <!-- Action Buttons -->
        <div class="flex gap-4">
            <flux:button href="{{ route('patient.appointments') }}" variant="outline" wire:navigate>
                Back to Appointments
            </flux:button>
            
            @if($appointment->status === 'approved' && $appointment->queue)
                <flux:button href="{{ route('patient.queue') }}" variant="primary" wire:navigate>
                    View Queue Status
                </flux:button>
            @endif
        </div>
    @else
        <flux:card>
            <flux:card.content class="text-center py-8">
                <flux:icon name="exclamation-triangle" class="w-12 h-12 mx-auto text-zinc-400 mb-4" />
                <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">Appointment Not Found</h3>
                <p class="text-zinc-600 dark:text-zinc-400 mb-4">The appointment you're looking for doesn't exist or you don't have access to it.</p>
                <flux:button href="{{ route('patient.appointments') }}" variant="primary" wire:navigate>
                    Back to Appointments
                </flux:button>
            </flux:card.content>
        </flux:card>
    @endif
</div>
