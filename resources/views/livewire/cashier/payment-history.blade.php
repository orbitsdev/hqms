<section class="space-y-4">
    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" level="1">{{ __('Payment History') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('View and manage past transactions') }}
            </flux:text>
        </div>
        <flux:button href="{{ route('cashier.queue') }}" wire:navigate variant="primary" icon="banknotes">
            {{ __('Billing Queue') }}
        </flux:button>
    </div>

    {{-- Today's Summary --}}
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __("Today's Transactions") }}</p>
            <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $this->todayCount }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __("Today's Collection") }}</p>
            <p class="text-2xl font-bold text-zinc-900 dark:text-white">₱{{ number_format($this->todayTotal, 2) }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
        <div class="flex-1">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('Search transaction or patient...') }}"
                icon="magnifying-glass"
            />
        </div>

        <flux:select wire:model.live="dateFilter" class="w-full sm:w-40">
            <flux:select.option value="today">{{ __('Today') }}</flux:select.option>
            <flux:select.option value="week">{{ __('This Week') }}</flux:select.option>
            <flux:select.option value="month">{{ __('This Month') }}</flux:select.option>
            <flux:select.option value="custom">{{ __('Custom') }}</flux:select.option>
        </flux:select>

        <flux:select wire:model.live="paymentMethodFilter" class="w-full sm:w-40">
            <flux:select.option value="">{{ __('All Methods') }}</flux:select.option>
            <flux:select.option value="cash">{{ __('Cash') }}</flux:select.option>
            <flux:select.option value="gcash">{{ __('GCash') }}</flux:select.option>
            <flux:select.option value="card">{{ __('Card') }}</flux:select.option>
            <flux:select.option value="bank_transfer">{{ __('Bank Transfer') }}</flux:select.option>
            <flux:select.option value="philhealth">{{ __('PhilHealth') }}</flux:select.option>
        </flux:select>
    </div>

    @if($dateFilter === 'custom')
        <div class="flex gap-3">
            <flux:input wire:model.live="startDate" type="date" label="{{ __('From') }}" />
            <flux:input wire:model.live="endDate" type="date" label="{{ __('To') }}" />
        </div>
    @endif

    {{-- Transactions Table --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">{{ __('Transaction') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">{{ __('Patient') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">{{ __('Method') }}</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">{{ __('Amount') }}</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($transactions as $transaction)
                    <tr wire:key="txn-{{ $transaction->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        <td class="px-4 py-3">
                            <p class="font-mono text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $transaction->transaction_number }}
                            </p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $transaction->created_at->format('M d, Y h:i A') }}
                            </p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-sm text-zinc-900 dark:text-white">
                                {{ $transaction->medicalRecord?->patient_first_name }}
                                {{ $transaction->medicalRecord?->patient_last_name }}
                            </p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $transaction->medicalRecord?->record_number }}
                            </p>
                        </td>
                        <td class="px-4 py-3">
                            <flux:badge size="sm" color="zinc">
                                {{ ucfirst(str_replace('_', ' ', $transaction->payment_method)) }}
                            </flux:badge>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <p class="font-semibold text-zinc-900 dark:text-white">₱{{ number_format($transaction->total_amount, 2) }}</p>
                            @if($transaction->discount_amount > 0)
                                <p class="text-xs text-zinc-500">{{ ucfirst($transaction->discount_type) }} discount</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-1">
                                <flux:button wire:click="viewTransaction({{ $transaction->id }})" variant="ghost" size="sm" icon="eye" />
                                <flux:button wire:click="printReceipt({{ $transaction->id }})" variant="ghost" size="sm" icon="printer" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center">
                            <img src="{{ asset('images/illustrations/empty-records.svg') }}" alt="" class="mx-auto h-24 w-24 opacity-60" />
                            <p class="mt-4 text-sm text-zinc-500 dark:text-zinc-400">{{ __('No transactions found') }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($transactions->hasPages())
        <div class="mt-4">
            {{ $transactions->links() }}
        </div>
    @endif

    {{-- Transaction Detail Modal --}}
    <flux:modal wire:model="showTransactionModal" class="max-w-lg">
        @if($selectedTransaction)
            <div class="space-y-4">
                <flux:heading size="lg">{{ __('Transaction Details') }}</flux:heading>

                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <div class="mb-4 grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-zinc-500 dark:text-zinc-400">{{ __('Transaction #') }}</p>
                            <p class="font-mono font-medium text-zinc-900 dark:text-white">{{ $selectedTransaction->transaction_number }}</p>
                        </div>
                        <div>
                            <p class="text-zinc-500 dark:text-zinc-400">{{ __('Date') }}</p>
                            <p class="font-medium text-zinc-900 dark:text-white">{{ $selectedTransaction->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                        <div>
                            <p class="text-zinc-500 dark:text-zinc-400">{{ __('Patient') }}</p>
                            <p class="font-medium text-zinc-900 dark:text-white">
                                {{ $selectedTransaction->medicalRecord?->patient_first_name }}
                                {{ $selectedTransaction->medicalRecord?->patient_last_name }}
                            </p>
                        </div>
                        <div>
                            <p class="text-zinc-500 dark:text-zinc-400">{{ __('Processed By') }}</p>
                            <p class="font-medium text-zinc-900 dark:text-white">{{ $selectedTransaction->processedBy?->first_name }} {{ $selectedTransaction->processedBy?->last_name }}</p>
                        </div>
                    </div>

                    <div class="border-t border-zinc-200 pt-4 dark:border-zinc-700">
                        <p class="mb-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Items') }}</p>
                        <div class="space-y-1">
                            @foreach($selectedTransaction->billingItems as $item)
                                <div class="flex justify-between text-sm">
                                    <span class="text-zinc-600 dark:text-zinc-400">
                                        {{ $item->item_description }}
                                        @if($item->quantity > 1) <span class="text-zinc-400">x{{ $item->quantity }}</span> @endif
                                    </span>
                                    <span class="text-zinc-900 dark:text-white">₱{{ number_format($item->total_price, 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-4 space-y-1 border-t border-zinc-200 pt-4 text-sm dark:border-zinc-700">
                        <div class="flex justify-between">
                            <span class="text-zinc-500">{{ __('Subtotal') }}</span>
                            <span>₱{{ number_format($selectedTransaction->subtotal, 2) }}</span>
                        </div>
                        @if($selectedTransaction->emergency_fee > 0)
                            <div class="flex justify-between">
                                <span class="text-zinc-500">{{ __('Special Charges') }}</span>
                                <span>₱{{ number_format($selectedTransaction->emergency_fee, 2) }}</span>
                            </div>
                        @endif
                        @if($selectedTransaction->discount_amount > 0)
                            <div class="flex justify-between">
                                <span class="text-zinc-500">{{ __('Discount') }} ({{ ucfirst($selectedTransaction->discount_type) }})</span>
                                <span class="text-destructive">-₱{{ number_format($selectedTransaction->discount_amount, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between border-t border-zinc-200 pt-2 font-bold dark:border-zinc-700">
                            <span>{{ __('Total') }}</span>
                            <span>₱{{ number_format($selectedTransaction->total_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-500">{{ __('Payment Method') }}</span>
                            <span>{{ ucfirst(str_replace('_', ' ', $selectedTransaction->payment_method)) }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <flux:button wire:click="closeTransactionModal" variant="ghost">{{ __('Close') }}</flux:button>
                    <flux:button wire:click="printReceipt({{ $selectedTransaction->id }})" variant="primary" icon="printer">
                        {{ __('Print Receipt') }}
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</section>

@script
<script>
    $wire.on('print-receipt', () => {
        // Get receipt content from the modal if available
        setTimeout(() => window.print(), 100);
    });
</script>
@endscript
