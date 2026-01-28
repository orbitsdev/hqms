<section class="space-y-4">
    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <flux:button href="{{ route('cashier.queue') }}" wire:navigate variant="ghost" size="sm" icon="arrow-left" />
                <flux:heading size="xl" level="1">{{ __('Process Billing') }}</flux:heading>
            </div>
            <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                {{ $this->medicalRecord->record_number }}
            </flux:text>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        {{-- Left Column: Patient & Items --}}
        <div class="space-y-4 lg:col-span-2">
            {{-- Patient Information --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="sm" class="mb-3">{{ __('Patient Information') }}</flux:heading>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Name') }}</p>
                        <p class="font-medium text-zinc-900 dark:text-white">
                            {{ $this->medicalRecord->patient_first_name }}
                            {{ $this->medicalRecord->patient_middle_name }}
                            {{ $this->medicalRecord->patient_last_name }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Consultation Type') }}</p>
                        <p class="font-medium text-zinc-900 dark:text-white">{{ $this->medicalRecord->consultationType?->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Doctor') }}</p>
                        <p class="font-medium text-zinc-900 dark:text-white">Dr. {{ $this->medicalRecord->doctor?->first_name }} {{ $this->medicalRecord->doctor?->last_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Diagnosis') }}</p>
                        <p class="font-medium text-zinc-900 dark:text-white">{{ $this->medicalRecord->diagnosis ?? '-' }}</p>
                    </div>
                </div>
            </div>

            {{-- Billing Items --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-3 flex items-center justify-between">
                    <flux:heading size="sm">{{ __('Billing Items') }}</flux:heading>
                    <flux:button wire:click="openAddItemModal" variant="ghost" size="sm" icon="plus">
                        {{ __('Add Item') }}
                    </flux:button>
                </div>

                @if(count($billingItems) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                    <th class="pb-2 text-left font-medium text-zinc-500 dark:text-zinc-400">{{ __('Description') }}</th>
                                    <th class="pb-2 text-center font-medium text-zinc-500 dark:text-zinc-400">{{ __('Qty') }}</th>
                                    <th class="pb-2 text-right font-medium text-zinc-500 dark:text-zinc-400">{{ __('Price') }}</th>
                                    <th class="pb-2 text-right font-medium text-zinc-500 dark:text-zinc-400">{{ __('Total') }}</th>
                                    <th class="pb-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @foreach($billingItems as $index => $item)
                                    <tr wire:key="item-{{ $index }}">
                                        <td class="py-2">
                                            <p class="text-zinc-900 dark:text-white">{{ $item['description'] }}</p>
                                            <p class="text-xs text-zinc-500">{{ ucfirst(str_replace('_', ' ', $item['type'])) }}</p>
                                        </td>
                                        <td class="py-2 text-center">
                                            <input
                                                type="number"
                                                min="1"
                                                value="{{ $item['quantity'] }}"
                                                wire:change="updateItemQuantity({{ $index }}, $event.target.value)"
                                                class="w-16 rounded border border-zinc-200 bg-white px-2 py-1 text-center text-sm dark:border-zinc-700 dark:bg-zinc-800"
                                            />
                                        </td>
                                        <td class="py-2 text-right text-zinc-600 dark:text-zinc-400">
                                            ₱{{ number_format($item['unit_price'], 2) }}
                                        </td>
                                        <td class="py-2 text-right font-medium text-zinc-900 dark:text-white">
                                            ₱{{ number_format($item['total_price'], 2) }}
                                        </td>
                                        <td class="py-2 text-right">
                                            <flux:button wire:click="removeItem({{ $index }})" variant="ghost" size="sm" icon="x-mark" class="text-red-500 hover:text-red-600" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-8 text-center">
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('No items added yet') }}</p>
                    </div>
                @endif
            </div>

            {{-- Special Charges --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="sm" class="mb-3">{{ __('Special Charges') }}</flux:heading>

                <div class="flex flex-wrap gap-4">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model.live="isEmergency" class="rounded border-zinc-300 dark:border-zinc-600" />
                        <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ __('Emergency (+₱200)') }}</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model.live="isHoliday" class="rounded border-zinc-300 dark:border-zinc-600" />
                        <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ __('Holiday (+₱150)') }}</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model.live="isSunday" class="rounded border-zinc-300 dark:border-zinc-600" />
                        <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ __('Sunday (+₱100)') }}</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model.live="isAfter5pm" class="rounded border-zinc-300 dark:border-zinc-600" />
                        <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ __('After 5PM (+₱50)') }}</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Right Column: Summary --}}
        <div class="space-y-4">
            {{-- Discount --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="sm" class="mb-3">{{ __('Discount') }}</flux:heading>

                @if($this->medicalRecord->suggested_discount_type && $this->medicalRecord->suggested_discount_type !== 'none')
                    <div class="mb-3 rounded-lg bg-zinc-100 p-2 text-xs dark:bg-zinc-800">
                        <p class="text-zinc-600 dark:text-zinc-400">{{ __('Doctor suggested:') }}
                            <span class="font-medium text-zinc-900 dark:text-white">{{ ucfirst($this->medicalRecord->suggested_discount_type) }}</span>
                        </p>
                    </div>
                @endif

                <flux:select wire:model.live="discountType" class="mb-3">
                    <flux:select.option value="none">{{ __('No Discount') }}</flux:select.option>
                    <flux:select.option value="senior">{{ __('Senior Citizen (20%)') }}</flux:select.option>
                    <flux:select.option value="pwd">{{ __('PWD (20%)') }}</flux:select.option>
                    <flux:select.option value="employee">{{ __('Employee (50%)') }}</flux:select.option>
                    <flux:select.option value="family">{{ __('Family (10%)') }}</flux:select.option>
                    <flux:select.option value="other">{{ __('Other') }}</flux:select.option>
                </flux:select>

                @if($discountType === 'other')
                    <flux:input wire:model="discountPercent" type="number" min="0" max="100" placeholder="{{ __('Discount %') }}" class="mb-2" />
                @endif

                <flux:input wire:model="discountReason" placeholder="{{ __('Reason (optional)') }}" />
            </div>

            {{-- Payment Summary --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="sm" class="mb-3">{{ __('Payment Summary') }}</flux:heading>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-zinc-500 dark:text-zinc-400">{{ __('Subtotal') }}</span>
                        <span class="text-zinc-900 dark:text-white">₱{{ number_format($this->subtotal, 2) }}</span>
                    </div>

                    @if($this->totalEmergencyFee > 0)
                        <div class="flex justify-between">
                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Special Charges') }}</span>
                            <span class="text-zinc-900 dark:text-white">₱{{ number_format($this->totalEmergencyFee, 2) }}</span>
                        </div>
                    @endif

                    @if($this->discountAmount > 0)
                        <div class="flex justify-between">
                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Discount') }} ({{ $discountPercent }}%)</span>
                            <span class="text-red-600 dark:text-red-400">-₱{{ number_format($this->discountAmount, 2) }}</span>
                        </div>
                    @endif

                    <div class="border-t border-zinc-200 pt-2 dark:border-zinc-700">
                        <div class="flex justify-between">
                            <span class="font-semibold text-zinc-900 dark:text-white">{{ __('Total') }}</span>
                            <span class="text-xl font-bold text-zinc-900 dark:text-white">₱{{ number_format($this->totalAmount, 2) }}</span>
                        </div>
                    </div>
                </div>

                <flux:button wire:click="openPaymentModal" variant="primary" class="mt-4 w-full" icon="banknotes">
                    {{ __('Process Payment') }}
                </flux:button>
            </div>
        </div>
    </div>

    {{-- Add Item Modal --}}
    <flux:modal wire:model="showAddItemModal" class="max-w-md">
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('Add Billing Item') }}</flux:heading>

            <flux:field>
                <flux:label>{{ __('Item Type') }}</flux:label>
                <flux:select wire:model.live="itemType">
                    <flux:select.option value="service">{{ __('Service') }}</flux:select.option>
                    <flux:select.option value="drug">{{ __('Hospital Drug') }}</flux:select.option>
                    <flux:select.option value="procedure">{{ __('Procedure') }}</flux:select.option>
                    <flux:select.option value="other">{{ __('Other') }}</flux:select.option>
                </flux:select>
            </flux:field>

            @if($itemType === 'service')
                <flux:field>
                    <flux:label>{{ __('Service') }}</flux:label>
                    <flux:select wire:model.live="serviceId">
                        <flux:select.option value="">{{ __('Select service...') }}</flux:select.option>
                        @foreach($this->services as $service)
                            <flux:select.option value="{{ $service->id }}">
                                {{ $service->service_name }} - ₱{{ number_format($service->base_price, 2) }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>
            @elseif($itemType === 'drug')
                <flux:field>
                    <flux:label>{{ __('Drug') }}</flux:label>
                    <flux:select wire:model.live="drugId">
                        <flux:select.option value="">{{ __('Select drug...') }}</flux:select.option>
                        @foreach($this->hospitalDrugs as $drug)
                            <flux:select.option value="{{ $drug->id }}">
                                {{ $drug->drug_name }} - ₱{{ number_format($drug->unit_price, 2) }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>
            @else
                <flux:field>
                    <flux:label>{{ __('Description') }}</flux:label>
                    <flux:input wire:model="customDescription" placeholder="{{ __('Enter description...') }}" />
                    <flux:error name="customDescription" />
                </flux:field>
            @endif

            <div class="grid grid-cols-2 gap-3">
                <flux:field>
                    <flux:label>{{ __('Quantity') }}</flux:label>
                    <flux:input wire:model="itemQuantity" type="number" min="1" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Unit Price (₱)') }}</flux:label>
                    <flux:input wire:model="itemUnitPrice" type="number" step="0.01" min="0" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-3 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <flux:button wire:click="closeAddItemModal" variant="ghost">{{ __('Cancel') }}</flux:button>
                <flux:button wire:click="addItem" variant="primary">{{ __('Add Item') }}</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Payment Modal --}}
    <flux:modal wire:model="showPaymentModal" class="max-w-md">
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('Process Payment') }}</flux:heading>

            <div class="rounded-lg bg-zinc-100 p-4 text-center dark:bg-zinc-800">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Total Amount') }}</p>
                <p class="text-3xl font-bold text-zinc-900 dark:text-white">₱{{ number_format($this->totalAmount, 2) }}</p>
            </div>

            <flux:field>
                <flux:label>{{ __('Payment Method') }}</flux:label>
                <flux:select wire:model="paymentMethod">
                    <flux:select.option value="cash">{{ __('Cash') }}</flux:select.option>
                    <flux:select.option value="gcash">{{ __('GCash') }}</flux:select.option>
                    <flux:select.option value="card">{{ __('Card') }}</flux:select.option>
                    <flux:select.option value="bank_transfer">{{ __('Bank Transfer') }}</flux:select.option>
                    <flux:select.option value="philhealth">{{ __('PhilHealth') }}</flux:select.option>
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Amount Tendered (₱)') }}</flux:label>
                <flux:input wire:model.live="amountTendered" type="number" step="0.01" min="{{ $this->totalAmount }}" />
                <flux:error name="amountTendered" />
            </flux:field>

            @if($amountTendered >= $this->totalAmount)
                <div class="rounded-lg bg-zinc-100 p-3 text-center dark:bg-zinc-800">
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Change') }}</p>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white">₱{{ number_format($this->change, 2) }}</p>
                </div>
            @endif

            <flux:field>
                <flux:label>{{ __('Notes (optional)') }}</flux:label>
                <flux:textarea wire:model="paymentNotes" rows="2" />
            </flux:field>

            <div class="flex justify-end gap-3 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <flux:button wire:click="closePaymentModal" variant="ghost">{{ __('Cancel') }}</flux:button>
                <flux:button wire:click="processPayment" variant="primary" icon="check">{{ __('Confirm Payment') }}</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Receipt Modal --}}
    <flux:modal wire:model="showReceiptModal" class="max-w-md" :dismissable="false">
        <div class="space-y-4">
            <div class="text-center">
                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon name="check" class="h-6 w-6 text-zinc-600 dark:text-zinc-400" />
                </div>
                <flux:heading size="lg">{{ __('Payment Successful') }}</flux:heading>
            </div>

            @if($completedTransaction)
                <div id="receipt-content" class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="mb-4 border-b border-dashed border-zinc-300 pb-4 text-center dark:border-zinc-600">
                        <p class="text-lg font-bold">{{ config('app.name') }}</p>
                        <p class="text-xs text-zinc-500">{{ __('Official Receipt') }}</p>
                    </div>

                    <div class="mb-3 space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span class="text-zinc-500">{{ __('Transaction') }}</span>
                            <span class="font-mono">{{ $completedTransaction->transaction_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-500">{{ __('Date') }}</span>
                            <span>{{ $completedTransaction->created_at->format('M d, Y h:i A') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-500">{{ __('Patient') }}</span>
                            <span>{{ $completedTransaction->medicalRecord?->patient_first_name }} {{ $completedTransaction->medicalRecord?->patient_last_name }}</span>
                        </div>
                    </div>

                    <div class="mb-3 border-t border-dashed border-zinc-300 pt-3 dark:border-zinc-600">
                        @foreach($completedTransaction->billingItems as $item)
                            <div class="flex justify-between text-sm">
                                <span class="text-zinc-600 dark:text-zinc-400">
                                    {{ $item->item_description }}
                                    @if($item->quantity > 1) x{{ $item->quantity }} @endif
                                </span>
                                <span>₱{{ number_format($item->total_price, 2) }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="border-t border-dashed border-zinc-300 pt-3 dark:border-zinc-600">
                        <div class="flex justify-between text-sm">
                            <span class="text-zinc-500">{{ __('Subtotal') }}</span>
                            <span>₱{{ number_format($completedTransaction->subtotal, 2) }}</span>
                        </div>
                        @if($completedTransaction->emergency_fee > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-zinc-500">{{ __('Special Charges') }}</span>
                                <span>₱{{ number_format($completedTransaction->emergency_fee, 2) }}</span>
                            </div>
                        @endif
                        @if($completedTransaction->discount_amount > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-zinc-500">{{ __('Discount') }}</span>
                                <span class="text-red-500">-₱{{ number_format($completedTransaction->discount_amount, 2) }}</span>
                            </div>
                        @endif
                        <div class="mt-2 flex justify-between border-t border-zinc-200 pt-2 font-bold dark:border-zinc-700">
                            <span>{{ __('Total') }}</span>
                            <span>₱{{ number_format($completedTransaction->total_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-zinc-500">{{ __('Amount Paid') }}</span>
                            <span>₱{{ number_format($completedTransaction->amount_paid, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-zinc-500">{{ __('Change') }}</span>
                            <span>₱{{ number_format($completedTransaction->amount_paid - $completedTransaction->total_amount, 2) }}</span>
                        </div>
                    </div>

                    <div class="mt-4 border-t border-dashed border-zinc-300 pt-3 text-center dark:border-zinc-600">
                        <p class="text-xs text-zinc-500">{{ __('Thank you for your visit!') }}</p>
                        <p class="text-xs text-zinc-400">{{ __('Processed by') }}: {{ $completedTransaction->processedBy?->first_name }}</p>
                    </div>
                </div>
            @endif

            <div class="flex gap-3">
                <flux:button wire:click="printReceipt" variant="ghost" class="flex-1" icon="printer">
                    {{ __('Print') }}
                </flux:button>
                <flux:button wire:click="closeReceiptModal" variant="primary" class="flex-1">
                    {{ __('Done') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</section>

@script
<script>
    $wire.on('print-receipt', () => {
        const content = document.getElementById('receipt-content');
        if (content) {
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Receipt</title>
                        <style>
                            body { font-family: monospace; font-size: 12px; padding: 20px; max-width: 300px; margin: 0 auto; }
                            .flex { display: flex; justify-content: space-between; }
                            .text-center { text-align: center; }
                            .font-bold { font-weight: bold; }
                            .border-dashed { border-top: 1px dashed #ccc; padding-top: 10px; margin-top: 10px; }
                            .text-sm { font-size: 11px; }
                            .text-xs { font-size: 10px; }
                            .mb-3 { margin-bottom: 10px; }
                            .mt-2 { margin-top: 8px; }
                            .pt-2 { padding-top: 8px; }
                        </style>
                    </head>
                    <body>${content.innerHTML}</body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }
    });
</script>
@endscript
