<?php

namespace App\Models;

use App\Traits\Models\BillingItemRelations;
use Illuminate\Database\Eloquent\Model;

class BillingItem extends Model
{
    use BillingItemRelations;

    protected $guarded = ['id'];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];
}
