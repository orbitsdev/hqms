<?php

namespace App\Livewire\Patient;

use Illuminate\View\View;
use Livewire\Component;

class MedicalRecordShow extends Component
{
    public function render(): View
    {
        return view('livewire.patient.medical-record-show')
            ->layout('layouts.app');
    }
}
