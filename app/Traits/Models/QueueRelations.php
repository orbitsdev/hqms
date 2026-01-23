<?php

namespace App\Traits\Models;

use App\Models\Appointment;
use App\Models\ConsultationType;
use App\Models\MedicalRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait QueueRelations
{
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function consultationType(): BelongsTo
    {
        return $this->belongsTo(ConsultationType::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function servedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'served_by');
    }

    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class);
    }

    public function getFormattedNumberAttribute(): string
    {
        return $this->consultationType->short_name . '-' . $this->queue_number;
    }

    public function getWaitTimeAttribute(): ?int
    {
        if (!$this->serving_started_at) {
            return null;
        }

        return $this->created_at->diffInMinutes($this->serving_started_at);
    }

    public function getServiceTimeAttribute(): ?int
    {
        if (!$this->serving_started_at || !$this->serving_ended_at) {
            return null;
        }

        return $this->serving_started_at->diffInMinutes($this->serving_ended_at);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->where('queue_date', today());
    }

    public function scopeWaiting(Builder $query): Builder
    {
        return $query->where('status', 'waiting');
    }

    public function scopeServing(Builder $query): Builder
    {
        return $query->where('status', 'serving');
    }
}
