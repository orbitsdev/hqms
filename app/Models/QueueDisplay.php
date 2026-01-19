<?php

namespace App\Models;

use App\Traits\Models\QueueDisplayRelations;
use Illuminate\Database\Eloquent\Model;

class QueueDisplay extends Model
{
    use QueueDisplayRelations;

    protected $guarded = ['id'];

    protected $casts = [
        'display_settings' => 'array',
        'last_heartbeat' => 'datetime',
        'is_active' => 'boolean',
    ];
}
