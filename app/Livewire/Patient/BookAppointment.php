<?php

namespace App\Livewire\Patient;

use Illuminate\View\View;
use Livewire\Component;

class BookAppointment extends Component
{
    public function render(): View
    {
        return view('livewire.patient.book-appointment')
            ->layout('layouts.app');
    }
}
