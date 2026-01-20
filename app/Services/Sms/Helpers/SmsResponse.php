<?php

namespace App\Services\Sms\Helpers;

class SmsResponse
{
    /**
     * Create a success response.
     *
     * @param  array<string, mixed>|null  $response
     * @return array{message_id: string|null, formatted_number: string, error: null, response: array<string, mixed>|null}
     */
    public static function success(?string $messageId, string $formattedNumber, ?array $response = null): array
    {
        return [
            'message_id' => $messageId,
            'formatted_number' => $formattedNumber,
            'error' => null,
            'response' => $response,
        ];
    }

    /**
     * Create an error response.
     *
     * @param  array<string, mixed>|null  $response
     * @return array{message_id: null, formatted_number: string, error: string, response: array<string, mixed>|null}
     */
    public static function error(string $error, string $formattedNumber, ?array $response = null): array
    {
        return [
            'message_id' => null,
            'formatted_number' => $formattedNumber,
            'error' => $error,
            'response' => $response,
        ];
    }
}
