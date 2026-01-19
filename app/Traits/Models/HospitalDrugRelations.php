<?php
namespace App\Traits\Models;

use App\Models\BillingItem;
use App\Models\Prescription;

trait HospitalDrugRelations
{
    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    public function billingItems()
    {
        return $this->hasMany(BillingItem::class);
    }
}
