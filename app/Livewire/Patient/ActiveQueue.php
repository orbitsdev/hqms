<?php

namespace App\Livewire\Patient;

use Illuminate\View\View;
use Livewire\Component;

class ActiveQueue extends Component
{
    public function render(): View
    {
        return view('livewire.patient.active-queue')
            ->layout('layouts.app');
    }
}
