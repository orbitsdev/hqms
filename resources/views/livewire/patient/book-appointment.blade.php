{{-- resources/views/livewire/patient/book-appointment.blade.php --}}

<section class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="space-y-1">
            <flux:heading size="xl" level="1">{{ __('Book Appointment') }}</flux:heading>
            <flux:text variant="subtle" class="text-sm">
                {{ __('Follow the steps to schedule your visit.') }}
            </flux:text>
        </div>

        <div class="flex items-center gap-3 self-start md:self-auto">
            <flux:button :href="route('patient.appointments')" variant="outline" size="sm"
                icon:trailing="arrow-left">
                {{ __('Back to Appointments') }}
            </flux:button>

            {{-- Mobile: open availability modal --}}
            @if (!empty($doctorAvailabilityByType))
                <flux:button type="button" variant="outline" size="sm" class="md:hidden"
                    icon:trailing="arrow-up-right" wire:click="openAvailabilityModal">
                    {{ __('View Availability') }}
                </flux:button>
            @endif

            @if ($maxStep >= 4)
                <flux:button type="button" wire:click="goToStep(4)" variant="outline" size="sm">
                    {{ __('Review') }}
                </flux:button>
            @endif

            <img src="{{ asset('images/undraw_schedule_ry1w.svg') }}" alt="{{ __('Schedule appointment') }}"
                class="h-20 w-auto opacity-80 hidden sm:block" />
        </div>
    </div>

    {{-- 2-column layout on desktop --}}
    <div class="grid gap-6 md:grid-cols-12">

        {{-- LEFT: Booking flow --}}
        <div class="md:col-span-8 space-y-6">

            @php
                $progressClass =
                    [
                        1 => 'w-0',
                        2 => 'w-1/3',
                        3 => 'w-2/3',
                        4 => 'w-full',
                    ][$currentStep] ?? 'w-0';

                $stepLabels = [
                    1 => __('Consultation Type'),
                    2 => __('Patient Info'),
                    3 => __('Select Date'),
                    4 => __('Review'),
                ];
            @endphp

            {{-- Progress --}}
            <div class="relative mb-2">
                <div class="absolute left-0 right-0 top-4 h-px bg-zinc-200 dark:bg-zinc-800"></div>
                <div class="absolute left-0 top-4 h-px bg-zinc-900 dark:bg-zinc-100 {{ $progressClass }}"></div>

                <div class="grid grid-cols-4 gap-4 text-center">
                    @foreach ($stepLabels as $step => $label)
                        @php
                            $isComplete = $currentStep >= $step;
                            $canNavigate = $step <= $maxStep;
                            $stateClasses = $isComplete
                                ? 'border-zinc-900 bg-zinc-900 text-white hover:bg-zinc-900 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-100'
                                : 'border-zinc-200 bg-white text-zinc-500 hover:bg-white dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-900';
                        @endphp

                        <div class="relative flex flex-col items-center gap-3">
                            @if ($canNavigate)
                                <button type="button" wire:click="goToStep({{ $step }})"
                                    class="h-9 w-9 rounded-full border text-sm font-semibold transition {{ $stateClasses }}">
                                    {{ $step }}
                                </button>
                            @else
                                <button type="button" disabled
                                    class="h-9 w-9 rounded-full border text-sm font-semibold opacity-40 {{ $stateClasses }}">
                                    {{ $step }}
                                </button>
                            @endif

                            <span
                                class="text-xs font-medium {{ $currentStep >= $step ? 'text-zinc-900 dark:text-zinc-100' : 'text-zinc-500 dark:text-zinc-400' }}">
                                {{ $label }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- STEP 1 --}}
            @if ($currentStep === 1)
                <div
                    class="rounded-lg border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="border-b border-zinc-200/70 px-4 py-3 dark:border-zinc-800">
                        <flux:heading>{{ __('Select Consultation Type') }}</flux:heading>
                        <flux:text>{{ __('Choose the type of consultation you need.') }}</flux:text>
                    </div>

                    <div class="p-4 space-y-4">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            @foreach ($consultationTypes as $type)
                                @php
                                    $doctorsCount = (int) ($type->doctors_count ?? 0);
                                    $hasDoctors = $doctorsCount > 0;
                                    $isSelected = (int) $consultationTypeId === (int) $type->id;

                                    $variant = $isSelected ? 'primary' : 'outline';
                                    $dotClass = $isSelected ? 'bg-white/20' : 'bg-zinc-100 dark:bg-zinc-800';
                                    $textClass = $isSelected ? 'text-white/70' : 'text-zinc-500 dark:text-zinc-400';
                                @endphp

                                @if ($hasDoctors)
                                    <flux:button wire:key="consultation-type-{{ $type->id }}"
                                        wire:click="selectConsultationType({{ $type->id }})"
                                        variant="{{ $variant }}"
                                        class="h-24 flex flex-col items-center justify-center space-y-2">
                                        <span aria-hidden="true"
                                            class="h-8 w-8 rounded-full {{ $dotClass }}"></span>

                                        <div class="text-center">
                                            <div class="font-medium">{{ $type->name }}</div>

                                            <div class="text-xs {{ $textClass }}">
                                                {{ $type->description ?? __('General care') }}
                                            </div>

                                            <div class="text-xs {{ $textClass }}">
                                                {{ trans_choice(
                                                    '{0} No doctors available|{1} :count doctor available|[2,*] :count doctors available',
                                                    $doctorsCount,
                                                    ['count' => $doctorsCount],
                                                ) }}
                                            </div>
                                        </div>
                                    </flux:button>
                                @else
                                    <flux:button wire:key="consultation-type-{{ $type->id }}" variant="outline"
                                        disabled
                                        class="h-24 flex flex-col items-center justify-center space-y-2 opacity-50 cursor-not-allowed">
                                        <span aria-hidden="true"
                                            class="h-8 w-8 rounded-full bg-zinc-100 dark:bg-zinc-800"></span>

                                        <div class="text-center">
                                            <div class="font-medium">{{ $type->name }}</div>

                                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ $type->description ?? __('General care') }}
                                            </div>

                                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ __('No doctors available') }}
                                            </div>

                                            <div class="mt-1 text-xs text-amber-600">
                                                {{ __('No doctors assigned yet.') }}
                                            </div>
                                        </div>
                                    </flux:button>
                                @endif
                            @endforeach
                        </div>

                        <div class="flex justify-end">
                            <flux:button type="button" wire:click="nextStep" variant="primary"
                                :disabled="!$consultationTypeId">
                                {{ __('Continue') }}
                            </flux:button>
                        </div>
                    </div>
                </div>
            @endif


            {{-- STEP 2 --}}
            @if ($currentStep === 2)
                <div
                    class="rounded-lg border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="border-b border-zinc-200/70 px-4 py-3 dark:border-zinc-800">
                        <flux:heading>{{ __('Patient Information') }}</flux:heading>
                        <flux:text>{{ __('Tell us who this appointment is for.') }}</flux:text>
                    </div>

                    <div class="p-4 space-y-6">
                        <form wire:submit.prevent="nextStep" class="space-y-6">
                            <div class="space-y-2">
                                <label
                                    class="flex items-center gap-3 rounded-lg border border-zinc-200/70 px-3 py-2 text-sm dark:border-zinc-800">
                                    <input type="radio" value="self" wire:model.live="patientType"
                                        class="h-4 w-4 text-zinc-900 focus:ring-zinc-900" />
                                    <span class="font-medium">{{ __('Myself') }}</span>
                                </label>

                                <label
                                    class="flex items-center gap-3 rounded-lg border border-zinc-200/70 px-3 py-2 text-sm dark:border-zinc-800">
                                    <input type="radio" value="dependent" wire:model.live="patientType"
                                        class="h-4 w-4 text-zinc-900 focus:ring-zinc-900" />
                                    <span class="font-medium">{{ __('Someone else (child/dependent)') }}</span>
                                </label>

                                @error('patientType')
                                    <span class="text-xs text-red-600">{{ $message }}</span>
                                @enderror
                            </div>

                            @if ($patientType === 'self')
                                <div
                                    class="rounded-lg border border-zinc-200/70 bg-zinc-50 p-4 text-sm text-zinc-700 dark:border-zinc-800 dark:bg-zinc-900/60 dark:text-zinc-200">
                                    <div class="font-medium">{{ __('Using your profile details') }}</div>
                                    <div class="mt-2 space-y-1 text-xs text-zinc-500 dark:text-zinc-400">
                                        <div>{{ $patientFirstName }} {{ $patientLastName }}</div>
                                        <div>{{ $patientDateOfBirth }}</div>
                                        <div>{{ ucfirst($patientGender ?? '') }}</div>
                                    </div>
                                </div>
                            @endif

                            @if ($patientType === 'dependent')
                                <div class="space-y-4 border-t border-zinc-200/70 pt-4 dark:border-zinc-800">
                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                        <flux:input wire:model.live="patientFirstName" :label="__('First name')"
                                            type="text" />
                                        <flux:input wire:model.live="patientMiddleName" :label="__('Middle name')"
                                            type="text" />
                                        <flux:input wire:model.live="patientLastName" :label="__('Last name')"
                                            type="text" />
                                    </div>

                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                        <flux:input wire:model.live="patientDateOfBirth" :label="__('Birth date')"
                                            type="date" />

                                        <div>
                                            <label
                                                class="block text-sm font-medium text-zinc-700 dark:text-zinc-200 mb-1">
                                                {{ __('Gender') }}
                                            </label>

                                            <select wire:model.live="patientGender"
                                                class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100">
                                                <option value="">{{ __('Select') }}</option>
                                                <option value="male">{{ __('Male') }}</option>
                                                <option value="female">{{ __('Female') }}</option>
                                            </select>

                                            @error('patientGender')
                                                <span class="text-xs text-red-600">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-200 mb-1">
                                            {{ __('Relationship to account') }}
                                        </label>

                                        <select wire:model.live="patientRelationship"
                                            class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100">
                                            <option value="child">{{ __('Child') }}</option>
                                            <option value="spouse">{{ __('Spouse') }}</option>
                                            <option value="parent">{{ __('Parent') }}</option>
                                            <option value="sibling">{{ __('Sibling') }}</option>
                                            <option value="other">{{ __('Other') }}</option>
                                        </select>

                                        @error('patientRelationship')
                                            <span class="text-xs text-red-600">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            @endif

                            <div class="flex flex-wrap gap-3">
                                <flux:button type="button" wire:click="previousStep" variant="outline">
                                    {{ __('Previous') }}
                                </flux:button>

                                <flux:button type="submit" variant="primary">
                                    {{ __('Next') }}
                                </flux:button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            {{-- STEP 3 --}}
            @if ($currentStep === 3)
                <div
                    class="rounded-lg border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="border-b border-zinc-200/70 px-4 py-3 dark:border-zinc-800">
                        <flux:heading>{{ __('Select Appointment Date') }}</flux:heading>
                        <flux:text>{{ __('Choose an available date for your appointment.') }}</flux:text>
                    </div>

                    <div class="p-4 space-y-4">
                        @if (empty($availableDates))
                            <flux:callout variant="warning" icon="exclamation-circle"
                                :heading="__('No dates available')">
                                <flux:text class="text-sm">
                                    {{ __('Please choose another consultation type or check back later.') }}
                                </flux:text>
                            </flux:callout>
                        @else
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3 lg:grid-cols-4">
                                @foreach ($availableDates as $date)
                                    @if ($date['available'])
                                        <flux:button wire:key="appointment-date-{{ $date['date'] }}"
                                            wire:click="selectDate('{{ $date['date'] }}')"
                                            variant="{{ $appointmentDate === $date['date'] ? 'primary' : 'outline' }}">
                                            <div class="text-center">
                                                <div class="font-medium">{{ $date['day_name'] }}</div>
                                                <div class="text-lg font-bold">{{ $date['formatted'] }}</div>
                                                <div class="text-xs text-zinc-600 dark:text-zinc-300">
                                                    {{ __('Available') }}</div>
                                            </div>
                                        </flux:button>
                                    @else
                                        <flux:button variant="outline" disabled class="opacity-50 cursor-not-allowed">
                                            <div class="text-center space-y-1">
                                                <div class="font-medium">{{ $date['day_name'] }}</div>
                                                <div class="text-lg font-bold">{{ $date['formatted'] }}</div>

                                                <flux:badge color="red" size="xs">
                                                    {{ __('Unavailable') }}
                                                </flux:badge>
                                            </div>
                                        </flux:button>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        <div class="flex flex-wrap gap-3">
                            <flux:button type="button" wire:click="previousStep" variant="outline">
                                {{ __('Previous') }}
                            </flux:button>

                            <flux:button type="button" wire:click="nextStep" variant="primary"
                                :disabled="!$appointmentDate">
                                {{ __('Next') }}
                            </flux:button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- STEP 4 --}}
            @if ($currentStep === 4)
                <div
                    class="rounded-lg border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="border-b border-zinc-200/70 px-4 py-3 dark:border-zinc-800">
                        <flux:heading>{{ __('Review & Submit') }}</flux:heading>
                        <flux:text>{{ __('Confirm your details before submitting your appointment request.') }}
                        </flux:text>
                    </div>

                    <div class="p-4 space-y-6">
                        <form wire:submit.prevent="submitAppointment" class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-200 mb-2">
                                    {{ __('Chief complaints') }}
                                </label>

                                <textarea wire:model.live="chiefComplaints" rows="5"
                                    class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                                    placeholder="{{ __('Describe symptoms or reason for visit...') }}"></textarea>

                                @error('chiefComplaints')
                                    <span class="text-xs text-red-600">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="border-t border-zinc-200/70 pt-4 dark:border-zinc-800">
                                <flux:heading size="sm">{{ __('Appointment Summary') }}</flux:heading>

                                <div class="mt-3 space-y-2 text-sm text-zinc-700 dark:text-zinc-200">
                                    <div class="flex items-center justify-between">
                                        <span class="text-zinc-500">{{ __('Consultation type') }}</span>
                                        <span>{{ $selectedConsultation?->name }}</span>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <span class="text-zinc-500">{{ __('Preferred date') }}</span>
                                        <span>{{ $selectedDate['formatted'] ?? $appointmentDate }}</span>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <span class="text-zinc-500">{{ __('Patient') }}</span>
                                        <span>
                                            @if ($patientType === 'self')
                                                {{ __('Myself') }}
                                            @else
                                                {{ trim($patientFirstName . ' ' . ($patientMiddleName ? $patientMiddleName . ' ' : '') . $patientLastName) }}
                                            @endif
                                        </span>
                                    </div>

                                    @if ($patientType === 'dependent')
                                        <div class="flex items-center justify-between">
                                            <span class="text-zinc-500">{{ __('Relationship') }}</span>
                                            <span>{{ ucfirst($patientRelationship) }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-3">
                                <flux:button type="button" wire:click="previousStep" variant="outline">
                                    {{ __('Previous') }}
                                </flux:button>

                                <flux:button type="submit" variant="primary">
                                    {{ __('Submit appointment') }}
                                </flux:button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

        </div>

        {{-- RIGHT: Availability sidebar (desktop only) --}}
        @if (!empty($doctorAvailabilityByType))
            <aside class="hidden md:block md:col-span-4">
                <div class="sticky top-6">
                    <div
                        class="rounded-lg border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="border-b border-zinc-200/70 px-4 py-3 dark:border-zinc-800">
                            <flux:heading size="sm">{{ __('Doctor availability overview') }}</flux:heading>
                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ __('Check clinic days first.') }}
                            </flux:text>
                        </div>

                        <div class="p-4 space-y-3 max-h-[70vh] overflow-auto">
                            @foreach ($doctorAvailabilityByType as $entry)
                                <div
                                    class="rounded-md border border-zinc-200/70 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-950/40">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                {{ $entry['type']->name }}
                                            </div>
                                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ $entry['type']->description ?? __('General care') }}
                                            </div>
                                        </div>

                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ trans_choice('{0} No doctors|{1} :count doctor|[2,*] :count doctors', $entry['type']->doctors_count ?? 0, ['count' => $entry['type']->doctors_count ?? 0]) }}
                                        </div>
                                    </div>



                                    @if (($entry['type']->doctors_count ?? 0) > 0 && empty($entry['availability']))
                                        <div class="mt-2 text-xs text-amber-600">
                                            {{ __('Doctors are assigned, but clinic schedule is not configured yet.') }}
                                        </div>
                                    @endif

                                    <div class="mt-2 space-y-2">
                                        @forelse(($entry['availability'] ?? []) as $availability)
                                            <div
                                                class="rounded-md border border-zinc-200/70 bg-white p-2 text-xs text-zinc-700 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-200">
                                                <div class="font-medium">{{ $availability['name'] ?? __('Doctor') }}
                                                </div>
                                                <div class="text-zinc-500 dark:text-zinc-400">
                                                    {{ __('Clinic days') }}:
                                                    {{ empty($availability['days']) ? __('To be announced') : implode(', ', $availability['days']) }}
                                                </div>

                                                @if (!empty($availability['unavailable']))
                                                    <div class="text-zinc-500 dark:text-zinc-400">
                                                        {{ __('Unavailable') }}:
                                                        {{ implode(', ', $availability['unavailable']) }}
                                                    </div>
                                                @endif

                                                @if (!empty($availability['extra']))
                                                    <div class="text-zinc-500 dark:text-zinc-400">
                                                        {{ __('Extra clinic day') }}:
                                                        {{ implode(', ', $availability['extra']) }}
                                                    </div>
                                                @endif
                                            </div>
                                        @empty
                                            <div
                                                class="rounded-md border border-zinc-200/70 bg-white p-2 text-xs text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400">
                                                {{ __('No schedules configured yet.') }}
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </aside>
        @endif

    </div>

    {{-- MOBILE MODAL --}}
    @if (!empty($doctorAvailabilityByType))
        <flux:modal wire:model="showAvailabilityModal" class="md:hidden">
            <flux:heading>{{ __('Doctor availability overview') }}</flux:heading>
            <flux:text class="text-sm">
                {{ __('Check clinic days first so you don’t go to the hospital when doctors are unavailable.') }}
            </flux:text>

            <div class="mt-4 space-y-4 max-h-[70vh] overflow-auto">
                @foreach ($doctorAvailabilityByType as $entry)
                    <div
                        class="rounded-lg border border-zinc-200/70 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-medium">{{ $entry['type']->name }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $entry['type']->description ?? __('General care') }}
                                </div>
                            </div>

                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ trans_choice('{0} No doctors|{1} :count doctor|[2,*] :count doctors', $entry['type']->doctors_count ?? 0, ['count' => $entry['type']->doctors_count ?? 0]) }}
                            </div>
                        </div>



                        @if (($entry['type']->doctors_count ?? 0) > 0 && empty($entry['availability']))
                            <div class="mt-3 text-xs text-amber-600">
                                {{ __('Doctors are assigned, but clinic schedule is not configured yet.') }}
                            </div>
                        @endif

                        <div class="mt-3 space-y-2">
                            @forelse(($entry['availability'] ?? []) as $availability)
                                <div
                                    class="rounded-md border border-zinc-200/70 bg-zinc-50 p-3 text-xs dark:border-zinc-800 dark:bg-zinc-950/40">
                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                        {{ $availability['name'] ?? __('Doctor') }}
                                    </div>

                                    <div class="mt-1 text-zinc-500 dark:text-zinc-400">
                                        {{ __('Clinic days') }}:
                                        {{ empty($availability['days']) ? __('To be announced') : implode(', ', $availability['days']) }}
                                    </div>

                                    @if (!empty($availability['unavailable']))
                                        <div class="text-zinc-500 dark:text-zinc-400">
                                            {{ __('Unavailable') }}:
                                            {{ implode(', ', $availability['unavailable']) }}
                                        </div>
                                    @endif

                                    @if (!empty($availability['extra']))
                                        <div class="text-zinc-500 dark:text-zinc-400">
                                            {{ __('Extra clinic day') }}:
                                            {{ implode(', ', $availability['extra']) }}
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div
                                    class="rounded-md border border-zinc-200/70 bg-white p-3 text-xs text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400">
                                    {{ __('No schedules configured yet.') }}
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-5 flex justify-end gap-2">
                <flux:button type="button" variant="primary" wire:click="closeAvailabilityModal">
                    {{ __('Close') }}
                </flux:button>
            </div>
        </flux:modal>
    @endif

</section>
