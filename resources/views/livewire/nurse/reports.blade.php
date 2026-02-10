<section class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-1">
            <flux:heading size="xl" level="1">{{ __('Reports') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('Generate and download daily patient census reports.') }}
            </flux:text>
        </div>
    </div>

    {{-- Report Controls --}}
    <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
            <div class="flex-1">
                <flux:field>
                    <flux:label>{{ __('Report Date') }}</flux:label>
                    <flux:input type="date" wire:model.live="reportDate" />
                </flux:field>
            </div>
            <div class="flex gap-2">
                <flux:button wire:click="downloadPdf" variant="primary" icon="document-arrow-down">
                    {{ __('Download PDF') }}
                </flux:button>
                <flux:button wire:click="downloadExcel" variant="filled" icon="table-cells">
                    {{ __('Download Excel') }}
                </flux:button>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Total Patients --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10">
                    <flux:icon name="users" class="h-6 w-6 text-primary" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $censusData['total_patients'] }}</p>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Total Patients') }}</p>
                </div>
            </div>
        </div>

        {{-- New Patients --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-500/10">
                    <flux:icon name="user-plus" class="h-6 w-6 text-green-500" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $censusData['by_visit_type']['new'] }}</p>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('New Patients') }}</p>
                </div>
            </div>
        </div>

        {{-- Old Patients --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-500/10">
                    <flux:icon name="user" class="h-6 w-6 text-blue-500" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $censusData['by_visit_type']['old'] }}</p>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Old Patients') }}</p>
                </div>
            </div>
        </div>

        {{-- Revisit --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-amber-500/10">
                    <flux:icon name="arrow-path" class="h-6 w-6 text-amber-500" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $censusData['by_visit_type']['revisit'] }}</p>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Revisit') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Breakdown by Consultation Type --}}
    @if(count($censusData['by_consultation_type']) > 0)
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <h3 class="mb-4 font-semibold text-zinc-900 dark:text-zinc-100">{{ __('By Consultation Type') }}</h3>
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach($censusData['by_consultation_type'] as $type => $count)
                    <div class="flex items-center justify-between rounded-lg bg-zinc-50 p-4 dark:bg-zinc-900">
                        <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $type }}</span>
                        <span class="text-lg font-bold text-primary">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Patient List --}}
    <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
        <div class="border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
            <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">
                {{ __('Patient List for :date', ['date' => \Carbon\Carbon::parse($reportDate)->format('F d, Y')]) }}
            </h3>
        </div>

        @if($censusData['records']->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                        <tr>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">#</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Queue') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Record No.') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Patient Name') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Gender') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Consultation') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Visit Type') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Time') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach($censusData['records'] as $index => $record)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900">
                                <td class="px-6 py-4 text-zinc-500">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 font-mono font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ $record->queue?->formatted_number ?? '-' }}
                                </td>
                                <td class="px-6 py-4 font-mono text-zinc-700 dark:text-zinc-300">
                                    {{ $record->record_number }}
                                </td>
                                <td class="px-6 py-4 font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ $record->patient_full_name }}
                                </td>
                                <td class="px-6 py-4 text-zinc-700 dark:text-zinc-300">
                                    {{ ucfirst($record->patient_gender ?? '-') }}
                                </td>
                                <td class="px-6 py-4 text-zinc-700 dark:text-zinc-300">
                                    {{ $record->consultationType?->short_name ?? '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($record->visit_type === 'new')
                                        <flux:badge color="green" size="sm">{{ __('New') }}</flux:badge>
                                    @elseif($record->visit_type === 'old')
                                        <flux:badge color="blue" size="sm">{{ __('Old') }}</flux:badge>
                                    @elseif($record->visit_type === 'revisit')
                                        <flux:badge color="amber" size="sm">{{ __('Revisit') }}</flux:badge>
                                    @else
                                        <span class="text-zinc-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-zinc-500">
                                    {{ $record->created_at->format('h:i A') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-12">
                <flux:icon name="document-magnifying-glass" class="mb-4 h-12 w-12 text-zinc-300 dark:text-zinc-600" />
                <p class="text-zinc-500 dark:text-zinc-400">{{ __('No patients recorded for this date.') }}</p>
            </div>
        @endif
    </div>
</section>
