@php
    $maxConsultation = count($data['by_consultation_type']) > 0 ? max($data['by_consultation_type']) : 1;
    $totalVisitTypes = $data['by_visit_type']['new'] + $data['by_visit_type']['old'] + $data['by_visit_type']['revisit'];
    $totalSource = $data['by_source']['online'] + $data['by_source']['walk-in'];
@endphp

{{-- Summary Cards --}}
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10">
                <flux:icon name="clipboard-document-list" class="h-6 w-6 text-primary" />
            </div>
            <div>
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $data['total'] }}</p>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Total Services') }}</p>
            </div>
        </div>
    </div>

    <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-500/10">
                <flux:icon name="user-plus" class="h-6 w-6 text-green-500" />
            </div>
            <div>
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $data['by_visit_type']['new'] }}</p>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('New Patients') }}</p>
            </div>
        </div>
    </div>

    <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-500/10">
                <flux:icon name="user" class="h-6 w-6 text-blue-500" />
            </div>
            <div>
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $data['by_visit_type']['old'] }}</p>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Old Patients') }}</p>
            </div>
        </div>
    </div>

    <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-amber-500/10">
                <flux:icon name="arrow-path" class="h-6 w-6 text-amber-500" />
            </div>
            <div>
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $data['by_visit_type']['revisit'] }}</p>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Revisits') }}</p>
            </div>
        </div>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-2">
    {{-- Consultation Type Distribution --}}
    <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <h3 class="mb-4 font-semibold text-zinc-900 dark:text-zinc-100">{{ __('By Consultation Type') }}</h3>
        @if(count($data['by_consultation_type']) > 0)
            <div class="space-y-3">
                @foreach($data['by_consultation_type'] as $type => $count)
                    <div>
                        <div class="mb-1 flex items-center justify-between text-sm">
                            <span class="text-zinc-600 dark:text-zinc-400">{{ $type }}</span>
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $count }} ({{ $data['total'] > 0 ? round(($count / $data['total']) * 100, 1) : 0 }}%)
                            </span>
                        </div>
                        <div class="h-3 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
                            <div
                                class="h-3 rounded-full bg-primary transition-all duration-500"
                                style="width: {{ $maxConsultation > 0 ? ($count / $maxConsultation) * 100 : 0 }}%"
                            ></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="py-8 text-center text-zinc-500 dark:text-zinc-400">{{ __('No data available.') }}</p>
        @endif
    </div>

    <div class="space-y-6">
        {{-- Visit Type Distribution --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <h3 class="mb-4 font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Visit Type Distribution') }}</h3>
            <div class="space-y-3">
                <div>
                    <div class="mb-1 flex items-center justify-between text-sm">
                        <span class="text-zinc-600 dark:text-zinc-400">{{ __('New') }}</span>
                        <span class="font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $data['by_visit_type']['new'] }} ({{ $totalVisitTypes > 0 ? round(($data['by_visit_type']['new'] / $totalVisitTypes) * 100, 1) : 0 }}%)
                        </span>
                    </div>
                    <div class="h-3 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
                        <div
                            class="h-3 rounded-full bg-green-500 transition-all duration-500"
                            style="width: {{ $totalVisitTypes > 0 ? ($data['by_visit_type']['new'] / $totalVisitTypes) * 100 : 0 }}%"
                        ></div>
                    </div>
                </div>
                <div>
                    <div class="mb-1 flex items-center justify-between text-sm">
                        <span class="text-zinc-600 dark:text-zinc-400">{{ __('Old') }}</span>
                        <span class="font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $data['by_visit_type']['old'] }} ({{ $totalVisitTypes > 0 ? round(($data['by_visit_type']['old'] / $totalVisitTypes) * 100, 1) : 0 }}%)
                        </span>
                    </div>
                    <div class="h-3 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
                        <div
                            class="h-3 rounded-full bg-blue-500 transition-all duration-500"
                            style="width: {{ $totalVisitTypes > 0 ? ($data['by_visit_type']['old'] / $totalVisitTypes) * 100 : 0 }}%"
                        ></div>
                    </div>
                </div>
                <div>
                    <div class="mb-1 flex items-center justify-between text-sm">
                        <span class="text-zinc-600 dark:text-zinc-400">{{ __('Revisit') }}</span>
                        <span class="font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $data['by_visit_type']['revisit'] }} ({{ $totalVisitTypes > 0 ? round(($data['by_visit_type']['revisit'] / $totalVisitTypes) * 100, 1) : 0 }}%)
                        </span>
                    </div>
                    <div class="h-3 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
                        <div
                            class="h-3 rounded-full bg-amber-500 transition-all duration-500"
                            style="width: {{ $totalVisitTypes > 0 ? ($data['by_visit_type']['revisit'] / $totalVisitTypes) * 100 : 0 }}%"
                        ></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Source Distribution --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <h3 class="mb-4 font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Source Distribution') }}</h3>
            <div class="space-y-3">
                <div>
                    <div class="mb-1 flex items-center justify-between text-sm">
                        <span class="text-zinc-600 dark:text-zinc-400">{{ __('Online') }}</span>
                        <span class="font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $data['by_source']['online'] }} ({{ $totalSource > 0 ? round(($data['by_source']['online'] / $totalSource) * 100, 1) : 0 }}%)
                        </span>
                    </div>
                    <div class="h-3 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
                        <div
                            class="h-3 rounded-full bg-blue-500 transition-all duration-500"
                            style="width: {{ $totalSource > 0 ? ($data['by_source']['online'] / $totalSource) * 100 : 0 }}%"
                        ></div>
                    </div>
                </div>
                <div>
                    <div class="mb-1 flex items-center justify-between text-sm">
                        <span class="text-zinc-600 dark:text-zinc-400">{{ __('Walk-in') }}</span>
                        <span class="font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $data['by_source']['walk-in'] }} ({{ $totalSource > 0 ? round(($data['by_source']['walk-in'] / $totalSource) * 100, 1) : 0 }}%)
                        </span>
                    </div>
                    <div class="h-3 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
                        <div
                            class="h-3 rounded-full bg-amber-500 transition-all duration-500"
                            style="width: {{ $totalSource > 0 ? ($data['by_source']['walk-in'] / $totalSource) * 100 : 0 }}%"
                        ></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
