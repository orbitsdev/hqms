@php
    $statusColors = [
        'pending' => 'bg-yellow-500',
        'approved' => 'bg-blue-500',
        'checked_in' => 'bg-indigo-500',
        'in_progress' => 'bg-purple-500',
        'completed' => 'bg-green-500',
        'cancelled' => 'bg-red-500',
        'no_show' => 'bg-zinc-500',
    ];

    $statusLabels = [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'checked_in' => 'Checked In',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'no_show' => 'No Show',
    ];

    $maxStatus = count($data['by_status']) > 0 ? max($data['by_status']) : 1;
    $maxTrend = count($data['daily_trend']) > 0 ? max(max($data['daily_trend']), 1) : 1;
@endphp

{{-- Summary Cards --}}
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10">
                <flux:icon name="calendar-days" class="h-6 w-6 text-primary" />
            </div>
            <div>
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $data['total'] }}</p>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Total Appointments') }}</p>
            </div>
        </div>
    </div>

    <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-500/10">
                <flux:icon name="check-circle" class="h-6 w-6 text-green-500" />
            </div>
            <div>
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $data['completed'] }}</p>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Completed') }}</p>
            </div>
        </div>
    </div>

    <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-500/10">
                <flux:icon name="globe-alt" class="h-6 w-6 text-blue-500" />
            </div>
            <div>
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $data['by_source']['online'] ?? 0 }}</p>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Online') }}</p>
            </div>
        </div>
    </div>

    <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-amber-500/10">
                <flux:icon name="user-plus" class="h-6 w-6 text-amber-500" />
            </div>
            <div>
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $data['by_source']['walk-in'] ?? 0 }}</p>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Walk-in') }}</p>
            </div>
        </div>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-2">
    {{-- Status Breakdown --}}
    <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <h3 class="mb-4 font-semibold text-zinc-900 dark:text-zinc-100">{{ __('By Status') }}</h3>
        <div class="space-y-3">
            @foreach($statusLabels as $status => $label)
                @php $count = $data['by_status'][$status] ?? 0; @endphp
                <div>
                    <div class="mb-1 flex items-center justify-between text-sm">
                        <span class="text-zinc-600 dark:text-zinc-400">{{ __($label) }}</span>
                        <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $count }}</span>
                    </div>
                    <div class="h-3 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
                        <div
                            class="{{ $statusColors[$status] }} h-3 rounded-full transition-all duration-500"
                            style="width: {{ $maxStatus > 0 ? ($count / $maxStatus) * 100 : 0 }}%"
                        ></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- By Consultation Type --}}
    <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <h3 class="mb-4 font-semibold text-zinc-900 dark:text-zinc-100">{{ __('By Consultation Type') }}</h3>
        @if(count($data['by_consultation_type']) > 0)
            <div class="space-y-3">
                @foreach($data['by_consultation_type'] as $type => $count)
                    <div class="flex items-center justify-between rounded-lg bg-zinc-50 p-4 dark:bg-zinc-900">
                        <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $type }}</span>
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $data['total'] > 0 ? round(($count / $data['total']) * 100, 1) : 0 }}%
                            </span>
                            <span class="text-lg font-bold text-primary">{{ $count }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="py-8 text-center text-zinc-500 dark:text-zinc-400">{{ __('No data available.') }}</p>
        @endif
    </div>
</div>

{{-- Daily Trend --}}
@if(count($data['daily_trend']) > 0)
    <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <h3 class="mb-4 font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Daily Appointment Trend') }}</h3>
        <div class="space-y-2">
            @foreach($data['daily_trend'] as $date => $count)
                <div class="flex items-center gap-3">
                    <span class="w-24 shrink-0 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ \Carbon\Carbon::parse($date)->format('M d') }}
                    </span>
                    <div class="flex h-6 flex-1 items-center overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
                        <div
                            class="flex h-6 items-center justify-end rounded-full bg-primary px-2 text-xs font-medium text-white transition-all duration-500"
                            style="width: {{ $maxTrend > 0 ? max(($count / $maxTrend) * 100, $count > 0 ? 8 : 0) : 0 }}%"
                        >
                            @if($count > 0)
                                {{ $count }}
                            @endif
                        </div>
                    </div>
                    @if($count === 0)
                        <span class="text-sm text-zinc-400">0</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif
