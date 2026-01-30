<?php

namespace App\Traits\Models;

use Illuminate\Database\Eloquent\Builder;

trait DiscountMethods
{
    /**
     * Scope for active discounts.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered discounts.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Calculate discounted amount from a given price.
     */
    public function calculateDiscount(float $amount): float
    {
        return $amount * ($this->percentage / 100);
    }

    /**
     * Calculate final price after discount.
     */
    public function calculateFinalPrice(float $amount): float
    {
        return $amount - $this->calculateDiscount($amount);
    }

    /**
     * Get formatted percentage display.
     */
    public function getFormattedPercentageAttribute(): string
    {
        return rtrim(rtrim(number_format($this->percentage, 2), '0'), '.').'%';
    }
}
