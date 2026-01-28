<section class="space-y-4">
    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" level="1">{{ __('Billing Queue') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __(':count patients waiting for billing', ['count' => $this->queueCount]) }}
            </flux:text>
        </div>
        <flux:button href="{{ route('cashier.history') }}" wire:navigate variant="ghost" icon="clock">
            {{ __('Payment History') }}
        </flux:button>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="max-w-sm flex-1">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('Search patient or record number...') }}"
                icon="magnifying-glass"
            />
        </div>

        <flux:select wire:model.live="consultationFilter" class="w-full sm:w-48">
            <flux:select.option value="">{{ __('All Types') }}</flux:select.option>
            @foreach($this->consultationTypes as $type)
                <flux:select.option value="{{ $type->id }}">{{ $type->name }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    {{-- Queue List --}}
    @if($records->isNotEmpty())
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($records as $record)
                <a href="{{ route('cashier.process', $record) }}" wire:navigate wire:key="record-{{ $record->id }}"
                   class="group rounded-xl border border-zinc-200 bg-white p-4 transition hover:border-zinc-300 hover:shadow-sm dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-600">
                    {{-- Patient Info --}}
                    <div class="mb-3 flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-zinc-100 text-sm font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                {{ strtoupper(substr($record->patient_first_name, 0, 1) . substr($record->patient_last_name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-medium text-zinc-900 dark:text-white">
                                    {{ $record->patient_first_name }} {{ $record->patient_last_name }}
                                </p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $record->record_number }}</p>
                            </div>
                        </div>
                        <flux:icon name="arrow-right" class="h-4 w-4 text-zinc-400 transition group-hover:translate-x-1" />
                    </div>

                    {{-- Details --}}
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Type') }}</span>
                            <flux:badge size="sm" color="zinc">{{ $record->consultationType?->name }}</flux:badge>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Doctor') }}</span>
                            <span class="text-zinc-700 dark:text-zinc-300">Dr. {{ $record->doctor?->last_name }}</span>
                        </div>

                        @if($record->prescriptions->count() > 0)
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-500 dark:text-zinc-400">{{ __('Hospital Drugs') }}</span>
                                <span class="text-zinc-700 dark:text-zinc-300">{{ $record->prescriptions->count() }} {{ __('items') }}</span>
                            </div>
                        @endif

                        @if($record->suggested_discount_type && $record->suggested_discount_type !== 'none')
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-500 dark:text-zinc-400">{{ __('Discount') }}</span>
                                <flux:badge size="sm" color="zinc">{{ ucfirst($record->suggested_discount_type) }}</flux:badge>
                            </div>
                        @endif
                    </div>

                    {{-- Footer --}}
                    <div class="mt-3 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                        <p class="text-xs text-zinc-400">
                            {{ __('Ready since') }} {{ $record->examination_ended_at?->diffForHumans() }}
                        </p>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($records->hasPages())
            <div class="mt-4">
                {{ $records->links() }}
            </div>
        @endif
    @else
        <div class="rounded-xl border border-zinc-200 bg-white py-16 text-center dark:border-zinc-700 dark:bg-zinc-900">
            <img src="{{ asset('images/illustrations/empty-queue.svg') }}" alt="" class="mx-auto h-32 w-32 opacity-60" />
            <p class="mt-4 text-zinc-600 dark:text-zinc-400">{{ __('No patients waiting for billing') }}</p>
            <p class="text-sm text-zinc-500 dark:text-zinc-500">{{ __('Patients will appear here after their consultation') }}</p>
        </div>
    @endif
</section>
