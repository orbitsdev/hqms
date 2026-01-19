<?php
namespace App\Traits\Models;

use App\Models\BillingItem;
use App\Models\MedicalRecord;
use App\Models\User;

trait BillingTransactionRelations
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function billingItems()
    {
        return $this->hasMany(BillingItem::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function discountApprovedBy()
    {
        return $this->belongsTo(User::class, 'discount_approved_by');
    }

    public function calculateTotals(): void
    {
        $this->subtotal = $this->billingItems->sum('total_price');
        $this->total_amount = $this->subtotal + $this->emergency_fee - $this->discount_amount;
        $this->balance = $this->total_amount - $this->amount_paid;
        $this->save();
    }

    public static function shouldApplyEmergencyFee($dateTime = null): bool
    {
        $dateTime = $dateTime ?? now();

        return $dateTime->hour >= 17 ||
               $dateTime->isSunday();
    }
}
