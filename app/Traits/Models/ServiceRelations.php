<?php
namespace App\Traits\Models;

use App\Models\BillingItem;

trait ServiceRelations
{
    public function billingItems()
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
