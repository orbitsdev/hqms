<?php

namespace App\Services;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FirebaseNotificationService
{
    protected Messaging $messaging;

    public function __construct()
    {
        $this->messaging = app('firebase.messaging');
    }

    /**
     * Send notification to a single device token.
     *
     * @param  array{title: string, body: string, image?: string}  $notification
     * @param  array<string, mixed>  $data
     * @return array{success: bool, message_id?: string, error?: string}
     */
    public function sendToToken(string $token, array $notification, array $data = []): array
    {
        try {
            $message = $this->buildMessage($notification, $data)
                ->withChangedTarget('token', $token);

            $result = $this->messaging->send($message);

            return [
                'success' => true,
                'message_id' => $result,
            ];
        } catch (MessagingException|FirebaseException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send notification to multiple device tokens.
     *
     * @param  array<string>  $tokens
     * @param  array{title: string, body: string, image?: string}  $notification
     * @param  array<string, mixed>  $data
     * @return array{success_count: int, failure_count: int, results: array}
     */
    public function sendToTokens(array $tokens, array $notification, array $data = []): array
    {
        if (empty($tokens)) {
            return [
                'success_count' => 0,
                'failure_count' => 0,
                'results' => [],
            ];
        }

        try {
            $message = $this->buildMessage($notification, $data);
            $report = $this->messaging->sendMulticast($message, $tokens);

            return [
                'success_count' => $report->successes()->count(),
                'failure_count' => $report->failures()->count(),
                'results' => [
                    'successes' => $report->successes()->count(),
                    'failures' => $report->failures()->count(),
                ],
            ];
        } catch (MessagingException|FirebaseException $e) {
            return [
                'success_count' => 0,
                'failure_count' => count($tokens),
                'results' => ['error' => $e->getMessage()],
            ];
        }
    }

    /**
     * Send notification to a topic.
     *
     * @param  array{title: string, body: string, image?: string}  $notification
     * @param  array<string, mixed>  $data
     * @return array{success: bool, message_id?: string, error?: string}
     */
    public function sendToTopic(string $topic, array $notification, array $data = []): array
    {
        try {
            $message = $this->buildMessage($notification, $data)
                ->withChangedTarget('topic', $topic);

            $result = $this->messaging->send($message);

            return [
                'success' => true,
                'message_id' => $result,
            ];
        } catch (MessagingException|FirebaseException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Build a CloudMessage with notification and data payload.
     *
     * @param  array{title: string, body: string, image?: string}  $notificationData
     * @param  array<string, mixed>  $data
     */
    protected function buildMessage(array $notificationData, array $data = []): CloudMessage
    {
        $notification = Notification::create(
            $notificationData['title'],
            $notificationData['body'],
            $notificationData['image'] ?? null
        );

        $message = CloudMessage::new()
            ->withNotification($notification);

        if (! empty($data)) {
            // Ensure all data values are strings (FCM requirement)
            $stringData = array_map(fn ($value) => is_string($value) ? $value : json_encode($value), $data);
            $message = $message->withData($stringData);
        }

        return $message;
    }
}
