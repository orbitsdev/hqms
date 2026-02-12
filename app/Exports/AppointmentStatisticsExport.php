<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AppointmentStatisticsExport implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(
        protected array $data
    ) {}

    public function title(): string
    {
        return 'Appointment Statistics';
    }

    public function array(): array
    {
        $rows = [];

        $rows[] = ['Appointment Statistics Report'];
        $rows[] = ['Period: '.$this->data['date_from']->format('F d, Y').' - '.$this->data['date_to']->format('F d, Y')];
        $rows[] = ['Generated: '.now()->format('F d, Y h:i A')];
        $rows[] = [];

        // Summary
        $rows[] = ['SUMMARY'];
        $rows[] = ['Total Appointments:', $this->data['total']];
        $rows[] = ['Completed:', $this->data['completed']];
        $rows[] = [];

        // By Status
        $rows[] = ['APPOINTMENT STATUS BREAKDOWN'];
        $rows[] = ['Status', 'Count'];
        $statusLabels = [
            'pending' => 'Pending',
            'approved' => 'Approved',
            'checked_in' => 'Checked In',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'no_show' => 'No Show',
        ];
        foreach ($statusLabels as $status => $label) {
            $rows[] = [$label, $this->data['by_status'][$status] ?? 0];
        }
        $rows[] = [];

        // By Source
        $rows[] = ['SOURCE BREAKDOWN'];
        $rows[] = ['Source', 'Count'];
        $rows[] = ['Online Booking', $this->data['by_source']['online'] ?? 0];
        $rows[] = ['Walk-in', $this->data['by_source']['walk-in'] ?? 0];
        $rows[] = [];

        // By Consultation Type
        $rows[] = ['CONSULTATION TYPE BREAKDOWN'];
        $rows[] = ['Type', 'Count', 'Percentage'];
        foreach ($this->data['by_consultation_type'] as $type => $count) {
            $pct = $this->data['total'] > 0 ? round(($count / $this->data['total']) * 100, 1) : 0;
            $rows[] = [$type, $count, $pct.'%'];
        }
        $rows[] = [];

        // Daily Trend
        $rows[] = ['DAILY TREND'];
        $rows[] = ['Date', 'Appointments'];
        foreach ($this->data['daily_trend'] as $date => $count) {
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
