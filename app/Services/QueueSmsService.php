<?php

namespace App\Services;

use App\Jobs\SendSmsJob;
use App\Models\Queue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class QueueSmsService
{
    /**
     * Number of positions before being called to send "almost your turn" SMS.
     */
    protected int $nearQueueThreshold;

    /**
     * Whether SMS notifications are enabled.
     */
    protected bool $enabled;

    public function __construct()
    {
        $this->nearQueueThreshold = (int) config('services.sms.queue_near_threshold', 3);
        $this->enabled = config('services.sms.queue_notifications_enabled', false);
    }

    /**
     * Send SMS when patient is being called.
     */
    public function notifyPatientCalled(Queue $queue): void
    {
        if (! $this->enabled) {
            return;
        }

        $phone = $this->getPatientPhone($queue);

        if (! $phone) {
            Log::info('Queue SMS: No phone number for called patient', ['queue_id' => $queue->id]);

            return;
        }

        $patientName = $queue->appointment?->patient_first_name ?? 'Patient';
        $queueNumber = $queue->formatted_number;
        $consultationType = $queue->consultationType?->name ?? 'Consultation';

        $message = "Hi {$patientName}! Your queue number {$queueNumber} is now being CALLED. Please proceed immediately to the {$consultationType} nurse station. - Guardiano Hospital";

        $this->dispatchSms($phone, $message, 'queue.called', $queue);
    }

    /**
     * Send SMS when patient is near in queue.
     */
    public function notifyPatientNearQueue(Queue $queue, int $position): void
    {
        if (! $this->enabled) {
            return;
        }

        $phone = $this->getPatientPhone($queue);

        if (! $phone) {
            return;
        }

        $patientName = $queue->appointment?->patient_first_name ?? 'Patient';
        $queueNumber = $queue->formatted_number;
        $estimatedMinutes = ($position - 1) * 10; // Rough estimate: 10 min per patient

        $message = "Hi {$patientName}! Your queue {$queueNumber} is almost up - you are #{$position} in line (~{$estimatedMinutes} min). Please stay nearby. - Guardiano Hospital";

        $this->dispatchSms($phone, $message, 'queue.near', $queue);
    }

    /**
     * Check and notify patients who are near in queue after a queue advances.
     * Call this after a patient completes their turn.
     */
    public function checkAndNotifyNearQueue(?int $consultationTypeId = null): void
    {
        if (! $this->enabled) {
            return;
        }

        $query = Queue::query()
            ->with(['appointment', 'consultationType'])
            ->today()
            ->where('status', 'waiting')
            ->orderByRaw("CASE priority WHEN 'emergency' THEN 1 WHEN 'urgent' THEN 2 WHEN 'normal' THEN 3 ELSE 4 END")
            ->orderBy('queue_number');

        if ($consultationTypeId) {
            $query->where('consultation_type_id', $consultationTypeId);
        }

        $waitingQueues = $query->limit($this->nearQueueThreshold)->get();

        foreach ($waitingQueues as $index => $queue) {
            $position = $index + 1;

            // Use cache to prevent duplicate SMS (expires at end of day)
            $cacheKey = "queue_sms_near_{$queue->id}";

            if (Cache::has($cacheKey)) {
                continue;
            }

            $this->notifyPatientNearQueue($queue, $position);
            Cache::put($cacheKey, true, now()->endOfDay());
        }
    }

    /**
     * Get patient phone number from queue.
     */
    protected function getPatientPhone(Queue $queue): ?string
    {
        // Try appointment phone first
        if ($queue->appointment?->patient_phone) {
            return $queue->appointment->patient_phone;
        }

        // Try user's personal information
        if ($queue->user?->personalInformation?->contact_number) {
            return $queue->user->personalInformation->contact_number;
        }

        // Try medical record
        if ($queue->medicalRecord?->patient_contact_number) {
            return $queue->medicalRecord->patient_contact_number;
        }

        return null;
    }

    /**
     * Dispatch SMS job.
     */
    protected function dispatchSms(string $phone, string $message, string $context, Queue $queue): void
    {
        try {
            SendSmsJob::dispatch(
                $phone,
                $message,
                $context,
                $queue->user_id,
                null // No specific sender for queue notifications
            );

            Log::info('Queue SMS dispatched', [
                'queue_id' => $queue->id,
                'context' => $context,
                'phone' => substr($phone, 0, 4).'****'.substr($phone, -4),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to dispatch queue SMS', [
                'queue_id' => $queue->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Check if SMS notifications are enabled.
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Get the near queue threshold.
     */
    public function getNearQueueThreshold(): int
    {
        return $this->nearQueueThreshold;
    }
}
