<section class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-1">
            <flux:heading size="xl" level="1">{{ __('Reports') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('Generate statistical reports on patient volume, appointments, services, and queue performance.') }}
            </flux:text>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
        <div class="flex flex-wrap gap-1 border-b border-zinc-200 p-2 dark:border-zinc-700">
            <button
                wire:click="$set('reportType', 'daily_census')"
                @class([
                    'flex items-center gap-2 rounded-md px-4 py-2 text-sm font-medium transition-colors',
                    'bg-primary text-white' => $reportType === 'daily_census',
                    'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-700' => $reportType !== 'daily_census',
                ])
            >
                <flux:icon name="users" class="h-4 w-4" />
                {{ __('Daily Census') }}
            </button>
            <button
                wire:click="$set('reportType', 'appointment_stats')"
                @class([
                    'flex items-center gap-2 rounded-md px-4 py-2 text-sm font-medium transition-colors',
                    'bg-primary text-white' => $reportType === 'appointment_stats',
                    'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-700' => $reportType !== 'appointment_stats',
                ])
            >
                <flux:icon name="calendar-days" class="h-4 w-4" />
                {{ __('Appointment Statistics') }}
            </button>
            <button
                wire:click="$set('reportType', 'service_utilization')"
                @class([
                    'flex items-center gap-2 rounded-md px-4 py-2 text-sm font-medium transition-colors',
                    'bg-primary text-white' => $reportType === 'service_utilization',
                    'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-700' => $reportType !== 'service_utilization',
                ])
            >
                <flux:icon name="chart-bar" class="h-4 w-4" />
                {{ __('Service Utilization') }}
            </button>
            <button
                wire:click="$set('reportType', 'queue_performance')"
                @class([
                    'flex items-center gap-2 rounded-md px-4 py-2 text-sm font-medium transition-colors',
                    'bg-primary text-white' => $reportType === 'queue_performance',
                    'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-700' => $reportType !== 'queue_performance',
                ])
            >
                <flux:icon name="clock" class="h-4 w-4" />
                {{ __('Queue Performance') }}
            </button>
        </div>

        {{-- Controls --}}
        <div class="p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                @if($reportType === 'daily_census')
                    <div class="flex-1">
                        <flux:field>
                            <flux:label>{{ __('Report Date') }}</flux:label>
                            <flux:input type="date" wire:model.live="reportDate" />
                        </flux:field>
                    </div>
                @else
                    <div class="flex-1">
                        <flux:field>
                            <flux:label>{{ __('Date From') }}</flux:label>
                            <flux:input type="date" wire:model.live="dateFrom" />
                        </flux:field>
                    </div>
                    <div class="flex-1">
                        <flux:field>
                            <flux:label>{{ __('Date To') }}</flux:label>
                            <flux:input type="date" wire:model.live="dateTo" />
                        </flux:field>
                    </div>
                @endif
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
    </div>

    {{-- Report Content --}}
    @if($reportType === 'daily_census')
        @include('livewire.nurse.reports.daily-census', ['data' => $reportData])
    @elseif($reportType === 'appointment_stats')
        @include('livewire.nurse.reports.appointment-stats', ['data' => $reportData])
    @elseif($reportType === 'service_utilization')
        @include('livewire.nurse.reports.service-utilization', ['data' => $reportData])
    @elseif($reportType === 'queue_performance')
        @include('livewire.nurse.reports.queue-performance', ['data' => $reportData])
    @endif
</section>
