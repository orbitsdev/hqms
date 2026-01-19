<?php
namespace App\Traits\Models;

use App\Models\ConsultationType;
use App\Models\MedicalRecord;
use App\Models\Queue;
use App\Models\User;

trait AppointmentRelations
{
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

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function queue()
    {
        return $this->hasOne(Queue::class);
    }

    public function medicalRecord()
    {
        return $this->hasOne(MedicalRecord::class);
    }

    // Scopes
    public function scopeToday($query)
    {
        return $query->where('appointment_date', today());
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
