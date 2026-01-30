# SMS Setup Guide

This guide covers configuring SMS notifications for the Hospital Queue Management System.

## Overview

The system supports SMS notifications for:
- **Queue Called**: Notify patient when their queue number is being called
- **Queue Near**: Notify patient when they're almost next in line (configurable threshold)
- **Appointment Reminders**: Notify patient about upcoming appointments (future feature)

## Supported SMS Providers

| Provider | Region | Status |
|----------|--------|--------|
| **Semaphore** | Philippines | Ready |
| Twilio | Global | Stub (needs implementation) |
| Movider | SEA | Stub (needs implementation) |
| M360 | Philippines | Stub (needs implementation) |

## Configuration

### 1. Environment Variables

Add these to your `.env` file:

```env
# SMS Provider Selection
SMS_PROVIDER=semaphore

# Rate Limiting (optional, recommended for production)
SMS_RATE_LIMIT_ENABLED=true
SMS_RATE_LIMIT_PER_HOUR=5

# Blacklist (auto-blocks numbers with repeated failures)
SMS_BLACKLIST_ENABLED=true
SMS_BLACKLIST_THRESHOLD=10
SMS_BLACKLIST_PERIOD_DAYS=30

# Queue Notifications
SMS_QUEUE_NOTIFICATIONS_ENABLED=true
SMS_QUEUE_NEAR_THRESHOLD=3  # Notify when X positions away
```

### 2. Semaphore Configuration (Recommended for Philippines)

```env
SEMAPHORE_API_KEY=your_api_key_here
SEMAPHORE_SENDER_NAME=GUARDIANO
```

**Get API Key:**
1. Register at [semaphore.co](https://semaphore.co)
2. Go to Dashboard > API Keys
3. Create a new API key
4. Set sender name (max 11 characters, no spaces)

**Pricing:** ~₱0.50-1.00 per SMS (check current rates)

### 3. Twilio Configuration (Global)

```env
TWILIO_ACCOUNT_SID=your_account_sid
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_FROM_NUMBER=+1234567890
```

## Queue Worker

SMS is sent via queued jobs. Ensure the queue worker is running:

```bash
# Using Supervisor (recommended)
sudo supervisorctl start hqms-worker:*

# Or manually
php artisan queue:work --queue=default --tries=3
```

## Enabling Queue SMS Notifications

The SMS notifications are commented out by default. To enable:

### 1. Edit `app/Livewire/Nurse/TodayQueue.php`

Find and uncomment the SMS code blocks:

**For "Patient Called" SMS (~line 430):**
```php
// TODO: Uncomment when SMS is configured in production
$cacheKey = "queue_sms_called_{$queue->id}";
if (! Cache::has($cacheKey)) {
    $smsService = app(QueueSmsService::class);
    $smsService->notifyPatientCalled($queue);
    Cache::put($cacheKey, true, now()->endOfDay());
}
```

**For "Near Queue" SMS (~line 1215):**
```php
// TODO: Uncomment when SMS is configured in production
$smsService = app(QueueSmsService::class);
$smsService->notifyPatientNearQueue($waitingQueue, $position);
```

### 2. Set Environment Variable

```env
SMS_QUEUE_NOTIFICATIONS_ENABLED=true
```

## Testing SMS

### 1. Test via Tinker

```bash
php artisan tinker
```

```php
use App\Jobs\SendSmsJob;

// Test SMS
SendSmsJob::dispatch(
    '09171234567',           // Phone number
    'Test message from HQMS', // Message
    'test',                   // Context
    null,                     // User ID
    null                      // Sender ID
);
```

### 2. Check SMS Logs

```php
use App\Models\SmsLog;

// Recent logs
SmsLog::latest()->take(10)->get();

// Failed SMS
SmsLog::failed()->get();

// Statistics
SmsLog::getStatistics();
```

### 3. Via API (if enabled)

```bash
# Send SMS
curl -X POST http://your-domain/api/sms/send-direct \
  -H "Content-Type: application/json" \
  -d '{"number": "09171234567", "message": "Test"}'

# Check stats
curl http://your-domain/api/sms/stats
```

## SMS Message Templates

The system uses these default messages:

**Patient Called:**
```
Hi {name}! Your queue number {number} is now being CALLED.
Please proceed immediately to the {type} nurse station.
- Guardiano Hospital
```

**Near Queue:**
```
Hi {name}! Your queue {number} is almost up - you are #{position} in line (~{minutes} min).
Please stay nearby.
- Guardiano Hospital
```

To customize, edit `app/Services/QueueSmsService.php`.

## Monitoring & Troubleshooting

### View SMS Logs

```sql
-- Recent SMS
SELECT * FROM sms_logs ORDER BY created_at DESC LIMIT 20;

-- Failed SMS
SELECT * FROM sms_logs WHERE status = 'failed';

-- Success rate
SELECT
    COUNT(*) as total,
    SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
FROM sms_logs
WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY);
```

### Common Issues

| Issue | Cause | Solution |
|-------|-------|----------|
| SMS not sending | Queue worker not running | Start Supervisor/queue worker |
| "Rate limited" | Too many SMS to same number | Wait or increase `SMS_RATE_LIMIT_PER_HOUR` |
| "Blacklisted" | Number had too many failures | Clear from `sms_logs` or wait `blacklist_period_days` |
| Invalid number | Wrong format | Ensure Philippine format: `09XXXXXXXXX` or `639XXXXXXXXX` |
| API error | Invalid credentials | Check `SEMAPHORE_API_KEY` |

### Clear Rate Limit Cache

```bash
php artisan cache:clear
```

## Cost Estimation

For a clinic with ~50 patients/day:
- Called notifications: 50 SMS/day
- Near queue (3 threshold): ~150 SMS/day (worst case)
- **Total:** ~200 SMS/day = ~6,000 SMS/month

At ₱0.50/SMS = **₱3,000/month**

**Tips to reduce costs:**
1. Lower `SMS_QUEUE_NEAR_THRESHOLD` to 2
2. Only send "called" SMS, skip "near queue"
3. Enable rate limiting to prevent abuse

## Security Notes

1. **Never commit API keys** - Use `.env` file
2. **Enable rate limiting** - Prevents SMS bombing
3. **Enable blacklisting** - Auto-blocks problematic numbers
4. **Monitor usage** - Check SMS logs regularly

## Files Reference

| File | Purpose |
|------|---------|
| `app/Services/SmsService.php` | Core SMS service |
| `app/Services/QueueSmsService.php` | Queue-specific SMS logic |
| `app/Jobs/SendSmsJob.php` | Queued SMS job |
| `app/Models/SmsLog.php` | SMS logging model |
| `app/Services/Sms/Providers/SemaphoreProvider.php` | Semaphore API integration |
| `config/services.php` | SMS configuration |
