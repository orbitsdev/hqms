<section class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" level="1">{{ __('Patient History') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('View past medical records and diagnoses') }}
            </flux:text>
        </div>
    </div>

    {{-- Search --}}
    <div class="max-w-md">
        <flux:input
            type="search"
            wire:model.live.debounce.400ms="search"
            placeholder="{{ __('Search by patient name, record number, or diagnosis...') }}"
            icon="magnifying-glass"
        />
    </div>

    {{-- Records List --}}
    @if($records->isNotEmpty())
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-zinc-500">{{ __('Record #') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-zinc-500">{{ __('Patient') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-zinc-500">{{ __('Type') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-zinc-500">{{ __('Visit Date') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-zinc-500">{{ __('Diagnosis') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-zinc-500">{{ __('Doctor') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase text-zinc-500">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach($records as $record)
                            <tr wire:key="record-{{ $record->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="whitespace-nowrap px-4 py-3">
                                    <span class="font-mono text-sm text-zinc-600 dark:text-zinc-400">{{ $record->record_number }}</span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    <div>
                                        <p class="font-medium text-zinc-900 dark:text-white">{{ $record->patient_full_name }}</p>
                                        <p class="text-xs text-zinc-500">
                                            @if($record->patient_age) {{ $record->patient_age }} {{ __('yrs') }} @endif
                                            @if($record->patient_gender) &bull; {{ ucfirst($record->patient_gender) }} @endif
                                        </p>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    <flux:badge size="sm" color="zinc">{{ $record->consultationType?->short_name }}</flux:badge>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ $record->visit_date?->format('M d, Y') }}
                                </td>
                                <td class="max-w-xs truncate px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ Str::limit($record->diagnosis, 40) ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ $record->doctor?->name ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <flux:button wire:click="viewRecord({{ $record->id }})" size="xs" variant="ghost" icon="eye">
                                            {{ __('View') }}
                                        </flux:button>
                                        <flux:button wire:click="downloadPdf({{ $record->id }})" size="xs" variant="ghost" icon="arrow-down-tray" title="{{ __('Download PDF') }}" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $records->links() }}
        </div>
    @else
        <div class="rounded-xl border border-dashed border-zinc-300 p-12 text-center dark:border-zinc-600">
            <flux:icon name="document-magnifying-glass" class="mx-auto h-12 w-12 text-zinc-300 dark:text-zinc-600" />
            <p class="mt-4 font-medium text-zinc-600 dark:text-zinc-400">{{ __('No records found') }}</p>
            <p class="mt-1 text-sm text-zinc-500">{{ __('Try adjusting your search criteria') }}</p>
        </div>
    @endif

    {{-- Detail Modal --}}
    <flux:modal wire:model="showDetailModal" class="max-w-3xl">
        @if($this->selectedRecord)
            @php $r = $this->selectedRecord; @endphp
            <div class="space-y-6">
                <div class="flex items-start justify-between">
                    <div>
                        <flux:heading size="lg">{{ __('Medical Record') }}</flux:heading>
                        <p class="text-sm text-zinc-500">{{ $r->record_number }}</p>
                    </div>
                    <flux:badge size="sm" :color="$r->status === 'for_billing' ? 'yellow' : ($r->status === 'for_admission' ? 'blue' : 'zinc')">
                        {{ str_replace('_', ' ', ucfirst($r->status)) }}
                    </flux:badge>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    {{-- Patient Info --}}
                    <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                        <p class="mb-3 text-xs font-medium uppercase text-zinc-500">{{ __('Patient Information') }}</p>
                        <dl class="space-y-2 text-sm">
                            <div>
                                <dt class="text-zinc-500">{{ __('Name') }}</dt>
                                <dd class="font-medium text-zinc-900 dark:text-white">{{ $r->patient_full_name }}</dd>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <dt class="text-zinc-500">{{ __('Age') }}</dt>
                                    <dd class="text-zinc-900 dark:text-white">{{ $r->patient_age ?? '-' }} {{ __('years') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-zinc-500">{{ __('Gender') }}</dt>
                                    <dd class="text-zinc-900 dark:text-white">{{ ucfirst($r->patient_gender ?? '-') }}</dd>
                                </div>
                            </div>
                            @if($r->patient_blood_type)
                                <div>
                                    <dt class="text-zinc-500">{{ __('Blood Type') }}</dt>
                                    <dd class="text-zinc-900 dark:text-white">{{ $r->patient_blood_type }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    {{-- Visit Info --}}
                    <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                        <p class="mb-3 text-xs font-medium uppercase text-zinc-500">{{ __('Visit Information') }}</p>
                        <dl class="space-y-2 text-sm">
                            <div>
                                <dt class="text-zinc-500">{{ __('Date') }}</dt>
                                <dd class="text-zinc-900 dark:text-white">{{ $r->visit_date?->format('F d, Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-zinc-500">{{ __('Consultation Type') }}</dt>
                                <dd class="text-zinc-900 dark:text-white">{{ $r->consultationType?->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-zinc-500">{{ __('Doctor') }}</dt>
                                <dd class="text-zinc-900 dark:text-white">{{ $r->doctor?->name ?? '-' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- Vital Signs --}}
                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <p class="mb-3 text-xs font-medium uppercase text-zinc-500">{{ __('Vital Signs') }}</p>
                    <div class="grid grid-cols-3 gap-4 text-sm sm:grid-cols-6">
                        @if($r->temperature)
                            <div>
                                <dt class="text-zinc-500">{{ __('Temp') }}</dt>
                                <dd class="font-medium text-zinc-900 dark:text-white">{{ $r->temperature }}°C</dd>
                            </div>
                        @endif
                        @if($r->blood_pressure)
                            <div>
                                <dt class="text-zinc-500">{{ __('BP') }}</dt>
                                <dd class="font-medium text-zinc-900 dark:text-white">{{ $r->blood_pressure }}</dd>
                            </div>
                        @endif
                        @if($r->cardiac_rate)
                            <div>
                                <dt class="text-zinc-500">{{ __('HR') }}</dt>
                                <dd class="font-medium text-zinc-900 dark:text-white">{{ $r->cardiac_rate }}</dd>
                            </div>
                        @endif
                        @if($r->respiratory_rate)
                            <div>
                                <dt class="text-zinc-500">{{ __('RR') }}</dt>
                                <dd class="font-medium text-zinc-900 dark:text-white">{{ $r->respiratory_rate }}</dd>
                            </div>
                        @endif
                        @if($r->weight)
                            <div>
                                <dt class="text-zinc-500">{{ __('Weight') }}</dt>
                                <dd class="font-medium text-zinc-900 dark:text-white">{{ $r->weight }} kg</dd>
                            </div>
                        @endif
                        @if($r->height)
                            <div>
                                <dt class="text-zinc-500">{{ __('Height') }}</dt>
                                <dd class="font-medium text-zinc-900 dark:text-white">{{ $r->height }} cm</dd>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Clinical Notes --}}
                <div class="space-y-4">
                    @if($r->effective_chief_complaints)
                        <div>
                            <p class="text-xs font-medium uppercase text-zinc-500">{{ __('Chief Complaints') }}</p>
                            <p class="mt-1 text-sm text-zinc-700 dark:text-zinc-300">{{ $r->effective_chief_complaints }}</p>
                        </div>
                    @endif

                    @if($r->pertinent_hpi_pe)
                        <div>
                            <p class="text-xs font-medium uppercase text-zinc-500">{{ __('HPI & PE') }}</p>
                            <p class="mt-1 whitespace-pre-wrap text-sm text-zinc-700 dark:text-zinc-300">{{ $r->pertinent_hpi_pe }}</p>
                        </div>
                    @endif

                    @if($r->diagnosis)
                        <div>
                            <p class="text-xs font-medium uppercase text-zinc-500">{{ __('Diagnosis') }}</p>
                            <p class="mt-1 text-sm font-medium text-zinc-900 dark:text-white">{{ $r->diagnosis }}</p>
                        </div>
                    @endif

                    @if($r->plan)
                        <div>
                            <p class="text-xs font-medium uppercase text-zinc-500">{{ __('Plan') }}</p>
                            <p class="mt-1 whitespace-pre-wrap text-sm text-zinc-700 dark:text-zinc-300">{{ $r->plan }}</p>
                        </div>
                    @endif
                </div>

                {{-- Prescriptions --}}
                @if($r->prescriptions->isNotEmpty())
                    <div>
                        <p class="mb-2 text-xs font-medium uppercase text-zinc-500">{{ __('Prescriptions') }}</p>
                        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                            @foreach($r->prescriptions as $rx)
                                <div class="flex items-center justify-between border-b border-zinc-100 p-3 last:border-0 dark:border-zinc-800">
                                    <div>
                                        <p class="font-medium text-zinc-900 dark:text-white">{{ $rx->medication_name }}</p>
                                        <p class="text-xs text-zinc-500">
                                            @if($rx->dosage) {{ $rx->dosage }} @endif
                                            @if($rx->frequency) &bull; {{ $rx->frequency }} @endif
                                            @if($rx->duration) &bull; {{ $rx->duration }} @endif
                                        </p>
                                    </div>
                                    @if($rx->quantity)
                                        <span class="text-sm text-zinc-500">x{{ $rx->quantity }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex justify-end gap-2 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                    <flux:button wire:click="downloadPdf({{ $r->id }})" variant="ghost" icon="arrow-down-tray">
                        {{ __('Download PDF') }}
                    </flux:button>
                    <flux:button wire:click="closeDetailModal" variant="ghost">{{ __('Close') }}</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</section>
