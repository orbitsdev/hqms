<?php

namespace App\Livewire\Cashier;

use App\Models\BillingTransaction;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class TransactionDetails extends Component
{
    #[Locked]
    public int $transactionId;

    public function mount(BillingTransaction $transaction): void
    {
        $this->transactionId = $transaction->id;
    }

    #[Computed]
    public function transaction(): BillingTransaction
    {
        return BillingTransaction::with([
            'billingItems.service',
            'billingItems.hospitalDrug',
            'medicalRecord.consultationType',
            'medicalRecord.doctor',
            'user',
            'processedBy',
        ])->findOrFail($this->transactionId);
    }

    public function printReceipt(): void
    {
        $this->dispatch('print-receipt');
    }

    public function render(): View
    {
        return view('livewire.cashier.transaction-details')
            ->layout('layouts.app');
    }
}
