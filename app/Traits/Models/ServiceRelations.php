<?php

namespace App\Traits\Models;

use App\Models\BillingItem;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait ServiceRelations
{
    public function billingItems(): HasMany
    {
        return $this->hasMany(BillingItem::class);
    }

    public function getPriceWithEmergency(bool $isEmergency = false): float
    {
        $price = $this->base_price;

        if ($isEmergency) {
            $price += 500;
        }

        return $price;
    }
}
