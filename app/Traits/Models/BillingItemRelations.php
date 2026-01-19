<?php
namespace App\Traits\Models;

use App\Models\BillingTransaction;
use App\Models\HospitalDrug;
use App\Models\Service;

trait BillingItemRelations
{
    public function billingTransaction()
    {
        return $this->belongsTo(BillingTransaction::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function hospitalDrug()
    {
        return $this->belongsTo(HospitalDrug::class);
    }

    protected static function bootBillingItemRelations(): void
    {
        static::saving(function ($item) {
            $item->total_price = $item->quantity * $item->unit_price;
        });
    }
}
