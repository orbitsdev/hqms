# 📱 Complete SMS System Guide

📦 SMS System Folder Structure
app/
 ├── Jobs/
 │    └── SendSmsJob.php
 ├── Models/
 │    └── SmsLog.php
 ├── Services/
 │    └── SmsService.php
 │
 └── Services/Sms/
      ├── Contracts/
      │    └── SmsProviderInterface.php
      ├── Providers/
      │    ├── SemaphoreProvider.php
      │    ├── TwilioProvider.php
      │    ├── MoviderProvider.php
      │    ├── M360Provider.php
      │    └── VonageProvider.php (optional)
      └── Helpers/
           └── SmsResponse.php

.env → config/services.php → SmsService → resolveProvider() → Provider Class

> **Everything You Need to Know About the SMS System**
>
> From Installation to Production - Complete Reference
>
> Version: 1.0 | Last Updated: January 27, 2025

---

## 📋 Table of Contents

### Part 1: Getting Started
1. [System Overview](#1-system-overview)
2. [What You Can Do](#2-what-you-can-do-capabilities)
3. [Installation](#3-installation)
4. [Quick Start](#4-quick-start)

### Part 2: Core Features
5. [Sending SMS](#5-sending-sms)
6. [Phone Number Formats](#6-phone-number-formats)
7. [SMS Providers](#7-sms-providers)
8. [Database Logging](#8-database-logging)
9. [Analytics & Reporting](#9-analytics--reporting)

### Part 3: Provider Management
10. [Understanding Providers](#10-understanding-providers)
11. [Using Current Provider (Semaphore)](#11-using-current-provider-semaphore)
12. [Switching Providers](#12-switching-providers)
13. [Adding New Providers](#13-adding-new-providers)
14. [Creating Custom Providers](#14-creating-custom-providers)

### Part 4: Advanced Features
15. [Security Features](#15-security-features)
16. [Queue System](#16-queue-system)
17. [Error Handling & Retries](#17-error-handling--retries)
18. [Context Tracking](#18-context-tracking)

### Part 5: API Usage
19. [Testing API](#19-testing-api)
20. [API Endpoints Reference](#20-api-endpoints-reference)
21. [Integration Examples](#21-integration-examples)

### Part 6: Production & Maintenance
22. [Configuration Reference](#22-configuration-reference)
23. [Troubleshooting](#23-troubleshooting)
24. [Best Practices](#24-best-practices)
25. [Production Deployment](#25-production-deployment)

---

# Part 1: Getting Started

## 1. System Overview

### What Is This System?

A **production-ready, multi-provider SMS system** for Laravel that allows you to:
- Send SMS through any provider (Semaphore, Twilio, Movider, M360, etc.)
- Switch providers instantly without code changes
- Track every SMS in database
- Get analytics and reports
- Test via API endpoints
- Handle failures automatically

### Architecture

```
Your Application
      ↓
SendSmsJob (Queue)
      ↓
SmsService (Router)
      ↓
Provider (Semaphore/Twilio/etc)
      ↓
SMS Gateway
      ↓
Recipient's Phone
```

### Key Components

| Component | Purpose | File Location |
|-----------|---------|---------------|
| **SendSmsJob** | Queue job for sending | `app/Jobs/SendSmsJob.php` |
| **SmsService** | Main service | `app/Services/SmsService.php` |
| **Providers** | Provider implementations | `app/Services/Sms/Providers/` |
| **SmsLog** | Database model | `app/Models/SmsLog.php` |
| **API Controller** | Testing endpoints | `app/Http/Controllers/Api/SmsTestController.php` |
| **Config** | Configuration | `config/services.php` |

---

## 2. What You Can Do (Capabilities)

### ✅ **Sending SMS**
- Send SMS to any Philippine number (any format)
- Send via queue (recommended) or direct
- Track sender and recipient
- Categorize by context (FMR, FMD, OTP, etc.)
- Send bulk SMS
- Schedule SMS (using Laravel scheduler)

### ✅ **Provider Management**
- Use any SMS provider
- Switch providers in 1 second (change .env)
- Support multiple providers simultaneously
- Add new providers in 5 minutes
- Test providers via API
- Fallback to backup provider (optional)

### ✅ **Phone Number Handling**
- Accept any Philippine format (09XX, 9XX, 639XX, +639XX)
- Auto-format for current provider
- Validate phone numbers
- Test formatting via API
- Support international numbers (provider-dependent)

### ✅ **Database & Logging**
- Every SMS logged automatically
- Track status (pending/sent/failed)
- Store provider response
- Track retry attempts
- Store error messages
- Link to users (sender/recipient)

### ✅ **Analytics & Reporting**
- Success rate percentage
- SMS by status (sent/failed/pending)
- SMS by context (FMR/FMD/OTP)
- SMS by date range
- Daily volume charts
- Problematic phone numbers
- Failure analysis

### ✅ **Security Features (Optional)**
- Rate limiting (max SMS per hour)
- Auto-blacklist (block bad numbers)
- Phone masking in logs
- API authentication
- Input validation

### ✅ **Error Handling**
- Automatic retries (3 attempts)
- Exponential backoff (1min, 5min, 15min)
- Timeout protection (30 seconds)
- Connection error handling
- Provider error handling
- Failed job tracking

### ✅ **Testing & Development**
- 7 API endpoints for testing
- Test phone formatting
- Test provider configuration
- Query logs via API
- Get statistics via API
- Direct send for testing

### ✅ **Integration**
- REST API
- PHP code
- JavaScript/AJAX
- Python
- cURL
- Any HTTP client

---

## 3. Installation

### Prerequisites
- Laravel application
- PHP 7.4+
- Database (MySQL/PostgreSQL)
- Queue driver configured (database/redis)

### Step 1: Run Migration

```bash
php artisan migrate --path=database/migrations/2025_11_27_194546_create_sms_logs_table.php
```

This creates the `sms_logs` table.

### Step 2: Configure Environment

Add to `.env`:

```env
#─────────────────────────────────────────────────────────────
# SMS SYSTEM CONFIGURATION
#─────────────────────────────────────────────────────────────

# Provider Selection (semaphore, twilio, movider, m360)
SMS_PROVIDER=semaphore

# Semaphore Configuration
SEMAPHORE_API_KEY=your_api_key_here
SEMAPHORE_SENDER_NAME=HIMS

# Twilio Configuration (if you want to use Twilio)
TWILIO_ACCOUNT_SID=
TWILIO_AUTH_TOKEN=
TWILIO_FROM_NUMBER=

# Movider Configuration (if you want to use Movider)
MOVIDER_API_KEY=
MOVIDER_API_SECRET=

# M360 Configuration (if you want to use M360)
M360_USERNAME=
M360_PASSWORD=
M360_SHORTCODE=

# Security Features (DISABLED by default)
SMS_RATE_LIMIT_ENABLED=false
SMS_RATE_LIMIT_PER_HOUR=5
SMS_BLACKLIST_ENABLED=false
SMS_BLACKLIST_THRESHOLD=10
SMS_BLACKLIST_PERIOD_DAYS=30

# Queue Configuration
QUEUE_CONNECTION=database
```

### Step 3: Start Queue Worker

```bash
php artisan queue:work
```

Or use Supervisor for production (see Production Deployment section).

### Step 4: Test

```bash
php artisan tinker
```

```php
use App\Jobs\SendSmsJob;

SendSmsJob::dispatch('09123456789', 'Test SMS!');
```

Check `sms_logs` table:
```sql
SELECT * FROM sms_logs ORDER BY created_at DESC LIMIT 1;
```

---

## 4. Quick Start

### Sending Your First SMS

**From PHP Code:**
```php
use App\Jobs\SendSmsJob;

SendSmsJob::dispatch('09123456789', 'Hello from SMS System!');
```

**From Controller:**
```php
public function sendReminder($userId)
{
    $user = User::find($userId);

    SendSmsJob::dispatch(
        $user->phone,
        'Your appointment is tomorrow',
        'REMINDER',
        $user->id,
        auth()->id()
    );

    return back()->with('success', 'SMS sent!');
}
```

**From API:**
```bash
curl -X POST http://your-domain.com/api/sms/send \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "09123456789",
    "message": "Test SMS"
  }'
```

**Check Status:**
```bash
curl http://your-domain.com/api/sms/logs?limit=5
```

---

# Part 2: Core Features

## 5. Sending SMS

### Method 1: Queue (Recommended)

**Basic:**
```php
use App\Jobs\SendSmsJob;

SendSmsJob::dispatch($phone, $message);
```

**With Context:**
```php
SendSmsJob::dispatch(
    '09123456789',
    'Your message',
    'FMR'  // Context: FMR, FMD, OTP, NOTIFICATION, etc.
);
```

**With Full Tracking:**
```php
SendSmsJob::dispatch(
    $phone,        // Phone number
    $message,      // Message text
    $context,      // Context (FMR, FMD, etc.)
    $recipientId,  // Who receives SMS
    $senderId      // Who triggered SMS
);
```

**Example:**
```php
SendSmsJob::dispatch(
    '09123456789',
    'FMR Reminder: Your CA is due',
    'FMR',
    123,  // user_id of recipient
    456   // user_id of sender
);
```

### Method 2: Direct (Testing Only)

```php
use App\Services\SmsService;

$sms = app(SmsService::class);
$result = $sms->sendSms('09123456789', 'Test');

if (!empty($result['error'])) {
    echo "Failed: " . $result['error'];
} else {
    echo "Sent! ID: " . $result['message_id'];
}
```

### Method 3: Via API

```bash
# Queued (recommended)
curl -X POST http://your-domain.com/api/sms/send \
  -H "Content-Type: application/json" \
  -d '{"phone":"09123456789","message":"Test"}'

# Direct (testing only)
curl -X POST http://your-domain.com/api/sms/test-direct \
  -H "Content-Type: application/json" \
  -d '{"phone":"09123456789","message":"Test"}'
```

### Bulk SMS

```php
$users = User::where('notify', true)->get();

foreach ($users as $user) {
    SendSmsJob::dispatch(
        $user->phone,
        'Important announcement',
        'ANNOUNCEMENT',
        $user->id,
        auth()->id()
    );
}

// All SMS will be queued and processed in background
```

### Scheduled SMS

```php
// In app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        $users = User::where('reminder_enabled', true)->get();

        foreach ($users as $user) {
            SendSmsJob::dispatch(
                $user->phone,
                'Daily reminder',
                'SCHEDULED',
                $user->id
            );
        }
    })->daily();
}
```

---

## 6. Phone Number Formats

### Input Formats (All Work)

The system accepts **any** Philippine phone format:

| Input Format | Example | Description |
|--------------|---------|-------------|
| `09XXXXXXXXX` | `09123456789` | Most common format |
| `9XXXXXXXXX` | `9123456789` | Without leading 0 |
| `639XXXXXXXXX` | `639123456789` | International without + |
| `+639XXXXXXXXX` | `+639123456789` | Full international |

**All these work:**
```php
SendSmsJob::dispatch('09123456789', 'Test');  // ✅
SendSmsJob::dispatch('9123456789', 'Test');   // ✅
SendSmsJob::dispatch('639123456789', 'Test'); // ✅
SendSmsJob::dispatch('+639123456789', 'Test');// ✅
```

### Output Formats (Provider-Specific)

Each provider requires a specific format:

| Provider | Required Format | Example |
|----------|----------------|---------|
| **Semaphore** | `+639XXXXXXXXX` | `+639123456789` |
| **Twilio** | `+639XXXXXXXXX` | `+639123456789` |
| **Movider** | `09XXXXXXXXX` | `09123456789` |
| **M360** | `639XXXXXXXXX` | `639123456789` |

**The system handles this automatically!**

### How It Works

```php
// You provide any format
SendSmsJob::dispatch('09123456789', 'Test');

// System formats for current provider
// Semaphore → +639123456789
// Movider  → 09123456789
// M360     → 639123456789

// SMS sent with correct format ✅
```

### Test Formatting

**Via Code:**
```php
$sms = app(SmsService::class);
echo $sms->formatPhoneNumber('09123456789');
// Output (Semaphore): +639123456789
```

**Via API:**
```bash
curl -X POST http://your-domain.com/api/sms/format-phone \
  -H "Content-Type: application/json" \
  -d '{"phone": "09123456789"}'
```

**Response:**
```json
{
    "status": true,
    "data": {
        "original": "09123456789",
        "formatted": "+639123456789",
        "provider": "Semaphore"
    }
}
```

---

## 7. SMS Providers

### Available Providers

| Provider | Status | Format | Setup Time |
|----------|--------|--------|------------|
| **Semaphore** | ✅ Active | `+639XX` | Ready (current) |
| **Twilio** | ✅ Ready | `+639XX` | 2 minutes |
| **Movider** | ✅ Ready | `09XX` | 2 minutes |
| **M360** | ✅ Ready | `639XX` | 2 minutes |
| **Custom** | ⚙️ Add Your Own | Custom | 5 minutes |

### Provider Features

**Semaphore:**
- ✅ Philippine SMS
- ✅ Affordable rates
- ✅ Good reliability
- ✅ Simple API
- Format: `+639123456789`

**Twilio:**
- ✅ International SMS
- ✅ Premium reliability
- ✅ Advanced features
- ✅ Extensive docs
- Format: `+639123456789`

**Movider:**
- ✅ Philippine SMS
- ✅ Competitive pricing
- ✅ Local support
- Format: `09123456789`

**M360:**
- ✅ Philippine SMS
- ✅ Corporate focused
- ✅ Bulk messaging
- Format: `639123456789`

### How Providers Work

```
┌─────────────────────────────────────┐
│         Your Application            │
│  SendSmsJob::dispatch($phone, $msg) │
└───────────────┬─────────────────────┘
                │
                ▼
┌─────────────────────────────────────┐
│          SmsService                 │
│  - Checks which provider to use     │
│  - Routes SMS to correct provider   │
└───────────────┬─────────────────────┘
                │
        ┌───────┴────────┐
        │                │
        ▼                ▼
┌──────────────┐  ┌──────────────┐
│  Semaphore   │  │   Twilio     │
│   Provider   │  │   Provider   │
│              │  │              │
│ - Formats #  │  │ - Formats #  │
│ - Sends SMS  │  │ - Sends SMS  │
└──────────────┘  └──────────────┘
```

---

## 8. Database Logging

### sms_logs Table Structure

Every SMS is logged automatically:

```sql
CREATE TABLE sms_logs (
    id BIGINT PRIMARY KEY,
    phone_number VARCHAR(255),           -- Original input
    formatted_phone_number VARCHAR(255), -- Provider format
    message TEXT,                        -- SMS content
    status ENUM('pending','sent','failed'),
    message_id VARCHAR(255),             -- Provider's ID
    attempts INT DEFAULT 0,              -- Retry count
    error_message TEXT,                  -- Error details
    api_response JSON,                   -- Full API response
    sent_at TIMESTAMP,                   -- Success time
    failed_at TIMESTAMP,                 -- Failure time
    context VARCHAR(255),                -- FMR, FMD, OTP, etc.
    user_id BIGINT,                      -- Recipient
    sender_id BIGINT,                    -- Sender
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Viewing Logs

**Via Database:**
```sql
-- Recent SMS
SELECT * FROM sms_logs ORDER BY created_at DESC LIMIT 10;

-- Failed SMS
SELECT * FROM sms_logs WHERE status = 'failed' ORDER BY created_at DESC;

-- SMS for specific phone
SELECT * FROM sms_logs WHERE phone_number = '09123456789';

-- SMS by context
SELECT * FROM sms_logs WHERE context = 'FMR';
```

**Via PHP:**
```php
use App\Models\SmsLog;

// Recent logs
$recent = SmsLog::latest()->limit(10)->get();

// Failed SMS
$failed = SmsLog::failed()->get();

// Sent SMS
$sent = SmsLog::sent()->get();

// Pending SMS
$pending = SmsLog::pending()->get();

// SMS for specific phone
$phoneLog = SmsLog::byPhone('09123456789')->get();

// SMS by context
$fmrLog = SmsLog::byContext('FMR')->get();
```

**Via API:**
```bash
# Recent logs
curl http://your-domain.com/api/sms/logs?limit=10

# Failed SMS
curl "http://your-domain.com/api/sms/logs?status=failed"

# SMS for phone
curl "http://your-domain.com/api/sms/logs?phone=09123456789"

# SMS by context
curl "http://your-domain.com/api/sms/logs?context=FMR"
```

### Log Status Flow

```
pending → sent     (Success)
pending → failed   (All retries failed)
```

---

## 9. Analytics & Reporting

### Success Rate

```php
use App\Models\SmsLog;

// Last 30 days
$rate = SmsLog::getSuccessRate(30);
echo "Success rate: {$rate}%";
// Output: Success rate: 95.5%

// Last 7 days
$weekRate = SmsLog::getSuccessRate(7);

// Last 90 days
$quarterRate = SmsLog::getSuccessRate(90);
```

### Detailed Statistics

```php
$stats = SmsLog::getStatistics('2025-01-01', '2025-01-31');

print_r($stats);
/*
Array (
    [total] => 1000
    [sent] => 955
    [failed] => 45
    [pending] => 0
    [success_rate] => 95.5
)
*/
```

### SMS by Context

```php
$byContext = SmsLog::getCountByContext(30);

print_r($byContext);
/*
Array (
    [FMR] => Array (
        [count] => 500
        [sent] => 480
        [failed] => 20
    )
    [FMD] => Array (
        [count] => 300
        [sent] => 285
        [failed] => 15
    )
    [OTP] => Array (
        [count] => 200
        [sent] => 190
        [failed] => 10
    )
)
*/
```

### Problematic Numbers

```php
$bad = SmsLog::getProblematicNumbers(10, 30);

foreach ($bad as $number) {
    echo "{$number['phone_number']}: ";
    echo "{$number['failure_rate']}% failure rate\n";
}
/*
09111111111: 80% failure rate
09222222222: 75% failure rate
...
*/
```

### Daily Volume

```php
$daily = SmsLog::getDailyVolume(30);

foreach ($daily as $day) {
    echo "{$day['date']}: ";
    echo "Total={$day['total']}, ";
    echo "Sent={$day['sent']}, ";
    echo "Failed={$day['failed']}\n";
}
/*
2025-01-01: Total=50, Sent=48, Failed=2
2025-01-02: Total=45, Sent=43, Failed=2
...
*/
```

### Via API

```bash
# Get statistics
curl "http://your-domain.com/api/sms/stats?days=30"
```

**Response:**
```json
{
    "status": true,
    "data": {
        "success_rate": 95.5,
        "statistics": {
            "total": 1000,
            "sent": 955,
            "failed": 45,
            "pending": 0,
            "success_rate": 95.5
        },
        "by_context": {
            "FMR": {"count": 500, "sent": 480, "failed": 20},
            "FMD": {"count": 300, "sent": 285, "failed": 15}
        }
    }
}
```

---

# Part 3: Provider Management

## 10. Understanding Providers

### What is a Provider?

A **provider** is an SMS gateway service (Semaphore, Twilio, etc.) that actually sends the SMS.

Your system is **provider-agnostic** - it works with any provider through a standard interface.

### Provider Interface

All providers implement this interface:

```php
interface SmsProviderInterface
{
    public function send(string $number, string $message): array;
    public function formatPhoneNumber(string $phone): string;
    public function getName(): string;
    public function isConfigured(): bool;
}
```

This ensures:
- ✅ Consistent behavior
- ✅ Easy switching
- ✅ Simple to add new providers

### How Provider Selection Works

```php
// In SmsService
protected function resolveProvider(): SmsProviderInterface
{
    $providerName = config('services.sms.default_provider');
    // Returns: "semaphore"

    $providers = [
        'semaphore' => SemaphoreProvider::class,
        'twilio' => TwilioProvider::class,
        // etc.
    ];

    return new $providers[$providerName]();
}
```

---

## 11. Using Current Provider (Semaphore)

### Configuration

**`.env`:**
```env
SMS_PROVIDER=semaphore
SEMAPHORE_API_KEY=your_key_here
SEMAPHORE_SENDER_NAME=HIMS
```

**`config/services.php`:**
```php
'semaphore' => [
    'api_key' => env('SEMAPHORE_API_KEY'),
    'sender_name' => env('SEMAPHORE_SENDER_NAME', 'HIMS'),
],
```

### Getting API Key

1. Go to [Semaphore.co](https://semaphore.co)
2. Sign up / Log in
3. Dashboard → API
4. Copy your API key
5. Add to `.env`

### Testing Semaphore

```bash
curl -X POST http://your-domain.com/api/sms/test-direct \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "09123456789",
    "message": "Test from Semaphore"
  }'
```

### Phone Format

Semaphore requires: `+639XXXXXXXXX`

```
Input:  09123456789
Output: +639123456789  (automatic)
```

---

## 12. Switching Providers

### How to Switch (3 Steps)

#### Step 1: Add Provider Credentials

**To Twilio:**
```env
SMS_PROVIDER=twilio
TWILIO_ACCOUNT_SID=your_sid
TWILIO_AUTH_TOKEN=your_token
TWILIO_FROM_NUMBER=+639XXXXXXXXX
```

**To Movider:**
```env
SMS_PROVIDER=movider
MOVIDER_API_KEY=your_key
MOVIDER_API_SECRET=your_secret
```

**To M360:**
```env
SMS_PROVIDER=m360
M360_USERNAME=your_username
M360_PASSWORD=your_password
M360_SHORTCODE=your_shortcode
```

#### Step 2: Clear Config Cache

```bash
php artisan config:clear
```

#### Step 3: Test

```bash
curl -X POST http://your-domain.com/api/sms/test-direct \
  -d '{"phone":"09123456789","message":"Test new provider"}'
```

### That's It!

**No code changes needed.** All SMS will now use the new provider.

### Verify Current Provider

```bash
curl http://your-domain.com/api/sms/provider
```

**Response:**
```json
{
    "provider": "Twilio",
    "config": {...}
}
```

---

## 13. Adding New Providers

### Step-by-Step: Add Vonage

#### Step 1: Create Provider Class

Create `app/Services/Sms/Providers/VonageProvider.php`:

```php
<?php

namespace App\Services\Sms\Providers;

use App\Helpers\SmsResponse;
use App\Services\Sms\Contracts\SmsProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VonageProvider implements SmsProviderInterface
{
    protected string $apiKey;
    protected string $apiSecret;

    public function __construct()
    {
        $this->apiKey = config('services.vonage.api_key', '');
        $this->apiSecret = config('services.vonage.api_secret', '');
    }

    public function send(string $number, string $message): array
    {
        try {
            $formattedNumber = $this->formatPhoneNumber($number);

            $response = Http::post('https://rest.nexmo.com/sms/json', [
                'api_key' => $this->apiKey,
                'api_secret' => $this->apiSecret,
                'from' => 'HIMS',
                'to' => $formattedNumber,
                'text' => $message,
            ]);

            if (!$response->successful()) {
                return SmsResponse::error(
                    'HTTP ' . $response->status(),
                    $formattedNumber,
                    $response->json()
                );
            }

            $data = $response->json();

            if (isset($data['messages'][0]['status']) && $data['messages'][0]['status'] !== '0') {
                return SmsResponse::error(
                    $data['messages'][0]['error-text'] ?? 'Unknown error',
                    $formattedNumber,
                    $data
                );
            }

            return SmsResponse::success(
                $data['messages'][0]['message-id'] ?? null,
                $formattedNumber,
                $data
            );

        } catch (\Exception $e) {
            Log::error('Vonage SMS Failed: ' . $e->getMessage());
            return SmsResponse::error($e->getMessage(), $formattedNumber ?? $number);
        }
    }

    public function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (substr($phone, 0, 2) === '09') {
            return '63' . substr($phone, 1);  // 09123456789 → 639123456789
        }

        if (substr($phone, 0, 1) === '9' && strlen($phone) === 10) {
            return '63' . $phone;
        }

        if (substr($phone, 0, 3) === '639') {
            return $phone;
        }

        return '63' . ltrim($phone, '0+');
    }

    public function getName(): string
    {
        return 'Vonage';
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->apiSecret);
    }
}
```

#### Step 2: Register Provider

Edit `app/Services/SmsService.php`:

```php
protected function resolveProvider(): SmsProviderInterface
{
    $providerName = config('services.sms.default_provider', 'semaphore');

    $providers = [
        'semaphore' => SemaphoreProvider::class,
        'twilio' => TwilioProvider::class,
        'movider' => MoviderProvider::class,
        'm360' => M360Provider::class,
        'vonage' => VonageProvider::class,  // Add here
    ];

    // ... rest of code
}
```

#### Step 3: Add Configuration

Edit `config/services.php`:

```php
'vonage' => [
    'api_key' => env('VONAGE_API_KEY'),
    'api_secret' => env('VONAGE_API_SECRET'),
],
```

#### Step 4: Configure `.env`

```env
SMS_PROVIDER=vonage
VONAGE_API_KEY=your_api_key
VONAGE_API_SECRET=your_api_secret
```

#### Step 5: Test

```bash
php artisan config:clear

curl -X POST http://your-domain.com/api/sms/test-direct \
  -d '{"phone":"09123456789","message":"Test Vonage"}'
```

**Done!** Vonage is now available.

---

## 14. Creating Custom Providers

### Template

Use this template for any provider:

```php
<?php

namespace App\Services\Sms\Providers;

use App\Helpers\SmsResponse;
use App\Services\Sms\Contracts\SmsProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YourProviderNameProvider implements SmsProviderInterface
{
    protected string $apiKey;  // Add your credentials here

    public function __construct()
    {
        $this->apiKey = config('services.yourprovider.api_key', '');
    }

    public function send(string $number, string $message): array
    {
        try {
            $formattedNumber = $this->formatPhoneNumber($number);

            // YOUR API CALL HERE
            $response = Http::timeout(30)->post('https://api.yourprovider.com/send', [
                'api_key' => $this->apiKey,
                'to' => $formattedNumber,
                'message' => $message,
            ]);

            if (!$response->successful()) {
                return SmsResponse::error(
                    'HTTP ' . $response->status(),
                    $formattedNumber,
                    $response->json()
                );
            }

            $data = $response->json();

            // CHECK FOR ERRORS (adjust to your API)
            if (isset($data['error'])) {
                return SmsResponse::error(
                    $data['error'],
                    $formattedNumber,
                    $data
                );
            }

            // SUCCESS (adjust field names to your API)
            return SmsResponse::success(
                $data['message_id'] ?? null,
                $formattedNumber,
                $data
            );

        } catch (\Exception $e) {
            Log::error('YourProvider SMS Failed: ' . $e->getMessage());
            return SmsResponse::error($e->getMessage(), $formattedNumber ?? $number);
        }
    }

    public function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        // FORMAT ACCORDING TO YOUR PROVIDER'S REQUIREMENTS
        // Example: Convert to +639XXXXXXXXX
        if (substr($phone, 0, 2) === '09') {
            return '+63' . substr($phone, 1);
        }

        if (substr($phone, 0, 1) === '9' && strlen($phone) === 10) {
            return '+63' . $phone;
        }

        if (substr($phone, 0, 3) === '639') {
            return '+' . $phone;
        }

        return '+63' . ltrim($phone, '0');
    }

    public function getName(): string
    {
        return 'YourProviderName';
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }
}
```

### Key Points

1. **Implement SmsProviderInterface** - Required
2. **Use SmsResponse helper** - For consistent responses
3. **Format phone numbers** - According to provider requirements
4. **Handle errors** - Return SmsResponse::error()
5. **Log errors** - Use Log facade
6. **Timeout** - Set 30 second timeout
7. **Check configuration** - Implement isConfigured()

---

# Part 4: Advanced Features

## 15. Security Features

### Rate Limiting

**Purpose:** Prevent sending too many SMS to same number

**Configuration:**
```env
SMS_RATE_LIMIT_ENABLED=true
SMS_RATE_LIMIT_PER_HOUR=5
```

**How it works:**
- Counts SMS sent to a number in last hour
- Blocks if exceeds limit
- Returns error message

**Example:**
```php
// User tries to send 6 SMS in 1 hour to same number
// 5th SMS: ✅ Sent
// 6th SMS: ❌ Blocked: "Too many SMS attempts"
```

**Check if rate limited:**
```php
$count = SmsLog::where('phone_number', '09123456789')
    ->where('created_at', '>=', now()->subHour())
    ->count();

if ($count >= 5) {
    echo "Rate limited!";
}
```

### Auto-Blacklist

**Purpose:** Automatically block numbers with repeated failures

**Configuration:**
```env
SMS_BLACKLIST_ENABLED=true
SMS_BLACKLIST_THRESHOLD=10
SMS_BLACKLIST_PERIOD_DAYS=30
```

**How it works:**
- Tracks failures per number over X days
- If failures >= threshold, blocks number
- Saves money on invalid numbers

**Example:**
```php
// Number failed 10 times in 30 days
// Next attempt: ❌ Blocked: "Phone number is blocked"
```

**Check if blacklisted:**
```php
$failures = SmsLog::where('phone_number', '09123456789')
    ->where('status', 'failed')
    ->where('created_at', '>=', now()->subDays(30))
    ->count();

if ($failures >= 10) {
    echo "Blacklisted!";
}
```

### Disable Security Features

**Default:** Both are **DISABLED**

To disable:
```env
SMS_RATE_LIMIT_ENABLED=false
SMS_BLACKLIST_ENABLED=false
```

---

## 16. Queue System

### Why Queue?

**Benefits:**
- ✅ Non-blocking (doesn't slow down your app)
- ✅ Automatic retries
- ✅ Better error handling
- ✅ Scalable (process many SMS)
- ✅ Resilient to failures

### How It Works

```
User Action
    ↓
SendSmsJob::dispatch()  ← Adds to queue instantly
    ↓
Returns immediately ✅
    ↓
(Background)
Queue Worker processes job
    ↓
Sends SMS
    ↓
Logs result
```

### Queue Configuration

**`.env`:**
```env
QUEUE_CONNECTION=database  # or redis, sync, etc.
```

**Options:**
- `database` - Store in database (simple, good for most)
- `redis` - Fast, requires Redis
- `sync` - No queue, immediate (testing only)
- `sqs` - AWS SQS (production scale)

### Starting Queue Worker

**Development:**
```bash
php artisan queue:work
```

**Production (Supervisor):**

Create `/etc/supervisor/conf.d/queue-worker.conf`:
```ini
[program:queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start queue-worker:*
```

### Monitoring Queue

```bash
# Check queue status
php artisan queue:work --once

# View failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Flush failed jobs
php artisan queue:flush
```

---

## 17. Error Handling & Retries

### Automatic Retries

**Configuration (in SendSmsJob):**
```php
public $tries = 3;  // Retry 3 times
public $backoff = [60, 300, 900];  // Wait 1min, 5min, 15min
public $timeout = 30;  // Max 30 seconds per attempt
```

### Retry Flow

```
Attempt 1: Send SMS
    ↓ (Failed)
Wait 1 minute
    ↓
Attempt 2: Send SMS
    ↓ (Failed)
Wait 5 minutes
    ↓
Attempt 3: Send SMS
    ↓ (Failed)
Wait 15 minutes
    ↓
Attempt 4: Send SMS (final)
    ↓ (Failed)
Mark as permanently failed
    ↓
Log error
    ↓
Trigger failed() method
```

### Error Types Handled

1. **Network Errors**
   - Connection timeout
   - DNS failure
   - Network unreachable

2. **Provider Errors**
   - Invalid API key
   - Insufficient credits
   - Invalid phone number
   - Rate limit from provider

3. **System Errors**
   - Database errors
   - Queue errors
   - Configuration errors

### Custom Error Handling

```php
// In SendSmsJob
public function failed(\Throwable $exception)
{
    // Update log
    SmsLog::where('phone_number', $this->number)
        ->where('message', $this->message)
        ->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
            'failed_at' => now(),
        ]);

    // Send alert to admin
    Mail::to('admin@example.com')->send(
        new SmsFailedAlert($this->number, $exception)
    );

    // Log to monitoring service
    report($exception);
}
```

---

## 18. Context Tracking

### What is Context?

**Context** categorizes SMS by purpose/type:
- `FMR` - Formal Management Reminder
- `FMD` - Formal Management Demand
- `SCO` - Show Cause Order
- `OTP` - One-Time Password
- `NOTIFICATION` - General notification
- `REMINDER` - Reminders
- `ALERT` - Urgent alerts

### Why Use Context?

- ✅ Filter logs by category
- ✅ Get statistics per type
- ✅ Track success rates by purpose
- ✅ Analyze which SMS types work best
- ✅ Debug specific flows

### Using Context

```php
// Send with context
SendSmsJob::dispatch(
    '09123456789',
    'Your CA is due',
    'FMR'  // ← Context
);

// Query by context
$fmrLogs = SmsLog::byContext('FMR')->get();

// Statistics by context
$stats = SmsLog::getCountByContext(30);
/*
Array (
    [FMR] => Array ( [count] => 500, [sent] => 480, [failed] => 20 )
    [FMD] => Array ( [count] => 300, [sent] => 285, [failed] => 15 )
)
*/
```

### Context Best Practices

**Use descriptive contexts:**
```php
// ✅ Good
SendSmsJob::dispatch($phone, $msg, 'PASSWORD_RESET');
SendSmsJob::dispatch($phone, $msg, 'APPOINTMENT_REMINDER');
SendSmsJob::dispatch($phone, $msg, 'PAYMENT_CONFIRMATION');

// ❌ Bad
SendSmsJob::dispatch($phone, $msg, 'SMS1');
SendSmsJob::dispatch($phone, $msg, 'TEST');
SendSmsJob::dispatch($phone, $msg, 'ABC');
```

---

# Part 5: API Usage

## 19. Testing API

### API Overview

**Base URL:** `http://your-domain.com/api/sms`

**7 Endpoints:**
1. `POST /send` - Send SMS (queued)
2. `POST /test-direct` - Send SMS (direct)
3. `GET /log/{id}` - Get log by ID
4. `GET /logs` - Get recent logs
5. `GET /stats` - Get statistics
6. `GET /provider` - Get provider info
7. `POST /format-phone` - Test phone formatting

### Quick Test

```bash
# 1. Send SMS
curl -X POST http://your-domain.com/api/sms/send \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "09123456789",
    "message": "Test SMS"
  }'

# 2. Check logs
curl http://your-domain.com/api/sms/logs?limit=5

# 3. Get statistics
curl http://your-domain.com/api/sms/stats?days=7
```

---

## 20. API Endpoints Reference

### 1. Send SMS (Queued)

**POST `/api/sms/send`**

**Request:**
```json
{
    "phone": "09123456789",      // Required
    "message": "Your message",   // Required
    "context": "FMR",            // Optional
    "user_id": 123,              // Optional
    "sender_id": 456             // Optional
}
```

**Response:**
```json
{
    "status": true,
    "message": "SMS queued successfully",
    "data": {
        "phone": "09123456789",
        "message": "Your message",
        "context": "FMR",
        "status": "queued"
    }
}
```

### 2. Send SMS Direct

**POST `/api/sms/test-direct`**

**Request:**
```json
{
    "phone": "09123456789",
    "message": "Test"
}
```

**Response:**
```json
{
    "status": true,
    "message": "SMS sent successfully",
    "data": {
        "provider": "Semaphore",
        "message_id": "msg_123456",
        "formatted_number": "+639123456789"
    }
}
```

### 3. Get Log by ID

**GET `/api/sms/log/{id}`**

**Example:** `GET /api/sms/log/123`

**Response:**
```json
{
    "status": true,
    "data": {
        "id": 123,
        "phone_number": "09123456789",
        "message": "Test",
        "status": "sent",
        "message_id": "msg_123",
        "attempts": 1,
        "error_message": null,
        "context": "TEST",
        "sent_at": "2025-01-27T10:30:00Z"
    }
}
```

### 4. Get Recent Logs

**GET `/api/sms/logs`**

**Query Parameters:**
- `limit` - Number of results (default: 10)
- `status` - Filter: `sent`, `failed`, `pending`
- `phone` - Filter by phone number
- `context` - Filter by context

**Examples:**
```bash
# Last 10 SMS
GET /api/sms/logs

# Last 20 sent SMS
GET /api/sms/logs?limit=20&status=sent

# Failed SMS
GET /api/sms/logs?status=failed

# SMS for specific phone
GET /api/sms/logs?phone=09123456789

# FMR context SMS
GET /api/sms/logs?context=FMR
```

### 5. Get Statistics

**GET `/api/sms/stats`**

**Query Parameters:**
- `days` - Number of days (default: 30)

**Response:**
```json
{
    "status": true,
    "data": {
        "success_rate": 95.5,
        "statistics": {
            "total": 1000,
            "sent": 955,
            "failed": 45,
            "pending": 0
        },
        "by_context": {
            "FMR": {"count": 500, "sent": 480, "failed": 20}
        }
    }
}
```

### 6. Get Provider Info

**GET `/api/sms/provider`**

**Response:**
```json
{
    "status": true,
    "data": {
        "provider": "Semaphore",
        "config": {
            "default_provider": "semaphore",
            "rate_limit_enabled": false
        }
    }
}
```

### 7. Format Phone

**POST `/api/sms/format-phone`**

**Request:**
```json
{
    "phone": "09123456789"
}
```

**Response:**
```json
{
    "status": true,
    "data": {
        "original": "09123456789",
        "formatted": "+639123456789",
        "provider": "Semaphore"
    }
}
```

---

## 21. Integration Examples

### JavaScript/Fetch

```javascript
async function sendSMS(phone, message, context = 'TEST') {
    const response = await fetch('/api/sms/send', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({phone, message, context}),
    });

    const data = await response.json();

    if (data.status) {
        console.log('SMS sent:', data.data);
        return data.data;
    } else {
        console.error('SMS failed:', data.message);
        throw new Error(data.message);
    }
}

// Usage
sendSMS('09123456789', 'Test SMS', 'FMR')
    .then(result => console.log('Success!', result))
    .catch(error => console.error('Error:', error));
```

### jQuery/AJAX

```javascript
$.ajax({
    url: '/api/sms/send',
    method: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({
        phone: '09123456789',
        message: 'Test SMS',
        context: 'FMR'
    }),
    success: function(response) {
        if (response.status) {
            alert('SMS sent successfully!');
        }
    },
    error: function(xhr) {
        alert('Error: ' + xhr.responseJSON.message);
    }
});
```

### PHP/Guzzle

```php
use GuzzleHttp\Client;

$client = new Client();

$response = $client->post('http://your-domain.com/api/sms/send', [
    'json' => [
        'phone' => '09123456789',
        'message' => 'Test SMS',
        'context' => 'FMR',
        'user_id' => 123,
    ]
]);

$data = json_decode($response->getBody(), true);

if ($data['status']) {
    echo "SMS queued!\n";
} else {
    echo "Failed: " . $data['message'] . "\n";
}
```

### Python/Requests

```python
import requests

response = requests.post(
    'http://your-domain.com/api/sms/send',
    json={
        'phone': '09123456789',
        'message': 'Test SMS',
        'context': 'FMR'
    }
)

data = response.json()

if data['status']:
    print('SMS queued successfully')
else:
    print(f"Failed: {data['message']}")
```

### cURL

```bash
curl -X POST http://your-domain.com/api/sms/send \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "09123456789",
    "message": "Test SMS",
    "context": "FMR",
    "user_id": 123,
    "sender_id": 456
  }'
```

---

# Part 6: Production & Maintenance

## 22. Configuration Reference

### Complete `.env` Configuration

```env
#─────────────────────────────────────────────────────────────
# SMS SYSTEM - COMPLETE CONFIGURATION
#─────────────────────────────────────────────────────────────

# Provider Selection
# Options: semaphore, twilio, movider, m360, vonage
SMS_PROVIDER=semaphore

#─────────────────────────────────────────────────────────────
# SEMAPHORE CONFIGURATION
#─────────────────────────────────────────────────────────────
SEMAPHORE_API_KEY=your_semaphore_api_key
SEMAPHORE_SENDER_NAME=HIMS

#─────────────────────────────────────────────────────────────
# TWILIO CONFIGURATION
#─────────────────────────────────────────────────────────────
TWILIO_ACCOUNT_SID=your_twilio_account_sid
TWILIO_AUTH_TOKEN=your_twilio_auth_token
TWILIO_FROM_NUMBER=+639XXXXXXXXX

#─────────────────────────────────────────────────────────────
# MOVIDER CONFIGURATION
#─────────────────────────────────────────────────────────────
MOVIDER_API_KEY=your_movider_api_key
MOVIDER_API_SECRET=your_movider_api_secret

#─────────────────────────────────────────────────────────────
# M360 CONFIGURATION
#─────────────────────────────────────────────────────────────
M360_USERNAME=your_m360_username
M360_PASSWORD=your_m360_password
M360_SHORTCODE=your_shortcode

#─────────────────────────────────────────────────────────────
# SECURITY FEATURES (Optional - DISABLED by default)
#─────────────────────────────────────────────────────────────

# Rate Limiting
SMS_RATE_LIMIT_ENABLED=false  # Set to true to enable
SMS_RATE_LIMIT_PER_HOUR=5     # Max SMS per phone per hour

# Auto-Blacklist
SMS_BLACKLIST_ENABLED=false    # Set to true to enable
SMS_BLACKLIST_THRESHOLD=10     # Block after X failures
SMS_BLACKLIST_PERIOD_DAYS=30   # Within X days

#─────────────────────────────────────────────────────────────
# QUEUE CONFIGURATION
#─────────────────────────────────────────────────────────────
QUEUE_CONNECTION=database      # or redis, sqs, etc.
```

### Config File: `config/services.php`

```php
return [
    // ... other services

    'sms' => [
        'default_provider' => env('SMS_PROVIDER', 'semaphore'),
        'rate_limit_enabled' => env('SMS_RATE_LIMIT_ENABLED', false),
        'rate_limit_per_hour' => env('SMS_RATE_LIMIT_PER_HOUR', 5),
        'blacklist_enabled' => env('SMS_BLACKLIST_ENABLED', false),
        'blacklist_threshold' => env('SMS_BLACKLIST_THRESHOLD', 10),
        'blacklist_period_days' => env('SMS_BLACKLIST_PERIOD_DAYS', 30),
    ],

    'semaphore' => [
        'api_key' => env('SEMAPHORE_API_KEY'),
        'sender_name' => env('SEMAPHORE_SENDER_NAME', 'HIMS'),
    ],

    'twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'from_number' => env('TWILIO_FROM_NUMBER'),
    ],

    'movider' => [
        'api_key' => env('MOVIDER_API_KEY'),
        'api_secret' => env('MOVIDER_API_SECRET'),
    ],

    'm360' => [
        'username' => env('M360_USERNAME'),
        'password' => env('M360_PASSWORD'),
        'shortcode' => env('M360_SHORTCODE'),
    ],
];
```

---

## 23. Troubleshooting

### Problem: SMS Not Sending

**Symptoms:**
- SMS queued but never sent
- No logs in `sms_logs` table

**Solutions:**

1. **Check Queue Worker:**
   ```bash
   # Is it running?
   ps aux | grep queue:work

   # Start it
   php artisan queue:work
   ```

2. **Check Failed Jobs:**
   ```bash
   php artisan queue:failed

   # Retry failed
   php artisan queue:retry all
   ```

3. **Check Logs:**
   ```bash
   tail -f storage/logs/laravel.log | grep SMS
   ```

4. **Check Provider Config:**
   ```bash
   php artisan tinker
   ```
   ```php
   config('services.sms.default_provider');
   config('services.semaphore.api_key');
   ```

### Problem: Wrong Phone Format

**Symptoms:**
- Provider rejects phone number
- SMS fails with "invalid number"

**Solutions:**

1. **Check Current Provider:**
   ```bash
   curl http://your-domain.com/api/sms/provider
   ```

2. **Test Formatting:**
   ```bash
   curl -X POST http://your-domain.com/api/sms/format-phone \
     -d '{"phone":"09123456789"}'
   ```

3. **Verify Provider Requirements:**
   - Semaphore: `+639XXXXXXXXX`
   - Movider: `09XXXXXXXXX`
   - M360: `639XXXXXXXXX`

### Problem: High Failure Rate

**Symptoms:**
- Many SMS showing as "failed"
- Success rate < 90%

**Solutions:**

1. **Check Statistics:**
   ```bash
   curl http://your-domain.com/api/sms/stats?days=7
   ```

2. **Find Problematic Numbers:**
   ```php
   $bad = SmsLog::getProblematicNumbers(20);
   ```

3. **Check Provider Status:**
   - Check provider dashboard
   - Verify credits/balance
   - Check for API rate limits

4. **Review Error Messages:**
   ```sql
   SELECT error_message, COUNT(*) as count
   FROM sms_logs
   WHERE status = 'failed'
   GROUP BY error_message
   ORDER BY count DESC;
   ```

### Problem: Queue Worker Crashes

**Symptoms:**
- Worker stops processing
- Jobs pile up

**Solutions:**

1. **Check Memory:**
   ```bash
   # Worker uses too much memory
   php artisan queue:work --memory=512
   ```

2. **Use Supervisor:**
   - Auto-restart on crash
   - See Queue System section

3. **Check for Errors:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

### Problem: Duplicate SMS

**Symptoms:**
- Same SMS sent multiple times

**Solutions:**

1. **Check Job Dispatching:**
   ```php
   // Make sure you're not dispatching twice
   SendSmsJob::dispatch($phone, $message); // Once only
   ```

2. **Check Queue Configuration:**
   - Ensure only one worker per queue
   - Check for duplicate job processors

### Problem: API Not Working

**Symptoms:**
- API returns 404
- Routes not found

**Solutions:**

1. **Clear Route Cache:**
   ```bash
   php artisan route:clear
   php artisan route:cache
   ```

2. **Check Routes:**
   ```bash
   php artisan route:list | grep sms
   ```

3. **Verify Controller:**
   ```bash
   php artisan tinker
   ```
   ```php
   app(\App\Http\Controllers\Api\SmsTestController::class);
   ```

---

## 24. Best Practices

### 1. Always Use Queue in Production

```php
// ✅ Good - Non-blocking
SendSmsJob::dispatch($phone, $message);

// ❌ Bad - Blocks execution
app(SmsService::class)->sendSms($phone, $message);
```

### 2. Always Track Context

```php
// ✅ Good - Trackable
SendSmsJob::dispatch($phone, $message, 'FMR', $userId, auth()->id());

// ❌ Bad - No context
SendSmsJob::dispatch($phone, $message);
```

### 3. Monitor SMS Health

```php
// Daily check
$rate = SmsLog::getSuccessRate(7);

if ($rate < 90) {
    // Alert admin
    Mail::to('admin@example.com')->send(new LowSuccessRateAlert($rate));
}
```

### 4. Handle Errors Gracefully

```php
try {
    SendSmsJob::dispatch($phone, $message);
    return back()->with('success', 'SMS queued');
} catch (\Exception $e) {
    Log::error('SMS dispatch failed: ' . $e->getMessage());
    return back()->with('error', 'Failed to send SMS');
}
```

### 5. Validate Phone Numbers

```php
$validator = Validator::make($request->all(), [
    'phone' => 'required|regex:/^(09|\+639|639|9)[0-9]{9}$/',
]);

if ($validator->fails()) {
    return back()->withErrors($validator);
}
```

### 6. Use Meaningful Messages

```php
// ✅ Good - Clear and actionable
$message = "FMR Reminder: Your Cash Advance (DV #12345) amounting to ₱5,000 is due for liquidation by Jan 30.";

// ❌ Bad - Vague
$message = "Reminder: Please act on your pending item.";
```

### 7. Test Before Production

```bash
# Test phone formatting
curl -X POST /api/sms/format-phone -d '{"phone":"09123456789"}'

# Test direct send
curl -X POST /api/sms/test-direct -d '{"phone":"09123456789","message":"Test"}'

# Check provider
curl /api/sms/provider
```

### 8. Keep Messages Concise

```php
// SMS is limited (160 chars per segment)
// ✅ Good - 1 segment
$message = "Your OTP is 123456. Valid for 5 minutes.";

// ❌ Bad - Multiple segments (costs more)
$message = "Dear valued customer, we are pleased to inform you that your one-time password for authentication purposes is 123456...";
```

### 9. Log Important Events

```php
Log::info('SMS sent to user', [
    'user_id' => $userId,
    'phone' => substr($phone, 0, 3) . 'XXX',
    'context' => 'FMR',
]);
```

### 10. Secure in Production

```php
// routes/api.php
Route::middleware(['auth:sanctum', 'throttle:60,1'])->prefix('sms')->group(function () {
    Route::post('/send', [SmsTestController::class, 'send']);
    // ...
});
```

---

## 25. Production Deployment

### Pre-Deployment Checklist

- [ ] Environment variables configured
- [ ] Provider API key tested
- [ ] Database migration run
- [ ] Queue worker configured (Supervisor)
- [ ] Error handling tested
- [ ] Phone formatting tested
- [ ] API authentication enabled
- [ ] Rate limiting configured (if needed)
- [ ] Monitoring/alerts set up
- [ ] Backup provider configured (optional)
- [ ] Documentation reviewed
- [ ] Test SMS sent successfully

### Supervisor Configuration

Create `/etc/supervisor/conf.d/queue-worker.conf`:

```ini
[program:queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --memory=512
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/worker.log
stopwaitsecs=3600
startsecs=0
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start queue-worker:*
sudo supervisorctl status
```

### Monitoring

**1. Set Up Alerts:**
```php
// In app/Console/Kernel.php
$schedule->call(function () {
    $rate = SmsLog::getSuccessRate(24);  // Last 24 hours

    if ($rate < 90) {
        Mail::to('admin@example.com')->send(
            new SmsHealthAlert($rate)
        );
    }
})->hourly();
```

**2. Log Monitoring:**
```bash
# Set up log rotation
sudo nano /etc/logrotate.d/laravel

/var/www/html/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    notifempty
    create 0640 www-data www-data
}
```

**3. Queue Monitoring:**
```bash
# Check queue size
php artisan queue:monitor

# Alert if jobs stuck
php artisan queue:retry all
```

### Performance Optimization

**1. Use Redis Queue:**
```env
QUEUE_CONNECTION=redis
```

**2. Multiple Workers:**
```ini
# In supervisor config
numprocs=4  # 4 workers
```

**3. Index Database:**
```sql
-- Already created in migration
CREATE INDEX idx_phone ON sms_logs(phone_number);
CREATE INDEX idx_status ON sms_logs(status);
CREATE INDEX idx_created ON sms_logs(created_at);
```

**4. Cache Statistics:**
```php
$stats = Cache::remember('sms_stats_daily', 3600, function () {
    return SmsLog::getStatistics();
});
```

### Security Hardening

**1. Enable Authentication:**
```php
// routes/api.php
Route::middleware('auth:sanctum')->prefix('sms')->group(function () {
    // All SMS routes
});
```

**2. Rate Limit API:**
```php
Route::middleware('throttle:60,1')->prefix('sms')->group(function () {
    // 60 requests per minute
});
```

**3. Validate Input:**
```php
// Already implemented in controller
$validator = Validator::make($request->all(), [
    'phone' => 'required|string',
    'message' => 'required|string|max:500',
]);
```

**4. Encrypt Sensitive Data:**
```php
// If storing sensitive SMS content
protected $casts = [
    'message' => 'encrypted',
];
```

### Backup Strategy

**1. Database Backup:**
```bash
# Backup sms_logs table
mysqldump -u root -p database_name sms_logs > sms_logs_backup.sql
```

**2. Failed Jobs Backup:**
```bash
# Export failed jobs
php artisan queue:failed > failed_jobs_backup.txt
```

**3. Configuration Backup:**
```bash
# Backup .env and config
cp .env .env.backup
cp config/services.php config/services.php.backup
```

---

## 📚 Documentation Files Summary

You have **4 comprehensive documentation files**:

1. **SMS_COMPLETE_GUIDE.md** (This file) - Everything in one place
2. **SMS_SYSTEM_DOCUMENTATION.md** - Detailed system documentation
3. **SMS_API_DOCUMENTATION.md** - API reference and examples
4. **SMS_QUICK_REFERENCE.md** - Quick commands cheat sheet
5. **SMS_PROVIDER_FORMATS.md** - Phone format guide

---

## 🎯 Quick Reference Card

### Send SMS
```php
SendSmsJob::dispatch($phone, $message, $context, $userId, $senderId);
```

### Switch Provider
```env
SMS_PROVIDER=twilio  # Change in .env only
```

### Add Provider
1. Create provider class
2. Register in SmsService
3. Add config
4. Use it!

### Check Status
```bash
curl /api/sms/logs?limit=10
curl /api/sms/stats?days=7
```

### Troubleshoot
```bash
php artisan queue:work
php artisan queue:failed
tail -f storage/logs/laravel.log
```

---

## ✅ System Capabilities Summary

### ✅ Can Do
- Send SMS to any Philippine number
- Use any SMS provider (Semaphore, Twilio, etc.)
- Switch providers instantly
- Track every SMS in database
- Get analytics and reports
- Handle failures automatically
- Retry failed SMS (3 attempts)
- Format phone numbers automatically
- Rate limit SMS (optional)
- Blacklist bad numbers (optional)
- Test via API
- Bulk send SMS
- Schedule SMS
- Track context and users
- Monitor success rates
- Find problematic numbers
- Export logs
- Integrate with any app

### 🚀 Production Ready
- Queue integration ✅
- Error handling ✅
- Retry logic ✅
- Database logging ✅
- Analytics ✅
- API testing ✅
- Documentation ✅
- Security features ✅
- Multi-provider ✅
- Scalable ✅

---

**You now have EVERYTHING you need to use, manage, and maintain the SMS system!** 📱✨

*Complete Guide v1.0 | Last Updated: January 27, 2025*
