<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold">Book Appointment</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Follow the steps to schedule your visit.</p>
        </div>
        <div class="flex items-center gap-3 self-start md:self-auto">
            @if($currentStep > 1)
                @if($maxStep >= 4)
                    <flux:button type="button" wire:click="goToStep(4)" variant="outline" size="sm">
                        Preview
                    </flux:button>
                @else
                    <flux:button type="button" variant="outline" size="sm" disabled>
                        Preview
                    </flux:button>
                @endif
            @endif
            <img
                src="{{ asset('images/undraw_schedule_ry1w.svg') }}"
                alt="Schedule appointment"
                class="h-20 w-auto opacity-80"
            />
        </div>
    </div>

    @php
        $progressClass = [
            1 => 'w-0',
            2 => 'w-1/3',
            3 => 'w-2/3',
            4 => 'w-full',
        ][$currentStep] ?? 'w-0';
        $stepLabels = [
            1 => 'Consultation Type',
            2 => 'Select Date',
            3 => 'Patient Details',
            4 => 'Chief Complaints',
        ];
    @endphp

    <div class="relative mb-10">
        <div class="absolute left-0 right-0 top-4 h-px bg-zinc-200 dark:bg-zinc-800"></div>
        <div class="absolute left-0 top-4 h-px bg-zinc-900 dark:bg-zinc-100 {{ $progressClass }}"></div>

        <div class="grid grid-cols-4 gap-4 text-center">
            @foreach($stepLabels as $step => $label)
                @php
                    $isComplete = $currentStep >= $step;
                    $canNavigate = $step <= $maxStep;
                    $stateClasses = $isComplete
                        ? 'border-zinc-900 bg-zinc-900 text-white hover:bg-zinc-900 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-100'
                        : 'border-zinc-200 bg-white text-zinc-500 hover:bg-white dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-900';
                    $navClasses = $canNavigate ? '' : 'opacity-40 pointer-events-none';
                @endphp
                <div class="relative flex flex-col items-center gap-3">
                    <flux:button
                        wire:click="goToStep({{ $step }})"
                        variant="ghost"
                        size="sm"
                        class="h-9 w-9 rounded-full border p-0 text-sm font-semibold {{ $stateClasses }} {{ $navClasses }}"
                    >
                        {{ $step }}
                    </flux:button>
                    <span class="text-xs font-medium {{ $currentStep >= $step ? 'text-zinc-900 dark:text-zinc-100' : 'text-zinc-500 dark:text-zinc-400' }}">
                        {{ $label }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Step 1: Consultation Type --}}
    @if($currentStep === 1)
        <div class="rounded-lg border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="border-b border-zinc-200/70 px-4 py-3 dark:border-zinc-800">
                <flux:heading>Select Consultation Type</flux:heading>
                <flux:text>Choose the type of consultation you need.</flux:text>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($consultationTypes as $type)
                        <flux:button
                            wire:key="consultation-type-{{ $type->id }}"
                            wire:click="selectConsultationType({{ $type->id }})"
                            variant="outline"
                            class="h-24 flex flex-col items-center justify-center space-y-2"
                        >
                            <span aria-hidden="true" class="h-8 w-8 rounded-full bg-zinc-100 dark:bg-zinc-800"></span>
                            <div class="text-center">
                                <div class="font-medium">{{ $type->name }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $type->description }}</div>
                            </div>
                        </flux:button>
                    @endforeach
                </div>
                <div class="mt-4 flex justify-end">
                    <flux:button
                        type="button"
                        wire:click="nextStep"
                        variant="primary"
                        :disabled="!$consultationTypeId"
                    >
                        Continue
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- Step 2: Date Selection --}}
    @if($currentStep === 2)
        <div class="rounded-lg border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="border-b border-zinc-200/70 px-4 py-3 dark:border-zinc-800">
                <flux:heading>Select Appointment Date</flux:heading>
                <flux:text>Choose an available date for your appointment.</flux:text>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($availableDates as $date)
                        @if($date['available'])
                            <flux:button
                                wire:key="appointment-date-{{ $date['date'] }}"
                                wire:click="selectDate('{{ $date['date'] }}')"
                                variant="{{ $appointmentDate === $date['date'] ? 'primary' : 'outline' }}"
                            >
                                <div class="text-center">
                                    <div class="font-medium">{{ $date['day_name'] }}</div>
                                    <div class="text-lg font-bold">{{ $date['formatted'] }}</div>
                                    <div class="text-xs text-zinc-600 dark:text-zinc-300">
                                        {{ $date['slots_left'] }} slots left
                                    </div>
                                </div>
                            </flux:button>
                        @else
                            <flux:button
                                wire:key="appointment-date-{{ $date['date'] }}"
                                variant="outline"
                                class="opacity-50 cursor-not-allowed"
                                disabled
                            >
                                <div class="text-center">
                                    <div class="font-medium">{{ $date['day_name'] }}</div>
                                    <div class="text-lg font-bold">{{ $date['formatted'] }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">Fully booked</div>
                                </div>
                            </flux:button>
                        @endif
                    @endforeach
                </div>
                <div class="mt-4 flex flex-wrap gap-3">
                    <flux:button type="button" wire:click="previousStep" variant="outline">
                        Previous
                    </flux:button>
                    <flux:button
                        type="button"
                        wire:click="nextStep"
                        variant="primary"
                        :disabled="!$appointmentDate"
                    >
                        Next
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- Step 3: Patient Details --}}
    @if($currentStep === 3)
        <div class="rounded-lg border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="border-b border-zinc-200/70 px-4 py-3 dark:border-zinc-800">
                <flux:heading>Patient Details</flux:heading>
                <flux:text>Is this appointment for yourself or someone else?</flux:text>
            </div>
            <div class="p-4">
                <form wire:submit.prevent="nextStep" class="space-y-4">
                    <flux:field label="Who is this appointment for?">
                        <div class="space-y-2">
                            <flux:radio wire:model.live="patientType" value="self" label="Myself" />
                            <flux:radio wire:model.live="patientType" value="dependent" label="Someone else (child/dependent)" />
                        </div>
                    </flux:field>

                    @if($patientType === 'dependent')
                        <div class="space-y-4 border-t pt-4">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <flux:field label="First Name">
                                    <flux:input type="text" wire:model.live="patientFirstName" placeholder="First name" required />
                                </flux:field>

                                <flux:field label="Middle Name">
                                    <flux:input type="text" wire:model.live="patientMiddleName" placeholder="Middle name (optional)" />
                                </flux:field>

                                <flux:field label="Last Name">
                                    <flux:input type="text" wire:model.live="patientLastName" placeholder="Last name" required />
                                </flux:field>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <flux:field label="Birth Date">
                                    <flux:input type="date" wire:model.live="patientDateOfBirth" required />
                                </flux:field>

                                <flux:field label="Gender">
                                    <flux:select wire:model.live="patientGender" required>
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                    </flux:select>
                                </flux:field>
                            </div>
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
            </div>
        </div>
    @endif

    {{-- Step 4: Chief Complaints --}}
    @if($currentStep === 4)
        <div class="rounded-lg border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="border-b border-zinc-200/70 px-4 py-3 dark:border-zinc-800">
                <flux:heading>Chief Complaints</flux:heading>
                <flux:text>Please describe your symptoms or reason for visit.</flux:text>
            </div>
            <div class="p-4">
                <form wire:submit.prevent="submitAppointment" class="space-y-6">
                    <flux:field label="Describe your symptoms or reason for this visit">
                        <flux:textarea
                            wire:model.live="chiefComplaints"
                            placeholder="Please describe what brings you in today..."
                            rows="6"
                            required
                        />
                        <flux:text class="text-sm text-zinc-500">Minimum 10 characters. Be as detailed as possible.</flux:text>
                    </flux:field>

                    <div class="border-t pt-6">
                        <flux:heading>Review Appointment Details</flux:heading>

                        <div class="space-y-3 mt-4">
                            <div class="flex justify-between">
                                <span class="font-medium">Consultation Type:</span>
                                <span>{{ $consultationType?->name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium">Date:</span>
                                <span>{{ $selectedDate['formatted'] ?? '' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium">Patient:</span>
                                <span>
                                    @if($patientType === 'self')
                                        Yourself
                                    @else
                                        {{ trim($patientFirstName . ' ' . ($patientMiddleName ? $patientMiddleName . ' ' : '') . $patientLastName) }}
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
            </div>
        </div>
    @endif
</div>
