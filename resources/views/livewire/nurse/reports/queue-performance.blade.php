@php
    $maxVolume = count($data['daily_volume']) > 0 ? max(max($data['daily_volume']), 1) : 1;
@endphp

{{-- KPI Cards --}}
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10">
                <flux:icon name="users" class="h-6 w-6 text-primary" />
            </div>
            <div>
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $data['total_served'] }}</p>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Patients Served') }}</p>
            </div>
        </div>
    </div>

    <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-amber-500/10">
                <flux:icon name="clock" class="h-6 w-6 text-amber-500" />
            </div>
            <div>
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $data['avg_wait'] }}</p>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Avg Wait (min)') }}</p>
            </div>
        </div>
    </div>

    <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-500/10">
                <flux:icon name="bolt" class="h-6 w-6 text-green-500" />
            </div>
            <div>
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $data['avg_service'] }}</p>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Avg Service (min)') }}</p>
            </div>
        </div>
    </div>

    <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-500/10">
                <flux:icon name="chart-bar" class="h-6 w-6 text-blue-500" />
            </div>
            <div>
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $data['avg_patients_per_day'] }}</p>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Avg Patients/Day') }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Performance by Consultation Type --}}
<div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
    <div class="border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
        <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Performance by Consultation Type') }}</h3>
    </div>

    @if(count($data['by_consultation_type']) > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                    <tr>
                        <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Consultation Type') }}</th>
                        <th class="px-6 py-3 text-center font-medium text-zinc-500 dark:text-zinc-400">{{ __('Patients') }}</th>
                        <th class="px-6 py-3 text-center font-medium text-zinc-500 dark:text-zinc-400">{{ __('Avg Wait (min)') }}</th>
                        <th class="px-6 py-3 text-center font-medium text-zinc-500 dark:text-zinc-400">{{ __('Avg Service (min)') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach($data['by_consultation_type'] as $type => $metrics)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900">
                            <td class="px-6 py-4 font-medium text-zinc-900 dark:text-zinc-100">{{ $type }}</td>
                            <td class="px-6 py-4 text-center text-zinc-700 dark:text-zinc-300">{{ $metrics['count'] }}</td>
                            <td class="px-6 py-4 text-center">
                                <span @class([
                                    'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' => $metrics['avg_wait'] <= 15,
                                    'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200' => $metrics['avg_wait'] > 15 && $metrics['avg_wait'] <= 30,
                                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' => $metrics['avg_wait'] > 30,
                                ])>
                                    {{ $metrics['avg_wait'] }} min
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    {{ $metrics['avg_service'] }} min
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="flex flex-col items-center justify-center py-12">
            <flux:icon name="clock" class="mb-4 h-12 w-12 text-zinc-300 dark:text-zinc-600" />
            <p class="text-zinc-500 dark:text-zinc-400">{{ __('No completed queue data for this period.') }}</p>
        </div>
    @endif
</div>

{{-- Daily Volume --}}
@if(count($data['daily_volume']) > 0)
    <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <h3 class="mb-4 font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Daily Patient Volume') }}</h3>
        <div class="space-y-2">
            @foreach($data['daily_volume'] as $date => $count)
                <div class="flex items-center gap-3">
                    <span class="w-24 shrink-0 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ \Carbon\Carbon::parse($date)->format('M d') }}
                    </span>
                    <div class="flex h-6 flex-1 items-center overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
                        <div
                            class="flex h-6 items-center justify-end rounded-full bg-primary px-2 text-xs font-medium text-white transition-all duration-500"
                            style="width: {{ $maxVolume > 0 ? max(($count / $maxVolume) * 100, $count > 0 ? 8 : 0) : 0 }}%"
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
