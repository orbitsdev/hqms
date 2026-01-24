<section class="mx-auto max-w-4xl space-y-6">
    <div class="flex items-center justify-between">
        <div class="space-y-1">
            <flux:heading size="xl" level="1">{{ __('Walk-in Registration') }}</flux:heading>
            <flux:text variant="subtle" class="text-sm">
                {{ __('Register a new walk-in patient for today.') }}
            </flux:text>
        </div>
        <flux:button href="{{ route('nurse.queue') }}" wire:navigate variant="ghost" icon="arrow-left">
            {{ __('Back to Queue') }}
        </flux:button>
    </div>

    <!-- Progress Steps -->
    <nav aria-label="Progress">
        <ol role="list" class="flex items-center">
            @foreach([1 => 'Consultation Type', 2 => 'Patient Info', 3 => 'Confirmation'] as $step => $label)
                <li class="@if($step < 3) flex-1 @endif relative @if($step > 1) pl-8 sm:pl-16 @endif">
                    @if($step > 1)
                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                            <div class="h-0.5 w-full @if($currentStep > $step - 1) bg-accent @else bg-zinc-200 dark:bg-zinc-700 @endif"></div>
                        </div>
                    @endif
                    <div class="relative flex items-center gap-3">
                        <span @class([
                            'flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-medium',
                            'bg-accent text-white' => $currentStep >= $step,
                            'bg-zinc-200 text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400' => $currentStep < $step,
                        ])>
                            @if($currentStep > $step)
                                <flux:icon name="check" class="h-4 w-4" />
                            @else
                                {{ $step }}
                            @endif
                        </span>
                        <span class="hidden text-sm font-medium sm:block @if($currentStep >= $step) text-zinc-900 dark:text-white @else text-zinc-500 dark:text-zinc-400 @endif">
                            {{ __($label) }}
                        </span>
                    </div>
                </li>
            @endforeach
        </ol>
    </nav>

    <div class="rounded-xl border border-zinc-200/70 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        @if($currentStep === 1)
            <!-- Step 1: Select Consultation Type -->
            <div class="space-y-4">
                <flux:heading size="lg" level="2">{{ __('Select Consultation Type') }}</flux:heading>
                <flux:text variant="subtle" class="text-sm">
                    {{ __('Choose the type of consultation for this patient.') }}
                </flux:text>

                <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($consultationTypes as $type)
                        @php
                            $shortName = $type->short_name ?? '?';
                            $bgColors = [
                                'O' => 'bg-pink-100 text-pink-700 dark:bg-pink-900/30 dark:text-pink-400 border-pink-300 dark:border-pink-700',
                                'P' => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-400 border-cyan-300 dark:border-cyan-700',
                                'G' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border-emerald-300 dark:border-emerald-700',
                            ];
                            $bgClass = $bgColors[$shortName] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600';
                        @endphp

                        <button
                            wire:click="selectConsultationType({{ $type->id }})"
                            type="button"
                            @class([
                                'relative flex flex-col items-center rounded-xl border-2 p-6 text-center transition-all hover:shadow-md',
                                'border-accent ring-2 ring-accent/20' => $consultationTypeId === $type->id,
                                'border-zinc-200 dark:border-zinc-700' => $consultationTypeId !== $type->id,
                            ])
                        >
                            <div class="flex h-14 w-14 items-center justify-center rounded-full {{ $bgClass }} border text-xl font-bold">
                                {{ $shortName }}
                            </div>
                            <h3 class="mt-3 text-base font-semibold text-zinc-900 dark:text-white">
                                {{ $type->name }}
                            </h3>
                            @if($type->description)
                                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ Str::limit($type->description, 60) }}
                                </p>
                            @endif
                            @if($consultationTypeId === $type->id)
                                <div class="absolute right-3 top-3">
                                    <flux:icon name="check-circle" class="h-6 w-6 text-accent" />
                                </div>
                            @endif
                        </button>
                    @endforeach
                </div>

                @error('consultationTypeId')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

        @elseif($currentStep === 2)
            <!-- Step 2: Patient Information -->
            <div class="space-y-6">
                <flux:heading size="lg" level="2">{{ __('Patient Information') }}</flux:heading>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>{{ __('First Name') }} *</flux:label>
                        <flux:input wire:model="patientFirstName" />
                        <flux:error name="patientFirstName" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Middle Name') }}</flux:label>
                        <flux:input wire:model="patientMiddleName" />
                        <flux:error name="patientMiddleName" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Last Name') }} *</flux:label>
                        <flux:input wire:model="patientLastName" />
                        <flux:error name="patientLastName" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Date of Birth') }} *</flux:label>
                        <flux:input type="date" wire:model="patientDateOfBirth" max="{{ now()->format('Y-m-d') }}" />
                        <flux:error name="patientDateOfBirth" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Gender') }} *</flux:label>
                        <flux:select wire:model="patientGender" placeholder="{{ __('Select gender') }}">
                            <flux:select.option value="male">{{ __('Male') }}</flux:select.option>
                            <flux:select.option value="female">{{ __('Female') }}</flux:select.option>
                        </flux:select>
                        <flux:error name="patientGender" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Phone Number') }}</flux:label>
                        <flux:input wire:model="patientPhone" placeholder="09XX XXX XXXX" />
                        <flux:error name="patientPhone" />
                    </flux:field>
                </div>

                <flux:separator />

                <flux:heading size="base" level="3">{{ __('Address') }}</flux:heading>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
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
                        <flux:label>{{ __('Street Address') }}</flux:label>
                        <flux:input wire:model="patientStreet" />
                    </flux:field>
                </div>

                <div class="flex justify-between pt-4">
                    <flux:button wire:click="previousStep" variant="ghost" icon="arrow-left">
                        {{ __('Previous') }}
                    </flux:button>
                    <flux:button wire:click="nextStep" variant="primary" icon-trailing="arrow-right">
                        {{ __('Continue') }}
                    </flux:button>
                </div>
            </div>

        @elseif($currentStep === 3)
            <!-- Step 3: Confirmation -->
            <div class="space-y-6">
                <flux:heading size="lg" level="2">{{ __('Confirm & Register') }}</flux:heading>

                <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800/50">
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Patient') }}</dt>
                            <dd class="text-base font-semibold text-zinc-900 dark:text-white">
                                {{ $patientFirstName }} {{ $patientMiddleName }} {{ $patientLastName }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Gender / DOB') }}</dt>
                            <dd class="text-base text-zinc-900 dark:text-white">
                                {{ ucfirst($patientGender ?? '-') }} / {{ $patientDateOfBirth ?? '-' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Consultation Type') }}</dt>
                            <dd class="text-base text-zinc-900 dark:text-white">
                                {{ $consultationTypes->firstWhere('id', $consultationTypeId)?->name ?? '-' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Phone') }}</dt>
                            <dd class="text-base text-zinc-900 dark:text-white">
                                {{ $patientPhone ?: '-' }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <flux:field>
                    <flux:label>{{ __('Chief Complaints') }} *</flux:label>
                    <flux:textarea
                        wire:model="chiefComplaints"
                        rows="3"
                        placeholder="{{ __('Describe the patient\'s main concerns or symptoms...') }}"
                    />
                    <flux:error name="chiefComplaints" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Priority') }}</flux:label>
                    <div class="flex gap-4">
                        <flux:radio wire:model="priority" value="normal" label="{{ __('Normal') }}" />
                        <flux:radio wire:model="priority" value="urgent" label="{{ __('Urgent') }}" />
                        <flux:radio wire:model="priority" value="emergency" label="{{ __('Emergency') }}" />
                    </div>
                    <flux:error name="priority" />
                </flux:field>

                <div class="flex justify-between pt-4">
                    <flux:button wire:click="previousStep" variant="ghost" icon="arrow-left">
                        {{ __('Previous') }}
                    </flux:button>
                    <flux:button wire:click="register" variant="primary" icon="check">
                        {{ __('Register Patient') }}
                    </flux:button>
                </div>
            </div>
        @endif
    </div>
</section>
