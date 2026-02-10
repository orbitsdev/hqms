<?php

namespace App\Livewire\Doctor;

use App\Models\MedicalRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;
use Spatie\LaravelPdf\Facades\Pdf;

class PatientHistory extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $selectedRecordId = null;

    public bool $showDetailModal = false;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function viewRecord(int $id): void
    {
        $this->selectedRecordId = $id;
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedRecordId = null;
    }

    public function downloadPdf(int $recordId): mixed
    {
        $record = MedicalRecord::with(['consultationType', 'doctor', 'nurse', 'prescriptions'])
            ->find($recordId);

        if (! $record) {
            Toaster::error(__('Record not found.'));

            return null;
        }

        try {
            $filename = 'medical-record-'.$record->record_number.'.pdf';
            $tempPath = storage_path('app/temp/'.$filename);

            if (! file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            // Use system Chrome/Chromium on production servers
            Pdf::view('pdf.medical-record', ['record' => $record])
                ->withBrowsershot(function (\Spatie\Browsershot\Browsershot $browsershot) {
                    if (file_exists('/usr/bin/google-chrome-stable')) {
                        $browsershot->setChromePath('/usr/bin/google-chrome-stable');
                    } elseif (file_exists('/usr/bin/chromium-browser')) {
                        $browsershot->setChromePath('/usr/bin/chromium-browser');
                    }
                    $browsershot->noSandbox();
                })
                ->format('a4')
                ->save($tempPath);

            return response()->download($tempPath, $filename, [
                'Content-Type' => 'application/pdf',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Toaster::error(__('Failed to generate PDF: ').$e->getMessage());

            return null;
        }
    }

    #[Computed]
    public function selectedRecord(): ?MedicalRecord
    {
        if (! $this->selectedRecordId) {
            return null;
        }

        return MedicalRecord::with([
            'prescriptions.hospitalDrug',
            'consultationType',
            'doctor',
            'nurse',
        ])->find($this->selectedRecordId);
    }

    public function render(): View
    {
        $doctor = Auth::user();
        $search = trim($this->search);

        // Get unique patients from medical records examined by this doctor
        // or from doctor's consultation types
        $consultationTypeIds = $doctor->consultationTypes()->pluck('consultation_types.id');

        $records = MedicalRecord::query()
            ->with(['consultationType', 'doctor'])
            ->where(function ($q) use ($doctor, $consultationTypeIds) {
                $q->where('doctor_id', $doctor->id)
                    ->orWhereIn('consultation_type_id', $consultationTypeIds);
            })
            ->whereIn('status', ['for_billing', 'for_admission', 'completed'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('patient_first_name', 'like', "%{$search}%")
                        ->orWhere('patient_last_name', 'like', "%{$search}%")
                        ->orWhere('record_number', 'like', "%{$search}%")
                        ->orWhere('diagnosis', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('visit_date')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.doctor.patient-history', [
            'records' => $records,
        ])->layout('layouts.app');
    }
}
