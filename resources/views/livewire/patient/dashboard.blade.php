<div class="space-y-6">
    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-6 text-white">
        <h1 class="text-2xl font-bold mb-2">Welcome back, {{ $userName }}!</h1>
        <p class="text-blue-100">Your health is our priority</p>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <flux:card>
            <flux:button href="{{ route('patient.appointments.book') }}" class="w-full h-full flex flex-col items-center justify-center py-4" variant="ghost" wire:navigate>
                <flux:icon name="plus-circle" class="w-8 h-8 mb-2 text-blue-600" />
                <span class="text-sm font-medium">Book Visit</span>
            </flux:button>
        </flux:card>

        <flux:card>
            <flux:button href="{{ route('patient.appointments') }}" class="w-full h-full flex flex-col items-center justify-center py-4" variant="ghost" wire:navigate>
                <flux:icon name="calendar" class="w-8 h-8 mb-2 text-green-600" />
                <span class="text-sm font-medium">My Visits</span>
            </flux:button>
        </flux:card>

        <flux:card>
            <flux:button href="{{ route('patient.queue') }}" class="w-full h-full flex flex-col items-center justify-center py-4" variant="ghost" wire:navigate>
                <flux:icon name="queue" class="w-8 h-8 mb-2 text-orange-600" />
                <span class="text-sm font-medium">Queue Status</span>
            </flux:button>
        </flux:card>

        <flux:card>
            <flux:button href="{{ route('patient.records') }}" class="w-full h-full flex flex-col items-center justify-center py-4" variant="ghost" wire:navigate>
                <flux:icon name="document-text" class="w-8 h-8 mb-2 text-purple-600" />
                <span class="text-sm font-medium">Records</span>
            </flux:button>
        </flux:card>
    </div>

    <!-- Upcoming Appointment -->
    @if($upcomingAppointment)
        <flux:card>
            <flux:card.header>
                <flux:heading>Upcoming Appointment</flux:heading>
            </flux:card.header>
            <flux:card.content>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium">{{ $upcomingAppointment->consultationType->name }}</p>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                {{ \Carbon\Carbon::parse($upcomingAppointment->appointment_date)->format('M d, Y \a\t h:i A') }}
                            </p>
                        </div>
                        <flux:badge variant="{{ $upcomingAppointment->status === 'approved' ? 'success' : 'warning' }}">
                            {{ str_replace('_', ' ', ucfirst($upcomingAppointment->status)) }}
                        </flux:badge>
                    </div>
                    @if($upcomingAppointment->queue)
                        <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg">
                            <p class="text-sm font-medium text-blue-900 dark:text-blue-100">
                                Queue Number: {{ $upcomingAppointment->queue->formatted_number }}
                            </p>
                        </div>
                    @endif
                </div>
            </flux:card.content>
        </flux:card>
    @endif

    <!-- Active Queue -->
    @if($activeQueue)
        <flux:card class="border-orange-200 dark:border-orange-800">
            <flux:card.header>
                <flux:heading class="flex items-center gap-2">
                    <flux:icon name="queue" class="w-5 h-5 text-orange-600" />
                    Active Queue
                </flux:heading>
            </flux:card.header>
            <flux:card.content>
                <div class="space-y-3">
                    <div class="text-center">
                        <p class="text-3xl font-bold text-orange-600">{{ $activeQueue->formatted_number }}</p>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $activeQueue->consultationType->name }}</p>
                    </div>
                    <flux:button href="{{ route('patient.queue') }}" variant="primary" class="w-full" wire:navigate>
                        View Queue Status
                    </flux:button>
                </div>
            </flux:card.content>
        </flux:card>
    @endif

    <!-- Empty State -->
    @if(!$upcomingAppointment && !$activeQueue)
        <flux:card>
            <flux:card.content class="text-center py-8">
                <flux:icon name="calendar-plus" class="w-12 h-12 mx-auto text-zinc-400 mb-4" />
                <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">No upcoming appointments</h3>
                <p class="text-zinc-600 dark:text-zinc-400 mb-4">Book your first appointment to get started</p>
                <flux:button href="{{ route('patient.appointments.book') }}" variant="primary" wire:navigate>
                    Book Appointment
                </flux:button>
            </flux:card.content>
        </flux:card>
    @endif
</div>
