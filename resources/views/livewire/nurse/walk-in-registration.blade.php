<section class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="space-y-1">
            <flux:heading size="xl" level="1">{{ __('Register Walk-in Patient') }}</flux:heading>
            <flux:text variant="subtle" class="text-sm">
                {{ __('Follow the steps to register a walk-in patient for today.') }}
            </flux:text>
        </div>

        <flux:button href="{{ route('nurse.appointments') }}" wire:navigate variant="ghost" icon="arrow-left">
            {{ __('Back') }}
        </flux:button>
    </div>

    {{-- Stepper Progress --}}
    @php
        $progressClass = [
            1 => 'w-0',
            2 => 'w-1/3',
            3 => 'w-2/3',
            4 => 'w-full',
        ][$currentStep] ?? 'w-0';

        $stepLabels = [
            1 => __('Consultation'),
            2 => __('Patient Info'),
            3 => __('Complaints'),
            4 => __('Review'),
        ];
    @endphp

    <div class="relative mb-2">
        <div class="absolute left-0 right-0 top-4 h-px bg-zinc-200 dark:bg-zinc-800"></div>
        <div class="absolute left-0 top-4 h-px bg-zinc-900 dark:bg-zinc-100 transition-all duration-300 {{ $progressClass }}"></div>

        <div class="grid grid-cols-4 gap-4 text-center">
            @foreach ($stepLabels as $step => $label)
                @php
                    $isComplete = $currentStep >= $step;
                    $canNavigate = $step <= $maxStep;
                    $stateClasses = $isComplete
                        ? 'border-zinc-900 bg-zinc-900 text-white dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900'
                        : 'border-zinc-200 bg-white text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400';
                @endphp

                <div class="relative flex flex-col items-center gap-3">
                    @if ($canNavigate)
                        <button type="button" wire:click="goToStep({{ $step }})"
                            class="h-9 w-9 rounded-full border text-sm font-semibold transition hover:opacity-80 {{ $stateClasses }}">
                            {{ $step }}
                        </button>
                    @else
                        <button type="button" disabled
                            class="h-9 w-9 rounded-full border text-sm font-semibold opacity-40 cursor-not-allowed {{ $stateClasses }}">
                            {{ $step }}
                        </button>
                    @endif

                    <span class="text-xs font-medium {{ $currentStep >= $step ? 'text-zinc-900 dark:text-zinc-100' : 'text-zinc-500 dark:text-zinc-400' }}">
                        {{ $label }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- STEP 1: Consultation Type --}}
    @if ($currentStep === 1)
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="sm" class="mb-2">{{ __('Select Consultation Type') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 mb-4">
                {{ __('Choose the type of consultation for this patient.') }}
            </flux:text>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($consultationTypes as $type)
                    @php
                        $isSelected = (int) $consultationTypeId === (int) $type->id;
                    @endphp

                    <button
                        type="button"
                        wire:click="selectConsultationType({{ $type->id }})"
                        @class([
                            'rounded-lg border p-4 text-left transition',
                            'border-zinc-900 bg-zinc-50 dark:border-zinc-100 dark:bg-zinc-800' => $isSelected,
                            'border-zinc-200 hover:border-zinc-400 dark:border-zinc-700 dark:hover:border-zinc-500' => !$isSelected,
                        ])
                    >
                        <div class="font-medium text-zinc-900 dark:text-white">{{ $type->name }}</div>
                        @if($type->description)
                            <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">{{ $type->description }}</div>
                        @endif
                    </button>
                @endforeach
            </div>

            @error('consultationTypeId')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div class="flex justify-end mt-6">
                <flux:button type="button" wire:click="nextStep" variant="primary" :disabled="!$consultationTypeId">
                    {{ __('Continue') }}
                </flux:button>
            </div>
        </div>
    @endif

    {{-- STEP 2: Patient Information --}}
    @if ($currentStep === 2)
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="sm" class="mb-2">{{ __('Patient Information') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 mb-4">
                {{ __('Enter the patient\'s personal details.') }}
            </flux:text>

            <div class="space-y-6">
                {{-- Name Fields --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <flux:field>
                        <flux:label>{{ __('First Name') }} *</flux:label>
                        <flux:input wire:model="patientFirstName" />
                        <flux:error name="patientFirstName" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Middle Name') }}</flux:label>
                        <flux:input wire:model="patientMiddleName" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Last Name') }} *</flux:label>
                        <flux:input wire:model="patientLastName" />
                        <flux:error name="patientLastName" />
                    </flux:field>
                </div>

                {{-- Demographics --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <flux:field>
                        <flux:label>{{ __('Date of Birth') }} *</flux:label>
                        <flux:input type="date" wire:model="patientDateOfBirth" max="{{ now()->format('Y-m-d') }}" />
                        <flux:error name="patientDateOfBirth" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Gender') }} *</flux:label>
                        <flux:select wire:model="patientGender" placeholder="{{ __('Select') }}">
                            <flux:select.option value="male">{{ __('Male') }}</flux:select.option>
                            <flux:select.option value="female">{{ __('Female') }}</flux:select.option>
                        </flux:select>
                        <flux:error name="patientGender" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Phone') }}</flux:label>
                        <flux:input wire:model="patientPhone" placeholder="09XX XXX XXXX" />
                    </flux:field>
                </div>

                {{-- Address --}}
                <div class="border-t border-zinc-200 pt-4 dark:border-zinc-700">
                    <flux:heading size="sm" class="mb-4">{{ __('Address (Optional)') }}</flux:heading>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <flux:field>
                            <flux:label>{{ __('Province') }}</flux:label>
                            <flux:input wire:model="patientProvince" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Municipality/City') }}</flux:label>
                            <flux:input wire:model="patientMunicipality" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Barangay') }}</flux:label>
                            <flux:input wire:model="patientBarangay" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Street') }}</flux:label>
                            <flux:input wire:model="patientStreet" />
                        </flux:field>
                    </div>
                </div>
            </div>

            <div class="flex justify-between mt-6">
                <flux:button type="button" wire:click="previousStep" variant="ghost">
                    {{ __('Previous') }}
                </flux:button>
                <flux:button type="button" wire:click="nextStep" variant="primary">
                    {{ __('Continue') }}
                </flux:button>
            </div>
        </div>
    @endif

    {{-- STEP 3: Chief Complaints --}}
    @if ($currentStep === 3)
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="sm" class="mb-2">{{ __('Chief Complaints') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 mb-4">
                {{ __('Describe the patient\'s main concerns or symptoms.') }}
            </flux:text>

            <flux:field>
                <flux:textarea
                    wire:model="chiefComplaints"
                    rows="5"
                    placeholder="{{ __('Describe the patient\'s main concerns, symptoms, or reason for visit...') }}"
                />
                <flux:error name="chiefComplaints" />
            </flux:field>

            <div class="flex justify-between mt-6">
                <flux:button type="button" wire:click="previousStep" variant="ghost">
                    {{ __('Previous') }}
                </flux:button>
                <flux:button type="button" wire:click="nextStep" variant="primary">
                    {{ __('Continue') }}
                </flux:button>
            </div>
        </div>
    @endif

    {{-- STEP 4: Review & Account Creation --}}
    @if ($currentStep === 4)
        <div class="space-y-6">
            {{-- Summary --}}
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="sm" class="mb-4">{{ __('Registration Summary') }}</flux:heading>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Consultation Type') }}</dt>
                            <dd class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $selectedConsultationType?->name ?? '-' }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Appointment Date') }}</dt>
                            <dd class="mt-1 text-sm text-zinc-900 dark:text-white">{{ now()->format('M d, Y') }} ({{ __('Today') }})</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Patient Name') }}</dt>
                            <dd class="mt-1 text-sm text-zinc-900 dark:text-white">
                                {{ $patientFirstName }} {{ $patientMiddleName }} {{ $patientLastName }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Date of Birth') }}</dt>
                            <dd class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $patientDateOfBirth }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Gender') }}</dt>
                            <dd class="mt-1 text-sm text-zinc-900 dark:text-white">{{ ucfirst($patientGender ?? '-') }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Phone') }}</dt>
                            <dd class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $patientPhone ?: __('Not provided') }}</dd>
                        </div>
                    </div>

                    <div class="border-t border-zinc-200 pt-4 dark:border-zinc-700">
                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Chief Complaints') }}</dt>
                        <dd class="mt-1 text-sm text-zinc-900 dark:text-white whitespace-pre-wrap">{{ $chiefComplaints }}</dd>
                    </div>
                </div>
            </div>

            {{-- Account Creation (Optional) --}}
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <flux:heading size="sm">{{ __('Create Patient Account') }}</flux:heading>
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                            {{ __('Optional: Create an account so patient can view their medical records online.') }}
                        </flux:text>
                    </div>
                    <flux:switch wire:model.live="createAccount" />
                </div>

                @if($createAccount)
                    <div class="space-y-4 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                        <flux:field>
                            <flux:label>{{ __('Email') }} *</flux:label>
                            <flux:input type="email" wire:model="accountEmail" placeholder="patient@example.com" />
                            <flux:error name="accountEmail" />
                        </flux:field>

                        <div class="flex items-center gap-3">
                            <flux:checkbox wire:model.live="generatePassword" />
                            <flux:label>{{ __('Generate random password') }}</flux:label>
                        </div>

                        @if(!$generatePassword)
                            <flux:field>
                                <flux:label>{{ __('Password') }} *</flux:label>
                                <flux:input type="password" wire:model="accountPassword" placeholder="{{ __('Minimum 8 characters') }}" />
                                <flux:error name="accountPassword" />
                            </flux:field>
                        @endif

                        <flux:callout variant="warning" icon="information-circle">
                            <flux:text class="text-sm">
                                {{ __('After registration, provide the patient with their login credentials. The password will be shown once after successful registration.') }}
                            </flux:text>
                        </flux:callout>
                    </div>
                @endif
            </div>

            {{-- Actions --}}
            <div class="flex justify-between">
                <flux:button type="button" wire:click="previousStep" variant="ghost">
                    {{ __('Previous') }}
                </flux:button>
                <flux:button type="button" wire:click="register" variant="primary">
                    {{ __('Register Patient') }}
                </flux:button>
            </div>
        </div>
    @endif
</section>
