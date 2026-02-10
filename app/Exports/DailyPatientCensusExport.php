<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DailyPatientCensusExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        protected array $data
    ) {}

    public function title(): string
    {
        return 'Daily Patient Census';
    }

    public function headings(): array
    {
        return [
            '#',
            'Queue No.',
            'Record No.',
            'Patient Name',
            'Gender',
            'Age',
            'Consultation Type',
            'Visit Type',
            'Doctor',
            'Time In',
        ];
    }

    public function array(): array
    {
        $rows = [];

        // Add report header info
        $rows[] = ['Daily Patient Census Report'];
        $rows[] = ['Date: '.$this->data['date']->format('F d, Y')];
        $rows[] = ['Generated: '.now()->format('F d, Y h:i A')];
        $rows[] = [];

        // Summary
        $rows[] = ['SUMMARY'];
        $rows[] = ['Total Patients:', $this->data['total_patients']];
        $rows[] = [];

        $rows[] = ['By Consultation Type:'];
        foreach ($this->data['by_consultation_type'] as $type => $count) {
            $rows[] = ['  '.$type.':', $count];
        }
        $rows[] = [];

        $rows[] = ['By Visit Type:'];
        $rows[] = ['  New Patient:', $this->data['by_visit_type']['new']];
        $rows[] = ['  Old Patient:', $this->data['by_visit_type']['old']];
        $rows[] = ['  Revisit:', $this->data['by_visit_type']['revisit']];
        $rows[] = [];
        $rows[] = [];

        // Patient list header
        $rows[] = $this->headings();

        // Patient data
        $counter = 1;
        foreach ($this->data['records'] as $record) {
            $rows[] = [
                $counter++,
                $record->queue?->formatted_number ?? '-',
                $record->record_number,
                $record->patient_full_name,
                ucfirst($record->patient_gender ?? '-'),
                $record->patient_age ?? '-',
                $record->consultationType?->name ?? '-',
                $this->getVisitTypeLabel($record->visit_type),
                $record->doctor?->personalInformation?->full_name ?? '-',
                $record->created_at->format('h:i A'),
            ];
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

    protected function getVisitTypeLabel(?string $type): string
    {
        return match ($type) {
            'new' => 'New Patient',
            'old' => 'Old Patient',
            'revisit' => 'Revisit',
            default => '-',
        };
    }
}
