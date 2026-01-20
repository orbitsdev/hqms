<div class="space-y-6">
    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-zinc-900 to-zinc-800 rounded-lg p-6 text-white dark:from-zinc-100 dark:to-zinc-200 dark:text-zinc-900">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">Welcome back, {{ $userName }}!</h1>
                <p class="text-zinc-200 dark:text-zinc-600">Your health is our priority</p>
            </div>
            <img
                src="{{ asset('images/undraw_medical-care_7m9g.svg') }}"
                alt="Medical care"
                class="h-20 w-auto opacity-90 dark:opacity-80"
            />
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="rounded-lg border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <flux:button href="{{ route('patient.appointments.book') }}" class="w-full h-full flex flex-col items-center justify-center py-4" variant="ghost" wire:navigate>
                <span aria-hidden="true" class="mb-2 h-8 w-8 rounded-full bg-zinc-100 dark:bg-zinc-800"></span>
                <span class="text-sm font-medium">Book Visit</span>
            </flux:button>
        </div>

        <div class="rounded-lg border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <flux:button href="{{ route('patient.appointments') }}" class="w-full h-full flex flex-col items-center justify-center py-4" variant="ghost" wire:navigate>
                <span aria-hidden="true" class="mb-2 h-8 w-8 rounded-full bg-zinc-100 dark:bg-zinc-800"></span>
                <span class="text-sm font-medium">My Visits</span>
            </flux:button>
        </div>

        <div class="rounded-lg border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <flux:button href="{{ route('patient.queue') }}" class="w-full h-full flex flex-col items-center justify-center py-4" variant="ghost" wire:navigate>
                <span aria-hidden="true" class="mb-2 h-8 w-8 rounded-full bg-zinc-100 dark:bg-zinc-800"></span>
                <span class="text-sm font-medium">Queue Status</span>
            </flux:button>
        </div>

        <div class="rounded-lg border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <flux:button href="{{ route('patient.records') }}" class="w-full h-full flex flex-col items-center justify-center py-4" variant="ghost" wire:navigate>
                <span aria-hidden="true" class="mb-2 h-8 w-8 rounded-full bg-zinc-100 dark:bg-zinc-800"></span>
                <span class="text-sm font-medium">Records</span>
            </flux:button>
        </div>
    </div>

    <!-- Upcoming Appointment -->
    @if($upcomingAppointment)
        <div class="rounded-lg border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="border-b border-zinc-200/70 px-4 py-3 dark:border-zinc-800">
                <flux:heading>Upcoming Appointment</flux:heading>
            </div>
            <div class="p-4">
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
                        <div class="bg-zinc-100 dark:bg-zinc-800 p-3 rounded-lg">
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                Queue Number: {{ $upcomingAppointment->queue->formatted_number }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Active Queue -->
    @if($activeQueue)
        <div class="rounded-lg border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="border-b border-zinc-200/70 px-4 py-3 dark:border-zinc-800">
                <flux:heading class="flex items-center gap-2">
                    <span aria-hidden="true" class="h-5 w-5 rounded-full bg-zinc-100 dark:bg-zinc-800"></span>
                    Active Queue
                </flux:heading>
            </div>
            <div class="p-4">
                <div class="space-y-3">
                    <div class="text-center">
                        <p class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">{{ $activeQueue->formatted_number }}</p>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $activeQueue->consultationType->name }}</p>
                    </div>
                    <flux:button href="{{ route('patient.queue') }}" variant="primary" class="w-full" wire:navigate>
                        View Queue Status
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    <!-- Empty State -->
    @if(!$upcomingAppointment && !$activeQueue)
        <div class="rounded-lg border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="px-4 py-8 text-center">
                <img
                    src="{{ asset('images/undraw_online-calendar_zaoc.svg') }}"
                    alt="Calendar"
                    class="mx-auto mb-4 h-28 w-auto opacity-80"
                />
                <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">No upcoming appointments</h3>
                <p class="text-zinc-600 dark:text-zinc-400 mb-4">Book your first appointment to get started</p>
                <flux:button href="{{ route('patient.appointments.book') }}" variant="primary" wire:navigate>
                    Book Appointment
                </flux:button>
            </div>
        </div>
    @endif
</div>
