<?php

namespace App\Livewire\Patient;

use App\Models\Appointment;
use App\Models\ConsultationType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class BookAppointment extends Component
{
    public $currentStep = 1;
    
    // Step 1: Consultation Type
    public $consultationTypeId;
    
    // Step 2: Date Selection
    public $appointmentDate;
    public $availableDates = [];
    
    // Step 3: Patient Details
    public $patientType = 'self'; // self or dependent
    public $dependentName;
    public $dependentBirthDate;
    public $dependentGender;
    
    // Step 4: Chief Complaints
    public $chiefComplaints;
    
    // Review data
    public $consultationType;
    public $selectedDate;

    protected function rules(): array
    {
        return [
            'consultationTypeId' => 'required|exists:consultation_types,id',
            'appointmentDate' => 'required|date|after_or_equal:today',
            'patientType' => ['required', Rule::in(['self', 'dependent'])],
            'chiefComplaints' => 'required|string|min:10|max:1000',
            'dependentName' => 'required_if:patientType,dependent|string|max:255',
            'dependentBirthDate' => 'required_if:patientType,dependent|date|before:today',
            'dependentGender' => 'required_if:patientType,dependent|in:male,female,other',
        ];
    }

    public function mount(): void
    {
        $this->generateAvailableDates();
    }

    public function generateAvailableDates(): void
    {
        $this->availableDates = [];
        $startDate = Carbon::today();
        $maxDailyPatients = $this->consultationType?->max_daily_patients ?? 50;
        
        // Generate dates for the next 30 days
        for ($i = 0; $i < 30; $i++) {
            $date = $startDate->copy()->addDays($i);
            
            // Skip weekends (Saturday, Sunday)
            if ($date->isWeekend()) {
                continue;
            }
            
            $dayCapacity = $this->getDayCapacity($date);
            
            $this->availableDates[] = [
                'date' => $date->format('Y-m-d'),
                'day_name' => $date->format('l'),
                'formatted' => $date->format('M d, Y'),
                'capacity' => $dayCapacity,
                'max' => $maxDailyPatients,
                'available' => $dayCapacity < $maxDailyPatients,
            ];
        }
    }

    private function getDayCapacity(Carbon $date): int
    {
        if (!$this->consultationTypeId) {
            return 0;
        }

        return Appointment::query()
            ->where('consultation_type_id', $this->consultationTypeId)
            ->whereDate('appointment_date', $date)
            ->whereIn('status', ['pending', 'approved'])
            ->count();
    }

    public function selectConsultationType(int $typeId): void
    {
        $this->consultationTypeId = $typeId;
        $this->consultationType = ConsultationType::find($typeId);
        $this->generateAvailableDates();
        $this->currentStep = 2;
    }

    public function selectDate(string $date): void
    {
        $this->appointmentDate = $date;
        $this->selectedDate = collect($this->availableDates)->firstWhere('date', $date);
        $this->currentStep = 3;
    }

    public function nextStep(): void
    {
        $this->validateStep($this->currentStep);
        $this->currentStep++;
    }

    public function previousStep(): void
    {
        $this->currentStep--;
    }

    private function validateStep(int $step): void
    {
        switch ($step) {
            case 1:
                $this->validate(['consultationTypeId' => 'required|exists:consultation_types,id']);
                break;
            case 2:
                $this->validate(['appointmentDate' => 'required|date|after_or_equal:today']);
                break;
            case 3:
                $this->validate([
                    'patientType' => ['required', Rule::in(['self', 'dependent'])],
                    'dependentName' => 'required_if:patientType,dependent|string|max:255',
                    'dependentBirthDate' => 'required_if:patientType,dependent|date|before:today',
                    'dependentGender' => 'required_if:patientType,dependent|in:male,female,other',
                ]);
                break;
            case 4:
                $this->validate(['chiefComplaints' => 'required|string|min:10|max:1000']);
                break;
        }
    }

    public function submitAppointment(): void
    {
        $this->validate();

        Appointment::create([
            'user_id' => Auth::id(),
            'consultation_type_id' => $this->consultationTypeId,
            'appointment_date' => $this->appointmentDate,
            'status' => 'pending',
            'chief_complaints' => $this->chiefComplaints,
        ]);

        // In real app, send SMS notification here
        $this->dispatch('appointmentBooked', 'Appointment submitted successfully! You will receive an SMS confirmation.');
        
        // Reset form
        $this->reset();
        $this->currentStep = 1;
        $this->generateAvailableDates();
    }

    public function render(): View
    {
        $consultationTypes = ConsultationType::where('is_active', true)->get();

        return view('livewire.patient.book-appointment', [
            'consultationTypes' => $consultationTypes,
        ])->layout('layouts.patient');
    }
}
