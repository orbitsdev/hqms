<?php

namespace App\Services;

use App\Models\SmsLog;
use App\Services\Sms\Contracts\SmsProviderInterface;
use App\Services\Sms\Providers\SemaphoreProvider;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected SmsProviderInterface $provider;

    public function __construct()
    {
        $this->provider = $this->resolveProvider();
    }

    /**
     * Send an SMS message.
     *
     * @return array{message_id: string|null, formatted_number: string, error: string|null, response: array<string, mixed>|null}
     */
    public function sendSms(string $number, string $message): array
    {
        if (! $this->provider->isConfigured()) {
            Log::error('SMS Provider not configured', [
                'provider' => $this->provider->getName(),
            ]);

            return [
                'message_id' => null,
                'formatted_number' => $number,
                'error' => 'SMS provider is not configured',
                'response' => null,
            ];
        }

        return $this->provider->send($number, $message);
    }

    /**
     * Format a phone number for the current provider.
     */
    public function formatPhoneNumber(string $phone): string
    {
        return $this->provider->formatPhoneNumber($phone);
    }

    /**
     * Get the current provider name.
     */
    public function getProviderName(): string
    {
        return $this->provider->getName();
    }

    /**
     * Check if the provider is configured.
     */
    public function isConfigured(): bool
    {
        return $this->provider->isConfigured();
    }

    /**
     * Check rate limit for a phone number.
     */
    public function isRateLimited(string $phone): bool
    {
        if (! config('services.sms.rate_limit_enabled', false)) {
            return false;
        }

        $limit = config('services.sms.rate_limit_per_hour', 5);

        $count = SmsLog::where('phone_number', $phone)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        return $count >= $limit;
    }

    /**
     * Check if a phone number is blacklisted.
     */
    public function isBlacklisted(string $phone): bool
    {
        if (! config('services.sms.blacklist_enabled', false)) {
            return false;
        }

        $threshold = config('services.sms.blacklist_threshold', 10);
        $periodDays = config('services.sms.blacklist_period_days', 30);

        $failures = SmsLog::where('phone_number', $phone)
            ->where('status', 'failed')
            ->where('created_at', '>=', now()->subDays($periodDays))
            ->count();

        return $failures >= $threshold;
    }

    /**
     * Resolve the SMS provider based on configuration.
     */
    protected function resolveProvider(): SmsProviderInterface
    {
        $providerName = config('services.sms.default_provider', 'semaphore');

        $providers = [
            'semaphore' => SemaphoreProvider::class,
            // Add more providers here as needed:
            // 'twilio' => TwilioProvider::class,
            // 'movider' => MoviderProvider::class,
            // 'm360' => M360Provider::class,
        ];

        $providerClass = $providers[$providerName] ?? SemaphoreProvider::class;

        return new $providerClass;
    }
}
