<?php

namespace App\Models;

use App\Traits\Models\ServiceRelations;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use ServiceRelations;

    protected $guarded = ['id'];

    protected $casts = [
        'base_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
