<?php

namespace App\Services\Sms\Providers;

use App\Services\Sms\Contracts\SmsProviderInterface;
use App\Services\Sms\Helpers\SmsResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SemaphoreProvider implements SmsProviderInterface
{
    protected string $apiKey;

    protected string $senderName;

    protected string $apiUrl = 'https://api.semaphore.co/api/v4/messages';

    public function __construct()
    {
        $this->apiKey = config('services.semaphore.api_key', '');
        $this->senderName = config('services.semaphore.sender_name', 'HQMS');
    }

    /**
     * Send an SMS message via Semaphore.
     *
     * @return array{message_id: string|null, formatted_number: string, error: string|null, response: array<string, mixed>|null}
     */
    public function send(string $number, string $message): array
    {
        $formattedNumber = $this->formatPhoneNumber($number);

        try {
            $response = Http::timeout(30)->asForm()->post($this->apiUrl, [
                'apikey' => $this->apiKey,
                'number' => $formattedNumber,
                'message' => $message,
                'sendername' => $this->senderName,
            ]);

            $data = $response->json();

            if (! $response->successful()) {
                $errorMessage = $data['message'] ?? $data['error'] ?? 'HTTP '.$response->status();
                Log::error('Semaphore SMS Failed', [
                    'number' => $formattedNumber,
                    'status' => $response->status(),
                    'response' => $data,
                ]);

                return SmsResponse::error($errorMessage, $formattedNumber, $data);
            }

            // Semaphore returns array of messages on success
            if (is_array($data) && isset($data[0])) {
                $messageData = $data[0];

                // Check for errors in response
                if (isset($messageData['status']) && $messageData['status'] === 'failed') {
                    return SmsResponse::error(
                        $messageData['message'] ?? 'Send failed',
                        $formattedNumber,
                        $data
                    );
                }

                return SmsResponse::success(
                    $messageData['message_id'] ?? null,
                    $formattedNumber,
                    $data
                );
            }

            // Handle error response format
            if (isset($data['error']) || isset($data['message'])) {
                return SmsResponse::error(
                    $data['error'] ?? $data['message'] ?? 'Unknown error',
                    $formattedNumber,
                    $data
                );
            }

            return SmsResponse::success(null, $formattedNumber, $data);

        } catch (\Exception $e) {
            Log::error('Semaphore SMS Exception', [
                'number' => $formattedNumber,
                'error' => $e->getMessage(),
            ]);

            return SmsResponse::error($e->getMessage(), $formattedNumber);
        }
    }

    /**
     * Format phone number for Semaphore (requires 09XXXXXXXXX or 639XXXXXXXXX).
     */
    public function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/\D/', '', $phone);

        // Handle different formats
        if (str_starts_with($phone, '09') && strlen($phone) === 11) {
            // Already in 09XXXXXXXXX format
            return $phone;
        }

        if (str_starts_with($phone, '9') && strlen($phone) === 10) {
            // 9XXXXXXXXX -> 09XXXXXXXXX
            return '0'.$phone;
        }

        if (str_starts_with($phone, '639') && strlen($phone) === 12) {
            // 639XXXXXXXXX -> 09XXXXXXXXX
            return '0'.substr($phone, 2);
        }

        if (str_starts_with($phone, '63') && strlen($phone) === 12) {
            // 63XXXXXXXXX -> 09XXXXXXXXX
            return '0'.substr($phone, 2);
        }

        // Default: return as-is with leading 0 if needed
        return str_starts_with($phone, '0') ? $phone : '0'.$phone;
    }

    public function getName(): string
    {
        return 'Semaphore';
    }

    public function isConfigured(): bool
    {
        return ! empty($this->apiKey);
    }
}
