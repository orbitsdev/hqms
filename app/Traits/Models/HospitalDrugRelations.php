<?php

namespace App\Traits\Models;

use App\Models\BillingItem;
use App\Models\Prescription;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HospitalDrugRelations
{
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function billingItems(): HasMany
    {
        return $this->hasMany(BillingItem::class);
    }
}
