# PDF Download Feature - Implementation Guide

This document explains how the PDF download feature works in HQMS, what was done in the code, and what was configured on the server to make it work. Use this as a reference for implementing PDF downloads in other Laravel + Livewire projects.

---

## Table of Contents

1. [How It Works (Overview)](#how-it-works-overview)
2. [Packages Used](#packages-used)
3. [Installation Steps](#installation-steps)
4. [Code Implementation](#code-implementation)
5. [PDF Blade Templates](#pdf-blade-templates)
6. [Livewire Button Integration](#livewire-button-integration)
7. [Server Setup (Production)](#server-setup-production)
8. [Error We Encountered and How We Fixed It](#error-we-encountered-and-how-we-fixed-it)
9. [File Summary](#file-summary)

---

## How It Works (Overview)

The flow is:

1. User clicks a **"Download PDF"** button in the browser
2. Livewire calls the `downloadPdf()` method on the server
3. The method uses `spatie/laravel-pdf` to render a Blade view into an HTML page
4. Under the hood, `spatie/laravel-pdf` uses **Browsershot** which launches a headless **Google Chrome** browser to convert the HTML into a PDF file
5. The PDF is saved to a temporary file in `storage/app/temp/`
6. Laravel sends the file as a download response to the browser
7. The temp file is automatically deleted after download

```
User clicks button
    -> Livewire wire:click="downloadPdf"
        -> Server: Pdf::view('pdf.template', $data)
            -> Browsershot opens headless Chrome
                -> Chrome renders HTML to PDF
                    -> PDF saved to storage/app/temp/
                        -> response()->download() sends to browser
                            -> deleteFileAfterSend(true) cleans up
```

---

## Packages Used

### PHP (Composer)

| Package | Version | Purpose |
|---------|---------|---------|
| `spatie/laravel-pdf` | ^1.8 | Main PDF generation package |

> `spatie/laravel-pdf` internally depends on `spatie/browsershot` which does the actual HTML-to-PDF conversion using Chrome.

### JavaScript (NPM)

| Package | Version | Purpose |
|---------|---------|---------|
| `puppeteer` | ^24.36.0 | Provides the Chrome browser that Browsershot uses to render PDFs |

---

## Installation Steps

To add PDF downloads to a new project, run these commands:

### 1. Install the PHP package

```bash
composer require spatie/laravel-pdf
```

### 2. Install Puppeteer (provides Chrome for PDF rendering)

```bash
npm install puppeteer
```

### 3. Create the temp directory (optional, code creates it automatically)

```bash
mkdir -p storage/app/temp
```

That's it for local development. For production servers, see [Server Setup](#server-setup-production) below.

---

## Code Implementation

### The `downloadPdf()` Method

Every Livewire component that supports PDF download has a `downloadPdf()` method. Here's the pattern:

#### For a Single Record (e.g., Medical Record)

```php
use Spatie\LaravelPdf\Facades\Pdf;

public function downloadPdf(int $recordId): mixed
{
    // 1. Load the record with relationships
    $record = MedicalRecord::with(['consultationType', 'doctor', 'nurse', 'prescriptions'])
        ->find($recordId);

    if (! $record) {
        Toaster::error(__('Record not found.'));
        return null;
    }

    try {
        // 2. Set up the filename and temp path
        $filename = 'medical-record-' . $record->record_number . '.pdf';
        $tempPath = storage_path('app/temp/' . $filename);

        // 3. Create temp directory if it doesn't exist
        if (! file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        // 4. Generate the PDF
        Pdf::view('pdf.medical-record', ['record' => $record])
            ->withBrowsershot(function (\Spatie\Browsershot\Browsershot $browsershot) {
                // Check for system-installed Chrome (needed on production servers)
                if (file_exists('/usr/bin/google-chrome-stable')) {
                    $browsershot->setChromePath('/usr/bin/google-chrome-stable');
                } elseif (file_exists('/usr/bin/chromium-browser')) {
                    $browsershot->setChromePath('/usr/bin/chromium-browser');
                }
                // Disable sandbox (required for server environments)
                $browsershot->noSandbox();
            })
            ->format('a4')
            ->save($tempPath);

        // 5. Send the file as a download and delete the temp file after
        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/pdf',
        ])->deleteFileAfterSend(true);
    } catch (\Exception $e) {
        Toaster::error(__('Failed to generate PDF: ') . $e->getMessage());
        return null;
    }
}
```

#### For Reports (Multiple Report Types)

```php
public function downloadPdf(): mixed
{
    try {
        // 1. Get the data based on the selected report type
        $data = match ($this->reportType) {
            'appointment_stats'   => $this->appointmentStatsData,
            'service_utilization' => $this->serviceUtilizationData,
            'queue_performance'   => $this->queuePerformanceData,
            default               => $this->dailyCensusData,
        };

        // 2. Map each report type to its Blade view and filename
        $viewAndFilename = match ($this->reportType) {
            'appointment_stats' => [
                'view'     => 'pdf.appointment-statistics',
                'filename' => 'appointment-statistics-' . $this->dateFrom . '-to-' . $this->dateTo . '.pdf',
            ],
            'service_utilization' => [
                'view'     => 'pdf.service-utilization',
                'filename' => 'service-utilization-' . $this->dateFrom . '-to-' . $this->dateTo . '.pdf',
            ],
            'queue_performance' => [
                'view'     => 'pdf.queue-performance',
                'filename' => 'queue-performance-' . $this->dateFrom . '-to-' . $this->dateTo . '.pdf',
            ],
            default => [
                'view'     => 'pdf.daily-patient-census',
                'filename' => 'daily-patient-census-' . $data['date']->format('Y-m-d') . '.pdf',
            ],
        };

        $tempPath = storage_path('app/temp/' . $viewAndFilename['filename']);

        if (! is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        // 3. Generate PDF (same pattern as above)
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
        Toaster::error(__('Failed to generate PDF: ') . $e->getMessage());
        return null;
    }
}
```

### Key Parts Explained

| Part | What It Does |
|------|-------------|
| `Pdf::view('pdf.template', $data)` | Renders a Blade view as the PDF content |
| `->withBrowsershot(callback)` | Configures the headless Chrome browser |
| `$browsershot->setChromePath(...)` | Tells Browsershot where Chrome is installed on the server |
| `$browsershot->noSandbox()` | Disables Chrome's sandbox mode (required on most servers) |
| `->format('a4')` | Sets the paper size to A4 |
| `->save($tempPath)` | Saves the generated PDF to a temporary file |
| `response()->download(...)` | Sends the file to the user's browser |
| `->deleteFileAfterSend(true)` | Cleans up the temp file automatically |

---

## PDF Blade Templates

PDF templates are regular Blade files located in `resources/views/pdf/`. They use **inline CSS** (not Tailwind) because the headless Chrome renders them independently from the app.

### Template Rules

- Use **inline CSS** inside a `<style>` tag (Tailwind is NOT available in PDF views)
- Use the `DejaVu Sans` font for universal character support
- Set `page-break-inside: avoid` on sections to prevent awkward page breaks
- Design for **A4 paper** (210mm x 297mm)

### Example Template Structure

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Medical Record - {{ $record->record_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
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
        .section {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        /* ... more styles ... */
    </style>
</head>
<body>
    <div class="header">
        <h1>Hospital Name</h1>
        <h2>Medical Record</h2>
    </div>

    <div class="section">
        <h3>Patient Information</h3>
        <p>{{ $record->patient_first_name }} {{ $record->patient_last_name }}</p>
        <!-- ... more fields ... -->
    </div>
</body>
</html>
```

### Templates in This Project

| File | Purpose |
|------|---------|
| `resources/views/pdf/medical-record.blade.php` | Individual medical examination record |
| `resources/views/pdf/daily-patient-census.blade.php` | Daily patient count and statistics |
| `resources/views/pdf/appointment-statistics.blade.php` | Appointment trends and stats |
| `resources/views/pdf/queue-performance.blade.php` | Queue wait time and service metrics |
| `resources/views/pdf/service-utilization.blade.php` | Service usage breakdown |

---

## Livewire Button Integration

In the Blade views, we trigger the download with `wire:click`:

### For a single download (no parameter)

```blade
<flux:button wire:click="downloadPdf" variant="primary" icon="document-arrow-down">
    {{ __('Download PDF') }}
</flux:button>
```

### For record-specific download (with ID parameter)

```blade
<flux:button
    wire:click="downloadPdf({{ $record->id }})"
    size="xs"
    variant="ghost"
    icon="arrow-down-tray"
/>
```

> **Important:** The `downloadPdf()` method must have a return type of `mixed` (not `void`) because it returns a download response.

---

## Server Setup (Production)

On your **local machine** (Windows/Mac), Puppeteer downloads its own Chrome automatically, so it works out of the box.

On a **production Linux server**, you need to install Chrome manually. This is what we did:

### Step 1: Install Google Chrome on the Server

SSH into your server and run:

```bash
# Update package list
sudo apt-get update

# Install Google Chrome (recommended)
sudo apt-get install -y google-chrome-stable

# OR install Chromium (lighter alternative)
sudo apt-get install -y chromium-browser
```

If `google-chrome-stable` is not found in the default repos, add the Google Chrome repository first:

```bash
wget -q -O - https://dl.google.com/linux/linux_signing_key.pub | sudo gpg --dearmor -o /usr/share/keyrings/google-chrome.gpg
echo "deb [arch=amd64 signed-by=/usr/share/keyrings/google-chrome.gpg] http://dl.google.com/linux/chrome/deb/ stable main" | sudo tee /etc/apt/sources.list.d/google-chrome.list
sudo apt-get update
sudo apt-get install -y google-chrome-stable
```

### Step 2: Verify Chrome is Installed

```bash
# Check the path (should return the version)
/usr/bin/google-chrome-stable --version

# Or for Chromium
/usr/bin/chromium-browser --version
```

### Step 3: Install Required System Dependencies

Chrome needs these system libraries. Most are installed with Chrome, but if you get errors:

```bash
sudo apt-get install -y \
    libnss3 \
    libatk1.0-0 \
    libatk-bridge2.0-0 \
    libcups2 \
    libdrm2 \
    libxkbcommon0 \
    libxcomposite1 \
    libxdamage1 \
    libxrandr2 \
    libgbm1 \
    libpango-1.0-0 \
    libasound2 \
    libxshmfence1
```

### Step 4: Set Directory Permissions

```bash
# Make sure the storage/app/temp directory is writable
chmod -R 775 storage/app/temp

# Make sure the web server user owns it
chown -R www-data:www-data storage/app/temp
```

### Step 5: Run NPM Install on the Server

```bash
npm install
```

This ensures `puppeteer` and its dependencies are available on the server.

---

## Error We Encountered and How We Fixed It

### The Problem

PDF generation worked perfectly on **local development** (Windows) but **failed on the production server** (Linux).

The error was that Browsershot could not find a Chrome executable. On local, Puppeteer downloads its own Chrome into `node_modules/puppeteer/.local-chromium/`. But on the production server, this bundled Chrome either wasn't available or didn't work due to missing system libraries.

### The Fix

We made two changes:

#### 1. Installed Google Chrome directly on the server

```bash
sudo apt-get install -y google-chrome-stable
```

This installs Chrome at `/usr/bin/google-chrome-stable` with all required system dependencies.

#### 2. Updated the code to use system Chrome

We added the `withBrowsershot()` callback to explicitly point to the system Chrome:

```php
Pdf::view('pdf.template', $data)
    ->withBrowsershot(function (\Spatie\Browsershot\Browsershot $browsershot) {
        // Use system Chrome if available (production server)
        if (file_exists('/usr/bin/google-chrome-stable')) {
            $browsershot->setChromePath('/usr/bin/google-chrome-stable');
        } elseif (file_exists('/usr/bin/chromium-browser')) {
            $browsershot->setChromePath('/usr/bin/chromium-browser');
        }
        // Required for running Chrome on servers (no display/GUI)
        $browsershot->noSandbox();
    })
    ->format('a4')
    ->save($tempPath);
```

**Why `noSandbox()`?** Linux servers typically run as root or a web server user without a display. Chrome's sandbox requires specific kernel features that may not be available in server environments. Disabling it allows Chrome to run headless on the server.

**Why the `file_exists()` check?** This makes the code work on both local (where Puppeteer's built-in Chrome is used) and production (where system Chrome is used). If neither path exists, Browsershot falls back to its default Chrome discovery.

### Commit History

| Commit | What Changed |
|--------|-------------|
| `cafcac8` | Initial PDF download implementation |
| `03bcd46` | PDF template updates |
| `839ac2d` | Fixed PDF to use system Chrome on production server |
| `cba92e9` | Added Daily Patient Census report |

---

## File Summary

### Livewire Components with PDF Download

| Component | File | Method |
|-----------|------|--------|
| Admin Reports | `app/Livewire/Admin/Reports.php` | `downloadPdf()` |
| Nurse Reports | `app/Livewire/Nurse/Reports.php` | `downloadPdf()` |
| Doctor Examination | `app/Livewire/Doctor/Examination.php` | `downloadPdf()` |
| Doctor Patient History | `app/Livewire/Doctor/PatientHistory.php` | `downloadPdf(int $recordId)` |
| Nurse Patient History | `app/Livewire/Nurse/PatientHistory.php` | `downloadPdf(int $recordId)` |
| Nurse Medical Records | `app/Livewire/Nurse/MedicalRecords.php` | `downloadPdf(int $recordId)` |

### PDF Blade Templates

| File | Purpose |
|------|---------|
| `resources/views/pdf/medical-record.blade.php` | Medical examination record |
| `resources/views/pdf/daily-patient-census.blade.php` | Daily patient statistics |
| `resources/views/pdf/appointment-statistics.blade.php` | Appointment trends |
| `resources/views/pdf/queue-performance.blade.php` | Queue wait/service metrics |
| `resources/views/pdf/service-utilization.blade.php` | Service usage report |

### Config & Dependencies

| File | Relevant Entry |
|------|---------------|
| `composer.json` | `"spatie/laravel-pdf": "^1.8"` |
| `package.json` | `"puppeteer": "^24.36.0"` |

---

## Quick Start for a New Project

```bash
# 1. Install packages
composer require spatie/laravel-pdf
npm install puppeteer

# 2. Create a PDF Blade template
# resources/views/pdf/my-report.blade.php (use inline CSS, not Tailwind)

# 3. Add downloadPdf() method to your Livewire component (copy the pattern above)

# 4. Add a button in your Blade view
# <flux:button wire:click="downloadPdf">Download PDF</flux:button>

# 5. On production server, install Chrome
# sudo apt-get install -y google-chrome-stable
```
