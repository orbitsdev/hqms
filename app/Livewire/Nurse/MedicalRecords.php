<?php

namespace App\Livewire\Nurse;

use App\Models\ConsultationType;
use App\Models\MedicalRecord;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;
use Spatie\LaravelPdf\Facades\Pdf;

class MedicalRecords extends Component
{
    use WithPagination;

    // ==================== SEARCH & FILTERS ====================
    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $consultationTypeFilter = '';

    #[Url(history: true)]
    public string $doctorFilter = '';

    #[Url(history: true)]
    public string $statusFilter = '';

    #[Url(history: true)]
    public string $visitTypeFilter = '';

    #[Url(history: true)]
    public string $dateFrom = '';

    #[Url(history: true)]
    public string $dateTo = '';

    #[Url(history: true)]
    public string $sortField = 'visit_date';

    #[Url(history: true)]
    public string $sortDirection = 'desc';

    public bool $showFilters = false;

    // ==================== VIEW MODAL ====================
    public bool $showViewModal = false;

    #[Locked]
    public ?int $viewRecordId = null;

    public string $viewTab = 'patient';

    // ==================== EDIT MODAL ====================
    public bool $showEditModal = false;

    #[Locked]
    public ?int $editRecordId = null;

    public string $editStep = 'patient';

    // Patient Information
    public string $patientFirstName = '';

    public ?string $patientMiddleName = null;

    public string $patientLastName = '';

    public ?string $patientDateOfBirth = null;

    public ?string $patientGender = null;

    public ?string $patientMaritalStatus = null;

    public ?string $patientContactNumber = null;

    public ?string $patientOccupation = null;

    public ?string $patientReligion = null;

    // Patient Address
    public ?string $patientProvince = null;

    public ?string $patientMunicipality = null;

    public ?string $patientBarangay = null;

    public ?string $patientStreet = null;

    // Companion/Watcher
    public ?string $companionName = null;

    public ?string $companionContact = null;

    public ?string $companionRelationship = null;

    // Emergency Contact
    public ?string $emergencyContactName = null;

    public ?string $emergencyContactPhone = null;

    // Medical Background
    public ?string $patientBloodType = null;

    public ?string $patientAllergies = null;

    public ?string $patientChronicConditions = null;

    // Visit Information
    public ?string $visitType = null;

    public ?string $serviceType = null;

    public ?string $obType = null;

    public ?string $serviceCategory = null;

    public ?string $chiefComplaintsInitial = null;

    public ?string $chiefComplaintsUpdated = null;

    // Vital Signs
    public ?string $temperature = null;

    public ?string $bloodPressure = null;

    public ?int $cardiacRate = null;

    public ?int $respiratoryRate = null;

    public ?string $weight = null;

    public ?string $height = null;

    public ?string $headCircumference = null;

    public ?string $chestCircumference = null;

    public ?int $fetalHeartTone = null;

    public ?string $fundalHeight = null;

    public ?string $lastMenstrualPeriod = null;

    // ==================== LIFECYCLE ====================

    public function mount(): void
    {
        // Default to last 30 days if no date filter
        if (! $this->dateFrom && ! $this->dateTo) {
            $this->dateFrom = now()->subDays(30)->format('Y-m-d');
            $this->dateTo = now()->format('Y-m-d');
        }
    }

    // ==================== FILTER METHODS ====================

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedConsultationTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDoctorFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedVisitTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function toggleFilters(): void
    {
        $this->showFilters = ! $this->showFilters;
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->consultationTypeFilter = '';
        $this->doctorFilter = '';
        $this->statusFilter = '';
        $this->visitTypeFilter = '';
        $this->dateFrom = now()->subDays(30)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
        $this->resetPage();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    // ==================== SORTING ====================

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    // ==================== VIEW MODAL ====================

    public function viewRecord(int $recordId): void
    {
        $this->viewRecordId = $recordId;
        $this->viewTab = 'patient';
        $this->showViewModal = true;
    }

    public function closeViewModal(): void
    {
        $this->showViewModal = false;
        $this->viewRecordId = null;
        $this->viewTab = 'patient';
    }

    public function setViewTab(string $tab): void
    {
        $this->viewTab = $tab;
    }

    #[Computed]
    public function viewingRecord(): ?MedicalRecord
    {
        if (! $this->viewRecordId) {
            return null;
        }

        return MedicalRecord::with(['consultationType', 'doctor', 'nurse', 'appointment', 'queue'])
            ->find($this->viewRecordId);
    }

    // ==================== EDIT MODAL ====================

    public function editRecord(int $recordId): void
    {
        $record = MedicalRecord::find($recordId);

        if (! $record) {
            Toaster::error(__('Medical record not found.'));

            return;
        }

        $this->editRecordId = $recordId;
        $this->editStep = 'patient';

        // Populate form fields
        $this->patientFirstName = $record->patient_first_name;
        $this->patientMiddleName = $record->patient_middle_name;
        $this->patientLastName = $record->patient_last_name;
        $this->patientDateOfBirth = $record->patient_date_of_birth?->format('Y-m-d');
        $this->patientGender = $record->patient_gender;
        $this->patientMaritalStatus = $record->patient_marital_status;
        $this->patientContactNumber = $record->patient_contact_number;
        $this->patientOccupation = $record->patient_occupation;
        $this->patientReligion = $record->patient_religion;

        $this->patientProvince = $record->patient_province;
        $this->patientMunicipality = $record->patient_municipality;
        $this->patientBarangay = $record->patient_barangay;
        $this->patientStreet = $record->patient_street;

        $this->companionName = $record->companion_name;
        $this->companionContact = $record->companion_contact;
        $this->companionRelationship = $record->companion_relationship;

        $this->emergencyContactName = $record->emergency_contact_name;
        $this->emergencyContactPhone = $record->emergency_contact_phone;

        $this->patientBloodType = $record->patient_blood_type;
        $this->patientAllergies = $record->patient_allergies;
        $this->patientChronicConditions = $record->patient_chronic_conditions;

        $this->visitType = $record->visit_type;
        $this->serviceType = $record->service_type;
        $this->obType = $record->ob_type;
        $this->serviceCategory = $record->service_category;
        $this->chiefComplaintsInitial = $record->chief_complaints_initial;
        $this->chiefComplaintsUpdated = $record->chief_complaints_updated;

        $this->temperature = $record->temperature;
        $this->bloodPressure = $record->blood_pressure;
        $this->cardiacRate = $record->cardiac_rate;
        $this->respiratoryRate = $record->respiratory_rate;
        $this->weight = $record->weight;
        $this->height = $record->height;
        $this->headCircumference = $record->head_circumference;
        $this->chestCircumference = $record->chest_circumference;
        $this->fetalHeartTone = $record->fetal_heart_tone;
        $this->fundalHeight = $record->fundal_height;
        $this->lastMenstrualPeriod = $record->last_menstrual_period?->format('Y-m-d');

        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editRecordId = null;
        $this->editStep = 'patient';
        $this->resetEditForm();
    }

    public function setEditStep(string $step): void
    {
        $this->editStep = $step;
    }

    public function nextStep(): void
    {
        $steps = ['patient', 'address', 'companion', 'medical', 'visit', 'vitals'];
        $currentIndex = array_search($this->editStep, $steps);

        if ($currentIndex !== false && $currentIndex < count($steps) - 1) {
            $this->editStep = $steps[$currentIndex + 1];
        }
    }

    public function previousStep(): void
    {
        $steps = ['patient', 'address', 'companion', 'medical', 'visit', 'vitals'];
        $currentIndex = array_search($this->editStep, $steps);

        if ($currentIndex !== false && $currentIndex > 0) {
            $this->editStep = $steps[$currentIndex - 1];
        }
    }

    protected function resetEditForm(): void
    {
        $this->patientFirstName = '';
        $this->patientMiddleName = null;
        $this->patientLastName = '';
        $this->patientDateOfBirth = null;
        $this->patientGender = null;
        $this->patientMaritalStatus = null;
        $this->patientContactNumber = null;
        $this->patientOccupation = null;
        $this->patientReligion = null;

        $this->patientProvince = null;
        $this->patientMunicipality = null;
        $this->patientBarangay = null;
        $this->patientStreet = null;

        $this->companionName = null;
        $this->companionContact = null;
        $this->companionRelationship = null;

        $this->emergencyContactName = null;
        $this->emergencyContactPhone = null;

        $this->patientBloodType = null;
        $this->patientAllergies = null;
        $this->patientChronicConditions = null;

        $this->visitType = null;
        $this->serviceType = null;
        $this->obType = null;
        $this->serviceCategory = null;
        $this->chiefComplaintsInitial = null;
        $this->chiefComplaintsUpdated = null;

        $this->temperature = null;
        $this->bloodPressure = null;
        $this->cardiacRate = null;
        $this->respiratoryRate = null;
        $this->weight = null;
        $this->height = null;
        $this->headCircumference = null;
        $this->chestCircumference = null;
        $this->fetalHeartTone = null;
        $this->fundalHeight = null;
        $this->lastMenstrualPeriod = null;
    }

    public function saveRecord(): void
    {
        $this->validate([
            // Patient Info
            'patientFirstName' => ['required', 'string', 'max:255'],
            'patientMiddleName' => ['nullable', 'string', 'max:255'],
            'patientLastName' => ['required', 'string', 'max:255'],
            'patientDateOfBirth' => ['nullable', 'date', 'before_or_equal:today'],
            'patientGender' => ['nullable', 'in:male,female'],
            'patientMaritalStatus' => ['nullable', 'in:child,single,married,widow'],
            'patientContactNumber' => ['nullable', 'string', 'max:20'],
            'patientOccupation' => ['nullable', 'string', 'max:255'],
            'patientReligion' => ['nullable', 'string', 'max:255'],

            // Address
            'patientProvince' => ['nullable', 'string', 'max:255'],
            'patientMunicipality' => ['nullable', 'string', 'max:255'],
            'patientBarangay' => ['nullable', 'string', 'max:255'],
            'patientStreet' => ['nullable', 'string', 'max:1000'],

            // Companion
            'companionName' => ['nullable', 'string', 'max:255'],
            'companionContact' => ['nullable', 'string', 'max:20'],
            'companionRelationship' => ['nullable', 'string', 'max:255'],

            // Emergency
            'emergencyContactName' => ['nullable', 'string', 'max:255'],
            'emergencyContactPhone' => ['nullable', 'string', 'max:20'],

            // Medical Background
            'patientBloodType' => ['nullable', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'patientAllergies' => ['nullable', 'string', 'max:2000'],
            'patientChronicConditions' => ['nullable', 'string', 'max:2000'],

            // Visit
            'visitType' => ['nullable', 'in:new,old,revisit'],
            'serviceType' => ['nullable', 'in:checkup,admission'],
            'obType' => ['nullable', 'in:prenatal,post-natal'],
            'serviceCategory' => ['nullable', 'in:surgical,non-surgical'],
            'chiefComplaintsInitial' => ['nullable', 'string', 'max:2000'],
            'chiefComplaintsUpdated' => ['nullable', 'string', 'max:2000'],

            // Vital Signs
            'temperature' => ['nullable', 'numeric', 'min:30', 'max:45'],
            'bloodPressure' => ['nullable', 'string', 'max:20', 'regex:/^\d{2,3}\/\d{2,3}$/'],
            'cardiacRate' => ['nullable', 'integer', 'min:30', 'max:250'],
            'respiratoryRate' => ['nullable', 'integer', 'min:5', 'max:60'],
            'weight' => ['nullable', 'numeric', 'min:0.1', 'max:500'],
            'height' => ['nullable', 'numeric', 'min:10', 'max:300'],
            'headCircumference' => ['nullable', 'numeric', 'min:20', 'max:100'],
            'chestCircumference' => ['nullable', 'numeric', 'min:20', 'max:200'],
            'fetalHeartTone' => ['nullable', 'integer', 'min:60', 'max:200'],
            'fundalHeight' => ['nullable', 'numeric', 'min:5', 'max:50'],
            'lastMenstrualPeriod' => ['nullable', 'date', 'before_or_equal:today'],
        ], [
            'bloodPressure.regex' => __('Blood pressure format: 120/80'),
        ]);

        $record = MedicalRecord::find($this->editRecordId);

        if (! $record) {
            Toaster::error(__('Medical record not found.'));
            $this->closeEditModal();

            return;
        }

        DB::transaction(function () use ($record): void {
            $hadVitals = $record->vital_signs_recorded_at !== null;
            $hasNewVitals = $this->temperature || $this->bloodPressure || $this->cardiacRate
                || $this->respiratoryRate || $this->weight || $this->height;

            $record->update([
                // Patient Info
                'patient_first_name' => $this->patientFirstName,
                'patient_middle_name' => $this->patientMiddleName,
                'patient_last_name' => $this->patientLastName,
                'patient_date_of_birth' => $this->patientDateOfBirth,
                'patient_gender' => $this->patientGender,
                'patient_marital_status' => $this->patientMaritalStatus,
                'patient_contact_number' => $this->patientContactNumber,
                'patient_occupation' => $this->patientOccupation,
                'patient_religion' => $this->patientReligion,

                // Address
                'patient_province' => $this->patientProvince,
                'patient_municipality' => $this->patientMunicipality,
                'patient_barangay' => $this->patientBarangay,
                'patient_street' => $this->patientStreet,

                // Companion
                'companion_name' => $this->companionName,
                'companion_contact' => $this->companionContact,
                'companion_relationship' => $this->companionRelationship,

                // Emergency
                'emergency_contact_name' => $this->emergencyContactName,
                'emergency_contact_phone' => $this->emergencyContactPhone,

                // Medical Background
                'patient_blood_type' => $this->patientBloodType,
                'patient_allergies' => $this->patientAllergies,
                'patient_chronic_conditions' => $this->patientChronicConditions,

                // Visit
                'visit_type' => $this->visitType,
                'service_type' => $this->serviceType,
                'ob_type' => $this->obType,
                'service_category' => $this->serviceCategory,
                'chief_complaints_initial' => $this->chiefComplaintsInitial,
                'chief_complaints_updated' => $this->chiefComplaintsUpdated,

                // Vital Signs
                'temperature' => $this->temperature,
                'blood_pressure' => $this->bloodPressure,
                'cardiac_rate' => $this->cardiacRate,
                'respiratory_rate' => $this->respiratoryRate,
                'weight' => $this->weight,
                'height' => $this->height,
                'head_circumference' => $this->headCircumference,
                'chest_circumference' => $this->chestCircumference,
                'fetal_heart_tone' => $this->fetalHeartTone,
                'fundal_height' => $this->fundalHeight,
                'last_menstrual_period' => $this->lastMenstrualPeriod,

                // Update vital signs timestamp if new vitals were added
                'vital_signs_recorded_at' => $hasNewVitals && ! $hadVitals ? now() : $record->vital_signs_recorded_at,
            ]);
        });

        Toaster::success(__('Medical record updated successfully.'));
        $this->closeEditModal();
    }

    // ==================== PDF DOWNLOAD ====================

    public function downloadPdf(int $recordId): mixed
    {
        $record = MedicalRecord::with(['consultationType', 'doctor', 'nurse'])
            ->find($recordId);

        if (! $record) {
            Toaster::error(__('Medical record not found.'));

            return null;
        }

        try {
            $filename = 'medical-record-'.$record->record_number.'.pdf';
            $tempPath = storage_path('app/temp/'.$filename);

            // Ensure temp directory exists
            if (! is_dir(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            // Generate PDF and save to temp file
            // Use system Chromium on production servers
            Pdf::view('pdf.medical-record', ['record' => $record])
                ->withBrowsershot(function (\Spatie\Browsershot\Browsershot $browsershot) {
                    // Check for system-installed Chromium (production server)
                    if (file_exists('/usr/bin/chromium-browser')) {
                        $browsershot->setChromePath('/usr/bin/chromium-browser');
                    } elseif (file_exists('/snap/bin/chromium')) {
                        $browsershot->setChromePath('/snap/bin/chromium');
                    }
                    $browsershot->noSandbox();
                })
                ->format('a4')
                ->save($tempPath);

            // Return download response and delete temp file after
            return response()->download($tempPath, $filename, [
                'Content-Type' => 'application/pdf',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Toaster::error(__('Failed to generate PDF: ').$e->getMessage());

            return null;
        }
    }

    // ==================== COMPUTED PROPERTIES ====================

    /** @return LengthAwarePaginator<MedicalRecord> */
    #[Computed]
    public function records(): LengthAwarePaginator
    {
        $query = MedicalRecord::query()
            ->with(['consultationType', 'doctor', 'nurse', 'appointment', 'queue']);

        // Search by patient name or record number
        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('record_number', 'like', "%{$search}%")
                    ->orWhere('patient_first_name', 'like', "%{$search}%")
                    ->orWhere('patient_last_name', 'like', "%{$search}%")
                    ->orWhereRaw("(patient_first_name || ' ' || patient_last_name) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("(patient_last_name || ', ' || patient_first_name) LIKE ?", ["%{$search}%"]);
            });
        }

        // Filter by consultation type
        if ($this->consultationTypeFilter) {
            $query->where('consultation_type_id', $this->consultationTypeFilter);
        }

        // Filter by doctor
        if ($this->doctorFilter) {
            $query->where('doctor_id', $this->doctorFilter);
        }

        // Filter by status
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        // Filter by visit type
        if ($this->visitTypeFilter) {
            $query->where('visit_type', $this->visitTypeFilter);
        }

        // Filter by date range
        if ($this->dateFrom) {
            $query->whereDate('visit_date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('visit_date', '<=', $this->dateTo);
        }

        // Apply sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        return $query->paginate(15);
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, ConsultationType> */
    #[Computed]
    public function consultationTypes()
    {
        return ConsultationType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, User> */
    #[Computed]
    public function doctors()
    {
        return User::role('doctor')
            ->orderBy('first_name')
            ->get();
    }

    /** @return array<string, int> */
    #[Computed]
    public function stats(): array
    {
        $today = now()->toDateString();
        $thisMonth = now()->startOfMonth()->toDateString();

        return [
            'today' => MedicalRecord::whereDate('visit_date', $today)->count(),
            'this_month' => MedicalRecord::whereDate('visit_date', '>=', $thisMonth)->count(),
            'in_progress' => MedicalRecord::where('status', 'in_progress')->count(),
            'for_billing' => MedicalRecord::where('status', 'for_billing')->count(),
        ];
    }

    /** @return array<string, string> */
    public function getStatusOptionsProperty(): array
    {
        return [
            'in_progress' => __('In Progress'),
            'for_billing' => __('For Billing'),
            'for_admission' => __('For Admission'),
            'completed' => __('Completed'),
        ];
    }

    /** @return array<string, string> */
    public function getVisitTypeOptionsProperty(): array
    {
        return [
            'new' => __('New Patient'),
            'old' => __('Old Patient'),
            'revisit' => __('Revisit'),
        ];
    }

    /** @return array<string, string> */
    public function getGenderOptionsProperty(): array
    {
        return [
            'male' => __('Male'),
            'female' => __('Female'),
        ];
    }

    /** @return array<string, string> */
    public function getMaritalStatusOptionsProperty(): array
    {
        return [
            'child' => __('Child'),
            'single' => __('Single'),
            'married' => __('Married'),
            'widow' => __('Widow'),
        ];
    }

    /** @return array<string, string> */
    public function getBloodTypeOptionsProperty(): array
    {
        return [
            'A+' => 'A+',
            'A-' => 'A-',
            'B+' => 'B+',
            'B-' => 'B-',
            'AB+' => 'AB+',
            'AB-' => 'AB-',
            'O+' => 'O+',
            'O-' => 'O-',
        ];
    }

    /** @return array<string, string> */
    public function getServiceTypeOptionsProperty(): array
    {
        return [
            'checkup' => __('Check-up'),
            'admission' => __('Admission'),
        ];
    }

    /** @return array<string, string> */
    public function getObTypeOptionsProperty(): array
    {
        return [
            'prenatal' => __('Prenatal'),
            'post-natal' => __('Post-natal'),
        ];
    }

    /** @return array<string, string> */
    public function getServiceCategoryOptionsProperty(): array
    {
        return [
            'surgical' => __('Surgical'),
            'non-surgical' => __('Non-surgical'),
        ];
    }

    public function render(): View
    {
        return view('livewire.nurse.medical-records', [
            'records' => $this->records,
            'consultationTypes' => $this->consultationTypes,
            'doctors' => $this->doctors,
            'stats' => $this->stats,
            'statusOptions' => $this->statusOptions,
            'visitTypeOptions' => $this->visitTypeOptions,
            'genderOptions' => $this->genderOptions,
            'maritalStatusOptions' => $this->maritalStatusOptions,
            'bloodTypeOptions' => $this->bloodTypeOptions,
            'serviceTypeOptions' => $this->serviceTypeOptions,
            'obTypeOptions' => $this->obTypeOptions,
            'serviceCategoryOptions' => $this->serviceCategoryOptions,
        ])->layout('layouts.app');
    }
}
