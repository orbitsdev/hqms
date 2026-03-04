# Auto-Queue Update — Remove Nurse Approval

**Branch:** `feature/auto-queue-remove-nurse-approval`
**Date:** 2026-03-03

---

## What Changed

### Before (Old Flow)
```
Patient books → pending → Nurse manually approves → approved + Queue created
```

### After (New Flow)
```
Future booking:  Patient books → confirmed → Midnight scheduler → approved + Queue created
Same-day booking: Patient books for today → approved + Queue created immediately
Walk-in:         Nurse registers → approved + Queue created immediately
```

---

## Status Flow

```
confirmed → approved → in_progress → completed
               ↘ cancelled
               ↘ no_show (overnight scheduler)
```

| Status | Meaning |
|--------|---------|
| `confirmed` | Booked, appointment day hasn't arrived yet |
| `approved` | Appointment day, queue exists |
| `in_progress` | Currently being served |
| `completed` | Done |
| `cancelled` | Cancelled by patient or nurse |
| `no_show` | Marked automatically if unserved after appointment day |

---

## Scheduler Tasks (bootstrap/app.php)

| Task | Schedule | What It Does |
|------|----------|-------------|
| `auto-queue-generation` | Daily 00:00 | Creates queue entries for today's `confirmed` appointments, sets status to `approved`, notifies patients |
| `mark-no-shows` | Daily 06:00 | Marks yesterday's unserved `approved` appointments and queues as `no_show` |
| `morning-reminders` | Daily 07:00 | Sends reminder notification with queue number to today's queued patients |

---

## What to Test

### Patient Side

- [ ] **Book future appointment** — status should be `confirmed`, toast shows estimated queue position
- [ ] **Book same-day appointment** — status should be `approved`, queue created immediately, toast shows queue number
- [ ] **Cancel confirmed appointment** — should work
- [ ] **Cancel approved appointment** — should work
- [ ] **Patient dashboard** — shows "Confirmed" count (not "Pending")
- [ ] **Appointment detail** — confirmed appointments show "You'll receive your queue number on the appointment day"

### Nurse Side

- [ ] **Appointments list** — "Confirmed" tab instead of "Pending", no Approve button
- [ ] **Appointment detail** — no Approve modal or button
- [ ] **Walk-in registration** — creates appointment as `approved` + queue immediately, redirects to queue page
- [ ] **Today's queue** — "Unqueued Appointments" section shows confirmed appointments without a queue (fallback)
- [ ] **Queue check-in fallback** — if a confirmed appointment was missed by scheduler, nurse can still queue it manually
- [ ] **Stop serving** — resets patient to `approved` (back to queue)
- [ ] **Nurse dashboard** — shows "Confirmed" count, alerts for unqueued today's appointments

### Doctor Side

- [ ] **My Schedule** — shows both `confirmed` and `approved` appointments

### Scheduler

- [ ] **`php artisan schedule:test`** → select `auto-queue-generation` → DONE
- [ ] **`php artisan schedule:test`** → select `mark-no-shows` → DONE
- [ ] **`php artisan schedule:test`** → select `morning-reminders` → DONE
- [ ] **`php artisan schedule:list`** — all 3 tasks show with correct times
- [ ] **Cron running** — check `grep "health check" storage/logs/laravel.log` shows entries

### Database

- [ ] **Migration ran** — `php artisan migrate` no errors
- [ ] **Old `pending` appointments** converted to `confirmed`
- [ ] **Status columns** are now `string` type (not enum)

---

## Files Changed (27 files)

### New Files
| File | Purpose |
|------|---------|
| `app/Services/QueueNumberService.php` | Shared queue number generation service |
| `tests/Feature/SchedulerTest.php` | 7 tests for scheduler logic |
| `database/migrations/2026_03_03_120727_update_appointment_and_queue_statuses.php` | enum → string, pending → confirmed |

### Modified — Patient
| File | Changes |
|------|---------|
| `app/Livewire/Patient/BookAppointment.php` | Same-day = immediate queue; future = confirmed + predicted position |
| `app/Livewire/Patient/Appointments.php` | Cancel check: `confirmed` or `approved` |
| `app/Livewire/Patient/AppointmentShow.php` | Cancel check: `confirmed` or `approved` |
| `app/Livewire/Patient/Dashboard.php` | `pending` → `confirmed` in stats and queries |
| `resources/views/livewire/patient/appointments.blade.php` | Added `confirmed` status color |
| `resources/views/livewire/patient/appointment-show.blade.php` | Added `confirmed` status message |
| `resources/views/livewire/patient/dashboard.blade.php` | Pending → Confirmed label |

### Modified — Nurse
| File | Changes |
|------|---------|
| `app/Livewire/Nurse/Appointments.php` | Removed approve modal/methods, `pending` → `confirmed` |
| `app/Livewire/Nurse/AppointmentShow.php` | Removed approve modal/methods |
| `app/Livewire/Nurse/TodayQueue.php` | Unqueued fallback, stop serving → `approved` |
| `app/Livewire/Nurse/WalkInRegistration.php` | Immediate queue creation, redirect to queue |
| `app/Livewire/Nurse/Dashboard.php` | `pending` → `confirmed`, unqueued alerts |
| `resources/views/livewire/nurse/appointments.blade.php` | Confirmed tab, removed approve UI |
| `resources/views/livewire/nurse/appointment-show.blade.php` | Removed approve UI |
| `resources/views/livewire/nurse/today-queue.blade.php` | Unqueued appointments label |
| `resources/views/livewire/nurse/dashboard.blade.php` | Confirmed stats |

### Modified — Doctor
| File | Changes |
|------|---------|
| `app/Livewire/Doctor/MySchedule.php` | Shows `confirmed` + `approved` |

### Modified — Other
| File | Changes |
|------|---------|
| `bootstrap/app.php` | 3 new scheduler tasks |
| `database/factories/AppointmentFactory.php` | Default `confirmed`, added state method |

### Modified — Tests
| File | Changes |
|------|---------|
| `tests/Feature/Patient/BookAppointmentTest.php` | Status assertions updated |
| `tests/Feature/Nurse/AppointmentsTest.php` | Removed approve tests, updated statuses |
| `tests/Feature/Nurse/WalkInRegistrationTest.php` | Queue creation check, redirect updated |
| `tests/Feature/Nurse/TodayQueueTest.php` | Stop serving assertion updated |
| `tests/Feature/Nurse/DoctorSchedulesTest.php` | Fixed pre-existing issue |

---

## Server Deployment

```bash
git fetch origin
git checkout feature/auto-queue-remove-nurse-approval
php artisan migrate
php artisan optimize:clear
php artisan queue:restart
```

### Cron (required for scheduler)
```bash
crontab -e
# Add this line:
* * * * * cd /var/www/hqms && php artisan schedule:run >> /dev/null 2>&1
```

### Verify
```bash
php artisan schedule:list          # See all tasks
php artisan schedule:test          # Run a task manually
grep "health check" storage/logs/laravel.log  # Confirm cron is running
```
