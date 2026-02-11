<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class QueuePerformanceExport implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(
        protected array $data
    ) {}

    public function title(): string
    {
        return 'Queue Performance';
    }

    public function array(): array
    {
        $rows = [];

        $rows[] = ['Queue Performance Report'];
        $rows[] = ['Period: '.$this->data['date_from']->format('F d, Y').' - '.$this->data['date_to']->format('F d, Y')];
        $rows[] = ['Generated: '.now()->format('F d, Y h:i A')];
        $rows[] = [];

        // KPIs
        $rows[] = ['KEY PERFORMANCE INDICATORS'];
        $rows[] = ['Total Patients Served:', $this->data['total_served']];
        $rows[] = ['Average Wait Time (min):', $this->data['avg_wait']];
        $rows[] = ['Average Service Time (min):', $this->data['avg_service']];
        $rows[] = ['Average Patients/Day:', $this->data['avg_patients_per_day']];
        $rows[] = [];

        // By Consultation Type
        $rows[] = ['PERFORMANCE BY CONSULTATION TYPE'];
        $rows[] = ['Type', 'Patients', 'Avg Wait (min)', 'Avg Service (min)'];
        foreach ($this->data['by_consultation_type'] as $type => $metrics) {
            $rows[] = [$type, $metrics['count'], $metrics['avg_wait'], $metrics['avg_service']];
        }
        $rows[] = [];

        // Daily Volume
        $rows[] = ['DAILY PATIENT VOLUME'];
        $rows[] = ['Date', 'Patients Served'];
        foreach ($this->data['daily_volume'] as $date => $count) {
            $rows[] = [$date, $count];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            2 => ['font' => ['bold' => true]],
            3 => ['font' => ['italic' => true]],
        ];
    }
}
