# PDF Generation Setup Guide

This project uses **Spatie Laravel PDF** with **Puppeteer** (headless Chrome) for generating PDF documents.

---

## Dependencies

- `spatie/laravel-pdf` (Composer) - Laravel PDF generation package
- `puppeteer` (npm) - Headless Chrome for rendering PDFs

---

## Quick Setup (After Cloning)

```bash
# 1. Install PHP dependencies
composer install

# 2. Install npm dependencies (includes Puppeteer)
npm install
```

---

## Common Error: "Cannot find module 'puppeteer'"

### Error Message

```
Failed to generate PDF: The command "node.EXE" ... failed.
Error: Cannot find module 'puppeteer'
```

### Cause

Puppeteer is not installed in `node_modules`. This can happen when:
1. `npm install` didn't run properly
2. `node_modules` was deleted or corrupted
3. Fresh clone without running `npm install`

### Solution

**Option 1: Standard npm install**
```bash
npm install
```

**Option 2: If npm commands produce no output (Windows issue)**

On some Windows setups, `npm` commands may silently fail. Run npm through Node directly:

```bash
node "C:/Program Files/nodejs/node_modules/npm/bin/npm-cli.js" install
```

**Option 3: Force reinstall Puppeteer**
```bash
npm install puppeteer --save
```

**Option 4: Clean reinstall (if issues persist)**
```bash
# Remove existing packages
rm -rf node_modules package-lock.json

# Reinstall everything
npm install

# Or on Windows if npm is not responding:
node "C:/Program Files/nodejs/node_modules/npm/bin/npm-cli.js" install
```

### Verify Installation

```bash
# Check Puppeteer is installed
node -e "console.log('Puppeteer version:', require('puppeteer/package.json').version)"
```

Should output something like:
```
Puppeteer version: 24.36.0
```

---

## Test PDF Generation

### Using Artisan Tinker

```bash
php artisan tinker
```

Then run:
```php
$record = App\Models\MedicalRecord::first();
\Spatie\LaravelPdf\Facades\Pdf::view('pdf.medical-record', ['record' => $record])
    ->format('a4')
    ->save(storage_path('app/temp/test.pdf'));
```

### Check Output

```bash
# Verify file was created
ls -la storage/app/temp/test.pdf
```

---

## How PDF Generation Works in HQMS

### Location
- Component: `app/Livewire/Nurse/PatientHistory.php` (method: `downloadPdf`)
- Component: `app/Livewire/Nurse/MedicalRecords.php` (method: `downloadPdf`)
- PDF Template: `resources/views/pdf/medical-record.blade.php`

### Flow
1. User clicks "Download PDF" button
2. Livewire calls `downloadPdf($recordId)` method
3. Method loads `MedicalRecord` with relationships
4. Spatie PDF renders the Blade template using Puppeteer (headless Chrome)
5. PDF is saved to `storage/app/temp/`
6. File is returned as download response and deleted after send

### Code Example
```php
public function downloadPdf(int $recordId): mixed
{
    $record = MedicalRecord::with(['consultationType', 'doctor', 'nurse'])
        ->find($recordId);

    $filename = 'medical-record-'.$record->record_number.'.pdf';
    $tempPath = storage_path('app/temp/'.$filename);

    // Ensure temp directory exists
    if (! is_dir(storage_path('app/temp'))) {
        mkdir(storage_path('app/temp'), 0755, true);
    }

    // Generate PDF
    Pdf::view('pdf.medical-record', ['record' => $record])
        ->format('a4')
        ->save($tempPath);

    // Return download and delete temp file after
    return response()->download($tempPath, $filename)
        ->deleteFileAfterSend(true);
}
```

---

## Troubleshooting

### PDF is blank or has missing styles
- Ensure Vite has built assets: `npm run build`
- Check the PDF Blade template for inline styles (external CSS may not load)

### PDF generation is slow
- First generation downloads Chromium (~150MB) - this is normal
- Subsequent generations are faster

### "Chrome executable not found"
Puppeteer should auto-download Chrome. If not:
```bash
node node_modules/puppeteer/install.mjs
```

### Permission errors on storage folder
```bash
# Linux/Mac
chmod -R 775 storage

# Windows (run as admin)
icacls storage /grant Everyone:F /T
```

---

## Package Versions

| Package | Version |
|---------|---------|
| spatie/laravel-pdf | ^1.8 |
| puppeteer | ^24.36.0 |
| Node.js | v24.13.0 |

---

## Resources

- [Spatie Laravel PDF Docs](https://spatie.be/docs/laravel-pdf)
- [Puppeteer Docs](https://pptr.dev/)
