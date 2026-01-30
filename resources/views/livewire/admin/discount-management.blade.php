<div class="flex h-full w-full flex-1 flex-col gap-6 overflow-auto p-6">
    {{-- Header --}}
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">{{ __('Discount Management') }}</flux:heading>
            <flux:text class="text-zinc-500">{{ __('Manage discount types and percentages for billing') }}</flux:text>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="openCreateModal">
            {{ __('Add Discount') }}
        </flux:button>
    </div>

    {{-- Info Callout (expanded by default) --}}
    <details open class="group rounded-xl border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/50">
        <summary class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-medium text-zinc-700 dark:text-zinc-300">
            <div class="flex items-center gap-2">
                <flux:icon name="information-circle" class="h-5 w-5 text-zinc-500" />
                <span>{{ __('How Discounts Work') }}</span>
            </div>
            <flux:icon name="chevron-down" class="h-4 w-4 text-zinc-400 transition-transform group-open:rotate-180" />
        </summary>
        <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-700">
            <div class="grid gap-4 text-sm lg:grid-cols-2">
                <div>
                    <p class="mb-2 font-medium text-zinc-700 dark:text-zinc-300">{{ __('Discount Flow') }}</p>
                    <ul class="list-inside list-disc space-y-1 text-zinc-600 dark:text-zinc-400">
                        <li>{{ __('Doctor recommends a discount during examination') }}</li>
                        <li>{{ __('Cashier sees the recommendation and applies it') }}</li>
                        <li>{{ __('Percentage is calculated from the total amount') }}</li>
                    </ul>
                </div>
                <div>
                    <p class="mb-2 font-medium text-zinc-700 dark:text-zinc-300">{{ __('Where Discounts Appear') }}</p>
                    <ul class="list-inside list-disc space-y-1 text-zinc-600 dark:text-zinc-400">
                        <li><strong>{{ __('Doctor') }}</strong> → {{ __('Billing Adjustments') }}</li>
                        <li><strong>{{ __('Cashier') }}</strong> → {{ __('Process Billing') }}</li>
                        <li><strong>{{ __('Reports') }}</strong> → {{ __('Receipts & Statements') }}</li>
                    </ul>
                </div>
            </div>
            <p class="mt-3 text-xs text-zinc-500">
                <flux:icon name="shield-check" class="inline h-4 w-4" />
                {{ __('Senior Citizen and PWD discounts are mandated by Philippine law at 20%.') }}
            </p>
        </div>
    </details>

    {{-- Status Toggle & Search --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
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
        <div class="max-w-md flex-1">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('Search discounts...') }}"
                icon="magnifying-glass"
            />
        </div>
    </div>

    {{-- Discounts Table --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-zinc-700 dark:text-zinc-300">{{ __('Discount Name') }}</th>
                        <th class="px-4 py-3 text-left font-medium text-zinc-700 dark:text-zinc-300">{{ __('Code') }}</th>
                        <th class="px-4 py-3 text-right font-medium text-zinc-700 dark:text-zinc-300">{{ __('Percentage') }}</th>
                        <th class="px-4 py-3 text-center font-medium text-zinc-700 dark:text-zinc-300">{{ __('Order') }}</th>
                        <th class="px-4 py-3 text-center font-medium text-zinc-700 dark:text-zinc-300">{{ __('Status') }}</th>
                        <th class="px-4 py-3 text-center font-medium text-zinc-700 dark:text-zinc-300">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($discounts as $discount)
                        <tr wire:key="discount-{{ $discount->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                            <td class="px-4 py-3">
                                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $discount->name }}</div>
                                @if($discount->description)
                                    <div class="mt-0.5 max-w-xs truncate text-xs text-zinc-500">{{ $discount->description }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <code class="rounded bg-zinc-100 px-2 py-0.5 text-xs font-mono text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">{{ $discount->code }}</code>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="text-lg font-semibold {{ $discount->percentage > 0 ? 'text-success' : 'text-zinc-400' }}">
                                    {{ $discount->formatted_percentage }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-zinc-500">
                                {{ $discount->sort_order }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($discount->is_active)
                                    <flux:badge size="sm" color="green">{{ __('Active') }}</flux:badge>
                                @else
                                    <flux:badge size="sm" color="red">{{ __('Inactive') }}</flux:badge>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <flux:button size="xs" variant="ghost" icon="pencil" wire:click="openEditModal({{ $discount->id }})" title="{{ __('Edit') }}" />
                                    <flux:button size="xs" variant="ghost" icon="{{ $discount->is_active ? 'eye-slash' : 'eye' }}" wire:click="toggleActive({{ $discount->id }})" title="{{ $discount->is_active ? __('Deactivate') : __('Activate') }}" />
                                    <flux:button size="xs" variant="ghost" icon="trash" wire:click="delete({{ $discount->id }})" wire:confirm="{{ __('Are you sure you want to delete this discount?') }}" title="{{ __('Delete') }}" class="text-destructive hover:text-destructive/80" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <flux:icon name="receipt-percent" class="mx-auto h-10 w-10 text-zinc-300 dark:text-zinc-600" />
                                <p class="mt-2 text-zinc-500">{{ __('No discounts found.') }}</p>
                                <flux:button wire:click="openCreateModal" variant="ghost" size="sm" class="mt-3">
                                    {{ __('Add your first discount') }}
                                </flux:button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if($discounts->hasPages())
        <div class="mt-4">
            {{ $discounts->links() }}
        </div>
    @endif

    {{-- Create/Edit Modal --}}
    <flux:modal wire:model="showModal" class="max-w-lg">
        <div class="space-y-6">
            <flux:heading size="lg">
                {{ $isEditing ? __('Edit Discount') : __('Add Discount') }}
            </flux:heading>

            <form wire:submit="save" class="space-y-4">
                <flux:input
                    wire:model.live="name"
                    label="{{ __('Discount Name') }}"
                    placeholder="{{ __('Senior Citizen') }}"
                    required
                />

                <flux:input
                    wire:model="code"
                    label="{{ __('Code') }}"
                    placeholder="{{ __('senior-citizen') }}"
                    required
                    :disabled="$isEditing"
                    description="{{ __('Unique identifier (auto-generated from name)') }}"
                />

                <flux:input
                    wire:model="percentage"
                    type="number"
                    step="0.01"
                    min="0"
                    max="100"
                    label="{{ __('Discount Percentage') }}"
                    placeholder="20"
                    required
                    description="{{ __('Enter 20 for 20% discount') }}"
                />

                <flux:textarea
                    wire:model="description"
                    label="{{ __('Description') }}"
                    placeholder="{{ __('e.g., Philippine law mandates 20% discount for senior citizens') }}"
                    rows="2"
                />

                <div class="grid grid-cols-2 gap-4">
                    <flux:input
                        wire:model="sortOrder"
                        type="number"
                        min="0"
                        label="{{ __('Sort Order') }}"
                        placeholder="1"
                        description="{{ __('Lower = first') }}"
                    />

                    <div class="flex items-end pb-1">
                        <flux:switch wire:model="isActive" label="{{ __('Active') }}" />
                    </div>
                </div>

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
