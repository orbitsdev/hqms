<section class="mx-auto max-w-2xl space-y-6">
    <div class="flex items-center justify-between">
        <div class="space-y-1">
            <flux:heading size="xl" level="1">{{ __('Register Walk-in Patient') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('Register a new walk-in patient for today.') }}
            </flux:text>
        </div>
        <flux:button href="{{ route('nurse.appointments') }}" wire:navigate variant="ghost" icon="arrow-left">
            {{ __('Back') }}
        </flux:button>
    </div>

    <form wire:submit="register" class="space-y-6">
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="sm" class="mb-4">{{ __('Consultation Type') }}</flux:heading>

            <flux:field>
                <flux:select wire:model="consultationTypeId" placeholder="{{ __('Select consultation type') }}">
                    @foreach($consultationTypes as $type)
                        <flux:select.option value="{{ $type->id }}">{{ $type->name }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="consultationTypeId" />
            </flux:field>
        </div>

        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="sm" class="mb-4">{{ __('Patient Information') }}</flux:heading>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
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
        </div>

        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="sm" class="mb-4">{{ __('Address') }}</flux:heading>

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

        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="sm" class="mb-4">{{ __('Chief Complaints') }}</flux:heading>

            <flux:field>
                <flux:textarea
                    wire:model="chiefComplaints"
                    rows="3"
                    placeholder="{{ __('Describe the patient\'s main concerns...') }}"
                />
                <flux:error name="chiefComplaints" />
            </flux:field>
        </div>

        <div class="flex justify-end gap-3">
            <flux:button href="{{ route('nurse.appointments') }}" wire:navigate variant="ghost">
                {{ __('Cancel') }}
            </flux:button>
            <flux:button type="submit" variant="primary">
                {{ __('Register Patient') }}
            </flux:button>
        </div>
    </form>
</section>
