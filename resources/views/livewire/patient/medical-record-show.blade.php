<div class="space-y-6">
    <h1 class="text-2xl font-bold">Medical Record Details</h1>

    @if($medicalRecord)
        <!-- Basic Information -->
        <div class="rounded-lg border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="border-b border-zinc-200/70 px-4 py-3 dark:border-zinc-800">
                <flux:heading>Visit Information</flux:heading>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-2">Consultation Type</h3>
                        <p class="font-medium">{{ $medicalRecord->consultationType->name }}</p>
                    </div>
                    
                    <div>
                        <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-2">Visit Date</h3>
                        <p class="font-medium">{{ \Carbon\Carbon::parse($medicalRecord->created_at)->format('M d, Y \a\t h:i A') }}</p>
                    </div>
                    
                    @if($medicalRecord->queue)
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-2">Queue Number</h3>
                            <p class="font-medium">{{ $medicalRecord->queue->formatted_number }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Vital Signs -->
        @if($vitalSigns)
            <div class="rounded-lg border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-200/70 px-4 py-3 dark:border-zinc-800">
                    <flux:heading>Vital Signs</flux:heading>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @if(isset($vitalSigns['temperature']))
                                <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-900 rounded-lg">
                                    <span aria-hidden="true" class="mx-auto mb-2 block h-6 w-6 rounded-full bg-zinc-100 dark:bg-zinc-800"></span>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Temperature</p>
                                    <p class="font-medium">{{ $vitalSigns['temperature'] }}°C</p>
                                </div>
                            @endif
                        
                            @if(isset($vitalSigns['blood_pressure']))
                                <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-900 rounded-lg">
                                    <span aria-hidden="true" class="mx-auto mb-2 block h-6 w-6 rounded-full bg-zinc-100 dark:bg-zinc-800"></span>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Blood Pressure</p>
                                    <p class="font-medium">{{ $vitalSigns['blood_pressure'] }}</p>
                                </div>
                            @endif
                        
                            @if(isset($vitalSigns['cardiac_rate']))
                                <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-900 rounded-lg">
                                    <span aria-hidden="true" class="mx-auto mb-2 block h-6 w-6 rounded-full bg-zinc-100 dark:bg-zinc-800"></span>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Cardiac Rate</p>
                                    <p class="font-medium">{{ $vitalSigns['cardiac_rate'] }} bpm</p>
                                </div>
                            @endif
                        
                            @if(isset($vitalSigns['respiratory_rate']))
                                <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-900 rounded-lg">
                                    <span aria-hidden="true" class="mx-auto mb-2 block h-6 w-6 rounded-full bg-zinc-100 dark:bg-zinc-800"></span>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Respiratory Rate</p>
                                    <p class="font-medium">{{ $vitalSigns['respiratory_rate'] }} cpm</p>
                                </div>
                            @endif
                    </div>
                    
                    <!-- Additional vital signs if available -->
                    @if(isset($vitalSigns['weight']) || isset($vitalSigns['height']))
                        <div class="grid grid-cols-2 md:grid-cols-2 gap-4 mt-4">
                            @if(isset($vitalSigns['weight']))
                                <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-900 rounded-lg">
                                    <span aria-hidden="true" class="mx-auto mb-2 block h-6 w-6 rounded-full bg-zinc-100 dark:bg-zinc-800"></span>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Weight</p>
                                    <p class="font-medium">{{ $vitalSigns['weight'] }} kg</p>
                                </div>
                            @endif
                            
                            @if(isset($vitalSigns['height']))
                                <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-900 rounded-lg">
                                    <span aria-hidden="true" class="mx-auto mb-2 block h-6 w-6 rounded-full bg-zinc-100 dark:bg-zinc-800"></span>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Height</p>
                                    <p class="font-medium">{{ $vitalSigns['height'] }} cm</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Chief Complaints -->
        @if($medicalRecord->effective_chief_complaints)
            <div class="rounded-lg border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-200/70 px-4 py-3 dark:border-zinc-800">
                    <flux:heading>Chief Complaints</flux:heading>
                </div>
                <div class="p-4">
                    <div class="bg-zinc-50 dark:bg-zinc-900 p-4 rounded-lg">
                        <p class="whitespace-pre-wrap">{{ $medicalRecord->effective_chief_complaints }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Diagnosis -->
        @if($medicalRecord->diagnosis)
            <div class="rounded-lg border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-200/70 px-4 py-3 dark:border-zinc-800">
                    <flux:heading>Diagnosis</flux:heading>
                </div>
                <div class="p-4">
                    <div class="bg-zinc-50 dark:bg-zinc-900 p-4 rounded-lg">
                        <p class="whitespace-pre-wrap">{{ $medicalRecord->diagnosis }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Treatment Plan -->
        @if($medicalRecord->plan)
            <div class="rounded-lg border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-200/70 px-4 py-3 dark:border-zinc-800">
                    <flux:heading>Treatment Plan</flux:heading>
                </div>
                <div class="p-4">
                    <div class="bg-zinc-50 dark:bg-zinc-900 p-4 rounded-lg">
                        <p class="whitespace-pre-wrap">{{ $medicalRecord->plan }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Prescriptions -->
        @if($prescriptions && $prescriptions->count() > 0)
            <div class="rounded-lg border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-200/70 px-4 py-3 dark:border-zinc-800">
                    <flux:heading>Prescriptions</flux:heading>
                </div>
                <div class="p-4">
                    <div class="space-y-4">
                        @foreach($prescriptions as $prescription)
                            <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <h4 class="font-medium">{{ $prescription->medication_name }}</h4>
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                            {{ $prescription->dosage }} - {{ $prescription->frequency }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Duration</p>
                                        <p class="font-medium">{{ $prescription->duration }}</p>
                                    </div>
                                </div>
                                
                                @if($prescription->instructions)
                                    <div class="mt-2 pt-2 border-t border-zinc-200 dark:border-zinc-700">
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                            <strong>Instructions:</strong> {{ $prescription->instructions }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Doctor Notes -->
        @if($medicalRecord->prescription_notes)
            <div class="rounded-lg border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-200/70 px-4 py-3 dark:border-zinc-800">
                    <flux:heading>Prescription Notes</flux:heading>
                </div>
                <div class="p-4">
                    <div class="bg-zinc-50 dark:bg-zinc-900 p-4 rounded-lg">
                        <p class="whitespace-pre-wrap">{{ $medicalRecord->prescription_notes }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="flex gap-4">
            <flux:button href="{{ route('patient.records') }}" variant="outline" wire:navigate>
                Back to Records
            </flux:button>
            
            <flux:button wire:click="downloadPDF" variant="outline">
                <span aria-hidden="true" class="mr-2 h-3 w-3 rounded-full bg-zinc-300"></span>
                Download PDF
            </flux:button>
            
            <flux:button wire:click="shareRecord" variant="outline">
                <span aria-hidden="true" class="mr-2 h-3 w-3 rounded-full bg-zinc-300"></span>
                Share Record
            </flux:button>
        </div>
    @else
        <div class="rounded-lg border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="px-4 py-8 text-center">
                <span aria-hidden="true" class="mx-auto mb-4 block h-12 w-12 rounded-full bg-zinc-200 dark:bg-zinc-700"></span>
                <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">Medical Record Not Found</h3>
                <p class="text-zinc-600 dark:text-zinc-400 mb-4">The medical record you're looking for doesn't exist or you don't have access to it.</p>
                <flux:button href="{{ route('patient.records') }}" variant="primary" wire:navigate>
                    Back to Records
                </flux:button>
            </div>
        </div>
    @endif
</div>
