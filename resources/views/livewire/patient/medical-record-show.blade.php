<div class="space-y-6">
    <h1 class="text-2xl font-bold">Medical Record Details</h1>

    @if($medicalRecord)
        <!-- Basic Information -->
        <flux:card>
            <flux:card.header>
                <flux:heading>Visit Information</flux:heading>
            </flux:card.header>
            <flux:card.content>
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
            </flux:card.content>
        </flux:card>

        <!-- Vital Signs -->
        @if($vitalSigns)
            <flux:card>
                <flux:card.header>
                    <flux:heading>Vital Signs</flux:heading>
                </flux:card.header>
                <flux:card.content>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @if(isset($vitalSigns['temperature']))
                            <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-900 rounded-lg">
                                <flux:icon name="thermometer" class="w-6 h-6 mx-auto mb-2 text-red-600" />
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">Temperature</p>
                                <p class="font-medium">{{ $vitalSigns['temperature'] }}°C</p>
                            </div>
                        @endif
                        
                        @if(isset($vitalSigns['blood_pressure']))
                            <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-900 rounded-lg">
                                <flux:icon name="heart" class="w-6 h-6 mx-auto mb-2 text-blue-600" />
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">Blood Pressure</p>
                                <p class="font-medium">{{ $vitalSigns['blood_pressure'] }}</p>
                            </div>
                        @endif
                        
                        @if(isset($vitalSigns['cardiac_rate']))
                            <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-900 rounded-lg">
                                <flux:icon name="pulse" class="w-6 h-6 mx-auto mb-2 text-green-600" />
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">Cardiac Rate</p>
                                <p class="font-medium">{{ $vitalSigns['cardiac_rate'] }} bpm</p>
                            </div>
                        @endif
                        
                        @if(isset($vitalSigns['respiratory_rate']))
                            <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-900 rounded-lg">
                                <flux:icon name="lungs" class="w-6 h-6 mx-auto mb-2 text-purple-600" />
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
                                    <flux:icon name="scale" class="w-6 h-6 mx-auto mb-2 text-orange-600" />
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Weight</p>
                                    <p class="font-medium">{{ $vitalSigns['weight'] }} kg</p>
                                </div>
                            @endif
                            
                            @if(isset($vitalSigns['height']))
                                <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-900 rounded-lg">
                                    <flux:icon name="ruler" class="w-6 h-6 mx-auto mb-2 text-teal-600" />
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Height</p>
                                    <p class="font-medium">{{ $vitalSigns['height'] }} cm</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </flux:card.content>
            </flux:card>
        @endif

        <!-- Chief Complaints -->
        @if($medicalRecord->effective_chief_complaints)
            <flux:card>
                <flux:card.header>
                    <flux:heading>Chief Complaints</flux:heading>
                </flux:card.header>
                <flux:card.content>
                    <div class="bg-zinc-50 dark:bg-zinc-900 p-4 rounded-lg">
                        <p class="whitespace-pre-wrap">{{ $medicalRecord->effective_chief_complaints }}</p>
                    </div>
                </flux:card.content>
            </flux:card>
        @endif

        <!-- Diagnosis -->
        @if($medicalRecord->diagnosis)
            <flux:card>
                <flux:card.header>
                    <flux:heading>Diagnosis</flux:heading>
                </flux:card.header>
                <flux:card.content>
                    <div class="bg-zinc-50 dark:bg-zinc-900 p-4 rounded-lg">
                        <p class="whitespace-pre-wrap">{{ $medicalRecord->diagnosis }}</p>
                    </div>
                </flux:card.content>
            </flux:card>
        @endif

        <!-- Treatment Plan -->
        @if($medicalRecord->plan)
            <flux:card>
                <flux:card.header>
                    <flux:heading>Treatment Plan</flux:heading>
                </flux:card.header>
                <flux:card.content>
                    <div class="bg-zinc-50 dark:bg-zinc-900 p-4 rounded-lg">
                        <p class="whitespace-pre-wrap">{{ $medicalRecord->plan }}</p>
                    </div>
                </flux:card.content>
            </flux:card>
        @endif

        <!-- Prescriptions -->
        @if($prescriptions && $prescriptions->count() > 0)
            <flux:card>
                <flux:card.header>
                    <flux:heading>Prescriptions</flux:heading>
                </flux:card.header>
                <flux:card.content>
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
                </flux:card.content>
            </flux:card>
        @endif

        <!-- Doctor Notes -->
        @if($medicalRecord->prescription_notes)
            <flux:card>
                <flux:card.header>
                    <flux:heading>Prescription Notes</flux:heading>
                </flux:card.header>
                <flux:card.content>
                    <div class="bg-zinc-50 dark:bg-zinc-900 p-4 rounded-lg">
                        <p class="whitespace-pre-wrap">{{ $medicalRecord->prescription_notes }}</p>
                    </div>
                </flux:card.content>
            </flux:card>
        @endif

        <!-- Action Buttons -->
        <div class="flex gap-4">
            <flux:button href="{{ route('patient.records') }}" variant="outline" wire:navigate>
                Back to Records
            </flux:button>
            
            <flux:button wire:click="downloadPDF" variant="outline">
                <flux:icon name="arrow-down-tray" class="w-4 h-4 mr-2" />
                Download PDF
            </flux:button>
            
            <flux:button wire:click="shareRecord" variant="outline">
                <flux:icon name="share" class="w-4 h-4 mr-2" />
                Share Record
            </flux:button>
        </div>
    @else
        <flux:card>
            <flux:card.content class="text-center py-8">
                <flux:icon name="exclamation-triangle" class="w-12 h-12 mx-auto text-zinc-400 mb-4" />
                <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">Medical Record Not Found</h3>
                <p class="text-zinc-600 dark:text-zinc-400 mb-4">The medical record you're looking for doesn't exist or you don't have access to it.</p>
                <flux:button href="{{ route('patient.records') }}" variant="primary" wire:navigate>
                    Back to Records
                </flux:button>
            </flux:card.content>
        </flux:card>
    @endif
</div>
