<div class="min-h-screen bg-gradient-to-b from-zinc-50 to-white dark:from-zinc-900 dark:to-zinc-800">
    <div class="mx-auto max-w-2xl px-4 py-6 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-6">
            <div class="flex items-center gap-3">
                <a href="{{ route('patient.records') }}" class="rounded-lg p-2 text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800" wire:navigate>
                    <flux:icon name="arrow-left" class="h-5 w-5" />
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">{{ __('Visit Details') }}</h1>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $record->record_number }}</p>
                </div>
            </div>
        </div>

        {{-- Visit Info Card --}}
        <div class="mb-6 rounded-2xl bg-gradient-to-br from-primary to-primary/80 p-5 text-white shadow-lg">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-white/80">{{ $record->consultationType?->name }}</p>
                    <p class="mt-1 text-2xl font-bold">{{ $record->visit_date?->format('F d, Y') }}</p>
                </div>
                <div class="rounded-full bg-white/20 p-3">
                    <flux:icon name="document-text" class="h-6 w-6" />
                </div>
            </div>
            @if($record->doctor)
                <p class="mt-3 text-sm text-white/90">
                    <span class="text-white/70">{{ __('Attending:') }}</span>
                    Dr. {{ $record->doctor->personalInformation?->full_name ?? $record->doctor->name }}
                </p>
            @endif
        </div>

        {{-- Patient Info --}}
        <div class="mb-6 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <h2 class="mb-3 text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Patient Information') }}</h2>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <p class="text-zinc-500 dark:text-zinc-400">{{ __('Name') }}</p>
                    <p class="font-medium text-zinc-900 dark:text-white">{{ $record->patient_full_name }}</p>
                </div>
                @if($record->patient_date_of_birth)
                    <div>
                        <p class="text-zinc-500 dark:text-zinc-400">{{ __('Age') }}</p>
                        <p class="font-medium text-zinc-900 dark:text-white">{{ $record->patient_age }} {{ __('years old') }}</p>
                    </div>
                @endif
                @if($record->patient_gender)
                    <div>
                        <p class="text-zinc-500 dark:text-zinc-400">{{ __('Gender') }}</p>
                        <p class="font-medium text-zinc-900 dark:text-white">{{ ucfirst($record->patient_gender) }}</p>
                    </div>
                @endif
                <div>
                    <p class="text-zinc-500 dark:text-zinc-400">{{ __('Visit Type') }}</p>
                    <p class="font-medium text-zinc-900 dark:text-white">{{ ucfirst($record->visit_type) }}</p>
                </div>
            </div>
        </div>

        {{-- Chief Complaints --}}
        @if($record->effective_chief_complaints)
            <div class="mb-6 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <h2 class="mb-2 text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Chief Complaints') }}</h2>
                <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ $record->effective_chief_complaints }}</p>
            </div>
        @endif

        {{-- Vital Signs --}}
        @if(count($vitalSigns) > 0)
            <div class="mb-6 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <h2 class="mb-3 text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Vital Signs') }}</h2>
                <div class="grid grid-cols-2 gap-3">
                    @foreach($vitalSigns as $vital)
                        <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-700/50">
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $vital['label'] }}</p>
                            <p class="font-semibold text-zinc-900 dark:text-white">{{ $vital['value'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Diagnosis & Plan --}}
        @if($record->diagnosis || $record->pertinent_hpi_pe || $record->plan)
            <div class="mb-6 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <h2 class="mb-3 text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Assessment & Plan') }}</h2>

                @if($record->pertinent_hpi_pe)
                    <div class="mb-4">
                        <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Clinical Findings') }}</p>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $record->pertinent_hpi_pe }}</p>
                    </div>
                @endif

                @if($record->diagnosis)
                    <div class="mb-4 rounded-lg bg-primary/10 p-3">
                        <p class="text-xs font-medium text-primary">{{ __('Diagnosis') }}</p>
                        <p class="mt-1 font-medium text-zinc-900 dark:text-white">{{ $record->diagnosis }}</p>
                    </div>
                @endif

                @if($record->plan)
                    <div class="mb-4">
                        <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Treatment Plan') }}</p>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $record->plan }}</p>
                    </div>
                @endif

                @if($record->procedures_done)
                    <div>
                        <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Procedures Done') }}</p>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $record->procedures_done }}</p>
                    </div>
                @endif
            </div>
        @endif

        {{-- Prescriptions --}}
        @if($record->prescriptions->count() > 0)
            <div class="mb-6 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <h2 class="mb-3 text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Prescriptions') }}</h2>
                <div class="space-y-3">
                    @foreach($record->prescriptions as $prescription)
                        <div class="rounded-lg border border-zinc-100 bg-zinc-50 p-3 dark:border-zinc-600 dark:bg-zinc-700/50">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="font-medium text-zinc-900 dark:text-white">{{ $prescription->medication_name }}</p>
                                    <div class="mt-1 flex flex-wrap gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                                        @if($prescription->dosage)
                                            <span>{{ $prescription->dosage }}</span>
                                        @endif
                                        @if($prescription->frequency)
                                            <span>&middot; {{ $prescription->frequency }}</span>
                                        @endif
                                        @if($prescription->duration)
                                            <span>&middot; {{ $prescription->duration }}</span>
                                        @endif
                                        @if($prescription->quantity)
                                            <span>&middot; Qty: {{ $prescription->quantity }}</span>
                                        @endif
                                    </div>
                                </div>
                                @if($prescription->is_hospital_drug)
                                    <flux:badge size="sm" variant="success">{{ __('Hospital') }}</flux:badge>
                                @endif
                            </div>
                            @if($prescription->instructions)
                                <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400 italic">{{ $prescription->instructions }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if($record->prescription_notes)
                    <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400 italic">{{ $record->prescription_notes }}</p>
                @endif
            </div>
        @endif

        {{-- Billing Summary --}}
        @if($record->billingTransaction)
            <div class="mb-6 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <h2 class="mb-3 text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Billing Summary') }}</h2>
                <div class="space-y-2 text-sm">
                    @foreach($record->billingTransaction->billingItems as $item)
                        <div class="flex justify-between">
                            <span class="text-zinc-600 dark:text-zinc-300">
                                {{ $item->item_description }}
                                @if($item->quantity > 1)
                                    <span class="text-zinc-400">x{{ $item->quantity }}</span>
                                @endif
                            </span>
                            <span class="font-medium text-zinc-900 dark:text-white">{{ number_format($item->total_price, 2) }}</span>
                        </div>
                    @endforeach

                    <div class="border-t border-zinc-200 pt-2 dark:border-zinc-600">
                        @if($record->billingTransaction->discount_amount > 0)
                            <div class="flex justify-between text-success">
                                <span>{{ __('Discount') }} ({{ ucfirst($record->billingTransaction->discount_type) }})</span>
                                <span>-{{ number_format($record->billingTransaction->discount_amount, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between font-semibold">
                            <span class="text-zinc-900 dark:text-white">{{ __('Total Paid') }}</span>
                            <span class="text-primary">PHP {{ number_format($record->billingTransaction->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Bottom spacing for mobile nav --}}
        <div class="h-20 lg:hidden"></div>
    </div>
</div>
