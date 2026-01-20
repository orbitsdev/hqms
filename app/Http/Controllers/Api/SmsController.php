<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Jobs\SendSmsJob;
use App\Models\SmsLog;
use App\Services\SmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class SmsController extends Controller
{
    /**
     * Send SMS via queue.
     *
     * POST /api/sms/send
     */
    public function send(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'message' => 'required|string|max:500',
            'context' => 'nullable|string|max:50',
            'user_id' => 'nullable|integer',
            'sender_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(
                'Validation failed',
                422,
                null,
                $validator->errors()
            );
        }

        try {
            $phone = $request->input('phone');
            $message = $request->input('message');
            $context = $request->input('context', 'API');
            $userId = $request->input('user_id');
            $senderId = $request->input('sender_id');

            SendSmsJob::dispatch(
                $phone,
                $message,
                $context,
                $userId,
                $senderId
            );

            return ApiResponse::success([
                'phone' => $phone,
                'message' => $message,
                'context' => $context,
                'user_id' => $userId,
                'sender_id' => $senderId,
                'status' => 'queued',
                'note' => 'SMS has been queued for sending. Check /api/sms/logs for results.',
            ], 'SMS queued successfully');

        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to queue SMS: '.$e->getMessage(),
                500
            );
        }
    }

    /**
     * Get SMS log by ID.
     *
     * GET /api/sms/log/{id}
     */
    public function getLog(int $id): JsonResponse
    {
        try {
            $log = SmsLog::find($id);

            if (! $log) {
                return ApiResponse::error('SMS log not found', 404);
            }

            return ApiResponse::success([
                'id' => $log->id,
                'phone_number' => $log->phone_number,
                'formatted_phone_number' => $log->formatted_phone_number,
                'message' => $log->message,
                'status' => $log->status,
                'message_id' => $log->message_id,
                'attempts' => $log->attempts,
                'error_message' => $log->error_message,
                'context' => $log->context,
                'user_id' => $log->user_id,
                'sender_id' => $log->sender_id,
                'sent_at' => $log->sent_at,
                'failed_at' => $log->failed_at,
                'created_at' => $log->created_at,
                'updated_at' => $log->updated_at,
            ]);

        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to retrieve log: '.$e->getMessage(),
                500
            );
        }
    }

    /**
     * Get recent SMS logs.
     *
     * GET /api/sms/logs?limit=10&status=sent
     */
    public function getLogs(Request $request): JsonResponse
    {
        try {
            $limit = $request->input('limit', 10);
            $status = $request->input('status');
            $phone = $request->input('phone');
            $context = $request->input('context');

            $query = SmsLog::query()->latest();

            if ($status) {
                $query->where('status', $status);
            }

            if ($phone) {
                $query->where('phone_number', $phone);
            }

            if ($context) {
                $query->where('context', $context);
            }

            $logs = $query->limit($limit)->get();

            return ApiResponse::success([
                'total' => $logs->count(),
                'logs' => $logs->map(fn ($log) => [
                    'id' => $log->id,
                    'phone_number' => $log->phone_number,
                    'message' => $log->message,
                    'status' => $log->status,
                    'context' => $log->context,
                    'attempts' => $log->attempts,
                    'error_message' => $log->error_message,
                    'sent_at' => $log->sent_at,
                    'created_at' => $log->created_at,
                ]),
            ]);

        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to retrieve logs: '.$e->getMessage(),
                500
            );
        }
    }

    /**
     * Get SMS statistics.
     *
     * GET /api/sms/stats?days=30
     */
    public function getStats(Request $request): JsonResponse
    {
        try {
            $days = $request->input('days', 30);

            $stats = [
                'success_rate' => SmsLog::getSuccessRate($days),
                'statistics' => SmsLog::getStatistics(
                    now()->subDays($days)->toDateTimeString(),
                    now()->toDateTimeString()
                ),
                'by_context' => SmsLog::getCountByContext($days),
            ];

            return ApiResponse::success($stats);

        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to retrieve stats: '.$e->getMessage(),
                500
            );
        }
    }

    /**
     * Send SMS directly (bypass queue, for immediate sending).
     *
     * POST /api/sms/send-direct
     */
    public function sendDirect(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'message' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(
                'Validation failed',
                422,
                null,
                $validator->errors()
            );
        }

        try {
            $phone = $request->input('phone');
            $message = $request->input('message');

            $smsService = app(SmsService::class);

            $result = $smsService->sendSms($phone, $message);

            if (! empty($result['error'])) {
                return ApiResponse::error(
                    'SMS failed: '.$result['error'],
                    400,
                    $result
                );
            }

            return ApiResponse::success([
                'phone' => $phone,
                'message' => $message,
                'provider' => $smsService->getProviderName(),
                'status' => 'sent',
                'message_id' => $result['message_id'] ?? null,
                'formatted_number' => $result['formatted_number'] ?? null,
                'response' => $result['response'] ?? null,
            ], 'SMS sent successfully');

        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to send SMS: '.$e->getMessage(),
                500
            );
        }
    }

    /**
     * Get current SMS provider info.
     *
     * GET /api/sms/provider
     */
    public function getProvider(): JsonResponse
    {
        try {
            $smsService = app(SmsService::class);

            return ApiResponse::success([
                'provider' => $smsService->getProviderName(),
                'configured' => $smsService->isConfigured(),
                'config' => [
                    'default_provider' => config('services.sms.default_provider'),
                    'rate_limit_enabled' => config('services.sms.rate_limit_enabled'),
                    'blacklist_enabled' => config('services.sms.blacklist_enabled'),
                ],
            ]);

        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to get provider info: '.$e->getMessage(),
                500
            );
        }
    }

    /**
     * Format phone number for current provider.
     *
     * POST /api/sms/format-phone
     */
    public function formatPhone(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(
                'Validation failed',
                422,
                null,
                $validator->errors()
            );
        }

        try {
            $phone = $request->input('phone');
            $smsService = app(SmsService::class);

            $formatted = $smsService->formatPhoneNumber($phone);

            return ApiResponse::success([
                'original' => $phone,
                'formatted' => $formatted,
                'provider' => $smsService->getProviderName(),
            ]);

        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to format phone: '.$e->getMessage(),
                500
            );
        }
    }
}
