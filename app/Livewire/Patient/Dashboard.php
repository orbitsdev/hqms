<?php

namespace App\Livewire\Patient;

use Illuminate\View\View;
use Livewire\Component;

class Dashboard extends Component
{
    public function render(): View
    {
        return view('livewire.patient.dashboard')
            ->layout('layouts.app');
    }
}
