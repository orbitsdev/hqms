<?php

namespace App\Livewire\Nurse;

use App\Exports\DailyPatientCensusExport;
use App\Models\MedicalRecord;
use App\Models\Queue;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Masmerise\Toaster\Toaster;
use Spatie\LaravelPdf\Facades\Pdf;

class Reports extends Component
{
    public string $reportType = 'daily_census';

    public string $reportDate = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public function mount(): void
    {
        $this->reportDate = today()->format('Y-m-d');
        $this->dateFrom = today()->startOfMonth()->format('Y-m-d');
        $this->dateTo = today()->format('Y-m-d');
    }

    #[Computed]
    public function dailyCensusData(): array
    {
        $date = Carbon::parse($this->reportDate);

        $records = MedicalRecord::whereDate('created_at', $date)
            ->with(['consultationType', 'doctor', 'queue'])
            ->orderBy('created_at')
            ->get();

        $queues = Queue::whereDate('queue_date', $date)
            ->with(['consultationType', 'appointment'])
            ->orderBy('queue_number')
            ->get();

        // Statistics
        $totalPatients = $records->count();
        $byConsultationType = $records->groupBy('consultation_type_id')->map->count();
        $byVisitType = $records->groupBy('visit_type')->map->count();

        // Get consultation type names
        $consultationTypes = [];
        foreach ($byConsultationType as $typeId => $count) {
            $type = $records->firstWhere('consultation_type_id', $typeId)?->consultationType;
            if ($type) {
                $consultationTypes[$type->name] = $count;
            }
        }

        // Visit type labels
        $visitTypes = [
            'new' => $byVisitType->get('new', 0),
            'old' => $byVisitType->get('old', 0),
            'revisit' => $byVisitType->get('revisit', 0),
        ];

        return [
            'date' => $date,
            'records' => $records,
            'queues' => $queues,
            'total_patients' => $totalPatients,
            'by_consultation_type' => $consultationTypes,
            'by_visit_type' => $visitTypes,
        ];
    }

    public function downloadPdf(): mixed
    {
        $data = $this->dailyCensusData;

        try {
            $filename = 'daily-patient-census-'.$data['date']->format('Y-m-d').'.pdf';
            $tempPath = storage_path('app/temp/'.$filename);

            if (! is_dir(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            Pdf::view('pdf.daily-patient-census', ['data' => $data])
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

    public function downloadExcel(): mixed
    {
        $data = $this->dailyCensusData;

        try {
            $filename = 'daily-patient-census-'.$data['date']->format('Y-m-d').'.xlsx';

            return Excel::download(new DailyPatientCensusExport($data), $filename);
        } catch (\Exception $e) {
            Toaster::error(__('Failed to generate Excel: ').$e->getMessage());

            return null;
        }
    }

    public function render(): View
    {
        return view('livewire.nurse.reports', [
            'censusData' => $this->dailyCensusData,
        ])->layout('layouts.app');
    }
}
