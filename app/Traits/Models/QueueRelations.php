<?php
namespace App\Traits\Models;

use App\Models\Appointment;
use App\Models\ConsultationType;
use App\Models\MedicalRecord;
use App\Models\User;

trait QueueRelations
{
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function consultationType()
    {
        return $this->belongsTo(ConsultationType::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function servedBy()
    {
        return $this->belongsTo(User::class, 'served_by');
    }

    public function medicalRecord()
    {
        return $this->hasOne(MedicalRecord::class);
    }

    // Accessors
    public function getFormattedNumberAttribute(): string
    {
        return $this->consultationType->short_name . '-' . $this->queue_number;
    }

    public function getWaitTimeAttribute(): ?int
    {
        if (!$this->serving_started_at) return null;
        return $this->created_at->diffInMinutes($this->serving_started_at);
    }

    public function getServiceTimeAttribute(): ?int
    {
        if (!$this->serving_started_at || !$this->serving_ended_at) return null;
        return $this->serving_started_at->diffInMinutes($this->serving_ended_at);
    }

    // Scopes
    public function scopeToday($query)
    {
        return $query->where('queue_date', today());
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    public function scopeServing($query)
    {
        return $query->where('status', 'serving');
    }
}
