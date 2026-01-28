<?php

namespace App\Livewire\Cashier;

use App\Models\BillingTransaction;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class PaymentHistory extends Component
{
    use WithPagination;

    public string $search = '';

    public string $dateFilter = 'today';

    public ?string $startDate = null;

    public ?string $endDate = null;

    public string $paymentMethodFilter = '';

    // View transaction modal
    public bool $showTransactionModal = false;

    public ?BillingTransaction $selectedTransaction = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedDateFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPaymentMethodFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function todayTotal(): float
    {
        return BillingTransaction::whereDate('transaction_date', today())
            ->where('payment_status', 'paid')
            ->sum('amount_paid');
    }

    #[Computed]
    public function todayCount(): int
    {
        return BillingTransaction::whereDate('transaction_date', today())
            ->where('payment_status', 'paid')
            ->count();
    }

    public function viewTransaction(int $id): void
    {
        $this->selectedTransaction = BillingTransaction::with([
            'billingItems.service',
            'billingItems.hospitalDrug',
            'medicalRecord.consultationType',
            'medicalRecord.doctor',
            'processedBy',
        ])->find($id);

        if ($this->selectedTransaction) {
            $this->showTransactionModal = true;
        }
    }

    public function closeTransactionModal(): void
    {
        $this->showTransactionModal = false;
        $this->selectedTransaction = null;
    }

    public function printReceipt(int $id): void
    {
        $this->viewTransaction($id);
        $this->dispatch('print-receipt');
    }

    public function render(): View
    {
        $query = BillingTransaction::query()
            ->with(['medicalRecord', 'processedBy'])
            ->where('payment_status', 'paid');

        // Date filter
        if ($this->dateFilter === 'today') {
            $query->whereDate('transaction_date', today());
        } elseif ($this->dateFilter === 'week') {
            $query->whereBetween('transaction_date', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($this->dateFilter === 'month') {
            $query->whereMonth('transaction_date', now()->month)
                ->whereYear('transaction_date', now()->year);
        } elseif ($this->dateFilter === 'custom' && $this->startDate && $this->endDate) {
            $query->whereBetween('transaction_date', [$this->startDate, $this->endDate]);
        }

        // Payment method filter
        if ($this->paymentMethodFilter) {
            $query->where('payment_method', $this->paymentMethodFilter);
        }

        // Search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('transaction_number', 'like', "%{$this->search}%")
                    ->orWhereHas('medicalRecord', function ($mq) {
                        $mq->where('patient_first_name', 'like', "%{$this->search}%")
                            ->orWhere('patient_last_name', 'like', "%{$this->search}%")
                            ->orWhere('record_number', 'like', "%{$this->search}%");
                    });
            });
        }

        $transactions = $query->orderByDesc('created_at')->paginate(15);

        return view('livewire.cashier.payment-history', [
            'transactions' => $transactions,
        ])->layout('layouts.app');
    }
}
