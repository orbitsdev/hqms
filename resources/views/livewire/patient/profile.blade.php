<div class="space-y-6">
    <h1 class="text-2xl font-bold">My Profile</h1>

    <div class="rounded-lg border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="border-b border-zinc-200/70 px-4 py-3 dark:border-zinc-800">
            <flux:heading>Personal Information</flux:heading>
        </div>
        <div class="p-4">
            <div class="mb-6 flex flex-col gap-4 rounded-lg border border-zinc-200/70 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-900/60 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Keep your profile details up to date.</p>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">We use this to match appointments and contact you quickly.</p>
                </div>
                <img
                    src="{{ asset('images/undraw_personal-information_h7kf.svg') }}"
                    alt="Personal information"
                    class="h-20 w-auto opacity-80"
                />
            </div>
            <form wire:submit.prevent="savePersonalInfo" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <flux:field label="First Name">
                        <flux:input type="text" wire:model.live="first_name" required />
                    </flux:field>

                    <flux:field label="Middle Name">
                        <flux:input type="text" wire:model.live="middle_name" />
                    </flux:field>

                    <flux:field label="Last Name">
                        <flux:input type="text" wire:model.live="last_name" required />
                    </flux:field>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <flux:field label="Birth Date">
                        <flux:input type="date" wire:model.live="date_of_birth" required />
                    </flux:field>

                    <flux:field label="Gender">
                        <flux:select wire:model.live="gender" required>
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </flux:select>
                    </flux:field>

                    <flux:field label="Phone Number">
                        <flux:input type="tel" wire:model.live="phone" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:field label="Occupation">
                        <flux:input type="text" wire:model.live="occupation" />
                    </flux:field>
                </div>

                <flux:separator />

                <flux:heading size="sm">Address</flux:heading>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:field label="Province">
                        <flux:input type="text" wire:model.live="province" />
                    </flux:field>

                    <flux:field label="Municipality">
                        <flux:input type="text" wire:model.live="municipality" />
                    </flux:field>

                    <flux:field label="Barangay">
                        <flux:input type="text" wire:model.live="barangay" />
                    </flux:field>

                    <flux:field label="Street">
                        <flux:input type="text" wire:model.live="street" />
                    </flux:field>
                </div>

                <flux:separator />

                <flux:heading size="sm">Emergency Contact</flux:heading>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:field label="Contact Name">
                        <flux:input type="text" wire:model.live="emergency_contact_name" />
                    </flux:field>

                    <flux:field label="Contact Phone">
                        <flux:input type="tel" wire:model.live="emergency_contact_phone" />
                    </flux:field>
                </div>

                <flux:button type="submit" variant="primary">Save Profile</flux:button>
            </form>
        </div>
    </div>
</div>
