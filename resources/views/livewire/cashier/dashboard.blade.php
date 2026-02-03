<section class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" level="1">{{ __('Cashier Dashboard') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('Billing overview and quick actions') }}
            </flux:text>
        </div>
        <flux:button href="{{ route('cashier.queue') }}" wire:navigate variant="primary" icon="banknotes">
            {{ __('Billing Queue') }}
        </flux:button>
    </div>

    {{-- Stats --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon name="clock" class="h-5 w-5 text-zinc-600 dark:text-zinc-400" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->stats['pending_bills'] }}</p>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Pending Bills') }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon name="check-circle" class="h-5 w-5 text-zinc-600 dark:text-zinc-400" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->stats['processed_today'] }}</p>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Processed Today') }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon name="banknotes" class="h-5 w-5 text-zinc-600 dark:text-zinc-400" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">₱{{ number_format($this->stats['total_collected_today'], 2) }}</p>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Collected Today') }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon name="exclamation-circle" class="h-5 w-5 text-zinc-600 dark:text-zinc-400" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->stats['pending_payments'] }}</p>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Pending Payments') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Pending Bills --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-4 flex items-center justify-between">
                <flux:heading size="sm">{{ __('Waiting for Billing') }}</flux:heading>
                <flux:button href="{{ route('cashier.queue') }}" wire:navigate variant="ghost" size="sm" icon-trailing="arrow-right">
                    {{ __('View All') }}
                </flux:button>
            </div>

            @if($this->pendingBills->isNotEmpty())
                <div class="space-y-2">
                    @foreach($this->pendingBills as $record)
                        <a href="{{ route('cashier.process', $record) }}" wire:navigate
                           class="flex items-center justify-between rounded-lg border border-zinc-100 bg-zinc-50 p-3 transition hover:bg-zinc-100 dark:border-zinc-800 dark:bg-zinc-800/50 dark:hover:bg-zinc-800">
                            <div>
                                <p class="font-medium text-zinc-900 dark:text-white">
                                    {{ $record->patient_first_name }} {{ $record->patient_last_name }}
                                </p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $record->record_number }} · {{ $record->consultationType?->name }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-zinc-500">{{ __('Dr.') }} {{ $record->doctor?->last_name }}</p>
                                <p class="text-xs text-zinc-400">{{ $record->examination_ended_at?->diffForHumans() }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center">
                    <img src="{{ asset('images/illustrations/empty-queue.svg') }}" alt="" class="mx-auto h-24 w-24 opacity-60" />
                    <p class="mt-4 text-sm text-zinc-500 dark:text-zinc-400">{{ __('No pending bills') }}</p>
                </div>
            @endif
        </div>

        {{-- Recent Transactions --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-4 flex items-center justify-between">
                <flux:heading size="sm">{{ __('Recent Transactions') }}</flux:heading>
                <flux:button href="{{ route('cashier.history') }}" wire:navigate variant="ghost" size="sm" icon-trailing="arrow-right">
                    {{ __('View All') }}
                </flux:button>
            </div>

            @if($this->recentTransactions->isNotEmpty())
                <div class="space-y-2">
                    @foreach($this->recentTransactions as $transaction)
                        <a href="{{ route('cashier.transaction', $transaction) }}" wire:navigate
                           class="flex items-center justify-between rounded-lg border border-zinc-100 bg-zinc-50 p-3 transition hover:bg-zinc-100 dark:border-zinc-800 dark:bg-zinc-800/50 dark:hover:bg-zinc-800">
                            <div>
                                <p class="font-medium text-zinc-900 dark:text-white">
                                    {{ $transaction->transaction_number }}
                                </p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $transaction->medicalRecord?->patient_first_name }}
                                    {{ $transaction->medicalRecord?->patient_last_name }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-zinc-900 dark:text-white">₱{{ number_format($transaction->total_amount, 2) }}</p>
                                <p class="text-xs text-zinc-400">{{ $transaction->created_at->format('h:i A') }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center">
                    <img src="{{ asset('images/illustrations/empty-records.svg') }}" alt="" class="mx-auto h-24 w-24 opacity-60" />
                    <p class="mt-4 text-sm text-zinc-500 dark:text-zinc-400">{{ __('No transactions today') }}</p>
                </div>
            @endif
        </div>
    </div>
</section>
