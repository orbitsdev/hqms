<?php

namespace App\Livewire\Patient;

use Illuminate\View\View;
use Livewire\Component;

class MedicalRecords extends Component
{
    public function render(): View
    {
        return view('livewire.patient.medical-records')
            ->layout('layouts.app');
    }
}
