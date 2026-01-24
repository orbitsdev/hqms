<?php
namespace App\Traits\Models;
use App\Models\ConsultationType;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait DoctorScheduleRelations
{
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function consultationType(): BelongsTo
    {
        return $this->belongsTo(ConsultationType::class);
    }
}
