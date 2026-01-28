<div class="flex h-full w-full flex-1 flex-col gap-6 p-6 overflow-auto">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">{{ __('Hospital Drug Management') }}</flux:heading>
            <flux:text class="text-zinc-500">{{ __('Manage hospital pharmacy drugs and pricing') }}</flux:text>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="openCreateModal">
            {{ __('Add Drug') }}
        </flux:button>
    </div>

    {{-- Search --}}
    <div class="flex-1 max-w-md">
        <flux:input
            wire:model.live.debounce.300ms="search"
            placeholder="{{ __('Search drugs...') }}"
            icon="magnifying-glass"
        />
    </div>

    {{-- Drugs Table --}}
    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-zinc-700 dark:text-zinc-300">{{ __('Drug Name') }}</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-700 dark:text-zinc-300">{{ __('Generic Name') }}</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-700 dark:text-zinc-300">{{ __('Form / Strength') }}</th>
                        <th class="px-4 py-3 text-right font-medium text-zinc-700 dark:text-zinc-300">{{ __('Unit Price') }}</th>
                        <th class="px-4 py-3 text-center font-medium text-zinc-700 dark:text-zinc-300">{{ __('Stock') }}</th>
                        <th class="px-4 py-3 text-center font-medium text-zinc-700 dark:text-zinc-300">{{ __('Status') }}</th>
                        <th class="px-4 py-3 text-center font-medium text-zinc-700 dark:text-zinc-300">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($drugs as $drug)
                        <tr wire:key="drug-{{ $drug->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                            <td class="px-4 py-3 font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $drug->drug_name }}
                            </td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                {{ $drug->generic_name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                @if($drug->dosage_form || $drug->strength)
                                    {{ $drug->dosage_form }} {{ $drug->strength }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-mono">
                                ₱{{ number_format($drug->unit_price, 2) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="{{ $drug->stock_quantity <= 10 ? 'text-destructive font-medium' : '' }}">
                                    {{ $drug->stock_quantity ?? 0 }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <flux:badge size="sm" :variant="$drug->is_active ? 'success' : 'danger'">
                                    {{ $drug->is_active ? __('Active') : __('Inactive') }}
                                </flux:badge>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <flux:button size="xs" variant="ghost" icon="pencil" wire:click="openEditModal({{ $drug->id }})" title="{{ __('Edit') }}" />
                                    <flux:button size="xs" variant="ghost" icon="{{ $drug->is_active ? 'eye-slash' : 'eye' }}" wire:click="toggleActive({{ $drug->id }})" title="{{ $drug->is_active ? __('Deactivate') : __('Activate') }}" />
                                    <flux:button size="xs" variant="ghost" icon="trash" wire:click="delete({{ $drug->id }})" wire:confirm="{{ __('Are you sure you want to delete this drug?') }}" title="{{ __('Delete') }}" class="text-destructive hover:text-destructive/80" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-zinc-500">
                                {{ __('No drugs found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if($drugs->hasPages())
        <div class="mt-4">
            {{ $drugs->links() }}
        </div>
    @endif

    {{-- Create/Edit Modal --}}
    <flux:modal wire:model="showModal" class="max-w-lg">
        <div class="space-y-6">
            <flux:heading size="lg">
                {{ $isEditing ? __('Edit Drug') : __('Add Drug') }}
            </flux:heading>

            <form wire:submit="save" class="space-y-4">
                <flux:input
                    wire:model="drugName"
                    label="{{ __('Drug Name') }}"
                    placeholder="{{ __('e.g., Paracetamol 500mg') }}"
                    required
                />

                <flux:input
                    wire:model="genericName"
                    label="{{ __('Generic Name') }}"
                    placeholder="{{ __('e.g., Paracetamol') }}"
                />

                <div class="grid grid-cols-2 gap-4">
                    <flux:input
                        wire:model="dosageForm"
                        label="{{ __('Dosage Form') }}"
                        placeholder="{{ __('e.g., Tablet, Capsule, Syrup') }}"
                    />

                    <flux:input
                        wire:model="strength"
                        label="{{ __('Strength') }}"
                        placeholder="{{ __('e.g., 500mg, 250mg/5ml') }}"
                    />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <flux:input
                        wire:model="unitPrice"
                        type="number"
                        step="0.01"
                        min="0"
                        label="{{ __('Unit Price (₱)') }}"
                        placeholder="0.00"
                        required
                    />

                    <flux:input
                        wire:model="stockQuantity"
                        type="number"
                        min="0"
                        label="{{ __('Stock Quantity') }}"
                        placeholder="0"
                    />
                </div>

                <flux:switch wire:model="isActive" label="{{ __('Active') }}" />

                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
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
