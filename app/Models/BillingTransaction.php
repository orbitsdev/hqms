<?php

namespace App\Models;

use App\Traits\Models\BillingTransactionRelations;
use Illuminate\Database\Eloquent\Model;

class BillingTransaction extends Model
{
    use BillingTransactionRelations;

    protected $guarded = ['id'];

    protected $casts = [
        'transaction_date' => 'date',
        'is_emergency' => 'boolean',
        'is_holiday' => 'boolean',
        'is_sunday' => 'boolean',
        'is_after_5pm' => 'boolean',
        'emergency_fee' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'received_in_billing_at' => 'datetime',
        'ended_in_billing_at' => 'datetime',
    ];
}
