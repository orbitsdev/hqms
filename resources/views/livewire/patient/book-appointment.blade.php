<div class="min-h-screen bg-gradient-to-b from-zinc-50 to-white dark:from-zinc-900 dark:to-zinc-800">
    <div class="mx-auto max-w-2xl px-4 py-6 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-6">
            <a href="{{ route('patient.appointments') }}"
               class="mb-3 inline-flex items-center gap-1 text-sm text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200"
               wire:navigate>
                <flux:icon name="arrow-left" class="h-4 w-4" />
                {{ __('Back to Appointments') }}
            </a>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">{{ __('Book Appointment') }}</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Follow the steps to schedule your visit') }}</p>
        </div>

        {{-- Progress Steps --}}
        @php
            $stepLabels = [
                1 => __('Type'),
                2 => __('Patient'),
                3 => __('Date'),
                4 => __('Review'),
            ];
        @endphp

        <div class="mb-6">
            <div class="flex items-center justify-between">
                @foreach($stepLabels as $step => $label)
                    @php
                        $isComplete = $currentStep > $step;
                        $isCurrent = $currentStep === $step;
                        $canNavigate = $step <= $maxStep;
                    @endphp
                    <div class="flex flex-1 items-center {{ $step < 4 ? '' : '' }}">
                        <button type="button"
                                @if($canNavigate) wire:click="goToStep({{ $step }})" @endif
                                @disabled(!$canNavigate)
                                class="flex flex-col items-center gap-1 {{ $canNavigate ? 'cursor-pointer' : 'cursor-not-allowed' }}">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold transition
                                {{ $isComplete ? 'bg-success text-success-foreground' : ($isCurrent ? 'bg-primary text-primary-foreground' : 'bg-zinc-200 text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400') }}
                                {{ !$canNavigate ? 'opacity-50' : '' }}">
                                @if($isComplete)
                                    <flux:icon name="check" class="h-4 w-4" />
                                @else
                                    {{ $step }}
                                @endif
                            </span>
                            <span class="text-xs font-medium {{ $isCurrent ? 'text-zinc-900 dark:text-white' : 'text-zinc-500 dark:text-zinc-400' }}">
                                {{ $label }}
                            </span>
                        </button>
                        @if($step < 4)
                            <div class="mx-2 h-0.5 flex-1 {{ $currentStep > $step ? 'bg-success' : 'bg-zinc-200 dark:bg-zinc-700' }}"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Step 1: Consultation Type --}}
        @if($currentStep === 1)
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                <h2 class="mb-1 font-semibold text-zinc-900 dark:text-white">{{ __('Select Consultation Type') }}</h2>
                <p class="mb-4 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Choose the type of care you need') }}</p>

                <div class="space-y-3">
                    @foreach($consultationTypes as $type)
                        @php
                            $doctorsCount = (int) ($type->doctors_count ?? 0);
                            $hasDoctors = $doctorsCount > 0;
                            $isSelected = (int) $consultationTypeId === (int) $type->id;
                        @endphp

                        <button type="button"
                                wire:key="type-{{ $type->id }}"
                                @if($hasDoctors) wire:click="selectConsultationType({{ $type->id }})" @endif
                                @disabled(!$hasDoctors)
                                class="w-full rounded-xl border p-4 text-left transition
                                    {{ $isSelected ? 'border-primary bg-primary/5 ring-2 ring-primary' : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-700 dark:hover:border-zinc-600' }}
                                    {{ !$hasDoctors ? 'cursor-not-allowed opacity-50' : '' }}">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full {{ $isSelected ? 'bg-primary text-primary-foreground' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400' }}">
                                    <flux:icon name="clipboard-document-list" class="h-5 w-5" />
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-zinc-900 dark:text-white">{{ $type->name }}</p>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $type->description ?? __('General consultation') }}
                                    </p>
                                    <p class="mt-1 text-xs {{ $hasDoctors ? 'text-success' : 'text-destructive' }}">
                                        @if($hasDoctors)
                                            {{ trans_choice('{1} :count doctor available|[2,*] :count doctors available', $doctorsCount, ['count' => $doctorsCount]) }}
                                        @else
                                            {{ __('No doctors available') }}
                                        @endif
                                    </p>
                                </div>
                                @if($isSelected)
                                    <flux:icon name="check-circle" class="h-6 w-6 text-primary" />
                                @endif
                            </div>
                        </button>
                    @endforeach
                </div>

                <div class="mt-6">
                    <button type="button"
                            wire:click="nextStep"
                            @disabled(!$consultationTypeId)
                            class="w-full rounded-xl bg-primary px-4 py-3 font-semibold text-primary-foreground shadow-sm transition hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-50">
                        {{ __('Continue') }}
                    </button>
                </div>
            </div>
        @endif

        {{-- Step 2: Patient Information --}}
        @if($currentStep === 2)
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                <h2 class="mb-1 font-semibold text-zinc-900 dark:text-white">{{ __('Patient Information') }}</h2>
                <p class="mb-4 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Who is this appointment for?') }}</p>

                <form wire:submit.prevent="nextStep" class="space-y-4">
                    {{-- Patient Type Selection --}}
                    <div class="space-y-2">
                        <button type="button"
                                wire:click="$set('patientType', 'self')"
                                class="w-full rounded-xl border p-4 text-left transition
                                    {{ $patientType === 'self' ? 'border-primary bg-primary/5 ring-2 ring-primary' : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-700 dark:hover:border-zinc-600' }}">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full {{ $patientType === 'self' ? 'bg-primary text-primary-foreground' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-700' }}">
                                    <flux:icon name="user" class="h-5 w-5" />
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-zinc-900 dark:text-white">{{ __('Myself') }}</p>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Book for my own visit') }}</p>
                                </div>
                                @if($patientType === 'self')
                                    <flux:icon name="check-circle" class="h-6 w-6 text-primary" />
                                @endif
                            </div>
                        </button>

                        <button type="button"
                                wire:click="$set('patientType', 'dependent')"
                                class="w-full rounded-xl border p-4 text-left transition
                                    {{ $patientType === 'dependent' ? 'border-primary bg-primary/5 ring-2 ring-primary' : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-700 dark:hover:border-zinc-600' }}">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full {{ $patientType === 'dependent' ? 'bg-primary text-primary-foreground' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-700' }}">
                                    <flux:icon name="users" class="h-5 w-5" />
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-zinc-900 dark:text-white">{{ __('Someone else') }}</p>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Child, spouse, or dependent') }}</p>
                                </div>
                                @if($patientType === 'dependent')
                                    <flux:icon name="check-circle" class="h-6 w-6 text-primary" />
                                @endif
                            </div>
                        </button>
                        @error('patientType') <span class="text-xs text-destructive">{{ $message }}</span> @enderror
                    </div>

                    {{-- Self Info Display --}}
                    @if($patientType === 'self')
                        <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                            <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Using your profile details') }}</p>
                            <div class="mt-2 space-y-1 text-sm text-zinc-500 dark:text-zinc-400">
                                <p>{{ $patientFirstName }} {{ $patientLastName }}</p>
                                <p>{{ $patientDateOfBirth }} &middot; {{ ucfirst($patientGender ?? '') }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Dependent Form --}}
                    @if($patientType === 'dependent')
                        <div class="space-y-4 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                            <div class="grid gap-4 sm:grid-cols-3">
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('First Name') }} <span class="text-destructive">*</span></label>
                                    <input wire:model.live="patientFirstName" type="text"
                                           class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 focus:border-primary focus:ring-primary dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100" />
                                    @error('patientFirstName') <span class="text-xs text-destructive">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Middle Name') }}</label>
                                    <input wire:model.live="patientMiddleName" type="text"
                                           class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 focus:border-primary focus:ring-primary dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100" />
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Last Name') }} <span class="text-destructive">*</span></label>
                                    <input wire:model.live="patientLastName" type="text"
                                           class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 focus:border-primary focus:ring-primary dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100" />
                                    @error('patientLastName') <span class="text-xs text-destructive">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Date of Birth') }} <span class="text-destructive">*</span></label>
                                    <input wire:model.live="patientDateOfBirth" type="date"
                                           class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 focus:border-primary focus:ring-primary dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100" />
                                    @error('patientDateOfBirth') <span class="text-xs text-destructive">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Gender') }} <span class="text-destructive">*</span></label>
                                    <select wire:model.live="patientGender"
                                            class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 focus:border-primary focus:ring-primary dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100">
                                        <option value="">{{ __('Select') }}</option>
                                        <option value="male">{{ __('Male') }}</option>
                                        <option value="female">{{ __('Female') }}</option>
                                    </select>
                                    @error('patientGender') <span class="text-xs text-destructive">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Relationship') }} <span class="text-destructive">*</span></label>
                                <select wire:model.live="patientRelationship"
                                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 focus:border-primary focus:ring-primary dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100">
                                    <option value="child">{{ __('Child') }}</option>
                                    <option value="spouse">{{ __('Spouse') }}</option>
                                    <option value="parent">{{ __('Parent') }}</option>
                                    <option value="sibling">{{ __('Sibling') }}</option>
                                    <option value="other">{{ __('Other') }}</option>
                                </select>
                                @error('patientRelationship') <span class="text-xs text-destructive">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    @endif

                    <div class="flex gap-3 pt-2">
                        <button type="button"
                                wire:click="previousStep"
                                class="flex-1 rounded-xl border border-zinc-300 bg-white px-4 py-3 font-semibold text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                            {{ __('Back') }}
                        </button>
                        <button type="submit"
                                class="flex-1 rounded-xl bg-primary px-4 py-3 font-semibold text-primary-foreground shadow-sm transition hover:bg-primary/90">
                            {{ __('Continue') }}
                        </button>
                    </div>
                </form>
            </div>
        @endif

        {{-- Step 3: Select Date --}}
        @if($currentStep === 3)
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                <div class="mb-4 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="font-semibold text-zinc-900 dark:text-white">{{ __('Select Date') }}</h2>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Choose an available date') }}</p>
                    </div>
                    @if($selectedDate)
                        <div class="rounded-lg bg-primary/10 px-3 py-1.5 text-sm font-medium text-primary">
                            {{ $selectedDate['formatted'] ?? __('Selected') }}
                        </div>
                    @endif
                </div>

                {{-- Legend --}}
                <div class="mb-4 flex flex-wrap gap-3 text-xs">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-full bg-zinc-300 dark:bg-zinc-600"></span>
                        {{ __('Unavailable') }}
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-full bg-success"></span>
                        {{ __('Available') }}
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-full bg-primary"></span>
                        {{ __('Selected') }}
                    </span>
                </div>

                @if(empty($availableDates))
                    <div class="rounded-xl border border-warning/30 bg-warning/10 p-4">
                        <div class="flex gap-3">
                            <flux:icon name="exclamation-circle" class="h-5 w-5 flex-shrink-0 text-warning" />
                            <div>
                                <p class="font-medium text-warning-foreground dark:text-warning">{{ __('No dates available') }}</p>
                                <p class="text-sm text-warning-foreground/80 dark:text-warning/80">{{ __('Please choose another consultation type or check back later.') }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                        @foreach($availableDates as $date)
                            @php
                                $isSelected = ($appointmentDate === $date['date']);
                                $isToday = $date['is_today'] ?? false;
                                $isAvailable = (bool) $date['available'];
                            @endphp

                            <button type="button"
                                    wire:key="date-{{ $date['date'] }}"
                                    @if($isAvailable) wire:click="selectDate('{{ $date['date'] }}')" @endif
                                    @disabled(!$isAvailable)
                                    class="rounded-xl border p-3 text-center transition
                                        {{ $isSelected ? 'border-primary bg-primary/5 ring-2 ring-primary' : ($isAvailable ? 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-700 dark:hover:border-zinc-600' : 'border-zinc-200 opacity-50 dark:border-zinc-700') }}
                                        {{ !$isAvailable ? 'cursor-not-allowed' : '' }}">
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $date['day_name'] }}
                                    @if($isToday)
                                        <span class="ml-1 rounded bg-zinc-200 px-1 py-0.5 text-[10px] font-medium dark:bg-zinc-700">{{ __('Today') }}</span>
                                    @endif
                                </p>
                                <p class="mt-1 text-2xl font-bold {{ $isSelected ? 'text-primary' : 'text-zinc-900 dark:text-white' }}">{{ $date['day'] }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $date['month'] }}</p>
                                <p class="mt-1 text-[10px] font-medium {{ $isAvailable ? 'text-success' : 'text-zinc-400 dark:text-zinc-500' }}">
                                    {{ $isAvailable ? __('Open') : __('Closed') }}
                                </p>
                            </button>
                        @endforeach
                    </div>
                @endif

                <div class="mt-6 flex gap-3">
                    <button type="button"
                            wire:click="previousStep"
                            class="flex-1 rounded-xl border border-zinc-300 bg-white px-4 py-3 font-semibold text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                        {{ __('Back') }}
                    </button>
                    <button type="button"
                            wire:click="nextStep"
                            @disabled(!$appointmentDate)
                            class="flex-1 rounded-xl bg-primary px-4 py-3 font-semibold text-primary-foreground shadow-sm transition hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-50">
                        {{ __('Continue') }}
                    </button>
                </div>
            </div>
        @endif

        {{-- Step 4: Review & Submit --}}
        @if($currentStep === 4)
            <div class="space-y-4">
                {{-- Summary Card --}}
                <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                    <h2 class="mb-4 font-semibold text-zinc-900 dark:text-white">{{ __('Appointment Summary') }}</h2>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-700">
                            <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Consultation') }}</span>
                            <span class="font-medium text-zinc-900 dark:text-white">{{ $selectedConsultation?->name }}</span>
                        </div>
                        <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-700">
                            <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Date') }}</span>
                            <span class="font-medium text-zinc-900 dark:text-white">{{ $selectedDate['formatted'] ?? $appointmentDate }}</span>
                        </div>
                        <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-700">
                            <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Patient') }}</span>
                            <span class="font-medium text-zinc-900 dark:text-white">
                                @if($patientType === 'self')
                                    {{ __('Myself') }}
                                @else
                                    {{ trim($patientFirstName . ' ' . ($patientMiddleName ? $patientMiddleName . ' ' : '') . $patientLastName) }}
                                @endif
                            </span>
                        </div>
                        @if($patientType === 'dependent')
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Relationship') }}</span>
                                <span class="font-medium text-zinc-900 dark:text-white">{{ ucfirst($patientRelationship) }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Chief Complaints --}}
                <form wire:submit.prevent="submitAppointment" class="space-y-4">
                    <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                        <label class="mb-2 block font-semibold text-zinc-900 dark:text-white">{{ __('Chief Complaints') }}</label>
                        <p class="mb-3 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Describe symptoms or reason for your visit') }}</p>
                        <textarea wire:model.live="chiefComplaints"
                                  rows="4"
                                  placeholder="{{ __('E.g., Fever for 2 days, cough, headache...') }}"
                                  class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 focus:border-primary focus:ring-primary dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100 dark:placeholder-zinc-500"></textarea>
                        @error('chiefComplaints') <span class="text-xs text-destructive">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex gap-3">
                        <button type="button"
                                wire:click="previousStep"
                                class="flex-1 rounded-xl border border-zinc-300 bg-white px-4 py-3 font-semibold text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                            {{ __('Back') }}
                        </button>
                        <button type="submit"
                                class="flex-1 rounded-xl bg-primary px-4 py-3 font-semibold text-primary-foreground shadow-sm transition hover:bg-primary/90">
                            <span wire:loading.remove wire:target="submitAppointment">{{ __('Submit') }}</span>
                            <span wire:loading wire:target="submitAppointment">{{ __('Submitting...') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        @endif

        {{-- Bottom spacing for mobile nav --}}
        <div class="h-20 lg:hidden"></div>
    </div>
</div>
