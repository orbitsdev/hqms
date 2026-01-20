<?php

namespace App\Jobs;

use App\Models\NotificationLog;
use App\Models\User;
use App\Models\UserDevice;
use App\Services\FirebaseNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendFirebaseNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    /**
     * @param  array{title: string, body: string, image?: string}  $notification
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public User $user,
        public string $type,
        public array $notification,
        public array $data = [],
        public ?int $referenceId = null,
        public ?string $referenceType = null,
    ) {
        $this->onQueue('notifications');
    }

    public function handle(): void
    {
        $devices = UserDevice::where('user_id', $this->user->id)
            ->where('is_active', true)
            ->whereNotNull('fcm_token')
            ->get();

        if ($devices->isEmpty()) {
            Log::info("No active devices for user {$this->user->id}, skipping FCM notification");

            return;
        }

        // Check if Firebase is configured
        $firebaseConfigured = config('firebase.projects.app.credentials.file')
            && file_exists(config('firebase.projects.app.credentials.file'));

        foreach ($devices as $device) {
            $this->sendToDevice($device, $firebaseConfigured);
        }
    }

    protected function sendToDevice(UserDevice $device, bool $firebaseConfigured): void
    {
        // Create notification log entry
        $notificationLog = NotificationLog::create([
            'user_id' => $this->user->id,
            'user_device_id' => $device->id,
            'type' => $this->type,
            'title' => $this->notification['title'],
            'message' => $this->notification['body'],
            'data' => array_merge($this->data, [
                'type' => $this->type,
                'reference_id' => $this->referenceId,
                'reference_type' => $this->referenceType,
            ]),
            'channel' => 'fcm',
            'status' => 'pending',
        ]);

        // TODO: Enable Firebase sending once configured
        // For now, just log the notification and mark as sent for testing
        if (! $firebaseConfigured) {
            Log::info('Firebase not configured, simulating notification send', [
                'user_id' => $this->user->id,
                'type' => $this->type,
                'title' => $this->notification['title'],
                'device_id' => $device->device_id,
            ]);
            $notificationLog->markAsSent();
            $device->update(['last_used_at' => now()]);

            return;
        }

        // Send the notification via Firebase
        $firebaseService = app(FirebaseNotificationService::class);
        $result = $firebaseService->sendToToken(
            $device->fcm_token,
            $this->notification,
            array_merge($this->data, [
                'type' => $this->type,
                'notification_log_id' => (string) $notificationLog->id,
            ])
        );

        if ($result['success']) {
            $notificationLog->markAsSent();
            $device->update(['last_used_at' => now()]);
        } else {
            $notificationLog->markAsFailed($result['error'] ?? 'Unknown error');

            // If token is invalid, deactivate the device
            if ($this->isInvalidTokenError($result['error'] ?? '')) {
                $device->update([
                    'is_active' => false,
                    'fcm_token' => null,
                ]);
                Log::warning("Deactivated device {$device->id} due to invalid FCM token");
            }
        }
    }

    protected function isInvalidTokenError(string $error): bool
    {
        $invalidTokenErrors = [
            'UNREGISTERED',
            'INVALID_ARGUMENT',
            'NotRegistered',
            'InvalidRegistration',
        ];

        foreach ($invalidTokenErrors as $invalidError) {
            if (str_contains($error, $invalidError)) {
                return true;
            }
        }

        return false;
    }
}
