<div class="min-h-screen bg-gradient-to-b from-zinc-50 to-white dark:from-zinc-900 dark:to-zinc-800">
    <div class="mx-auto max-w-2xl px-4 py-6 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-6">
            <div class="flex items-center gap-3">
                <a href="{{ route('patient.dashboard') }}" class="rounded-lg p-2 text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800" wire:navigate>
                    <flux:icon name="arrow-left" class="h-5 w-5" />
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">{{ __('Medical Records') }}</h1>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Your visit history and diagnoses') }}</p>
                </div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="mb-6 grid grid-cols-2 gap-3">
            <div class="rounded-xl border border-zinc-200 bg-white p-4 text-center dark:border-zinc-700 dark:bg-zinc-800">
                <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $this->stats['total'] }}</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Total Records') }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 text-center dark:border-zinc-700 dark:bg-zinc-800">
                <p class="text-2xl font-bold text-primary">{{ $this->stats['this_year'] }}</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('This Year') }}</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="mb-6 space-y-3">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('Search diagnosis, complaints...') }}"
                icon="magnifying-glass"
            />
            <div class="flex gap-2">
                <flux:select wire:model.live="consultationTypeFilter" class="flex-1">
                    <option value="">{{ __('All Types') }}</option>
                    @foreach($this->consultationTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </flux:select>
                <flux:select wire:model.live="yearFilter" class="flex-1">
                    <option value="">{{ __('All Years') }}</option>
                    @foreach($this->availableYears as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        {{-- Records List --}}
        <div class="space-y-3">
            @forelse($records as $record)
                <a href="{{ route('patient.records.show', $record) }}"
                   class="block rounded-xl border border-zinc-200 bg-white p-4 shadow-sm transition hover:border-zinc-300 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600"
                   wire:navigate
                   wire:key="record-{{ $record->id }}">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 flex-shrink-0 flex-col items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-700">
                            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ $record->visit_date?->format('M') }}</span>
                            <span class="text-lg font-bold text-zinc-900 dark:text-white">{{ $record->visit_date?->format('d') }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="font-medium text-zinc-900 dark:text-white truncate">
                                    {{ $record->consultationType?->name }}
                                </p>
                                <flux:badge size="sm" variant="success">{{ __('Completed') }}</flux:badge>
                            </div>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $record->visit_date?->format('Y') }}
                                @if($record->doctor)
                                    &middot; Dr. {{ $record->doctor->personalInformation?->last_name ?? $record->doctor->last_name }}
                                @endif
                            </p>
                            @if($record->diagnosis)
                                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300 line-clamp-2">
                                    {{ $record->diagnosis }}
                                </p>
                            @elseif($record->effective_chief_complaints)
                                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400 line-clamp-2 italic">
                                    {{ $record->effective_chief_complaints }}
                                </p>
                            @endif
                        </div>
                        <flux:icon name="chevron-right" class="h-5 w-5 flex-shrink-0 text-zinc-400" />
                    </div>
                </a>
            @empty
                <div class="rounded-xl border border-dashed border-zinc-300 bg-zinc-50 p-8 text-center dark:border-zinc-700 dark:bg-zinc-800/50">
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-zinc-200 dark:bg-zinc-700">
                        <flux:icon name="document-text" class="h-6 w-6 text-zinc-400" />
                    </div>
                    <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('No records found') }}</p>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        @if($search || $consultationTypeFilter || $yearFilter)
                            {{ __('Try adjusting your filters') }}
                        @else
                            {{ __('Your medical records will appear here after your visits') }}
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($records->hasPages())
            <div class="mt-6">
                {{ $records->links() }}
            </div>
        @endif

        {{-- Bottom spacing for mobile nav --}}
        <div class="h-20 lg:hidden"></div>
    </div>
</div>
