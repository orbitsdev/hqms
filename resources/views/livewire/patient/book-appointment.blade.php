<div class="space-y-6">
    <h1 class="text-2xl font-bold">Book Appointment</h1>

    <!-- Progress Indicator -->
    <div class="flex items-center justify-between mb-8">
        @for($i = 1; $i <= 4; $i++)
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium
                    {{ $currentStep >= $i 
                        ? 'bg-blue-600 text-white' 
                        : 'bg-zinc-200 dark:bg-zinc-700 text-zinc-500 dark:text-zinc-400' }}">
                    {{ $i }}
                </div>
                @if($i < 4)
                    <div class="w-8 h-1 mx-2
                        {{ $currentStep > $i 
                            ? 'bg-blue-600' 
                            : 'bg-zinc-200 dark:bg-zinc-700' }}">
                    </div>
                @endif
            </div>
        @endfor
    </div>

    <!-- Step Labels -->
    <div class="grid grid-cols-4 gap-4 mb-8 text-center">
        <div class="text-sm {{ $currentStep >= 1 ? 'text-blue-600 font-medium' : 'text-zinc-500' }}">
            Consultation Type
        </div>
        <div class="text-sm {{ $currentStep >= 2 ? 'text-blue-600 font-medium' : 'text-zinc-500' }}">
            Select Date
        </div>
        <div class="text-sm {{ $currentStep >= 3 ? 'text-blue-600 font-medium' : 'text-zinc-500' }}">
            Patient Details
        </div>
        <div class="text-sm {{ $currentStep >= 4 ? 'text-blue-600 font-medium' : 'text-zinc-500' }}">
            Chief Complaints
        </div>
    </div>

    <!-- Step 1: Consultation Type -->
    @if($currentStep === 1)
        <flux:card>
            <flux:card.header>
                <flux:heading>Select Consultation Type</flux:heading>
                <flux:text>Choose the type of consultation you need</flux:text>
            </flux:card.header>
            <flux:card.content>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($consultationTypes as $type)
                        <flux:button 
                            wire:click="selectConsultationType({{ $type->id }})"
                            variant="outline"
                            class="h-24 flex flex-col items-center justify-center space-y-2"
                        >
                            <flux:icon name="{{ match($type->code) {
                                'ob' => 'heart',
                                'pedia' => 'baby',
                                'general' => 'stethoscope',
                                default => 'medical'
                            } }}" class="w-8 h-8" />
                            <div class="text-center">
                                <div class="font-medium">{{ $type->name }}</div>
                                <div class="text-xs text-zinc-500">{{ $type->description }}</div>
                            </div>
                        </flux:button>
                    @endforeach
                </div>
            </flux:card.content>
        </flux:card>
    @endif

    <!-- Step 2: Date Selection -->
    @if($currentStep === 2)
        <flux:card>
            <flux:card.header>
                <flux:heading>Select Appointment Date</flux:heading>
                <flux:text>Choose an available date for your appointment</flux:text>
            </flux:card.header>
            <flux:card.content>
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($availableDates as $date)
                        <flux:button 
                            wire:click="selectDate('{{ $date['date'] }}')"
                            variant="{{ $appointmentDate === $date['date'] ? 'primary' : 'outline' }}"
                            class="{{ !$date['available'] ? 'opacity-50 cursor-not-allowed' : '' }}"
                            {{ !$date['available'] ? 'disabled' : '' }}
                        >
                            <div class="text-center">
                                <div class="font-medium">{{ $date['day_name'] }}</div>
                                <div class="text-lg font-bold">{{ $date['formatted'] }}</div>
                                @if($date['available'])
                                    <div class="text-xs text-green-600">
                                        {{ $date['max'] - $date['capacity'] }} slots left
                                    </div>
                                @else
                                    <div class="text-xs text-red-600">Fully Booked</div>
                                @endif
                            </div>
                        </flux:button>
                    @endforeach
                </div>
            </flux:card.content>
        </flux:card>
    @endif

    <!-- Step 3: Patient Details -->
    @if($currentStep === 3)
        <flux:card>
            <flux:card.header>
                <flux:heading>Patient Details</flux:heading>
                <flux:text>Is this appointment for yourself or someone else?</flux:text>
            </flux:card.header>
            <flux:card.content>
                <form wire:submit.prevent="nextStep" class="space-y-4">
                    <flux:field label="Who is this appointment for?">
                        <div class="space-y-2">
                            <flux:radio 
                                wire:model.live="patientType" 
                                value="self"
                                label="Myself"
                            />
                            <flux:radio 
                                wire:model.live="patientType" 
                                value="dependent"
                                label="Someone else (child/dependent)"
                            />
                        </div>
                    </flux:field>

                    @if($patientType === 'dependent')
                        <div class="space-y-4 border-t pt-4">
                            <flux:field label="Patient Name">
                                <flux:input 
                                    type="text" 
                                    wire:model.live="dependentName"
                                    required
                                />
                            </flux:field>
                            
                            <flux:field label="Birth Date">
                                <flux:input 
                                    type="date" 
                                    wire:model.live="dependentBirthDate"
                                    required
                                />
                            </flux:field>
                            
                            <flux:field label="Gender">
                                <flux:select wire:model.live="dependentGender" required>
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </flux:select>
                            </flux:field>
                        </div>
                    @endif

                    <div class="flex gap-4">
                        <flux:button type="button" wire:click="previousStep" variant="outline">
                            Previous
                        </flux:button>
                        <flux:button type="submit" variant="primary">
                            Next
                        </flux:button>
                    </div>
                </form>
            </flux:card.content>
        </flux:card>
    @endif

    <!-- Step 4: Chief Complaints -->
    @if($currentStep === 4)
        <flux:card>
            <flux:card.header>
                <flux:heading>Chief Complaints</flux:heading>
                <flux:text>Please describe your symptoms or reason for visit</flux:text>
            </flux:card.header>
            <flux:card.content>
                <form wire:submit.prevent="submitAppointment" class="space-y-6">
                    <flux:field label="Describe your symptoms or reason for this visit">
                        <flux:textarea 
                            wire:model.live="chiefComplaints"
                            placeholder="Please describe what brings you in today..."
                            rows="6"
                            required
                        />
                        <flux:text class="text-sm text-zinc-500">
                            Minimum 10 characters. Be as detailed as possible.
                        </flux:text>
                    </flux:field>

                    <!-- Review Section -->
                    <div class="border-t pt-6">
                        <flux:heading>Review Appointment Details</flux:heading>
                        
                        <div class="space-y-3 mt-4">
                            <div class="flex justify-between">
                                <span class="font-medium">Consultation Type:</span>
                                <span>{{ $consultationType->name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium">Date:</span>
                                <span>{{ $selectedDate['formatted'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium">Patient:</span>
                                <span>
                                    @if($patientType === 'self')
                                        Yourself
                                    @else
                                        {{ $dependentName }}
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <flux:button type="button" wire:click="previousStep" variant="outline">
                            Previous
                        </flux:button>
                        <flux:button type="submit" variant="primary">
                            Submit Appointment
                        </flux:button>
                    </div>
                </form>
            </flux:card.content>
        </flux:card>
    @endif
</div>
