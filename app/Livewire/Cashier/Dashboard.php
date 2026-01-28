<?php

namespace App\Livewire\Cashier;

use App\Models\BillingTransaction;
use App\Models\MedicalRecord;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Dashboard extends Component
{
    #[Computed]
    public function stats(): array
    {
        $today = today();

        return [
            'pending_bills' => MedicalRecord::where('status', 'for_billing')->count(),
            'processed_today' => BillingTransaction::whereDate('transaction_date', $today)
                ->where('payment_status', 'paid')
                ->count(),
            'total_collected_today' => BillingTransaction::whereDate('transaction_date', $today)
                ->where('payment_status', 'paid')
                ->sum('amount_paid'),
            'pending_payments' => BillingTransaction::where('payment_status', 'pending')->count(),
        ];
    }

    #[Computed]
    public function pendingBills(): \Illuminate\Database\Eloquent\Collection
    {
        return MedicalRecord::query()
            ->where('status', 'for_billing')
            ->with(['consultationType', 'doctor'])
            ->orderBy('examination_ended_at')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function recentTransactions(): \Illuminate\Database\Eloquent\Collection
    {
        return BillingTransaction::query()
            ->with(['medicalRecord', 'processedBy'])
            ->whereDate('transaction_date', today())
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }

    public function render(): View
    {
        return view('livewire.cashier.dashboard')
            ->layout('layouts.app');
    }
}
