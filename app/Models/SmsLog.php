<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class SmsLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'phone_number',
        'formatted_phone_number',
        'message',
        'status',
        'message_id',
        'attempts',
        'error_message',
        'api_response',
        'sent_at',
        'failed_at',
        'context',
        'user_id',
        'sender_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'api_response' => 'array',
            'sent_at' => 'datetime',
            'failed_at' => 'datetime',
            'attempts' => 'integer',
        ];
    }

    /**
     * Get the recipient user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the sender user.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Scope to get sent SMS.
     */
    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope to get failed SMS.
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope to get pending SMS.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to filter by phone number.
     */
    public function scopeByPhone(Builder $query, string $phone): Builder
    {
        return $query->where('phone_number', $phone);
    }

    /**
     * Scope to filter by context.
     */
    public function scopeByContext(Builder $query, string $context): Builder
    {
        return $query->where('context', $context);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeBetweenDates(Builder $query, string $start, string $end): Builder
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    /**
     * Get success rate for a given number of days.
     */
    public static function getSuccessRate(int $days = 30): float
    {
        $startDate = Carbon::now()->subDays($days);

        $total = static::where('created_at', '>=', $startDate)->count();

        if ($total === 0) {
            return 0.0;
        }

        $sent = static::where('created_at', '>=', $startDate)
            ->where('status', 'sent')
            ->count();

        return round(($sent / $total) * 100, 2);
    }

    /**
     * Get SMS statistics for a date range.
     *
     * @return array{total: int, sent: int, failed: int, pending: int, success_rate: float}
     */
    public static function getStatistics(?string $startDate = null, ?string $endDate = null): array
    {
        $query = static::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $total = $query->count();
        $sent = (clone $query)->where('status', 'sent')->count();
        $failed = (clone $query)->where('status', 'failed')->count();
        $pending = (clone $query)->where('status', 'pending')->count();

        return [
            'total' => $total,
            'sent' => $sent,
            'failed' => $failed,
            'pending' => $pending,
            'success_rate' => $total > 0 ? round(($sent / $total) * 100, 2) : 0.0,
        ];
    }

    /**
     * Get SMS count grouped by context.
     *
     * @return array<string, array{count: int, sent: int, failed: int}>
     */
    public static function getCountByContext(int $days = 30): array
    {
        $startDate = Carbon::now()->subDays($days);

        $results = static::where('created_at', '>=', $startDate)
            ->whereNotNull('context')
            ->selectRaw('context, status, COUNT(*) as count')
            ->groupBy('context', 'status')
            ->get();

        $grouped = [];

        foreach ($results as $result) {
            $context = $result->context;
            if (! isset($grouped[$context])) {
                $grouped[$context] = ['count' => 0, 'sent' => 0, 'failed' => 0];
            }

            $grouped[$context]['count'] += $result->count;

            if ($result->status === 'sent') {
                $grouped[$context]['sent'] = $result->count;
            } elseif ($result->status === 'failed') {
                $grouped[$context]['failed'] = $result->count;
            }
        }

        return $grouped;
    }

    /**
     * Get problematic phone numbers with high failure rates.
     *
     * @return array<int, array{phone_number: string, total: int, failures: int, failure_rate: float}>
     */
    public static function getProblematicNumbers(int $limit = 10, int $days = 30): array
    {
        $startDate = Carbon::now()->subDays($days);

        return static::where('created_at', '>=', $startDate)
            ->selectRaw('phone_number, COUNT(*) as total, SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failures')
            ->groupBy('phone_number')
            ->havingRaw('failures > 0')
            ->orderByRaw('failures / total DESC')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'phone_number' => $row->phone_number,
                'total' => (int) $row->total,
                'failures' => (int) $row->failures,
                'failure_rate' => round(($row->failures / $row->total) * 100, 2),
            ])
            ->toArray();
    }

    /**
     * Get daily SMS volume.
     *
     * @return array<int, array{date: string, total: int, sent: int, failed: int}>
     */
    public static function getDailyVolume(int $days = 30): array
    {
        $startDate = Carbon::now()->subDays($days);

        return static::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total, SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent, SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date' => $row->date,
                'total' => (int) $row->total,
                'sent' => (int) $row->sent,
                'failed' => (int) $row->failed,
            ])
            ->toArray();
    }
}
