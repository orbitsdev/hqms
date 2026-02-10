<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Patient Census - {{ $data['date']->format('F d, Y') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.4;
            color: #333;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .header h2 {
            font-size: 11pt;
            font-weight: normal;
            color: #666;
        }

        .header .subtitle {
            font-size: 9pt;
            color: #888;
            margin-top: 5px;
        }

        .report-title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .report-date {
            text-align: center;
            font-size: 10pt;
            margin-bottom: 20px;
        }

        .summary-section {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f5f5f5;
            border-radius: 4px;
        }

        .summary-title {
            font-size: 10pt;
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }

        .summary-grid {
            display: table;
            width: 100%;
        }

        .summary-row {
            display: table-row;
        }

        .summary-cell {
            display: table-cell;
            padding: 5px 10px;
            width: 33%;
            vertical-align: top;
        }

        .stat-box {
            background: white;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
            border: 1px solid #ddd;
        }

        .stat-number {
            font-size: 18pt;
            font-weight: bold;
            color: #2563eb;
        }

        .stat-label {
            font-size: 8pt;
            color: #666;
            margin-top: 5px;
        }

        .breakdown-section {
            margin-bottom: 20px;
        }

        .breakdown-grid {
            display: table;
            width: 100%;
        }

        .breakdown-col {
            display: table-cell;
            width: 50%;
            padding: 0 10px;
            vertical-align: top;
        }

        .breakdown-title {
            font-size: 9pt;
            font-weight: bold;
            margin-bottom: 8px;
            color: #555;
        }

        .breakdown-item {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            border-bottom: 1px dotted #ddd;
        }

        .breakdown-label {
            color: #666;
        }

        .breakdown-value {
            font-weight: bold;
        }

        table.patient-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table.patient-table th {
            background-color: #333;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-size: 8pt;
            font-weight: bold;
        }

        table.patient-table td {
            padding: 6px 5px;
            border-bottom: 1px solid #ddd;
            font-size: 8pt;
        }

        table.patient-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .section-title {
            font-size: 10pt;
            font-weight: bold;
            background-color: #333;
            color: white;
            padding: 5px 10px;
            margin-top: 20px;
        }

        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ccc;
            font-size: 8pt;
            color: #888;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
        }

        .badge-new {
            background-color: #dcfce7;
            color: #166534;
        }

        .badge-old {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .badge-revisit {
            background-color: #fef3c7;
            color: #92400e;
        }
    </style>
</head>
<body>
    {{-- Hospital Header --}}
    <div class="header">
        <h1>Guardiano Maternity and Children Clinic and Hospital</h1>
        <h2>Daily Patient Census Report</h2>
        <div class="subtitle">Generated on {{ now()->format('F d, Y h:i A') }}</div>
    </div>

    {{-- Report Date --}}
    <div class="report-date">
        <strong>Report Date:</strong> {{ $data['date']->format('l, F d, Y') }}
    </div>

    {{-- Summary Statistics --}}
    <div class="summary-section">
        <div class="summary-title">Summary Statistics</div>
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-cell">
                    <div class="stat-box">
                        <div class="stat-number">{{ $data['total_patients'] }}</div>
                        <div class="stat-label">Total Patients</div>
                    </div>
                </div>
                <div class="summary-cell">
                    <div class="stat-box">
                        <div class="stat-number">{{ $data['by_consultation_type']['OB-GYN'] ?? 0 }}</div>
                        <div class="stat-label">OB-GYN</div>
                    </div>
                </div>
                <div class="summary-cell">
                    <div class="stat-box">
                        <div class="stat-number">{{ $data['by_consultation_type']['Pediatrics'] ?? 0 }}</div>
                        <div class="stat-label">Pediatrics</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Breakdown --}}
    <div class="breakdown-section">
        <div class="breakdown-grid">
            <div class="breakdown-col">
                <div class="breakdown-title">By Consultation Type</div>
                @forelse($data['by_consultation_type'] as $type => $count)
                    <div class="breakdown-item">
                        <span class="breakdown-label">{{ $type }}</span>
                        <span class="breakdown-value">{{ $count }}</span>
                    </div>
                @empty
                    <div class="breakdown-item">
                        <span class="breakdown-label">No data</span>
                    </div>
                @endforelse
            </div>
            <div class="breakdown-col">
                <div class="breakdown-title">By Visit Type</div>
                <div class="breakdown-item">
                    <span class="breakdown-label">New Patient</span>
                    <span class="breakdown-value">{{ $data['by_visit_type']['new'] }}</span>
                </div>
                <div class="breakdown-item">
                    <span class="breakdown-label">Old Patient</span>
                    <span class="breakdown-value">{{ $data['by_visit_type']['old'] }}</span>
                </div>
                <div class="breakdown-item">
                    <span class="breakdown-label">Revisit</span>
                    <span class="breakdown-value">{{ $data['by_visit_type']['revisit'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Patient List --}}
    <div class="section-title">Patient List</div>

    @if($data['records']->count() > 0)
        <table class="patient-table">
            <thead>
                <tr>
                    <th style="width: 5%">#</th>
                    <th style="width: 10%">Queue</th>
                    <th style="width: 12%">Record No.</th>
                    <th style="width: 20%">Patient Name</th>
                    <th style="width: 8%">Gender</th>
                    <th style="width: 8%">Age</th>
                    <th style="width: 12%">Consultation</th>
                    <th style="width: 12%">Visit Type</th>
                    <th style="width: 8%">Time</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['records'] as $index => $record)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">{{ $record->queue?->formatted_number ?? '-' }}</td>
                        <td>{{ $record->record_number }}</td>
                        <td>{{ $record->patient_full_name }}</td>
                        <td class="text-center">{{ ucfirst($record->patient_gender ?? '-') }}</td>
                        <td class="text-center">{{ $record->patient_age ?? '-' }}</td>
                        <td>{{ $record->consultationType?->short_name ?? '-' }}</td>
                        <td class="text-center">
                            @if($record->visit_type === 'new')
                                <span class="badge badge-new">New</span>
                            @elseif($record->visit_type === 'old')
                                <span class="badge badge-old">Old</span>
                            @elseif($record->visit_type === 'revisit')
                                <span class="badge badge-revisit">Revisit</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center">{{ $record->created_at->format('h:i A') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="text-align: center; padding: 20px; color: #888;">No patients recorded for this date.</p>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <p>This is a computer-generated report. No signature required.</p>
        <p>Guardiano Maternity and Children Clinic and Hospital &copy; {{ date('Y') }}</p>
    </div>
</body>
</html>
