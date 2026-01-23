<?php

namespace App\Livewire\Patient;

use Illuminate\View\View;
use Livewire\Component;

class Profile extends Component
{
    public function render(): View
    {
        return view('livewire.patient.profile')
            ->layout('layouts.app');
    }
}
