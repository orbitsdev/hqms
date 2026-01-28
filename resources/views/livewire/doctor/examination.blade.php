<section class="space-y-4">
    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <flux:button href="{{ route('doctor.queue') }}" wire:navigate variant="ghost" icon="arrow-left" size="sm" />
            <div>
                <flux:heading size="xl" level="1">{{ __('Patient Examination') }}</flux:heading>
                <flux:text class="text-sm text-zinc-500">{{ $record->record_number }}</flux:text>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <flux:button wire:click="downloadPdf" variant="ghost" icon="arrow-down-tray">
                {{ __('PDF') }}
            </flux:button>
            <flux:button wire:click="saveDraft" variant="ghost" icon="bookmark">
                {{ __('Save Draft') }}
            </flux:button>
            <flux:button wire:click="openCompleteModal" variant="primary" icon="check-circle">
                {{ __('Complete') }}
            </flux:button>
        </div>
    </div>

    {{-- Main Layout --}}
    <div class="grid gap-4 lg:grid-cols-12">
        {{-- Left Panel: Patient Info (Read-only from nurse) --}}
        <div class="space-y-4 lg:col-span-3">
            {{-- Patient Card --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-3 flex items-center justify-between">
                    <flux:heading size="sm">{{ __('Patient') }}</flux:heading>
                    <span class="rounded bg-blue-100 px-2 py-0.5 text-xs font-bold text-blue-700 dark:bg-blue-900/50 dark:text-blue-300">
                        {{ $record->queue?->formatted_number }}
                    </span>
                </div>

                <p class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $record->patient_full_name }}</p>
                <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    @if($record->patient_age) {{ $record->patient_age }} {{ __('yrs') }} @endif
                    @if($record->patient_gender) &bull; {{ ucfirst($record->patient_gender) }} @endif
                    @if($record->patient_blood_type) &bull; {{ $record->patient_blood_type }} @endif
                </div>
                <p class="mt-1 text-xs text-zinc-500">{{ $record->consultationType?->name }}</p>
            </div>

            {{-- Vital Signs --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="sm" class="mb-3">{{ __('Vital Signs') }}</flux:heading>
                <dl class="space-y-2 text-sm">
                    @if($record->temperature)
                        <div class="flex justify-between {{ $record->temperature >= 38 ? 'text-red-600 dark:text-red-400 font-medium' : '' }}">
                            <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Temperature') }}</dt>
                            <dd>{{ $record->temperature }}°C</dd>
                        </div>
                    @endif
                    @if($record->blood_pressure)
                        <div class="flex justify-between">
                            <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Blood Pressure') }}</dt>
                            <dd class="text-zinc-900 dark:text-white">{{ $record->blood_pressure }}</dd>
                        </div>
                    @endif
                    @if($record->cardiac_rate)
                        <div class="flex justify-between">
                            <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Heart Rate') }}</dt>
                            <dd class="text-zinc-900 dark:text-white">{{ $record->cardiac_rate }} bpm</dd>
                        </div>
                    @endif
                    @if($record->respiratory_rate)
                        <div class="flex justify-between">
                            <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Resp. Rate') }}</dt>
                            <dd class="text-zinc-900 dark:text-white">{{ $record->respiratory_rate }}/min</dd>
                        </div>
                    @endif
                    @if($record->weight)
                        <div class="flex justify-between">
                            <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Weight') }}</dt>
                            <dd class="text-zinc-900 dark:text-white">{{ $record->weight }} kg</dd>
                        </div>
                    @endif
                    @if($record->height)
                        <div class="flex justify-between">
                            <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Height') }}</dt>
                            <dd class="text-zinc-900 dark:text-white">{{ $record->height }} cm</dd>
                        </div>
                    @endif
                    @if($record->fetal_heart_tone)
                        <div class="flex justify-between">
                            <dt class="text-zinc-500 dark:text-zinc-400">{{ __('FHT') }}</dt>
                            <dd class="text-zinc-900 dark:text-white">{{ $record->fetal_heart_tone }}</dd>
                        </div>
                    @endif
                </dl>
                @if($record->vital_signs_recorded_at)
                    <p class="mt-3 text-xs text-zinc-400">
                        {{ __('Recorded') }} {{ $record->vital_signs_recorded_at->diffForHumans() }}
                        @if($record->nurse) {{ __('by') }} {{ $record->nurse->name }} @endif
                    </p>
                @endif
            </div>

            {{-- Chief Complaints --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="sm" class="mb-3">{{ __('Chief Complaints') }}</flux:heading>
                <p class="text-sm text-zinc-700 dark:text-zinc-300">
                    {{ $record->effective_chief_complaints ?? '-' }}
                </p>
            </div>

            {{-- Allergies & Conditions --}}
            @if($record->patient_allergies || $record->patient_chronic_conditions)
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
                    <flux:heading size="sm" class="mb-3 text-amber-800 dark:text-amber-200">{{ __('Medical Alert') }}</flux:heading>
                    @if($record->patient_allergies)
                        <div class="mb-2">
                            <p class="text-xs font-medium uppercase text-red-700 dark:text-red-300">{{ __('Allergies') }}</p>
                            <p class="text-sm text-red-600 dark:text-red-400">{{ $record->patient_allergies }}</p>
                        </div>
                    @endif
                    @if($record->patient_chronic_conditions)
                        <div>
                            <p class="text-xs font-medium uppercase text-amber-700 dark:text-amber-300">{{ __('Chronic Conditions') }}</p>
                            <p class="text-sm text-amber-600 dark:text-amber-400">{{ $record->patient_chronic_conditions }}</p>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Patient History Button --}}
            <flux:button wire:click="openHistoryModal" class="w-full" variant="ghost" icon="clock">
                {{ __('View Past Records') }} ({{ $history->count() }})
            </flux:button>
        </div>

        {{-- Center Panel: Doctor's Input --}}
        <div class="space-y-4 lg:col-span-6">
            {{-- Examination Form --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg" class="mb-4">{{ __("Doctor's Findings") }}</flux:heading>

                <div class="space-y-4">
                    <flux:field>
                        <flux:label>{{ __('Pertinent HPI & PE') }}</flux:label>
                        <flux:textarea
                            wire:model="pertinentHpiPe"
                            rows="4"
                            placeholder="{{ __('History of Present Illness & Physical Examination findings...') }}"
                        />
                        <flux:description>{{ __('Document relevant history and examination findings') }}</flux:description>
                        <flux:error name="pertinentHpiPe" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Diagnosis') }} *</flux:label>
                        <flux:textarea
                            wire:model="diagnosis"
                            rows="3"
                            placeholder="{{ __('Enter diagnosis...') }}"
                        />
                        <flux:error name="diagnosis" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Plan / Treatment') }}</flux:label>
                        <flux:textarea
                            wire:model="plan"
                            rows="3"
                            placeholder="{{ __('Treatment plan, follow-up instructions...') }}"
                        />
                        <flux:error name="plan" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Procedures Done') }}</flux:label>
                        <flux:textarea
                            wire:model="proceduresDone"
                            rows="2"
                            placeholder="{{ __('Any procedures performed during this visit...') }}"
                        />
                        <flux:error name="proceduresDone" />
                    </flux:field>
                </div>
            </div>

            {{-- Discount Recommendation --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="sm" class="mb-3">{{ __('Discount Recommendation') }}</flux:heading>
                <div class="grid gap-3 sm:grid-cols-2">
                    <flux:select wire:model="suggestedDiscountType" label="{{ __('Discount Type') }}">
                        <flux:select.option value="none">{{ __('None') }}</flux:select.option>
                        <flux:select.option value="senior">{{ __('Senior Citizen') }}</flux:select.option>
                        <flux:select.option value="pwd">{{ __('PWD') }}</flux:select.option>
                        <flux:select.option value="family">{{ __('Family/Relative') }}</flux:select.option>
                        <flux:select.option value="employee">{{ __('Employee') }}</flux:select.option>
                        <flux:select.option value="other">{{ __('Other') }}</flux:select.option>
                    </flux:select>
                    @if($suggestedDiscountType !== 'none')
                        <flux:input wire:model="suggestedDiscountReason" label="{{ __('Reason/Notes') }}" placeholder="{{ __('Optional notes...') }}" />
                    @endif
                </div>
            </div>
        </div>

        {{-- Right Panel: Prescriptions --}}
        <div class="space-y-4 lg:col-span-3">
            {{-- Prescriptions --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-3 flex items-center justify-between">
                    <flux:heading size="sm">{{ __('Prescriptions') }}</flux:heading>
                    <flux:button wire:click="openPrescriptionModal" size="sm" variant="primary" icon="plus">
                        {{ __('Add') }}
                    </flux:button>
                </div>

                @if($prescriptions->isNotEmpty())
                    <div class="space-y-2">
                        @foreach($prescriptions as $rx)
                            <div class="rounded-lg border border-zinc-100 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p class="font-medium text-zinc-900 dark:text-white">{{ $rx->medication_name }}</p>
                                        <p class="text-xs text-zinc-600 dark:text-zinc-400">
                                            @if($rx->dosage) {{ $rx->dosage }} @endif
                                            @if($rx->frequency) &bull; {{ $rx->frequency }} @endif
                                            @if($rx->duration) &bull; {{ $rx->duration }} @endif
                                        </p>
                                        @if($rx->quantity)
                                            <p class="mt-1 text-xs text-zinc-500">{{ __('Qty:') }} {{ $rx->quantity }}</p>
                                        @endif
                                        @if($rx->instructions)
                                            <p class="mt-1 text-xs italic text-zinc-500">{{ $rx->instructions }}</p>
                                        @endif
                                        @if($rx->is_hospital_drug)
                                            <flux:badge size="sm" color="blue" class="mt-1">{{ __('Hospital Pharmacy') }}</flux:badge>
                                        @endif
                                    </div>
                                    <div class="flex gap-1">
                                        <button wire:click="editPrescription({{ $rx->id }})" class="rounded p-1 text-zinc-400 hover:bg-zinc-200 hover:text-zinc-600 dark:hover:bg-zinc-700">
                                            <flux:icon name="pencil" class="h-4 w-4" />
                                        </button>
                                        <button wire:click="deletePrescription({{ $rx->id }})" wire:confirm="{{ __('Remove this prescription?') }}" class="rounded p-1 text-zinc-400 hover:bg-red-100 hover:text-red-600 dark:hover:bg-red-900/30">
                                            <flux:icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-lg border border-dashed border-zinc-200 p-6 text-center dark:border-zinc-700">
                        <flux:icon name="beaker" class="mx-auto h-8 w-8 text-zinc-300 dark:text-zinc-600" />
                        <p class="mt-2 text-sm text-zinc-500">{{ __('No prescriptions yet') }}</p>
                    </div>
                @endif
            </div>

            {{-- Prescription Notes --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:field>
                    <flux:label>{{ __('Prescription Notes') }}</flux:label>
                    <flux:textarea
                        wire:model="prescriptionNotes"
                        rows="3"
                        placeholder="{{ __('Additional instructions for patient...') }}"
                    />
                </flux:field>
            </div>
        </div>
    </div>

    {{-- Prescription Modal --}}
    <flux:modal wire:model="showPrescriptionModal" class="max-w-lg">
        <div class="space-y-4">
            <flux:heading size="lg">{{ $editingPrescriptionId ? __('Edit Prescription') : __('Add Prescription') }}</flux:heading>

            {{-- Hospital Drug Search --}}
            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:field>
                    <flux:label>{{ __('Search Hospital Pharmacy') }}</flux:label>
                    <flux:input wire:model.live.debounce.300ms="drugSearch" placeholder="{{ __('Type drug name...') }}" icon="magnifying-glass" />
                </flux:field>

                @if($drugs->isNotEmpty())
                    <div class="mt-2 max-h-32 overflow-y-auto rounded border border-zinc-200 bg-white dark:border-zinc-600 dark:bg-zinc-900">
                        @foreach($drugs as $drug)
                            <button
                                wire:click="selectHospitalDrug({{ $drug->id }})"
                                type="button"
                                class="w-full px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800"
                            >
                                <span class="font-medium">{{ $drug->drug_name }}</span>
                                @if($drug->generic_name)
                                    <span class="text-zinc-500">({{ $drug->generic_name }})</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                @endif

                @if($hospitalDrugId)
                    <div class="mt-2 flex items-center justify-between rounded bg-blue-100 px-3 py-2 dark:bg-blue-900/30">
                        <span class="text-sm font-medium text-blue-800 dark:text-blue-200">{{ __('Hospital drug selected') }}</span>
                        <button wire:click="clearHospitalDrug" type="button" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                            <flux:icon name="x-mark" class="h-4 w-4" />
                        </button>
                    </div>
                @endif
            </div>

            <flux:field>
                <flux:label>{{ __('Medication Name') }} *</flux:label>
                <flux:input wire:model="medicationName" placeholder="{{ __('e.g., Amoxicillin 500mg') }}" />
                <flux:error name="medicationName" />
            </flux:field>

            <div class="grid grid-cols-2 gap-3">
                <flux:field>
                    <flux:label>{{ __('Dosage') }}</flux:label>
                    <flux:input wire:model="dosage" placeholder="{{ __('e.g., 500mg') }}" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Frequency') }}</flux:label>
                    <flux:input wire:model="frequency" placeholder="{{ __('e.g., 3x daily') }}" />
                </flux:field>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <flux:field>
                    <flux:label>{{ __('Duration') }}</flux:label>
                    <flux:input wire:model="duration" placeholder="{{ __('e.g., 7 days') }}" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Quantity') }}</flux:label>
                    <flux:input type="number" wire:model="quantity" min="1" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>{{ __('Special Instructions') }}</flux:label>
                <flux:textarea wire:model="instructions" rows="2" placeholder="{{ __('e.g., Take after meals...') }}" />
            </flux:field>

            <div class="flex justify-end gap-2 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <flux:button wire:click="closePrescriptionModal" variant="ghost">{{ __('Cancel') }}</flux:button>
                <flux:button wire:click="savePrescription" variant="primary" icon="check">
                    {{ $editingPrescriptionId ? __('Update') : __('Add Prescription') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Complete Modal --}}
    <flux:modal wire:model="showCompleteModal" class="max-w-2xl">
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('Complete Examination') }}</flux:heading>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Select the next step for this patient:') }}</p>

            <div class="grid grid-cols-3 gap-2">
                <label class="cursor-pointer">
                    <input type="radio" wire:model.live="completionAction" value="for_billing" class="peer sr-only">
                    <div class="flex flex-col items-center gap-1 rounded-lg border p-3 text-center transition hover:bg-zinc-50 dark:hover:bg-zinc-800 peer-checked:border-zinc-900 peer-checked:bg-zinc-100 dark:peer-checked:border-white dark:peer-checked:bg-zinc-800 {{ $completionAction === 'for_billing' ? 'border-zinc-900 dark:border-white' : 'border-zinc-200 dark:border-zinc-700' }}">
                        <flux:icon name="banknotes" class="h-5 w-5 text-zinc-600 dark:text-zinc-400" />
                        <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ __('For Billing') }}</p>
                        <p class="text-xs text-zinc-500">{{ __('To cashier') }}</p>
                    </div>
                </label>

                <label class="cursor-pointer">
                    <input type="radio" wire:model.live="completionAction" value="for_admission" class="peer sr-only">
                    <div class="flex flex-col items-center gap-1 rounded-lg border p-3 text-center transition hover:bg-zinc-50 dark:hover:bg-zinc-800 peer-checked:border-zinc-900 peer-checked:bg-zinc-100 dark:peer-checked:border-white dark:peer-checked:bg-zinc-800 {{ $completionAction === 'for_admission' ? 'border-zinc-900 dark:border-white' : 'border-zinc-200 dark:border-zinc-700' }}">
                        <flux:icon name="building-office-2" class="h-5 w-5 text-zinc-600 dark:text-zinc-400" />
                        <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ __('For Admission') }}</p>
                        <p class="text-xs text-zinc-500">{{ __('Admit patient') }}</p>
                    </div>
                </label>

                <label class="cursor-pointer">
                    <input type="radio" wire:model.live="completionAction" value="completed" class="peer sr-only">
                    <div class="flex flex-col items-center gap-1 rounded-lg border p-3 text-center transition hover:bg-zinc-50 dark:hover:bg-zinc-800 peer-checked:border-zinc-900 peer-checked:bg-zinc-100 dark:peer-checked:border-white dark:peer-checked:bg-zinc-800 {{ $completionAction === 'completed' ? 'border-zinc-900 dark:border-white' : 'border-zinc-200 dark:border-zinc-700' }}">
                        <flux:icon name="check-circle" class="h-5 w-5 text-zinc-600 dark:text-zinc-400" />
                        <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ __('Completed') }}</p>
                        <p class="text-xs text-zinc-500">{{ __('No billing') }}</p>
                    </div>
                </label>
            </div>

            {{-- Admission Fields (shown when for_admission is selected) --}}
            @if($completionAction === 'for_admission')
                <div class="space-y-3 rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-800 dark:bg-amber-900/20">
                    <p class="text-xs font-medium uppercase text-amber-700 dark:text-amber-300">{{ __('Admission Details') }}</p>

                    <flux:field>
                        <flux:label>{{ __('Reason for Admission') }} <span class="text-red-500">*</span></flux:label>
                        <flux:textarea wire:model="admissionReason" rows="2" placeholder="{{ __('Enter reason for admission...') }}" />
                        <flux:error name="admissionReason" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Urgency') }} <span class="text-red-500">*</span></flux:label>
                        <div class="flex gap-2">
                            <label class="flex-1">
                                <input type="radio" wire:model="admissionUrgency" value="routine" class="peer sr-only">
                                <span class="flex cursor-pointer items-center justify-center rounded-lg border p-2 text-sm transition peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:text-green-700 dark:peer-checked:bg-green-900/30">
                                    {{ __('Routine') }}
                                </span>
                            </label>
                            <label class="flex-1">
                                <input type="radio" wire:model="admissionUrgency" value="urgent" class="peer sr-only">
                                <span class="flex cursor-pointer items-center justify-center rounded-lg border p-2 text-sm transition peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-700 dark:peer-checked:bg-amber-900/30">
                                    {{ __('Urgent') }}
                                </span>
                            </label>
                            <label class="flex-1">
                                <input type="radio" wire:model="admissionUrgency" value="emergency" class="peer sr-only">
                                <span class="flex cursor-pointer items-center justify-center rounded-lg border p-2 text-sm transition peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:text-red-700 dark:peer-checked:bg-red-900/30">
                                    {{ __('Emergency') }}
                                </span>
                            </label>
                        </div>
                        <flux:error name="admissionUrgency" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Additional Notes') }}</flux:label>
                        <flux:textarea wire:model="admissionNotes" rows="2" placeholder="{{ __('Special instructions for admissions desk...') }}" />
                        <flux:error name="admissionNotes" />
                    </flux:field>
                </div>
            @endif

            <div class="flex justify-end gap-2 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <flux:button wire:click="closeCompleteModal" variant="ghost">{{ __('Cancel') }}</flux:button>
                <flux:button wire:click="completeExamination" variant="primary" icon="check-circle">
                    {{ __('Complete') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Patient History Modal --}}
    <flux:modal wire:model="showHistoryModal" class="max-w-2xl">
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('Patient History') }}</flux:heading>

            @if($history->isNotEmpty())
                <div class="max-h-96 space-y-3 overflow-y-auto">
                    @foreach($history as $pastRecord)
                        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="font-medium text-zinc-900 dark:text-white">
                                        {{ $pastRecord->visit_date?->format('M d, Y') }}
                                    </p>
                                    <p class="text-sm text-zinc-500">
                                        {{ $pastRecord->consultationType?->name }}
                                        @if($pastRecord->doctor) &bull; Dr. {{ $pastRecord->doctor->name }} @endif
                                    </p>
                                </div>
                                <flux:badge size="sm" color="zinc">{{ $pastRecord->record_number }}</flux:badge>
                            </div>

                            @if($pastRecord->diagnosis)
                                <div class="mt-3">
                                    <p class="text-xs font-medium uppercase text-zinc-500">{{ __('Diagnosis') }}</p>
                                    <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ $pastRecord->diagnosis }}</p>
                                </div>
                            @endif

                            @if($pastRecord->prescriptions->isNotEmpty())
                                <div class="mt-3">
                                    <p class="text-xs font-medium uppercase text-zinc-500">{{ __('Prescriptions') }}</p>
                                    <ul class="mt-1 list-inside list-disc text-sm text-zinc-600 dark:text-zinc-400">
                                        @foreach($pastRecord->prescriptions as $rx)
                                            <li>{{ $rx->medication_name }} @if($rx->dosage) - {{ $rx->dosage }} @endif</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- Vital Signs Summary --}}
                            <div class="mt-3 flex flex-wrap gap-2 text-xs text-zinc-500">
                                @if($pastRecord->temperature) <span>T: {{ $pastRecord->temperature }}°C</span> @endif
                                @if($pastRecord->blood_pressure) <span>BP: {{ $pastRecord->blood_pressure }}</span> @endif
                                @if($pastRecord->weight) <span>Wt: {{ $pastRecord->weight }}kg</span> @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center">
                    <flux:icon name="document-text" class="mx-auto h-10 w-10 text-zinc-300 dark:text-zinc-600" />
                    <p class="mt-2 text-sm text-zinc-500">{{ __('No previous records found') }}</p>
                </div>
            @endif

            <div class="flex justify-end border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <flux:button wire:click="closeHistoryModal" variant="ghost">{{ __('Close') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</section>
