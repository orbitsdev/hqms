<?php

namespace App\Services\Sms\Contracts;

interface SmsProviderInterface
{
    /**
     * Send an SMS message.
     *
     * @return array{message_id: string|null, formatted_number: string, error: string|null, response: array<string, mixed>|null}
     */
    public function send(string $number, string $message): array;

    /**
     * Format phone number for this provider.
     */
    public function formatPhoneNumber(string $phone): string;

    /**
     * Get the provider name.
     */
    public function getName(): string;

    /**
     * Check if the provider is configured.
     */
    public function isConfigured(): bool;
}
