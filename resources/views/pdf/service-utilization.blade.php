<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Utilization - {{ $data['date_from']->format('F d, Y') }} to {{ $data['date_to']->format('F d, Y') }}</title>
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
            width: 25%;
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

        .section-title {
            font-size: 10pt;
            font-weight: bold;
            background-color: #333;
            color: white;
            padding: 5px 10px;
            margin-top: 20px;
            margin-bottom: 10px;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        table.data-table th {
            background-color: #333;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-size: 8pt;
            font-weight: bold;
        }

        table.data-table td {
            padding: 6px 5px;
            border-bottom: 1px solid #ddd;
            font-size: 8pt;
        }

        table.data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .bar-container {
            width: 100%;
            background-color: #e5e7eb;
            height: 14px;
            border-radius: 3px;
            overflow: hidden;
        }

        .bar-fill {
            height: 14px;
            border-radius: 3px;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ccc;
            font-size: 8pt;
            color: #888;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Guardiano Maternity and Children Clinic and Hospital</h1>
        <h2>Service Utilization Report</h2>
        <div class="subtitle">Generated on {{ now()->format('F d, Y h:i A') }}</div>
    </div>

    <div class="report-date">
        <strong>Period:</strong> {{ $data['date_from']->format('F d, Y') }} - {{ $data['date_to']->format('F d, Y') }}
    </div>

    {{-- Summary --}}
    <div class="summary-section">
        <div class="summary-title">Summary Statistics</div>
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-cell">
                    <div class="stat-box">
                        <div class="stat-number">{{ $data['total'] }}</div>
                        <div class="stat-label">Total Services</div>
                    </div>
                </div>
                <div class="summary-cell">
                    <div class="stat-box">
                        <div class="stat-number">{{ $data['by_visit_type']['new'] }}</div>
                        <div class="stat-label">New Patients</div>
                    </div>
                </div>
                <div class="summary-cell">
                    <div class="stat-box">
                        <div class="stat-number">{{ $data['by_visit_type']['old'] }}</div>
                        <div class="stat-label">Old Patients</div>
                    </div>
                </div>
                <div class="summary-cell">
                    <div class="stat-box">
                        <div class="stat-number">{{ $data['by_visit_type']['revisit'] }}</div>
                        <div class="stat-label">Revisits</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Consultation Type Distribution --}}
    @php
        $maxConsultation = count($data['by_consultation_type']) > 0 ? max($data['by_consultation_type']) : 1;
    @endphp
    <div class="section-title">Consultation Type Distribution</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30%">Type</th>
                <th style="width: 15%" class="text-center">Count</th>
                <th style="width: 15%" class="text-center">Percentage</th>
                <th style="width: 40%">Distribution</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['by_consultation_type'] as $type => $count)
                <tr>
                    <td>{{ $type }}</td>
                    <td class="text-center">{{ $count }}</td>
                    <td class="text-center">{{ $data['total'] > 0 ? round(($count / $data['total']) * 100, 1) : 0 }}%</td>
                    <td>
                        <div class="bar-container">
                            <div class="bar-fill" style="width: {{ $maxConsultation > 0 ? ($count / $maxConsultation) * 100 : 0 }}%; background-color: #2563eb;"></div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center" style="color: #888;">No data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Visit Type Distribution --}}
    @php
        $totalVisit = $data['by_visit_type']['new'] + $data['by_visit_type']['old'] + $data['by_visit_type']['revisit'];
        $visitColors = ['new' => '#22c55e', 'old' => '#3b82f6', 'revisit' => '#f59e0b'];
    @endphp
    <div class="section-title">Visit Type Distribution</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30%">Type</th>
                <th style="width: 15%" class="text-center">Count</th>
                <th style="width: 15%" class="text-center">Percentage</th>
                <th style="width: 40%">Distribution</th>
            </tr>
        </thead>
        <tbody>
            @foreach(['new' => 'New Patient', 'old' => 'Old Patient', 'revisit' => 'Revisit'] as $key => $label)
                @php $count = $data['by_visit_type'][$key]; @endphp
                <tr>
                    <td>{{ $label }}</td>
                    <td class="text-center">{{ $count }}</td>
                    <td class="text-center">{{ $totalVisit > 0 ? round(($count / $totalVisit) * 100, 1) : 0 }}%</td>
                    <td>
                        <div class="bar-container">
                            <div class="bar-fill" style="width: {{ $totalVisit > 0 ? ($count / $totalVisit) * 100 : 0 }}%; background-color: {{ $visitColors[$key] }};"></div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Source Distribution --}}
    @php
        $totalSource = $data['by_source']['online'] + $data['by_source']['walk-in'];
    @endphp
    <div class="section-title">Source Distribution</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30%">Source</th>
                <th style="width: 15%" class="text-center">Count</th>
                <th style="width: 15%" class="text-center">Percentage</th>
                <th style="width: 40%">Distribution</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Online</td>
                <td class="text-center">{{ $data['by_source']['online'] }}</td>
                <td class="text-center">{{ $totalSource > 0 ? round(($data['by_source']['online'] / $totalSource) * 100, 1) : 0 }}%</td>
                <td>
                    <div class="bar-container">
                        <div class="bar-fill" style="width: {{ $totalSource > 0 ? ($data['by_source']['online'] / $totalSource) * 100 : 0 }}%; background-color: #3b82f6;"></div>
                    </div>
                </td>
            </tr>
            <tr>
                <td>Walk-in</td>
                <td class="text-center">{{ $data['by_source']['walk-in'] }}</td>
                <td class="text-center">{{ $totalSource > 0 ? round(($data['by_source']['walk-in'] / $totalSource) * 100, 1) : 0 }}%</td>
                <td>
                    <div class="bar-container">
                        <div class="bar-fill" style="width: {{ $totalSource > 0 ? ($data['by_source']['walk-in'] / $totalSource) * 100 : 0 }}%; background-color: #f59e0b;"></div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>This is a computer-generated report. No signature required.</p>
        <p>Guardiano Maternity and Children Clinic and Hospital &copy; {{ date('Y') }}</p>
    </div>
</body>
</html>
