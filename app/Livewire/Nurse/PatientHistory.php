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
     * Get unique patients based on search criteria.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getPatientsProperty(): Collection
    {
        if (strlen($this->search) < 2) {
            return collect();
        }

        $searchTerm = '%'.$this->search.'%';

        // Get distinct patients grouped by name + DOB
        return MedicalRecord::query()
            ->where(function ($q) use ($searchTerm) {
                $q->whereRaw("(patient_first_name || ' ' || COALESCE(patient_middle_name, '') || ' ' || patient_last_name) LIKE ?", [$searchTerm])
                    ->orWhere('patient_first_name', 'like', $searchTerm)
                    ->orWhere('patient_last_name', 'like', $searchTerm)
                    ->orWhere('patient_contact_number', 'like', $searchTerm);
            })
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
            ->limit(20)
            ->get()
            ->map(function ($patient) {
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

        return $this->patients->firstWhere('key', $this->selectedPatientKey);
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
            ->with(['consultationType', 'doctor', 'nurse'])
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
            ->with(['consultationType', 'doctor', 'nurse'])
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
