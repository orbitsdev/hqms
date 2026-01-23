<section class="space-y-6">
    <div class="flex items-start justify-between gap-4">
        <div class="space-y-1">
            <flux:heading size="xl" level="1">{{ __('Profile') }}</flux:heading>
            <flux:text variant="subtle" class="text-sm">
                {{ __('These details are for the account owner. Patient visit details are captured in medical records.') }}
            </flux:text>
        </div>

        <flux:button :href="route('profile.edit')" icon="lock-closed" variant="ghost" size="sm">
            {{ __('Change password') }}
        </flux:button>
    </div>

    @if($showCompletionNotice || session('profile_incomplete'))
        <flux:callout variant="warning" icon="exclamation-circle" :heading="__('Please complete your profile')">
            <flux:text>
                {{ session('profile_incomplete') ?? __('Complete your profile to access the patient portal.') }}
            </flux:text>
        </flux:callout>
    @endif

    @if(session('status') === 'profile-saved')
        <flux:callout variant="success" icon="check-circle" :heading="__('Profile updated')">
            <flux:text>{{ session('profile_message') }}</flux:text>
        </flux:callout>
    @endif

    <form wire:submit.prevent="save" class="space-y-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 shadow-sm">
        <div class="grid gap-4 md:grid-cols-3">
            <flux:input wire:model.defer="first_name" :label="__('First name')" type="text" required autocomplete="given-name" />
            <flux:input wire:model.defer="middle_name" :label="__('Middle name')" type="text" autocomplete="additional-name" />
            <flux:input wire:model.defer="last_name" :label="__('Last name')" type="text" required autocomplete="family-name" />
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <flux:input :label="__('Email')" type="email" :value="auth()->user()->email" disabled readonly />
            <flux:input wire:model.defer="phone" :label="__('Phone')" type="tel" required autocomplete="tel" />
            <div class="grid gap-2 md:grid-cols-2">
                <flux:input wire:model.defer="date_of_birth" :label="__('Date of birth')" type="date" required />
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-200 mb-1">{{ __('Gender') }}</label>
                    <select wire:model.defer="gender" required class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100">
                        <option value="">{{ __('Select') }}</option>
                        <option value="male">{{ __('Male') }}</option>
                        <option value="female">{{ __('Female') }}</option>
                    </select>
                    @error('gender') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-200 mb-1">{{ __('Marital status') }}</label>
                <select wire:model.defer="marital_status" class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100">
                    <option value="">{{ __('Select') }}</option>
                    <option value="child">{{ __('Child') }}</option>
                    <option value="single">{{ __('Single') }}</option>
                    <option value="married">{{ __('Married') }}</option>
                    <option value="widow">{{ __('Widowed') }}</option>
                </select>
                @error('marital_status') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>
            <flux:input wire:model.defer="occupation" :label="__('Occupation')" type="text" />
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <flux:input wire:model.defer="province" :label="__('Province')" type="text" required />
            <flux:input wire:model.defer="municipality" :label="__('Municipality/City')" type="text" required />
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <flux:input wire:model.defer="barangay" :label="__('Barangay')" type="text" required />
            <flux:input wire:model.defer="street" :label="__('Street / House No. / Landmark')" type="text" required />
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <flux:input wire:model.defer="emergency_contact_name" :label="__('Emergency contact name')" type="text" required />
            <flux:input wire:model.defer="emergency_contact_phone" :label="__('Emergency contact phone')" type="tel" required />
        </div>

        <div class="flex items-center justify-between gap-4">
            <flux:text variant="subtle" class="text-xs">
                {{ __('Email cannot be edited here. Use Change password for credentials updates.') }}
            </flux:text>
            <flux:button type="submit" variant="primary" icon="check">
                {{ __('Save profile') }}
            </flux:button>
        </div>
    </form>
</section>
