<?php

namespace App\Livewire\Nurse;

use App\Models\MedicalRecord;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;
use Spatie\LaravelPdf\Facades\Pdf;

class PatientHistory extends Component
{
    use WithPagination;

    public string $search = '';

    public ?string $selectedPatientKey = null;

    public ?int $viewingRecordId = null;

    public bool $showViewModal = false;

    public string $viewTab = 'patient';

    /**
     * Reset pagination when search changes.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Get unique patients with pagination.
     * Shows recent patients by default when no search term.
     * Searches by patient name, contact, OR account holder (user) name.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPatientsProperty()
    {
        $query = MedicalRecord::query();

        // Apply search filter if provided (minimum 2 characters)
        if (strlen($this->search) >= 2) {
            $searchTerm = '%'.$this->search.'%';
            $query->where(function ($q) use ($searchTerm) {
                // Search by patient name
                $q->whereRaw("(patient_first_name || ' ' || COALESCE(patient_middle_name, '') || ' ' || patient_last_name) LIKE ?", [$searchTerm])
                    ->orWhere('patient_first_name', 'like', $searchTerm)
                    ->orWhere('patient_last_name', 'like', $searchTerm)
                    // Search by account holder (user) name
                    ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                        $userQuery->where('name', 'like', $searchTerm)
                            ->orWhereHas('personalInformation', function ($piQuery) use ($searchTerm) {
                                $piQuery->whereRaw("(first_name || ' ' || COALESCE(middle_name, '') || ' ' || last_name) LIKE ?", [$searchTerm])
                                    ->orWhere('first_name', 'like', $searchTerm)
                                    ->orWhere('last_name', 'like', $searchTerm);
                            });
                    });
            });
        }

        // Get distinct patients grouped by name + DOB with pagination
        $paginated = $query
            ->selectRaw('
                patient_first_name,
                patient_middle_name,
                patient_last_name,
                patient_date_of_birth,
                patient_gender,
                patient_contact_number,
                COUNT(*) as visit_count,
                MAX(visit_date) as last_visit,
                MIN(visit_date) as first_visit
            ')
            ->groupBy([
                'patient_first_name',
                'patient_middle_name',
                'patient_last_name',
                'patient_date_of_birth',
                'patient_gender',
                'patient_contact_number',
            ])
            ->orderByDesc('last_visit')
            ->paginate(12);

        // Transform the items
        $paginated->getCollection()->transform(function ($patient) {
            $fullName = trim(implode(' ', array_filter([
                $patient->patient_first_name,
                $patient->patient_middle_name,
                $patient->patient_last_name,
            ])));

            $key = md5($patient->patient_first_name.$patient->patient_last_name.$patient->patient_date_of_birth);

            return [
                'key' => $key,
                'full_name' => $fullName,
                'first_name' => $patient->patient_first_name,
                'middle_name' => $patient->patient_middle_name,
                'last_name' => $patient->patient_last_name,
                'date_of_birth' => $patient->patient_date_of_birth,
                'gender' => $patient->patient_gender,
                'contact_number' => $patient->patient_contact_number,
                'visit_count' => $patient->visit_count,
                'last_visit' => $patient->last_visit,
                'first_visit' => $patient->first_visit,
                'age' => $patient->patient_date_of_birth
                    ? now()->diffInYears($patient->patient_date_of_birth)
                    : null,
            ];
        });

        return $paginated;
    }

    /**
     * Get selected patient details.
     *
     * @return array<string, mixed>|null
     */
    public function getSelectedPatientProperty(): ?array
    {
        if (! $this->selectedPatientKey) {
            return null;
        }

        return $this->patients->getCollection()->firstWhere('key', $this->selectedPatientKey);
    }

    /**
     * Get medical records for selected patient.
     *
     * @return Collection<int, MedicalRecord>
     */
    public function getPatientRecordsProperty(): Collection
    {
        if (! $this->selectedPatient) {
            return collect();
        }

        return MedicalRecord::query()
            ->with(['consultationType', 'doctor', 'nurse', 'user.personalInformation'])
            ->where('patient_first_name', $this->selectedPatient['first_name'])
            ->where('patient_last_name', $this->selectedPatient['last_name'])
            ->when($this->selectedPatient['date_of_birth'], function ($q) {
                $q->whereDate('patient_date_of_birth', $this->selectedPatient['date_of_birth']);
            })
            ->orderByDesc('visit_date')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get the record being viewed.
     */
    public function getViewingRecordProperty(): ?MedicalRecord
    {
        if (! $this->viewingRecordId) {
            return null;
        }

        return MedicalRecord::query()
            ->with(['consultationType', 'doctor', 'nurse', 'user.personalInformation'])
            ->find($this->viewingRecordId);
    }

    public function selectPatient(string $key): void
    {
        $this->selectedPatientKey = $key;
    }

    public function clearSelection(): void
    {
        $this->selectedPatientKey = null;
    }

    public function viewRecord(int $recordId): void
    {
        $this->viewingRecordId = $recordId;
        $this->viewTab = 'patient';
        $this->showViewModal = true;
    }

    public function setViewTab(string $tab): void
    {
        $this->viewTab = $tab;
    }

    public function closeViewModal(): void
    {
        $this->showViewModal = false;
        $this->viewingRecordId = null;
    }

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
            Pdf::view('pdf.medical-record', ['record' => $record])
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

    public function render(): View
    {
        return view('livewire.nurse.patient-history', [
            'patients' => $this->patients,
            'selectedPatient' => $this->selectedPatient,
            'patientRecords' => $this->patientRecords,
        ])->layout('layouts.app');
    }
}
