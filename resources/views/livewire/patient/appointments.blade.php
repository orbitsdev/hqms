<div class="space-y-6">
    <h1 class="text-2xl font-bold">My Appointments</h1>

    <!-- Filter Tabs -->
    <flux:button.group>
        <flux:button 
            wire:click="$set('filter', 'upcoming')"
            variant="{{ $filter === 'upcoming' ? 'primary' : 'outline' }}"
        >
            Upcoming
        </flux:button>
        <flux:button 
            wire:click="$set('filter', 'past')"
            variant="{{ $filter === 'past' ? 'primary' : 'outline' }}"
        >
            Past
        </flux:button>
        <flux:button 
            wire:click="$set('filter', 'all')"
            variant="{{ $filter === 'all' ? 'primary' : 'outline' }}"
        >
            All
        </flux:button>
    </flux:button.group>

    <!-- Search -->
    <flux:field>
        <flux:input 
            type="search" 
            wire:model.live="search"
            placeholder="Search appointments..."
            icon="magnifying-glass"
        />
    </flux:field>

    <!-- Appointments List -->
    @if($appointments->count() > 0)
        <div class="space-y-4">
            @foreach($appointments as $appointment)
                <div class="rounded-lg border border-zinc-200/70 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <h3 class="font-medium">{{ $appointment->consultationType->name }}</h3>
                                    <flux:badge variant="{{ match($appointment->status) {
                                        'pending' => 'warning',
                                        'approved' => 'success',
                                        'completed' => 'primary',
                                        'cancelled' => 'danger',
                                        'no_show' => 'danger',
                                        default => 'neutral'
                                    } }">
                                        {{ str_replace('_', ' ', ucfirst($appointment->status)) }}
                                    </flux:badge>
                                </div>
                                
                                <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-2">
                                    {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y \a\t h:i A') }}
                                </p>
                                
                                <p class="text-sm text-zinc-700 dark:text-zinc-300 line-clamp-2">
                                    {{ $appointment->chief_complaints }}
                                </p>
                                
                                @if($appointment->queue)
                                    <div class="mt-2">
                                        <span class="text-sm font-medium">Queue: {{ $appointment->queue->formatted_number }}</span>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="flex gap-2 ml-4">
                                <flux:button 
                                    href="{{ route('patient.appointments.show', $appointment->id) }}" 
                                    variant="outline" 
                                    size="sm"
                                    wire:navigate
                                >
                                    View
                                </flux:button>
                                
                                @if(in_array($appointment->status, ['pending', 'approved']))
                                    <flux:button 
                                        wire:click="cancelAppointment({{ $appointment->id }})"
                                        variant="danger" 
                                        size="sm"
                                    >
                                        Cancel
                                    </flux:button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        {{ $appointments->links() }}
    @else
        <div class="rounded-lg border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="px-4 py-8 text-center">
                <img
                    src="{{ asset('images/undraw_booked_bb22.svg') }}"
                    alt="Appointments"
                    class="mx-auto mb-4 h-28 w-auto opacity-80"
                />
                <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">No appointments found</h3>
                <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                    @if($search) 
                        Try adjusting your search criteria
                    @else
                        @if($filter === 'upcoming') 
                            You have no upcoming appointments
                        @elseif($filter === 'past') 
                            You have no past appointments
                        @else 
                            Book your first appointment to get started
                        @endif
                    @endif
                </p>
                @if(!$search && $filter !== 'all')
                    <flux:button href="{{ route('patient.appointments.book') }}" variant="primary" wire:navigate>
                        Book Appointment
                    </flux:button>
                @endif
            </div>
        </div>
    @endif
</div>
