<?php

namespace App\Traits\Models;

use App\Models\BillingItem;
use App\Models\BillingTransaction;
use App\Models\HospitalDrug;
use App\Models\Service;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BillingItemRelations
{
    public function billingTransaction(): BelongsTo
    {
        return $this->belongsTo(BillingTransaction::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function hospitalDrug(): BelongsTo
    {
        return $this->belongsTo(HospitalDrug::class);
    }

    protected static function bootBillingItemRelations(): void
    {
        static::saving(function (BillingItem $item): void {
            $item->total_price = $item->quantity * $item->unit_price;
        });
    }
}
