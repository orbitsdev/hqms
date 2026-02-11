<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ServiceUtilizationExport implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(
        protected array $data
    ) {}

    public function title(): string
    {
        return 'Service Utilization';
    }

    public function array(): array
    {
        $rows = [];

        $rows[] = ['Service Utilization Report'];
        $rows[] = ['Period: '.$this->data['date_from']->format('F d, Y').' - '.$this->data['date_to']->format('F d, Y')];
        $rows[] = ['Generated: '.now()->format('F d, Y h:i A')];
        $rows[] = [];

        // Summary
        $rows[] = ['SUMMARY'];
        $rows[] = ['Total Services:', $this->data['total']];
        $rows[] = [];

        // By Consultation Type
        $rows[] = ['CONSULTATION TYPE DISTRIBUTION'];
        $rows[] = ['Type', 'Count', 'Percentage'];
        foreach ($this->data['by_consultation_type'] as $type => $count) {
            $pct = $this->data['total'] > 0 ? round(($count / $this->data['total']) * 100, 1) : 0;
            $rows[] = [$type, $count, $pct.'%'];
        }
        $rows[] = [];

        // By Visit Type
        $totalVisit = $this->data['by_visit_type']['new'] + $this->data['by_visit_type']['old'] + $this->data['by_visit_type']['revisit'];
        $rows[] = ['VISIT TYPE DISTRIBUTION'];
        $rows[] = ['Type', 'Count', 'Percentage'];
        foreach (['new' => 'New Patient', 'old' => 'Old Patient', 'revisit' => 'Revisit'] as $key => $label) {
            $count = $this->data['by_visit_type'][$key];
            $pct = $totalVisit > 0 ? round(($count / $totalVisit) * 100, 1) : 0;
            $rows[] = [$label, $count, $pct.'%'];
        }
        $rows[] = [];

        // By Source
        $totalSource = $this->data['by_source']['online'] + $this->data['by_source']['walk-in'];
        $rows[] = ['SOURCE DISTRIBUTION'];
        $rows[] = ['Source', 'Count', 'Percentage'];
        foreach (['online' => 'Online', 'walk-in' => 'Walk-in'] as $key => $label) {
            $count = $this->data['by_source'][$key];
            $pct = $totalSource > 0 ? round(($count / $totalSource) * 100, 1) : 0;
            $rows[] = [$label, $count, $pct.'%'];
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
