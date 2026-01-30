<section class="space-y-4">
    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" level="1">{{ __('User Management') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Manage system users') }}</flux:text>
        </div>
        <flux:button wire:click="openCreateModal" variant="primary" icon="user-plus">
            {{ __('Add User') }}
        </flux:button>
    </div>

    {{-- Status Toggle --}}
    <div class="flex gap-2">
        <button
            wire:click="$set('statusFilter', 'active')"
            class="rounded-lg border px-4 py-2 text-sm font-medium transition {{ $statusFilter === 'active' ? 'border-zinc-900 bg-zinc-900 text-white dark:border-white dark:bg-white dark:text-zinc-900' : 'border-zinc-200 bg-white hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700' }}"
        >
            {{ __('Active') }}
        </button>
        <button
            wire:click="$set('statusFilter', 'inactive')"
            class="rounded-lg border px-4 py-2 text-sm font-medium transition {{ $statusFilter === 'inactive' ? 'border-zinc-900 bg-zinc-900 text-white dark:border-white dark:bg-white dark:text-zinc-900' : 'border-zinc-200 bg-white hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700' }}"
        >
            {{ __('Inactive') }}
        </button>
    </div>

    {{-- Role Filter --}}
    <div class="flex flex-wrap gap-2">
        <button
            wire:click="$set('roleFilter', '')"
            class="rounded-full px-3 py-1 text-xs font-medium transition {{ $roleFilter === '' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300' }}"
        >
            {{ __('All') }} ({{ $this->roleCounts['all'] }})
        </button>
        <button
            wire:click="$set('roleFilter', 'patient')"
            class="rounded-full px-3 py-1 text-xs font-medium transition {{ $roleFilter === 'patient' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300' }}"
        >
            {{ __('Patients') }} ({{ $this->roleCounts['patient'] }})
        </button>
        <button
            wire:click="$set('roleFilter', 'doctor')"
            class="rounded-full px-3 py-1 text-xs font-medium transition {{ $roleFilter === 'doctor' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300' }}"
        >
            {{ __('Doctors') }} ({{ $this->roleCounts['doctor'] }})
        </button>
        <button
            wire:click="$set('roleFilter', 'nurse')"
            class="rounded-full px-3 py-1 text-xs font-medium transition {{ $roleFilter === 'nurse' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300' }}"
        >
            {{ __('Nurses') }} ({{ $this->roleCounts['nurse'] }})
        </button>
        <button
            wire:click="$set('roleFilter', 'cashier')"
            class="rounded-full px-3 py-1 text-xs font-medium transition {{ $roleFilter === 'cashier' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300' }}"
        >
            {{ __('Cashiers') }} ({{ $this->roleCounts['cashier'] }})
        </button>
        <button
            wire:click="$set('roleFilter', 'admin')"
            class="rounded-full px-3 py-1 text-xs font-medium transition {{ $roleFilter === 'admin' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300' }}"
        >
            {{ __('Admins') }} ({{ $this->roleCounts['admin'] }})
        </button>
    </div>

    {{-- Search --}}
    <div class="max-w-sm">
        <flux:input
            wire:model.live.debounce.300ms="search"
            placeholder="{{ __('Search by name or email...') }}"
            icon="magnifying-glass"
        />
    </div>

    {{-- Users Table --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">{{ __('User') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">{{ __('Role') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">{{ __('Contact') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">{{ __('Joined') }}</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($users as $user)
                    <tr wire:key="user-{{ $user->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-zinc-200 text-sm font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                    {{ $user->initials() }}
                                </div>
                                <div>
                                    <p class="font-medium text-zinc-900 dark:text-white">{{ $user->name }}</p>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @php $roleName = $user->roles->first()?->name; @endphp
                            <flux:badge size="sm" :color="match($roleName) {
                                'admin' => 'purple',
                                'doctor' => 'blue',
                                'nurse' => 'green',
                                'cashier' => 'amber',
                                default => 'zinc'
                            }">
                                {{ ucfirst($roleName ?? 'No role') }}
                            </flux:badge>
                            @if($roleName === 'doctor' && $user->consultationTypes->isNotEmpty())
                                <div class="mt-1 flex flex-wrap gap-1">
                                    @foreach($user->consultationTypes as $ct)
                                        <span class="inline-flex items-center rounded bg-blue-100 px-1.5 py-0.5 text-[10px] font-medium text-blue-700 dark:bg-blue-900/50 dark:text-blue-300">
                                            {{ $ct->code ?? $ct->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                {{ $user->personalInformation?->phone_number ?? '-' }}
                            </p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $user->created_at->format('M d, Y') }}</p>
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($statusFilter === 'active')
                                <div class="flex justify-end gap-1">
                                    <flux:button wire:click="openEditModal({{ $user->id }})" variant="ghost" size="sm" icon="pencil-square" />
                                    <flux:button wire:click="openDeleteModal({{ $user->id }})" variant="ghost" size="sm" icon="trash" class="text-destructive hover:text-destructive/80" />
                                </div>
                            @else
                                <flux:button wire:click="restoreUser({{ $user->id }})" variant="ghost" size="sm" icon="arrow-path">
                                    {{ __('Restore') }}
                                </flux:button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center">
                            <img src="{{ asset('images/illustrations/empty-records.svg') }}" alt="" class="mx-auto h-24 w-24 opacity-60" />
                            <p class="mt-4 text-sm text-zinc-500 dark:text-zinc-400">{{ __('No users found') }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($users->hasPages())
        <div class="mt-4">
            {{ $users->links() }}
        </div>
    @endif

    {{-- Create/Edit User Modal --}}
    <flux:modal wire:model="showUserModal" class="max-w-2xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingUserId ? __('Edit User') : __('Create User') }}</flux:heading>
                <flux:text class="mt-1">{{ $editingUserId ? __('Update user information.') : __('Add a new user to the system.') }}</flux:text>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                {{-- Account Information --}}
                <div class="sm:col-span-2">
                    <p class="mb-3 text-xs font-medium uppercase text-zinc-500">{{ __('Account Information') }}</p>
                </div>

                <flux:field>
                    <flux:label>{{ __('Email') }} <span class="text-destructive">*</span></flux:label>
                    <flux:input wire:model="email" type="email" placeholder="user@example.com" />
                    <flux:error name="email" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Role') }} <span class="text-destructive">*</span></flux:label>
                    <flux:select wire:model.live="role">
                        @foreach($this->roles as $r)
                            <flux:select.option value="{{ $r->name }}">{{ ucfirst($r->name) }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="role" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Password') }} @unless($editingUserId)<span class="text-destructive">*</span>@endunless</flux:label>
                    <flux:input wire:model="password" type="password" placeholder="{{ $editingUserId ? __('Leave blank to keep current') : '' }}" />
                    <flux:error name="password" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Confirm Password') }} @unless($editingUserId)<span class="text-destructive">*</span>@endunless</flux:label>
                    <flux:input wire:model="passwordConfirmation" type="password" />
                    <flux:error name="passwordConfirmation" />
                </flux:field>

                {{-- Personal Information --}}
                <div class="sm:col-span-2 mt-2">
                    <p class="mb-3 text-xs font-medium uppercase text-zinc-500">{{ __('Personal Information') }}</p>
                </div>

                <flux:field>
                    <flux:label>{{ __('First Name') }} <span class="text-destructive">*</span></flux:label>
                    <flux:input wire:model="firstName" placeholder="Juan" />
                    <flux:error name="firstName" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Last Name') }} <span class="text-destructive">*</span></flux:label>
                    <flux:input wire:model="lastName" placeholder="Dela Cruz" />
                    <flux:error name="lastName" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Middle Name') }}</flux:label>
                    <flux:input wire:model="middleName" placeholder="Santos" />
                    <flux:error name="middleName" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Phone Number') }}</flux:label>
                    <flux:input wire:model="phoneNumber" placeholder="09171234567" />
                    <flux:error name="phoneNumber" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Date of Birth') }}</flux:label>
                    <flux:input wire:model="dateOfBirth" type="date" />
                    <flux:error name="dateOfBirth" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Gender') }}</flux:label>
                    <flux:select wire:model="gender">
                        <flux:select.option value="">{{ __('Select...') }}</flux:select.option>
                        <flux:select.option value="male">{{ __('Male') }}</flux:select.option>
                        <flux:select.option value="female">{{ __('Female') }}</flux:select.option>
                    </flux:select>
                    <flux:error name="gender" />
                </flux:field>

                {{-- Doctor Specific: Consultation Types --}}
                @if($role === 'doctor')
                    <div class="sm:col-span-2 mt-2">
                        <p class="mb-3 text-xs font-medium uppercase text-zinc-500">{{ __('Doctor Settings') }}</p>
                    </div>

                    <flux:field class="sm:col-span-2">
                        <flux:label>{{ __('Consultation Types') }} <span class="text-destructive">*</span></flux:label>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($this->consultationTypes as $ct)
                                <label class="cursor-pointer">
                                    <input type="checkbox" wire:model="selectedConsultationTypes" value="{{ $ct->id }}" class="peer sr-only">
                                    <span class="flex items-center gap-2 rounded-lg border px-3 py-2 text-sm transition peer-checked:border-zinc-900 peer-checked:bg-zinc-100 dark:peer-checked:border-white dark:peer-checked:bg-zinc-800 {{ in_array($ct->id, $selectedConsultationTypes) ? 'border-zinc-900 bg-zinc-100 dark:border-white dark:bg-zinc-800' : 'border-zinc-200 hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800' }}">
                                        {{ $ct->name }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <flux:error name="selectedConsultationTypes" />
                    </flux:field>
                @endif
            </div>

            <div class="flex justify-end gap-3 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <flux:button wire:click="closeUserModal" variant="ghost">{{ __('Cancel') }}</flux:button>
                <flux:button wire:click="saveUser" variant="primary">
                    {{ $editingUserId ? __('Update User') : __('Create User') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Delete Confirmation Modal --}}
    <flux:modal wire:model="showDeleteModal" class="max-w-md">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">{{ __('Deactivate User') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Are you sure you want to deactivate this user? They will no longer be able to access the system.') }}</flux:text>
            </div>

            <div class="flex justify-end gap-3">
                <flux:button wire:click="closeDeleteModal" variant="ghost">{{ __('Cancel') }}</flux:button>
                <flux:button wire:click="deleteUser" variant="danger">{{ __('Deactivate') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</section>
