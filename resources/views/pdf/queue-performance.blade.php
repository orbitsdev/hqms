<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queue Performance - {{ $data['date_from']->format('F d, Y') }} to {{ $data['date_to']->format('F d, Y') }}</title>
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
            background-color: #2563eb;
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

        .badge-good {
            background-color: #dcfce7;
            color: #166534;
        }

        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-bad {
            background-color: #fee2e2;
            color: #991b1b;
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
        <h2>Queue Performance Report</h2>
        <div class="subtitle">Generated on {{ now()->format('F d, Y h:i A') }}</div>
    </div>

    <div class="report-date">
        <strong>Period:</strong> {{ $data['date_from']->format('F d, Y') }} - {{ $data['date_to']->format('F d, Y') }}
    </div>

    {{-- KPI Summary --}}
    <div class="summary-section">
        <div class="summary-title">Key Performance Indicators</div>
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-cell">
                    <div class="stat-box">
                        <div class="stat-number">{{ $data['total_served'] }}</div>
                        <div class="stat-label">Patients Served</div>
                    </div>
                </div>
                <div class="summary-cell">
                    <div class="stat-box">
                        <div class="stat-number">{{ $data['avg_wait'] }}</div>
                        <div class="stat-label">Avg Wait (min)</div>
                    </div>
                </div>
                <div class="summary-cell">
                    <div class="stat-box">
                        <div class="stat-number">{{ $data['avg_service'] }}</div>
                        <div class="stat-label">Avg Service (min)</div>
                    </div>
                </div>
                <div class="summary-cell">
                    <div class="stat-box">
                        <div class="stat-number">{{ $data['avg_patients_per_day'] }}</div>
                        <div class="stat-label">Avg Patients/Day</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Performance by Consultation Type --}}
    <div class="section-title">Performance by Consultation Type</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Consultation Type</th>
                <th class="text-center">Patients</th>
                <th class="text-center">Avg Wait (min)</th>
                <th class="text-center">Avg Service (min)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['by_consultation_type'] as $type => $metrics)
                <tr>
                    <td>{{ $type }}</td>
                    <td class="text-center">{{ $metrics['count'] }}</td>
                    <td class="text-center">
                        @if($metrics['avg_wait'] <= 15)
                            <span class="badge badge-good">{{ $metrics['avg_wait'] }} min</span>
                        @elseif($metrics['avg_wait'] <= 30)
                            <span class="badge badge-warning">{{ $metrics['avg_wait'] }} min</span>
                        @else
                            <span class="badge badge-bad">{{ $metrics['avg_wait'] }} min</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $metrics['avg_service'] }} min</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center" style="color: #888;">No data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Daily Volume --}}
    @if(count($data['daily_volume']) > 0)
        @php $maxVolume = max(max($data['daily_volume']), 1); @endphp
        <div class="section-title">Daily Patient Volume</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25%">Date</th>
                    <th style="width: 15%" class="text-center">Patients</th>
                    <th style="width: 60%">Volume</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['daily_volume'] as $date => $count)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($date)->format('M d, Y') }}</td>
                        <td class="text-center">{{ $count }}</td>
                        <td>
                            <div class="bar-container">
                                <div class="bar-fill" style="width: {{ ($count / $maxVolume) * 100 }}%;"></div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        <p>This is a computer-generated report. No signature required.</p>
        <p>Guardiano Maternity and Children Clinic and Hospital &copy; {{ date('Y') }}</p>
    </div>
</body>
</html>
