<?php

namespace App\Livewire\Admin;

use App\Exports\AppointmentStatisticsExport;
use App\Exports\DailyPatientCensusExport;
use App\Exports\QueuePerformanceExport;
use App\Exports\ServiceUtilizationExport;
use App\Models\Appointment;
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

    #[Computed]
    public function appointmentStatsData(): array
    {
        $from = Carbon::parse($this->dateFrom)->startOfDay();
        $to = Carbon::parse($this->dateTo)->endOfDay();

        $appointments = Appointment::whereDate('appointment_date', '>=', $from->toDateString())
            ->whereDate('appointment_date', '<=', $to->toDateString())
            ->with('consultationType')
            ->get();

        $total = $appointments->count();
        $byStatus = $appointments->groupBy('status')->map->count();
        $bySource = $appointments->groupBy('source')->map->count();

        // By consultation type
        $byConsultationType = [];
        foreach ($appointments->groupBy('consultation_type_id') as $typeId => $group) {
            $typeName = $group->first()->consultationType?->name ?? 'Unknown';
            $byConsultationType[$typeName] = $group->count();
        }

        // Daily trend
        $dailyTrend = [];
        $period = Carbon::parse($this->dateFrom)->daysUntil(Carbon::parse($this->dateTo));
        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $dailyTrend[$dateStr] = $appointments->filter(
                fn ($a) => $a->appointment_date->format('Y-m-d') === $dateStr
            )->count();
        }

        return [
            'date_from' => $from,
            'date_to' => $to,
            'total' => $total,
            'completed' => $byStatus->get('completed', 0),
            'by_status' => $byStatus->toArray(),
            'by_source' => $bySource->toArray(),
            'by_consultation_type' => $byConsultationType,
            'daily_trend' => $dailyTrend,
        ];
    }

    #[Computed]
    public function serviceUtilizationData(): array
    {
        $from = Carbon::parse($this->dateFrom)->toDateString();
        $to = Carbon::parse($this->dateTo)->toDateString();

        $records = MedicalRecord::whereDate('visit_date', '>=', $from)
            ->whereDate('visit_date', '<=', $to)
            ->with('consultationType')
            ->get();

        $total = $records->count();

        // By consultation type
        $byConsultationType = [];
        foreach ($records->groupBy('consultation_type_id') as $typeId => $group) {
            $typeName = $group->first()->consultationType?->name ?? 'Unknown';
            $byConsultationType[$typeName] = $group->count();
        }

        // By visit type
        $byVisitType = $records->groupBy('visit_type')->map->count();
        $visitTypes = [
            'new' => $byVisitType->get('new', 0),
            'old' => $byVisitType->get('old', 0),
            'revisit' => $byVisitType->get('revisit', 0),
        ];

        // By source (from associated queues)
        $recordsWithQueue = MedicalRecord::whereDate('visit_date', '>=', $from)
            ->whereDate('visit_date', '<=', $to)
            ->with('queue')
            ->get();

        $online = $recordsWithQueue->filter(fn ($r) => $r->queue?->source === 'online')->count();
        $walkIn = $recordsWithQueue->filter(fn ($r) => $r->queue?->source === 'walk-in')->count();

        return [
            'date_from' => Carbon::parse($this->dateFrom),
            'date_to' => Carbon::parse($this->dateTo),
            'total' => $total,
            'by_consultation_type' => $byConsultationType,
            'by_visit_type' => $visitTypes,
            'by_source' => [
                'online' => $online,
                'walk-in' => $walkIn,
            ],
        ];
    }

    #[Computed]
    public function queuePerformanceData(): array
    {
        $from = Carbon::parse($this->dateFrom)->toDateString();
        $to = Carbon::parse($this->dateTo)->toDateString();

        $queues = Queue::where('status', 'completed')
            ->whereDate('queue_date', '>=', $from)
            ->whereDate('queue_date', '<=', $to)
            ->with('consultationType')
            ->get();

        $totalServed = $queues->count();

        // Calculate wait and service times
        $waitTimes = $queues->map(fn ($q) => $q->wait_time)->filter()->values();
        $serviceTimes = $queues->map(fn ($q) => $q->service_time)->filter()->values();

        $avgWait = $waitTimes->count() > 0 ? round($waitTimes->avg(), 1) : 0;
        $avgService = $serviceTimes->count() > 0 ? round($serviceTimes->avg(), 1) : 0;

        // Days in range
        $daysInRange = Carbon::parse($this->dateFrom)->diffInDays(Carbon::parse($this->dateTo)) + 1;
        $avgPatientsPerDay = $daysInRange > 0 ? round($totalServed / $daysInRange, 1) : 0;

        // By consultation type
        $byConsultationType = [];
        foreach ($queues->groupBy('consultation_type_id') as $typeId => $group) {
            $typeName = $group->first()->consultationType?->name ?? 'Unknown';
            $groupWait = $group->map(fn ($q) => $q->wait_time)->filter()->values();
            $groupService = $group->map(fn ($q) => $q->service_time)->filter()->values();

            $byConsultationType[$typeName] = [
                'count' => $group->count(),
                'avg_wait' => $groupWait->count() > 0 ? round($groupWait->avg(), 1) : 0,
                'avg_service' => $groupService->count() > 0 ? round($groupService->avg(), 1) : 0,
            ];
        }

        // Daily volume
        $dailyVolume = [];
        $period = Carbon::parse($this->dateFrom)->daysUntil(Carbon::parse($this->dateTo));
        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $dailyVolume[$dateStr] = $queues->filter(
                fn ($q) => $q->queue_date->format('Y-m-d') === $dateStr
            )->count();
        }

        return [
            'date_from' => Carbon::parse($this->dateFrom),
            'date_to' => Carbon::parse($this->dateTo),
            'total_served' => $totalServed,
            'avg_wait' => $avgWait,
            'avg_service' => $avgService,
            'avg_patients_per_day' => $avgPatientsPerDay,
            'by_consultation_type' => $byConsultationType,
            'daily_volume' => $dailyVolume,
        ];
    }

    public function downloadPdf(): mixed
    {
        try {
            $data = match ($this->reportType) {
                'appointment_stats' => $this->appointmentStatsData,
                'service_utilization' => $this->serviceUtilizationData,
                'queue_performance' => $this->queuePerformanceData,
                default => $this->dailyCensusData,
            };

            $viewAndFilename = match ($this->reportType) {
                'appointment_stats' => [
                    'view' => 'pdf.appointment-statistics',
                    'filename' => 'appointment-statistics-'.$this->dateFrom.'-to-'.$this->dateTo.'.pdf',
                ],
                'service_utilization' => [
                    'view' => 'pdf.service-utilization',
                    'filename' => 'service-utilization-'.$this->dateFrom.'-to-'.$this->dateTo.'.pdf',
                ],
                'queue_performance' => [
                    'view' => 'pdf.queue-performance',
                    'filename' => 'queue-performance-'.$this->dateFrom.'-to-'.$this->dateTo.'.pdf',
                ],
                default => [
                    'view' => 'pdf.daily-patient-census',
                    'filename' => 'daily-patient-census-'.$data['date']->format('Y-m-d').'.pdf',
                ],
            };

            $tempPath = storage_path('app/temp/'.$viewAndFilename['filename']);

            if (! is_dir(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            Pdf::view($viewAndFilename['view'], ['data' => $data])
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

            return response()->download($tempPath, $viewAndFilename['filename'], [
                'Content-Type' => 'application/pdf',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Toaster::error(__('Failed to generate PDF: ').$e->getMessage());

            return null;
        }
    }

    public function downloadExcel(): mixed
    {
        try {
            $exportAndFilename = match ($this->reportType) {
                'appointment_stats' => [
                    'export' => new AppointmentStatisticsExport($this->appointmentStatsData),
                    'filename' => 'appointment-statistics-'.$this->dateFrom.'-to-'.$this->dateTo.'.xlsx',
                ],
                'service_utilization' => [
                    'export' => new ServiceUtilizationExport($this->serviceUtilizationData),
                    'filename' => 'service-utilization-'.$this->dateFrom.'-to-'.$this->dateTo.'.xlsx',
                ],
                'queue_performance' => [
                    'export' => new QueuePerformanceExport($this->queuePerformanceData),
                    'filename' => 'queue-performance-'.$this->dateFrom.'-to-'.$this->dateTo.'.xlsx',
                ],
                default => [
                    'export' => new DailyPatientCensusExport($this->dailyCensusData),
                    'filename' => 'daily-patient-census-'.$this->dailyCensusData['date']->format('Y-m-d').'.xlsx',
                ],
            };

            return Excel::download($exportAndFilename['export'], $exportAndFilename['filename']);
        } catch (\Exception $e) {
            Toaster::error(__('Failed to generate Excel: ').$e->getMessage());

            return null;
        }
    }

    public function render(): View
    {
        $reportData = match ($this->reportType) {
            'appointment_stats' => $this->appointmentStatsData,
            'service_utilization' => $this->serviceUtilizationData,
            'queue_performance' => $this->queuePerformanceData,
            default => $this->dailyCensusData,
        };

        return view('livewire.admin.reports', [
            'reportData' => $reportData,
        ])->layout('layouts.app');
    }
}
