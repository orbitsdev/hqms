<div class="space-y-6">
    <h1 class="text-2xl font-bold">Medical Records</h1>

    <!-- Search and Filter -->
    <div class="flex flex-col sm:flex-row gap-4">
        <flux:field class="flex-1">
            <flux:input 
                type="search" 
                wire:model.live="search"
                placeholder="Search medical records..."
                icon="magnifying-glass"
            />
        </flux:field>
        
        <flux:field>
            <flux:input 
                type="date" 
                wire:model.live="dateFilter"
                placeholder="Filter by date"
            />
        </flux:field>
    </div>

    <!-- Records List -->
    @if($records->count() > 0)
        <div class="space-y-4">
            @foreach($records as $record)
                <flux:card>
                    <flux:card.content>
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <h3 class="font-medium">{{ $record->consultationType->name }} Consultation</h3>
                                    <flux:badge variant="primary">
                                        {{ \Carbon\Carbon::parse($record->created_at)->format('M d, Y') }}
                                    </flux:badge>
                                </div>
                                
                                @if($record->diagnosis)
                                    <div class="mb-2">
                                        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Diagnosis:</p>
                                        <p class="text-sm line-clamp-2">{{ $record->diagnosis }}</p>
                                    </div>
                                @endif
                                
                                @if($record->effective_chief_complaints)
                                    <div class="mb-2">
                                        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Chief Complaints:</p>
                                        <p class="text-sm line-clamp-2">{{ $record->effective_chief_complaints }}</p>
                                    </div>
                                @endif

                                @if($record->plan)
                                    <div class="mb-2">
                                        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Treatment Plan:</p>
                                        <p class="text-sm line-clamp-2">{{ $record->plan }}</p>
                                    </div>
                                @endif
                                
                                @if($record->prescriptions && $record->prescriptions->count() > 0)
                                    <div class="flex items-center gap-1 text-sm text-zinc-600 dark:text-zinc-400">
                                        <flux:icon name="prescription-bottle" class="w-4 h-4" />
                                        <span>{{ $record->prescriptions->count() }} prescription(s)</span>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="flex gap-2 ml-4">
                                <flux:button 
                                    href="{{ route('patient.records.show', $record->id) }}" 
                                    variant="outline" 
                                    size="sm"
                                    wire:navigate
                                >
                                    View Details
                                </flux:button>
                            </div>
                        </div>
                    </flux:card.content>
                </flux:card>
            @endforeach
        </div>

        <!-- Pagination -->
        {{ $records->links() }}
    @else
        <flux:card>
            <flux:card.content class="text-center py-8">
                <flux:icon name="document-text" class="w-12 h-12 mx-auto text-zinc-400 mb-4" />
                <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">No medical records found</h3>
                <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                    @if($search || $dateFilter) 
                        Try adjusting your search criteria or date filter
                    @else
                        You don't have any medical records yet. After your first consultation, your records will appear here.
                    @endif
                </p>
                @if(!$search && !$dateFilter)
                    <flux:button href="{{ route('patient.appointments.book') }}" variant="primary" wire:navigate>
                        Book Appointment
                    </flux:button>
                @endif
            </flux:card.content>
        </flux:card>
    @endif
</div>
