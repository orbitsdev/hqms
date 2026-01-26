<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Record - {{ $record->record_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
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
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .header h2 {
            font-size: 12pt;
            font-weight: normal;
            color: #666;
        }

        .header .subtitle {
            font-size: 9pt;
            color: #888;
        }

        .record-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 4px;
        }

        .record-info-left, .record-info-right {
            display: inline-block;
            vertical-align: top;
        }

        .record-number {
            font-family: monospace;
            font-size: 12pt;
            font-weight: bold;
        }

        .section {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 11pt;
            font-weight: bold;
            background-color: #333;
            color: white;
            padding: 5px 10px;
            margin-bottom: 10px;
        }

        .section-content {
            padding: 0 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table.info-table td {
            padding: 4px 8px;
            vertical-align: top;
        }

        table.info-table td:first-child {
            width: 35%;
            font-weight: bold;
            color: #555;
        }

        table.info-table td:last-child {
            width: 65%;
        }

        table.vitals-table {
            margin-top: 10px;
        }

        table.vitals-table th,
        table.vitals-table td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
        }

        table.vitals-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .two-column {
            display: table;
            width: 100%;
        }

        .two-column > div {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 15px;
        }

        .two-column > div:last-child {
            padding-right: 0;
            padding-left: 15px;
        }

        .text-box {
            border: 1px solid #ddd;
            padding: 10px;
            min-height: 60px;
            background-color: #fafafa;
            margin-top: 5px;
        }

        .label {
            font-weight: bold;
            color: #555;
            margin-bottom: 3px;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9pt;
            font-weight: bold;
        }

        .status-in_progress { background-color: #fef3c7; color: #92400e; }
        .status-for_billing { background-color: #dbeafe; color: #1e40af; }
        .status-for_admission { background-color: #ede9fe; color: #5b21b6; }
        .status-completed { background-color: #d1fae5; color: #065f46; }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 8pt;
            color: #888;
            text-align: center;
        }

        .signature-section {
            margin-top: 40px;
            display: table;
            width: 100%;
        }

        .signature-box {
            display: table-cell;
            width: 33%;
            text-align: center;
            padding: 0 10px;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 5px;
        }

        @media print {
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Guardiano Maternity and Children Clinic and Hospital</h1>
        <h2>Medical Record</h2>
        <p class="subtitle">Hospital Queue Management System</p>
    </div>

    <!-- Record Info Bar -->
    <table style="width: 100%; margin-bottom: 20px; background-color: #f5f5f5; padding: 10px;">
        <tr>
            <td style="width: 50%;">
                <strong>Record Number:</strong> <span class="record-number">{{ $record->record_number }}</span>
            </td>
            <td style="width: 50%; text-align: right;">
                <strong>Visit Date:</strong> {{ $record->visit_date->format('F d, Y') }}
                <span class="status-badge status-{{ $record->status }}">{{ strtoupper(str_replace('_', ' ', $record->status)) }}</span>
            </td>
        </tr>
    </table>

    <!-- Patient Information -->
    <div class="section">
        <div class="section-title">PATIENT INFORMATION</div>
        <div class="section-content">
            <div class="two-column">
                <div>
                    <table class="info-table">
                        <tr>
                            <td>Full Name:</td>
                            <td>{{ $record->patient_full_name }}</td>
                        </tr>
                        <tr>
                            <td>Date of Birth:</td>
                            <td>{{ $record->patient_date_of_birth?->format('F d, Y') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Age:</td>
                            <td>{{ $record->patient_age ? $record->patient_age . ' years' : '-' }}</td>
                        </tr>
                        <tr>
                            <td>Gender:</td>
                            <td>{{ ucfirst($record->patient_gender ?? '-') }}</td>
                        </tr>
                        <tr>
                            <td>Marital Status:</td>
                            <td>{{ ucfirst($record->patient_marital_status ?? '-') }}</td>
                        </tr>
                    </table>
                </div>
                <div>
                    <table class="info-table">
                        <tr>
                            <td>Contact Number:</td>
                            <td>{{ $record->patient_contact_number ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Occupation:</td>
                            <td>{{ $record->patient_occupation ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Religion:</td>
                            <td>{{ $record->patient_religion ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Blood Type:</td>
                            <td>{{ $record->patient_blood_type ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <table class="info-table" style="margin-top: 10px;">
                <tr>
                    <td style="width: 17%;">Address:</td>
                    <td>{{ $record->patient_full_address ?: '-' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Companion & Emergency Contact -->
    <div class="section">
        <div class="section-title">COMPANION & EMERGENCY CONTACT</div>
        <div class="section-content">
            <div class="two-column">
                <div>
                    <p class="label">Companion/Watcher</p>
                    <table class="info-table">
                        <tr>
                            <td>Name:</td>
                            <td>{{ $record->companion_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Contact:</td>
                            <td>{{ $record->companion_contact ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Relationship:</td>
                            <td>{{ $record->companion_relationship ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
                <div>
                    <p class="label">Emergency Contact</p>
                    <table class="info-table">
                        <tr>
                            <td>Name:</td>
                            <td>{{ $record->emergency_contact_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Phone:</td>
                            <td>{{ $record->emergency_contact_phone ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Medical Background -->
    @if($record->patient_allergies || $record->patient_chronic_conditions)
    <div class="section">
        <div class="section-title">MEDICAL BACKGROUND</div>
        <div class="section-content">
            <div class="two-column">
                <div>
                    <p class="label">Known Allergies</p>
                    <div class="text-box">{{ $record->patient_allergies ?? 'None reported' }}</div>
                </div>
                <div>
                    <p class="label">Chronic Conditions</p>
                    <div class="text-box">{{ $record->patient_chronic_conditions ?? 'None reported' }}</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Visit Information -->
    <div class="section">
        <div class="section-title">VISIT INFORMATION</div>
        <div class="section-content">
            <div class="two-column">
                <div>
                    <table class="info-table">
                        <tr>
                            <td>Consultation Type:</td>
                            <td>{{ $record->consultationType?->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Visit Type:</td>
                            <td>{{ ucfirst($record->visit_type ?? '-') }}</td>
                        </tr>
                        <tr>
                            <td>Service Type:</td>
                            <td>{{ ucfirst($record->service_type ?? '-') }}</td>
                        </tr>
                        @if($record->ob_type)
                        <tr>
                            <td>OB Type:</td>
                            <td>{{ ucfirst($record->ob_type) }}</td>
                        </tr>
                        @endif
                        @if($record->service_category)
                        <tr>
                            <td>Service Category:</td>
                            <td>{{ ucfirst($record->service_category) }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
                <div>
                    <table class="info-table">
                        <tr>
                            <td>Time In:</td>
                            <td>{{ $record->time_in?->format('h:i A') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Attending Nurse:</td>
                            <td>{{ $record->nurse?->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Attending Doctor:</td>
                            <td>{{ $record->doctor?->name ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div style="margin-top: 15px;">
                <p class="label">Chief Complaints</p>
                <div class="text-box">{{ $record->effective_chief_complaints ?? 'None recorded' }}</div>
            </div>
        </div>
    </div>

    <!-- Vital Signs -->
    <div class="section">
        <div class="section-title">VITAL SIGNS</div>
        <div class="section-content">
            <table class="vitals-table">
                <thead>
                    <tr>
                        <th>Temperature</th>
                        <th>Blood Pressure</th>
                        <th>Cardiac Rate</th>
                        <th>Respiratory Rate</th>
                        <th>Weight</th>
                        <th>Height</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $record->temperature ? $record->temperature . ' °C' : '-' }}</td>
                        <td>{{ $record->blood_pressure ?? '-' }}</td>
                        <td>{{ $record->cardiac_rate ? $record->cardiac_rate . ' bpm' : '-' }}</td>
                        <td>{{ $record->respiratory_rate ? $record->respiratory_rate . '/min' : '-' }}</td>
                        <td>{{ $record->weight ? $record->weight . ' kg' : '-' }}</td>
                        <td>{{ $record->height ? $record->height . ' cm' : '-' }}</td>
                    </tr>
                </tbody>
            </table>

            @if($record->head_circumference || $record->chest_circumference || $record->fetal_heart_tone || $record->fundal_height)
            <table class="vitals-table" style="margin-top: 10px;">
                <thead>
                    <tr>
                        @if($record->head_circumference || $record->chest_circumference)
                        <th>Head Circumference</th>
                        <th>Chest Circumference</th>
                        @endif
                        @if($record->fetal_heart_tone || $record->fundal_height || $record->last_menstrual_period)
                        <th>Fetal Heart Tone</th>
                        <th>Fundal Height</th>
                        <th>Last Menstrual Period</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @if($record->head_circumference || $record->chest_circumference)
                        <td>{{ $record->head_circumference ? $record->head_circumference . ' cm' : '-' }}</td>
                        <td>{{ $record->chest_circumference ? $record->chest_circumference . ' cm' : '-' }}</td>
                        @endif
                        @if($record->fetal_heart_tone || $record->fundal_height || $record->last_menstrual_period)
                        <td>{{ $record->fetal_heart_tone ? $record->fetal_heart_tone . ' bpm' : '-' }}</td>
                        <td>{{ $record->fundal_height ? $record->fundal_height . ' cm' : '-' }}</td>
                        <td>{{ $record->last_menstrual_period?->format('M d, Y') ?? '-' }}</td>
                        @endif
                    </tr>
                </tbody>
            </table>
            @endif

            @if($record->vital_signs_recorded_at)
            <p style="margin-top: 10px; font-size: 9pt; color: #888;">
                Recorded: {{ $record->vital_signs_recorded_at->format('F d, Y h:i A') }}
            </p>
            @endif
        </div>
    </div>

    <!-- Diagnosis (if available) -->
    @if($record->diagnosis || $record->pertinent_hpi_pe || $record->plan)
    <div class="section">
        <div class="section-title">DIAGNOSIS & PLAN</div>
        <div class="section-content">
            @if($record->pertinent_hpi_pe)
            <div style="margin-bottom: 15px;">
                <p class="label">Pertinent HPI/PE</p>
                <div class="text-box">{{ $record->pertinent_hpi_pe }}</div>
            </div>
            @endif

            @if($record->diagnosis)
            <div style="margin-bottom: 15px;">
                <p class="label">Diagnosis</p>
                <div class="text-box">{{ $record->diagnosis }}</div>
            </div>
            @endif

            @if($record->plan)
            <div style="margin-bottom: 15px;">
                <p class="label">Plan</p>
                <div class="text-box">{{ $record->plan }}</div>
            </div>
            @endif

            @if($record->procedures_done)
            <div style="margin-bottom: 15px;">
                <p class="label">Procedures Done</p>
                <div class="text-box">{{ $record->procedures_done }}</div>
            </div>
            @endif

            @if($record->prescription_notes)
            <div style="margin-bottom: 15px;">
                <p class="label">Prescription Notes</p>
                <div class="text-box">{{ $record->prescription_notes }}</div>
            </div>
            @endif

            @if($record->examined_at)
            <p style="font-size: 9pt; color: #888;">
                Examined: {{ $record->examined_at->format('F d, Y h:i A') }}
                @if($record->examination_ended_at)
                    - {{ $record->examination_ended_at->format('h:i A') }}
                @endif
            </p>
            @endif
        </div>
    </div>
    @endif

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">
                {{ $record->nurse?->name ?? 'Nurse' }}
            </div>
            <p style="font-size: 8pt; color: #888;">Attending Nurse</p>
        </div>
        <div class="signature-box">
            <div class="signature-line">
                {{ $record->doctor?->name ?? 'Doctor' }}
            </div>
            <p style="font-size: 8pt; color: #888;">Attending Physician</p>
        </div>
        <div class="signature-box">
            <div class="signature-line">
                Patient/Guardian
            </div>
            <p style="font-size: 8pt; color: #888;">Signature Over Printed Name</p>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Generated on {{ now()->format('F d, Y h:i A') }} | {{ $record->record_number }}</p>
        <p>This is a computer-generated document. No signature is required unless printed.</p>
    </div>
</body>
</html>
