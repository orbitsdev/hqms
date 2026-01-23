<?php

namespace App\Livewire\Patient;

use Illuminate\View\View;
use Livewire\Component;

class AppointmentShow extends Component
{
    public function render(): View
    {
        return view('livewire.patient.appointment-show')
            ->layout('layouts.app');
    }
}
