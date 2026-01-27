@props(['queue'])

@php
    // Build the queue number safely
    $queueNumber = $queue->queue_number ?? '?';
    $shortName = $queue->consultationType?->short_name ?? 'Q';
    $formattedNumber = $shortName . '-' . $queueNumber;
    $patientName = trim(($queue->appointment?->patient_first_name ?? '') . ' ' . ($queue->appointment?->patient_last_name ?? ''));
    $patientAge = $queue->appointment?->patient_date_of_birth ? \Carbon\Carbon::parse($queue->appointment->patient_date_of_birth)->age : null;
    $consultationName = $queue->consultationType?->name ?? 'Consultation';
    $queueDate = $queue->queue_date?->format('M d, Y') ?? now()->format('M d, Y');
    $currentTime = now()->format('h:i A');
    $queueId = $queue->id ?? 0;
    $priority = $queue->priority ?? 'normal';

    // Build patient HTML
    $patientHtml = '';
    if ($patientName) {
        $patientHtml = '<div class="patient"><div class="patient-name">' . e($patientName) . '</div>';
        if ($patientAge) {
            $patientHtml .= '<div class="patient-age">' . $patientAge . ' years old</div>';
        }
        $patientHtml .= '</div>';
    }

    // Build priority HTML
    $priorityHtml = '';
    if ($priority === 'emergency') {
        $priorityHtml = '<div class="priority-emergency">*** EMERGENCY ***</div>';
    } elseif ($priority === 'urgent') {
        $priorityHtml = '<div class="priority-urgent">URGENT</div>';
    }

    // Build print HTML
    $printHtml = '<!DOCTYPE html><html><head><title>Queue ' . $formattedNumber . '</title>' .
        '<style>' .
        'html, body { margin: 0 !important; padding: 0 !important; width: 80mm; font-family: Courier New, Courier, monospace; font-size: 10pt; background: white; }' .
        '@page { size: 80mm 150mm; margin: 0; }' .
        '@media print { html, body { width: 80mm; } }' .
        '.ticket { width: 80mm; padding: 3mm; box-sizing: border-box; }' .
        '.center { text-align: center; }' .
        '.header { border-bottom: 1px dashed #000; padding-bottom: 2mm; margin-bottom: 2mm; }' .
        '.header h1 { font-size: 14pt; margin: 0; }' .
        '.header p { font-size: 8pt; margin: 1mm 0 0 0; color: #333; }' .
        '.queue-num { font-size: 36pt; font-weight: bold; margin: 3mm 0; letter-spacing: 2px; }' .
        '.label { font-size: 7pt; color: #666; text-transform: uppercase; letter-spacing: 1px; }' .
        '.type-box { background: #eee; padding: 2mm; margin: 2mm 0; font-weight: bold; font-size: 9pt; }' .
        '.patient { margin: 2mm 0; }' .
        '.patient-name { font-weight: bold; font-size: 9pt; }' .
        '.patient-age { font-size: 8pt; color: #666; }' .
        '.priority-emergency { display: inline-block; background: #000; color: #fff; padding: 1mm 2mm; font-size: 9pt; font-weight: bold; margin: 2mm 0; }' .
        '.priority-urgent { display: inline-block; border: 1px solid #000; padding: 1mm 2mm; font-size: 9pt; font-weight: bold; margin: 2mm 0; }' .
        '.datetime { border-top: 1px dashed #000; padding-top: 2mm; margin-top: 2mm; font-size: 8pt; }' .
        '.instructions { border-top: 1px dashed #000; padding-top: 2mm; margin-top: 2mm; font-size: 7pt; color: #666; }' .
        '.barcode { margin-top: 2mm; font-size: 6pt; letter-spacing: 1px; }' .
        '</style></head><body>' .
        '<div class="ticket center">' .
        '<div class="header"><h1>HQMS</h1><p>Guardiano Maternity</p><p>and Children Clinic</p></div>' .
        '<div class="label">Queue Number</div>' .
        '<div class="queue-num">' . $formattedNumber . '</div>' .
        '<div class="type-box">' . e($consultationName) . '</div>' .
        $patientHtml .
        $priorityHtml .
        '<div class="datetime"><div>' . $queueDate . '</div><div>' . $currentTime . '</div></div>' .
        '<div class="instructions"><p>Please wait for your number</p><p>to be called. Thank you!</p></div>' .
        '<div class="barcode">||| |||| ||| |||| ||| ||||<br>' . $queueId . '-' . $queueNumber . '</div>' .
        '</div></body></html>';
@endphp

<div
    x-data="{
        printHtml: {{ Js::from($printHtml) }},
        printTicket() {
            var w = window.open('', '_blank', 'width=900,height=700,scrollbars=yes,resizable=yes');
            if (!w) {
                alert('Please allow popups to print tickets.');
                return;
            }
            w.document.write(this.printHtml);
            w.document.close();
            w.focus();
            setTimeout(function() {
                w.print();
                w.close();
            }, 300);
        }
    }"
>
    {{-- Screen Preview --}}
    <div class="queue-ticket-preview mx-auto bg-white text-center" style="width: 280px; padding: 16px; font-family: 'Courier New', monospace; border: 2px dashed #ccc;">
        {{-- Hospital Header --}}
        <div style="border-bottom: 2px dashed #333; padding-bottom: 12px; margin-bottom: 12px;">
            <p style="font-size: 18px; font-weight: bold; margin: 0;">HQMS</p>
            <p style="font-size: 11px; margin: 4px 0 0 0; color: #666;">Guardiano Maternity</p>
            <p style="font-size: 11px; margin: 0; color: #666;">and Children Clinic</p>
        </div>

        {{-- Queue Number --}}
        <div style="margin: 16px 0;">
            <p style="font-size: 11px; margin: 0; color: #888; text-transform: uppercase; letter-spacing: 2px;">Queue Number</p>
            <p style="font-size: 48px; font-weight: bold; margin: 8px 0; letter-spacing: 2px;">{{ $formattedNumber }}</p>
        </div>

        {{-- Consultation Type --}}
        <div style="background: #f5f5f5; padding: 8px 12px; margin: 12px 0; border-radius: 4px;">
            <p style="font-size: 13px; font-weight: bold; margin: 0;">{{ $consultationName }}</p>
        </div>

        {{-- Patient Info --}}
        @if($patientName)
            <div style="margin: 12px 0;">
                <p style="font-size: 14px; font-weight: bold; margin: 0;">{{ $patientName }}</p>
                @if($patientAge)
                    <p style="font-size: 11px; margin: 4px 0 0 0; color: #666;">{{ $patientAge }} years old</p>
                @endif
            </div>
        @endif

        {{-- Priority Badge --}}
        @if($priority === 'emergency')
            <div style="margin: 12px 0;">
                <span style="display: inline-block; background: #000; color: #fff; padding: 4px 12px; font-size: 12px; font-weight: bold;">*** EMERGENCY ***</span>
            </div>
        @elseif($priority === 'urgent')
            <div style="margin: 12px 0;">
                <span style="display: inline-block; border: 2px solid #000; padding: 4px 12px; font-size: 12px; font-weight: bold;">URGENT</span>
            </div>
        @endif

        {{-- Date & Time --}}
        <div style="border-top: 2px dashed #333; padding-top: 12px; margin-top: 12px;">
            <p style="font-size: 11px; margin: 0;">{{ $queueDate }}</p>
            <p style="font-size: 11px; margin: 4px 0 0 0;">{{ $currentTime }}</p>
        </div>

        {{-- Instructions --}}
        <div style="border-top: 1px dashed #ccc; padding-top: 12px; margin-top: 12px;">
            <p style="font-size: 10px; margin: 0; color: #666;">Please wait for your number</p>
            <p style="font-size: 10px; margin: 2px 0 0 0; color: #666;">to be called. Thank you!</p>
        </div>
    </div>

    {{-- Print Button --}}
    <div class="mt-4 text-center">
        <button
            type="button"
            x-on:click="printTicket()"
            class="inline-flex items-center gap-2 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            {{ __('Print Ticket') }}
        </button>
    </div>

    {{-- Print Settings Info --}}
    <div class="mt-3 rounded-lg bg-amber-50 p-3 text-xs text-amber-800 dark:bg-amber-900/20 dark:text-amber-200">
        <p class="font-medium">{{ __('Recommended Print Settings:') }}</p>
        <ul class="mt-1 list-inside list-disc space-y-0.5">
            <li>{{ __('Paper size: A7 or 80mm') }}</li>
            <li>{{ __('Margins: None') }}</li>
            <li>{{ __('Scale: 100%') }}</li>
        </ul>
    </div>
</div>
