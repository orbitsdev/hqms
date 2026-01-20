<?php

namespace App\Enums;

enum NotificationType: string
{
    // Appointment notifications
    case AppointmentApproved = 'appointment_approved';
    case AppointmentRejected = 'appointment_rejected';
    case AppointmentCancelled = 'appointment_cancelled';
    case AppointmentReminder = 'appointment_reminder';
    case AppointmentRescheduled = 'appointment_rescheduled';

    // Queue notifications
    case QueueNearby = 'queue_nearby';
    case QueueCalled = 'queue_called';
    case QueueSkipped = 'queue_skipped';
    case QueueCompleted = 'queue_completed';

    // General notifications
    case SystemAnnouncement = 'system_announcement';
    case AccountUpdate = 'account_update';

    public function title(): string
    {
        return match ($this) {
            self::AppointmentApproved => 'Appointment Approved',
            self::AppointmentRejected => 'Appointment Rejected',
            self::AppointmentCancelled => 'Appointment Cancelled',
            self::AppointmentReminder => 'Appointment Reminder',
            self::AppointmentRescheduled => 'Appointment Rescheduled',
            self::QueueNearby => 'Your Turn is Coming',
            self::QueueCalled => 'You Are Being Called',
            self::QueueSkipped => 'Queue Skipped',
            self::QueueCompleted => 'Consultation Complete',
            self::SystemAnnouncement => 'Announcement',
            self::AccountUpdate => 'Account Update',
        };
    }

    public function defaultMessage(): string
    {
        return match ($this) {
            self::AppointmentApproved => 'Your appointment has been approved.',
            self::AppointmentRejected => 'Your appointment request has been rejected.',
            self::AppointmentCancelled => 'Your appointment has been cancelled.',
            self::AppointmentReminder => 'You have an upcoming appointment.',
            self::AppointmentRescheduled => 'Your appointment has been rescheduled.',
            self::QueueNearby => 'Please prepare, your turn is approaching.',
            self::QueueCalled => 'Please proceed to the consultation room.',
            self::QueueSkipped => 'Your queue number has been skipped.',
            self::QueueCompleted => 'Your consultation has been completed.',
            self::SystemAnnouncement => 'You have a new announcement.',
            self::AccountUpdate => 'Your account has been updated.',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::AppointmentApproved => 'check-circle',
            self::AppointmentRejected => 'x-circle',
            self::AppointmentCancelled => 'x-circle',
            self::AppointmentReminder => 'clock',
            self::AppointmentRescheduled => 'calendar',
            self::QueueNearby => 'bell',
            self::QueueCalled => 'megaphone',
            self::QueueSkipped => 'forward',
            self::QueueCompleted => 'check-circle',
            self::SystemAnnouncement => 'speakerphone',
            self::AccountUpdate => 'user',
        };
    }
}
