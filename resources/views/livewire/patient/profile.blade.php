<div class="space-y-6">
    <h1 class="text-2xl font-bold">My Profile</h1>

    <flux:card>
        <flux:card.header>
            <flux:heading>Personal Information</flux:heading>
        </flux:card.header>
        <flux:card.content>
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
        </flux:card.content>
    </flux:card>
</div>
