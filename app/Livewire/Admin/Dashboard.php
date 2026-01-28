<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Dashboard extends Component
{
    #[Computed]
    public function stats(): array
    {
        return [
            'total_users' => User::count(),
            'total_patients' => User::role('patient')->count(),
            'total_doctors' => User::role('doctor')->count(),
            'total_nurses' => User::role('nurse')->count(),
            'total_cashiers' => User::role('cashier')->count(),
            'total_admins' => User::role('admin')->count(),
        ];
    }

    #[Computed]
    public function recentUsers(): \Illuminate\Database\Eloquent\Collection
    {
        return User::with('roles')
            ->latest()
            ->limit(5)
            ->get();
    }

    public function render(): View
    {
        return view('livewire.admin.dashboard')
            ->layout('layouts.app');
    }
}
