<?php

namespace App\Models;

use App\Traits\Models\QueueRelations;
use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    use QueueRelations;

    protected $guarded = ['id'];

    protected $casts = [
        'queue_date' => 'date',
        'estimated_time' => 'datetime:H:i',
        'called_at' => 'datetime',
        'serving_started_at' => 'datetime',
        'serving_ended_at' => 'datetime',
    ];
}
