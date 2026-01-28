<section class="space-y-4">
    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" level="1">{{ __('Admissions') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('View admitted patients') }}</flux:text>
        </div>
    </div>

    {{-- Status Tabs --}}
    <div class="flex flex-wrap gap-2">
        <button
            wire:click="setStatus('active')"
            class="flex items-center gap-2 rounded-lg border px-4 py-2 transition {{ $status === 'active' ? 'border-emerald-500 bg-emerald-50 dark:border-emerald-400 dark:bg-emerald-900/30' : 'border-zinc-200 bg-white hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700' }}"
        >
            <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"></span>
            <span class="font-medium">{{ $this->statusCounts['active'] }}</span>
            <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Active') }}</span>
        </button>

        <button
            wire:click="setStatus('discharged')"
            class="flex items-center gap-2 rounded-lg border px-4 py-2 transition {{ $status === 'discharged' ? 'border-zinc-500 bg-zinc-100 dark:border-zinc-400 dark:bg-zinc-700' : 'border-zinc-200 bg-white hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700' }}"
        >
            <span class="h-2 w-2 rounded-full bg-zinc-400"></span>
            <span class="font-medium">{{ $this->statusCounts['discharged'] }}</span>
            <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Discharged') }}</span>
        </button>
    </div>

    {{-- Search --}}
    <div class="max-w-sm">
        <flux:input
            wire:model.live.debounce.300ms="search"
            placeholder="{{ __('Search by name or admission #...') }}"
            icon="magnifying-glass"
        />
    </div>

    {{-- Main Content --}}
    <div class="grid gap-4 lg:grid-cols-3">
        {{-- Admissions List --}}
        <div class="space-y-2 lg:col-span-2">
            @forelse($admissions as $admission)
                @php
                    $record = $admission->medicalRecord;
                    $patientName = $record?->patient_full_name ?? 'Unknown';
                    $isSelected = $selectedAdmissionId === $admission->id;
                @endphp
                <button
                    wire:click="selectAdmission({{ $admission->id }})"
                    wire:key="admission-{{ $admission->id }}"
                    class="w-full rounded-lg border p-4 text-left transition {{ $isSelected ? 'border-emerald-500 bg-emerald-50 ring-2 ring-emerald-500 dark:bg-emerald-900/20' : 'border-zinc-200 bg-white hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:bg-zinc-800' }}"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-3">
                            {{-- Admission Number --}}
                            <div class="flex h-12 w-14 shrink-0 items-center justify-center rounded-lg {{ $admission->status === 'active' ? 'bg-emerald-100 dark:bg-emerald-900/50' : 'bg-zinc-100 dark:bg-zinc-800' }}">
                                <span class="text-xs font-bold {{ $admission->status === 'active' ? 'text-emerald-700 dark:text-emerald-300' : 'text-zinc-600 dark:text-zinc-400' }}">
                                    {{ Str::after($admission->admission_number, 'ADM-') }}
                                </span>
                            </div>

                            <div>
                                <p class="font-semibold text-zinc-900 dark:text-white">{{ $patientName }}</p>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ $record?->consultationType?->name ?? 'N/A' }}
                                    @if($admission->room_number)
                                        &bull; {{ __('Room') }} {{ $admission->room_number }}
                                    @endif
                                    @if($admission->bed_number)
                                        &bull; {{ __('Bed') }} {{ $admission->bed_number }}
                                    @endif
                                </p>
                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ __('Admitted:') }} {{ $admission->admission_date->format('M d, Y g:i A') }}
                                    &bull; {{ __('Dr.') }} {{ $admission->admittedBy?->name ?? 'N/A' }}
                                </p>
                            </div>
                        </div>

                        {{-- Status / Days --}}
                        <div class="text-right">
                            @if($admission->status === 'active')
                                <flux:badge size="sm" color="green">{{ __('Active') }}</flux:badge>
                                <p class="mt-1 text-xs text-zinc-500">{{ $admission->length_of_stay }} {{ __('day(s)') }}</p>
                            @else
                                <flux:badge size="sm" color="zinc">{{ __('Discharged') }}</flux:badge>
                                <p class="mt-1 text-xs text-zinc-500">{{ $admission->discharge_date?->format('M d, Y') }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Reason --}}
                    @if($admission->reason_for_admission)
                        <div class="mt-3 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                <span class="font-medium">{{ __('Reason:') }}</span>
                                {{ Str::limit($admission->reason_for_admission, 80) }}
                            </p>
                        </div>
                    @endif
                </button>
            @empty
                <div class="rounded-lg border border-dashed border-zinc-300 p-8 text-center dark:border-zinc-600">
                    <img src="{{ asset('images/illustrations/empty-admissions.svg') }}" alt="" class="mx-auto h-32 w-32 opacity-60" />
                    <p class="mt-4 font-medium text-zinc-600 dark:text-zinc-400">
                        @if($status === 'active')
                            {{ __('No active admissions') }}
                        @else
                            {{ __('No discharged patients') }}
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

        {{-- Selected Admission Panel --}}
        <div class="lg:col-span-1">
            @if($this->selectedAdmission)
                @php
                    $sa = $this->selectedAdmission;
                    $sr = $sa->medicalRecord;
                @endphp
                <div class="sticky top-4 rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    {{-- Header --}}
                    <div class="border-b border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-lg font-bold text-zinc-900 dark:text-white">
                                    {{ $sa->admission_number }}
                                </p>
                                <p class="text-sm text-zinc-500">
                                    {{ $sa->status === 'active' ? __('Currently Admitted') : __('Discharged') }}
                                </p>
                            </div>
                            <flux:badge size="sm" :color="$sa->status === 'active' ? 'green' : 'zinc'">
                                {{ $sa->length_of_stay }} {{ __('day(s)') }}
                            </flux:badge>
                        </div>
                    </div>

                    @if($sr)
                        {{-- Patient Info --}}
                        <div class="border-b border-zinc-200 p-4 dark:border-zinc-700">
                            <p class="mb-2 text-xs font-medium uppercase text-zinc-500">{{ __('Patient') }}</p>
                            <p class="font-semibold text-zinc-900 dark:text-white">{{ $sr->patient_full_name }}</p>
                            <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                @if($sr->patient_age)
                                    {{ $sr->patient_age }} {{ __('years old') }}
                                @endif
                                @if($sr->patient_gender)
                                    &bull; {{ ucfirst($sr->patient_gender) }}
                                @endif
                            </div>
                            <p class="mt-1 text-xs text-zinc-500">{{ $sr->consultationType?->name }}</p>
                        </div>

                        {{-- Room/Bed --}}
                        <div class="border-b border-zinc-200 p-4 dark:border-zinc-700">
                            <p class="mb-2 text-xs font-medium uppercase text-zinc-500">{{ __('Location') }}</p>
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <span class="text-zinc-500 dark:text-zinc-400">{{ __('Room:') }}</span>
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ $sa->room_number ?? '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-zinc-500 dark:text-zinc-400">{{ __('Bed:') }}</span>
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ $sa->bed_number ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Attending Doctor --}}
                    <div class="border-b border-zinc-200 p-4 dark:border-zinc-700">
                        <p class="mb-2 text-xs font-medium uppercase text-zinc-500">{{ __('Attending Doctor') }}</p>
                        <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $sa->admittedBy?->name ?? 'N/A' }}</p>
                    </div>

                    {{-- Reason for Admission --}}
                    <div class="border-b border-zinc-200 p-4 dark:border-zinc-700">
                        <p class="mb-2 text-xs font-medium uppercase text-zinc-500">{{ __('Reason for Admission') }}</p>
                        <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ $sa->reason_for_admission }}</p>
                    </div>

                    {{-- Notes --}}
                    @if($sa->notes)
                        <div class="border-b border-zinc-200 p-4 dark:border-zinc-700">
                            <p class="mb-2 text-xs font-medium uppercase text-zinc-500">{{ __('Notes') }}</p>
                            <p class="text-sm whitespace-pre-line text-zinc-700 dark:text-zinc-300">{{ $sa->notes }}</p>
                        </div>
                    @endif

                    {{-- Dates --}}
                    <div class="border-b border-zinc-200 p-4 dark:border-zinc-700">
                        <p class="mb-2 text-xs font-medium uppercase text-zinc-500">{{ __('Timeline') }}</p>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-zinc-500 dark:text-zinc-400">{{ __('Admitted:') }}</span>
                                <span class="text-zinc-900 dark:text-white">{{ $sa->admission_date->format('M d, Y g:i A') }}</span>
                            </div>
                            @if($sa->discharge_date)
                                <div class="flex justify-between">
                                    <span class="text-zinc-500 dark:text-zinc-400">{{ __('Discharged:') }}</span>
                                    <span class="text-zinc-900 dark:text-white">{{ $sa->discharge_date->format('M d, Y g:i A') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Discharge Summary (if discharged) --}}
                    @if($sa->status === 'discharged' && $sa->discharge_summary)
                        <div class="p-4">
                            <p class="mb-2 text-xs font-medium uppercase text-zinc-500">{{ __('Discharge Summary') }}</p>
                            <p class="text-sm whitespace-pre-line text-zinc-700 dark:text-zinc-300">{{ $sa->discharge_summary }}</p>
                        </div>
                    @endif
                </div>
            @else
                <div class="rounded-xl border border-dashed border-zinc-300 p-8 text-center dark:border-zinc-600">
                    <flux:icon name="cursor-arrow-rays" class="mx-auto h-10 w-10 text-zinc-400" />
                    <p class="mt-3 text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Select an admission') }}</p>
                    <p class="mt-1 text-xs text-zinc-500">{{ __('Click on an admission to view details') }}</p>
                </div>
            @endif
        </div>
    </div>
</section>
