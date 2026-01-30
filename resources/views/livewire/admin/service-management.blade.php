<div class="flex h-full w-full flex-1 flex-col gap-6 overflow-auto p-6">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">{{ __('Service & Fee Management') }}</flux:heading>
            <flux:text class="text-zinc-500">{{ __('Manage services, professional fees, and pricing') }}</flux:text>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="openCreateModal">
            {{ __('Add Service') }}
        </flux:button>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col gap-4 sm:flex-row">
        <div class="flex-1">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('Search services...') }}"
                icon="magnifying-glass"
            />
        </div>
        <flux:select wire:model.live="categoryFilter" class="sm:w-64">
            <option value="">{{ __('All Categories') }}</option>
            @foreach($this->categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
        </flux:select>
    </div>

    {{-- Services Table --}}
    <div class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-zinc-700 dark:text-zinc-300">{{ __('Service Name') }}</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-700 dark:text-zinc-300">{{ __('Category') }}</th>
                        <th class="px-4 py-3 text-right font-medium text-zinc-700 dark:text-zinc-300">{{ __('Price') }}</th>
                        <th class="px-4 py-3 text-center font-medium text-zinc-700 dark:text-zinc-300">{{ __('Status') }}</th>
                        <th class="px-4 py-3 text-center font-medium text-zinc-700 dark:text-zinc-300">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($services as $service)
                        <tr wire:key="service-{{ $service->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                            <td class="px-4 py-3">
                                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $service->service_name }}</div>
                                @if($service->description)
                                    <div class="max-w-xs truncate text-xs text-zinc-500">{{ $service->description }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $categoryCode = $service->serviceCategory?->code ?? $service->category;
                                @endphp
                                <flux:badge size="sm" :color="match($categoryCode) {
                                    'consultation' => 'blue',
                                    'ultrasound' => 'cyan',
                                    'procedure' => 'amber',
                                    'laboratory' => 'green',
                                    default => 'zinc'
                                }">
                                    {{ $service->serviceCategory?->name ?? ucfirst($service->category) }}
                                </flux:badge>
                            </td>
                            <td class="px-4 py-3 text-right font-mono">
                                P{{ number_format($service->base_price, 2) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($service->is_active)
                                    <flux:badge size="sm" color="green">{{ __('Active') }}</flux:badge>
                                @else
                                    <flux:badge size="sm" color="red">{{ __('Inactive') }}</flux:badge>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <flux:button size="xs" variant="ghost" icon="pencil" wire:click="openEditModal({{ $service->id }})" title="{{ __('Edit') }}" />
                                    <flux:button size="xs" variant="ghost" icon="{{ $service->is_active ? 'eye-slash' : 'eye' }}" wire:click="toggleActive({{ $service->id }})" title="{{ $service->is_active ? __('Deactivate') : __('Activate') }}" />
                                    <flux:button size="xs" variant="ghost" icon="trash" wire:click="delete({{ $service->id }})" wire:confirm="{{ __('Are you sure you want to delete this service?') }}" title="{{ __('Delete') }}" class="text-destructive hover:text-destructive/80" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <flux:icon name="document-plus" class="mx-auto h-10 w-10 text-zinc-300 dark:text-zinc-600" />
                                <p class="mt-2 text-zinc-500">{{ __('No services found.') }}</p>
                                <flux:button wire:click="openCreateModal" variant="ghost" size="sm" class="mt-3">
                                    {{ __('Add your first service') }}
                                </flux:button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if($services->hasPages())
        <div class="mt-4">
            {{ $services->links() }}
        </div>
    @endif

    {{-- Create/Edit Modal --}}
    <flux:modal wire:model="showModal" class="max-w-lg">
        <div class="space-y-6">
            <flux:heading size="lg">
                {{ $isEditing ? __('Edit Service') : __('Add Service') }}
            </flux:heading>

            <form wire:submit="save" class="space-y-4">
                <flux:input
                    wire:model="serviceName"
                    label="{{ __('Service Name') }}"
                    placeholder="{{ __('e.g., Professional Fee - OB') }}"
                    required
                />

                <flux:select wire:model="serviceCategoryId" label="{{ __('Category') }}" required>
                    @foreach($this->categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </flux:select>

                <flux:input
                    wire:model="basePrice"
                    type="number"
                    step="0.01"
                    min="0"
                    label="{{ __('Base Price (P)') }}"
                    placeholder="0.00"
                    required
                />

                <flux:textarea
                    wire:model="description"
                    label="{{ __('Description') }}"
                    placeholder="{{ __('Optional description...') }}"
                    rows="2"
                />

                <flux:input
                    wire:model="displayOrder"
                    type="number"
                    min="0"
                    label="{{ __('Display Order') }}"
                    placeholder="0"
                />

                <flux:switch wire:model="isActive" label="{{ __('Active') }}" />

                <div class="flex justify-end gap-3 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                    <flux:button variant="ghost" wire:click="closeModal">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $isEditing ? __('Update') : __('Create') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
