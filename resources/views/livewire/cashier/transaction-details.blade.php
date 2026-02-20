<section class="space-y-4">
    {{-- Header (hidden when printing) --}}
    <div class="flex flex-col gap-3 print:hidden sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <flux:button href="{{ route('cashier.history') }}" wire:navigate variant="ghost" size="sm" icon="arrow-left" />
                <flux:heading size="xl" level="1">{{ __('Transaction Details') }}</flux:heading>
            </div>
            <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                {{ $this->transaction->transaction_number }}
            </flux:text>
        </div>
        <div class="flex gap-2">
            <flux:button wire:click="printReceipt" variant="ghost" icon="printer">
                {{ __('Print Receipt') }}
            </flux:button>
            <flux:button href="{{ route('cashier.history') }}" wire:navigate variant="primary" icon="arrow-left">
                {{ __('Back to History') }}
            </flux:button>
        </div>
    </div>

    <div class="grid gap-4 print:hidden lg:grid-cols-3">
        {{-- Left Column: Transaction & Patient Info (hidden when printing) --}}
        <div class="space-y-4 lg:col-span-2">
            {{-- Transaction Info --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="sm" class="mb-3">{{ __('Transaction Information') }}</flux:heading>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Transaction Number') }}</p>
                        <p class="font-mono font-medium text-zinc-900 dark:text-white">{{ $this->transaction->transaction_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Date & Time') }}</p>
                        <p class="font-medium text-zinc-900 dark:text-white">{{ $this->transaction->created_at->format('F d, Y h:i A') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Payment Method') }}</p>
                        <flux:badge size="sm" color="zinc">{{ ucfirst(str_replace('_', ' ', $this->transaction->payment_method)) }}</flux:badge>
                    </div>
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Payment Status') }}</p>
                        <flux:badge size="sm" color="{{ $this->transaction->payment_status === 'paid' ? 'green' : 'yellow' }}">
                            {{ ucfirst($this->transaction->payment_status) }}
                        </flux:badge>
                    </div>
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Processed By') }}</p>
                        <p class="font-medium text-zinc-900 dark:text-white">
                            {{ $this->transaction->processedBy?->first_name }} {{ $this->transaction->processedBy?->middle_name }} {{ $this->transaction->processedBy?->last_name }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Medical Record') }}</p>
                        <p class="font-medium text-zinc-900 dark:text-white">{{ $this->transaction->medicalRecord?->record_number }}</p>
                    </div>
                </div>
            </div>

            {{-- Patient Info --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="sm" class="mb-3">{{ __('Patient Information') }}</flux:heading>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Patient Name') }}</p>
                        <p class="font-medium text-zinc-900 dark:text-white">
                            {{ $this->transaction->medicalRecord?->patient_first_name }}
                            {{ $this->transaction->medicalRecord?->patient_middle_name }}
                            {{ $this->transaction->medicalRecord?->patient_last_name }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Account Holder') }}</p>
                        <p class="font-medium text-zinc-900 dark:text-white">
                            {{ $this->transaction->user?->first_name }} {{ $this->transaction->user?->last_name }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Consultation Type') }}</p>
                        <p class="font-medium text-zinc-900 dark:text-white">{{ $this->transaction->medicalRecord?->consultationType?->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Doctor') }}</p>
                        <p class="font-medium text-zinc-900 dark:text-white">
                            Dr. {{ $this->transaction->medicalRecord?->doctor?->first_name }}
                            {{ $this->transaction->medicalRecord?->doctor?->last_name }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Billing Items --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="sm" class="mb-3">{{ __('Billing Items') }}</flux:heading>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                <th class="pb-2 text-left font-medium text-zinc-500 dark:text-zinc-400">{{ __('Description') }}</th>
                                <th class="pb-2 text-center font-medium text-zinc-500 dark:text-zinc-400">{{ __('Qty') }}</th>
                                <th class="pb-2 text-right font-medium text-zinc-500 dark:text-zinc-400">{{ __('Unit Price') }}</th>
                                <th class="pb-2 text-right font-medium text-zinc-500 dark:text-zinc-400">{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach($this->transaction->billingItems as $item)
                                <tr wire:key="item-{{ $item->id }}">
                                    <td class="py-2">
                                        <p class="text-zinc-900 dark:text-white">{{ $item->item_description }}</p>
                                        <p class="text-xs text-zinc-500">{{ ucfirst(str_replace('_', ' ', $item->item_type)) }}</p>
                                    </td>
                                    <td class="py-2 text-center text-zinc-600 dark:text-zinc-400">{{ $item->quantity }}</td>
                                    <td class="py-2 text-right text-zinc-600 dark:text-zinc-400">{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="py-2 text-right font-medium text-zinc-900 dark:text-white">{{ number_format($item->total_price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Notes --}}
            @if($this->transaction->notes)
                <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:heading size="sm" class="mb-2">{{ __('Notes') }}</flux:heading>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $this->transaction->notes }}</p>
                </div>
            @endif
        </div>

        {{-- Right Column: Payment Summary --}}
        <div class="space-y-4">
            {{-- Payment Summary --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="sm" class="mb-3">{{ __('Payment Summary') }}</flux:heading>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-zinc-500 dark:text-zinc-400">{{ __('Subtotal') }}</span>
                        <span class="text-zinc-900 dark:text-white">{{ number_format($this->transaction->subtotal, 2) }}</span>
                    </div>

                    @if($this->transaction->emergency_fee > 0)
                        <div class="flex justify-between">
                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Special Charges') }}</span>
                            <span class="text-zinc-900 dark:text-white">{{ number_format($this->transaction->emergency_fee, 2) }}</span>
                        </div>
                        <div class="ml-4 space-y-1 text-xs text-zinc-500">
                            @if($this->transaction->is_emergency)
                                <p>+ Emergency Fee</p>
                            @endif
                            @if($this->transaction->is_holiday)
                                <p>+ Holiday Fee</p>
                            @endif
                            @if($this->transaction->is_sunday)
                                <p>+ Sunday Fee</p>
                            @endif
                            @if($this->transaction->is_after_5pm)
                                <p>+ After 5PM Fee</p>
                            @endif
                        </div>
                    @endif

                    @if($this->transaction->discount_amount > 0)
                        <div class="flex justify-between">
                            <span class="text-zinc-500 dark:text-zinc-400">
                                {{ __('Discount') }}
                                <span class="text-xs">({{ ucfirst($this->transaction->discount_type) }})</span>
                            </span>
                            <span class="text-red-600 dark:text-red-400">-{{ number_format($this->transaction->discount_amount, 2) }}</span>
                        </div>
                        @if($this->transaction->discount_reason)
                            <p class="ml-4 text-xs text-zinc-500">{{ $this->transaction->discount_reason }}</p>
                        @endif
                    @endif

                    <div class="border-t border-zinc-200 pt-2 dark:border-zinc-700">
                        <div class="flex justify-between">
                            <span class="font-semibold text-zinc-900 dark:text-white">{{ __('Total Amount') }}</span>
                            <span class="text-xl font-bold text-zinc-900 dark:text-white">{{ number_format($this->transaction->total_amount, 2) }}</span>
                        </div>
                    </div>

                    <div class="border-t border-zinc-200 pt-2 dark:border-zinc-700">
                        <div class="flex justify-between">
                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Amount Paid') }}</span>
                            <span class="font-medium text-zinc-900 dark:text-white">{{ number_format($this->transaction->amount_paid, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Change') }}</span>
                            <span class="font-medium text-zinc-900 dark:text-white">{{ number_format($this->transaction->amount_paid - $this->transaction->total_amount, 2) }}</span>
                        </div>
                        @if($this->transaction->balance > 0)
                            <div class="flex justify-between">
                                <span class="text-zinc-500 dark:text-zinc-400">{{ __('Balance') }}</span>
                                <span class="font-medium text-red-600 dark:text-red-400">{{ number_format($this->transaction->balance, 2) }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Timestamps --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="sm" class="mb-3">{{ __('Timeline') }}</flux:heading>

                <div class="space-y-2 text-sm">
                    @if($this->transaction->received_in_billing_at)
                        <div class="flex justify-between">
                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Received') }}</span>
                            <span class="text-zinc-900 dark:text-white">{{ $this->transaction->received_in_billing_at->format('h:i A') }}</span>
                        </div>
                    @endif
                    @if($this->transaction->ended_in_billing_at)
                        <div class="flex justify-between">
                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Completed') }}</span>
                            <span class="text-zinc-900 dark:text-white">{{ $this->transaction->ended_in_billing_at->format('h:i A') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="sm" class="mb-3">{{ __('Actions') }}</flux:heading>

                <div class="space-y-2">
                    <flux:button wire:click="printReceipt" variant="primary" class="w-full" icon="printer">
                        {{ __('Print Receipt') }}
                    </flux:button>
                </div>
            </div>
        </div>
    </div>

    {{-- Printable Receipt (Hidden on screen, shown only when printing) --}}
    <div id="receipt-content" class="hidden print:block print:absolute print:inset-0 print:bg-white">
        <div class="mx-auto max-w-xs p-4 font-mono text-xs text-black">
            <div class="mb-4 border-b border-dashed pb-4 text-center">
                <p class="text-lg font-bold">{{ config('app.name') }}</p>
                <p class="text-xs">{{ __('Official Receipt') }}</p>
            </div>

            <div class="mb-3 space-y-1">
                <div class="flex justify-between">
                    <span>{{ __('Transaction') }}</span>
                    <span>{{ $this->transaction->transaction_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span>{{ __('Date') }}</span>
                    <span>{{ $this->transaction->created_at->format('M d, Y h:i A') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>{{ __('Patient') }}</span>
                    <span>{{ $this->transaction->medicalRecord?->patient_full_name }}</span>
                </div>
            </div>

            <div class="mb-3 border-t border-dashed pt-3">
                @foreach($this->transaction->billingItems as $item)
                    <div class="flex justify-between">
                        <span>
                            {{ Str::limit($item->item_description, 25) }}
                            @if($item->quantity > 1) x{{ $item->quantity }} @endif
                        </span>
                        <span>{{ number_format($item->total_price, 2) }}</span>
                    </div>
                @endforeach
            </div>

            <div class="border-t border-dashed pt-3">
                <div class="flex justify-between">
                    <span>{{ __('Subtotal') }}</span>
                    <span>{{ number_format($this->transaction->subtotal, 2) }}</span>
                </div>
                @if($this->transaction->emergency_fee > 0)
                    <div class="flex justify-between">
                        <span>{{ __('Special Charges') }}</span>
                        <span>{{ number_format($this->transaction->emergency_fee, 2) }}</span>
                    </div>
                @endif
                @if($this->transaction->discount_amount > 0)
                    <div class="flex justify-between">
                        <span>{{ __('Discount') }}</span>
                        <span>-{{ number_format($this->transaction->discount_amount, 2) }}</span>
                    </div>
                @endif
                <div class="mt-2 flex justify-between border-t pt-2 font-bold">
                    <span>{{ __('Total') }}</span>
                    <span>{{ number_format($this->transaction->total_amount, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>{{ __('Amount Paid') }}</span>
                    <span>{{ number_format($this->transaction->amount_paid, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>{{ __('Change') }}</span>
                    <span>{{ number_format($this->transaction->amount_paid - $this->transaction->total_amount, 2) }}</span>
                </div>
            </div>

            <div class="mt-4 border-t border-dashed pt-3 text-center">
                <p>{{ __('Thank you for your visit!') }}</p>
                <p class="text-xs">{{ __('Processed by') }}: {{ $this->transaction->processedBy?->first_name }} {{ $this->transaction->processedBy?->middle_name }} {{ $this->transaction->processedBy?->last_name }}</p>
            </div>
        </div>
    </div>
    @script
    <script>
        $wire.on('print-receipt', () => {
            window.print();
        });
    </script>
    @endscript

    <style>
        @media print {
            /* Hide all layout elements */
            nav, header, aside, footer,
            [data-flux-sidebar], [data-flux-navbar],
            .flux-sidebar, .flux-navbar,
            [x-data*="sidebar"], [x-data*="navbar"] {
                display: none !important;
            }

            /* Reset page margins */
            body {
                margin: 0 !important;
                padding: 0 !important;
            }

            /* Ensure receipt is visible and positioned correctly */
            #receipt-content {
                display: block !important;
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                width: 100% !important;
                height: auto !important;
                background: white !important;
                z-index: 9999 !important;
            }

            /* Hide everything else in main content */
            main > *:not(#receipt-content) {
                display: none !important;
            }
        }
    </style>
</section>
