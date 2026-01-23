<?php

namespace App\Livewire\Patient;

use Illuminate\View\View;
use Livewire\Component;

class Appointments extends Component
{
    public function render(): View
    {
        return view('livewire.patient.appointments')
            ->layout('layouts.app');
    }
}
