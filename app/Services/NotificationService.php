<?php

namespace App\Services;

use App\Enums\NotificationType;
use App\Jobs\SendFirebaseNotificationJob;
use App\Models\Appointment;
use App\Models\Queue;
use App\Models\User;

class NotificationService
{
    /**
     * Notify patient that their appointment has been approved.
     */
    public function appointmentApproved(Appointment $appointment): void
    {
        $patient = $appointment->patient;
        $type = NotificationType::AppointmentApproved;

        $message = sprintf(
            'Your appointment on %s at %s has been approved.',
            $appointment->appointment_date->format('M d, Y'),
            $appointment->appointment_date->format('h:i A')
        );

        $this->dispatch($patient, $type, $message, [
            'appointment_id' => $appointment->id,
        ], $appointment->id, 'appointment');
    }

    /**
     * Notify patient that their appointment has been rejected.
     */
    public function appointmentRejected(Appointment $appointment, ?string $reason = null): void
    {
        $patient = $appointment->patient;
        $type = NotificationType::AppointmentRejected;

        $message = $reason
            ? sprintf('Your appointment request has been rejected. Reason: %s', $reason)
            : 'Your appointment request has been rejected.';

        $this->dispatch($patient, $type, $message, [
            'appointment_id' => $appointment->id,
            'reason' => $reason,
        ], $appointment->id, 'appointment');
    }

    /**
     * Notify patient that their appointment has been cancelled.
     */
    public function appointmentCancelled(Appointment $appointment, ?string $reason = null): void
    {
        $patient = $appointment->patient;
        $type = NotificationType::AppointmentCancelled;

        $message = $reason
            ? sprintf('Your appointment has been cancelled. Reason: %s', $reason)
            : 'Your appointment has been cancelled.';

        $this->dispatch($patient, $type, $message, [
            'appointment_id' => $appointment->id,
            'reason' => $reason,
        ], $appointment->id, 'appointment');
    }

    /**
     * Send appointment reminder to patient.
     */
    public function appointmentReminder(Appointment $appointment): void
    {
        $patient = $appointment->patient;
        $type = NotificationType::AppointmentReminder;

        $message = sprintf(
            'Reminder: You have an appointment on %s at %s.',
            $appointment->appointment_date->format('M d, Y'),
            $appointment->appointment_date->format('h:i A')
        );

        $this->dispatch($patient, $type, $message, [
            'appointment_id' => $appointment->id,
        ], $appointment->id, 'appointment');
    }

    /**
     * Notify patient when their queue turn is approaching.
     */
    public function queueNearby(Queue $queue, int $positionsAway): void
    {
        $patient = $queue->patient;
        $type = NotificationType::QueueNearby;

        $message = sprintf(
            'Your turn is approaching! You are %d position(s) away. Please prepare.',
            $positionsAway
        );

        $this->dispatch($patient, $type, $message, [
            'queue_id' => $queue->id,
            'queue_number' => $queue->queue_number,
            'positions_away' => $positionsAway,
        ], $queue->id, 'queue');
    }

    /**
     * Notify patient when they are being called.
     */
    public function queueCalled(Queue $queue): void
    {
        $patient = $queue->patient;
        $type = NotificationType::QueueCalled;

        $roomInfo = $queue->doctor?->personalInformation
            ? sprintf(' with Dr. %s', $queue->doctor->personalInformation->full_name)
            : '';

        $message = sprintf(
            'Queue #%s - Please proceed to the consultation room%s.',
            $queue->queue_number,
            $roomInfo
        );

        $this->dispatch($patient, $type, $message, [
            'queue_id' => $queue->id,
            'queue_number' => $queue->queue_number,
        ], $queue->id, 'queue');
    }

    /**
     * Notify patient when their queue is skipped.
     */
    public function queueSkipped(Queue $queue, ?string $reason = null): void
    {
        $patient = $queue->patient;
        $type = NotificationType::QueueSkipped;

        $message = $reason
            ? sprintf('Your queue #%s has been skipped. Reason: %s', $queue->queue_number, $reason)
            : sprintf('Your queue #%s has been skipped. Please check with the front desk.', $queue->queue_number);

        $this->dispatch($patient, $type, $message, [
            'queue_id' => $queue->id,
            'queue_number' => $queue->queue_number,
            'reason' => $reason,
        ], $queue->id, 'queue');
    }

    /**
     * Notify patient when consultation is complete.
     */
    public function queueCompleted(Queue $queue): void
    {
        $patient = $queue->patient;
        $type = NotificationType::QueueCompleted;

        $message = sprintf(
            'Your consultation (Queue #%s) has been completed. Thank you for visiting!',
            $queue->queue_number
        );

        $this->dispatch($patient, $type, $message, [
            'queue_id' => $queue->id,
            'queue_number' => $queue->queue_number,
        ], $queue->id, 'queue');
    }

    /**
     * Send a system announcement to a user.
     */
    public function systemAnnouncement(User $user, string $title, string $message): void
    {
        $type = NotificationType::SystemAnnouncement;

        SendFirebaseNotificationJob::dispatch(
            $user,
            $type->value,
            ['title' => $title, 'body' => $message],
            [],
            null,
            null
        );
    }

    /**
     * Dispatch a notification job.
     *
     * @param  array<string, mixed>  $data
     */
    protected function dispatch(
        User $user,
        NotificationType $type,
        string $message,
        array $data = [],
        ?int $referenceId = null,
        ?string $referenceType = null
    ): void {
        SendFirebaseNotificationJob::dispatch(
            $user,
            $type->value,
            ['title' => $type->title(), 'body' => $message],
            $data,
            $referenceId,
            $referenceType
        );
    }
}
