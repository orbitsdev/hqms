<?php

namespace App\Jobs;

use App\Models\SmsLog;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array<int>
     */
    public array $backoff = [60, 300, 900];

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 30;

    protected ?SmsLog $smsLog = null;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $number,
        public string $message,
        public ?string $context = null,
        public ?int $userId = null,
        public ?int $senderId = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(SmsService $smsService): void
    {
        // Check rate limiting
        if ($smsService->isRateLimited($this->number)) {
            Log::warning('SMS rate limited', ['number' => $this->number]);
            $this->createLog('failed', null, 'Rate limit exceeded');

            return;
        }

        // Check blacklist
        if ($smsService->isBlacklisted($this->number)) {
            Log::warning('SMS blacklisted', ['number' => $this->number]);
            $this->createLog('failed', null, 'Phone number is blacklisted');

            return;
        }

        // Create pending log
        $this->smsLog = $this->createLog('pending');

        // Send SMS
        $result = $smsService->sendSms($this->number, $this->message);

        // Update log with result
        $this->updateLog($result);

        // If failed, throw exception to trigger retry
        if (! empty($result['error'])) {
            throw new \Exception($result['error']);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?\Throwable $exception): void
    {
        Log::error('SMS Job Failed', [
            'number' => $this->number,
            'message' => $this->message,
            'error' => $exception?->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        // Update log if exists
        if ($this->smsLog) {
            $this->smsLog->update([
                'status' => 'failed',
                'error_message' => $exception?->getMessage(),
                'failed_at' => now(),
                'attempts' => $this->attempts(),
            ]);
        } else {
            // Create failed log if not exists
            $this->createLog('failed', null, $exception?->getMessage());
        }
    }

    /**
     * Create an SMS log entry.
     */
    protected function createLog(string $status, ?string $messageId = null, ?string $error = null): SmsLog
    {
        $smsService = app(SmsService::class);

        return SmsLog::create([
            'phone_number' => $this->number,
            'formatted_phone_number' => $smsService->formatPhoneNumber($this->number),
            'message' => $this->message,
            'status' => $status,
            'message_id' => $messageId,
            'error_message' => $error,
            'context' => $this->context,
            'user_id' => $this->userId,
            'sender_id' => $this->senderId,
            'attempts' => $this->attempts(),
            'sent_at' => $status === 'sent' ? now() : null,
            'failed_at' => $status === 'failed' ? now() : null,
        ]);
    }

    /**
     * Update the SMS log with result.
     *
     * @param  array{message_id: string|null, formatted_number: string, error: string|null, response: array<string, mixed>|null}  $result
     */
    protected function updateLog(array $result): void
    {
        if (! $this->smsLog) {
            return;
        }

        $status = empty($result['error']) ? 'sent' : 'failed';

        $this->smsLog->update([
            'status' => $status,
            'message_id' => $result['message_id'],
            'formatted_phone_number' => $result['formatted_number'],
            'error_message' => $result['error'],
            'api_response' => $result['response'],
            'attempts' => $this->attempts(),
            'sent_at' => $status === 'sent' ? now() : null,
            'failed_at' => $status === 'failed' ? now() : null,
        ]);
    }
}
