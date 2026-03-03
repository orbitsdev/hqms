<?php

namespace App\Livewire\Display;

use App\Models\ConsultationType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class QueueDisplaySelector extends Component
{
    #[Computed]
    public function consultationTypes(): Collection
    {
        return ConsultationType::query()
            ->where('is_active', true)
            ->withCount([
                'queues as waiting_count' => fn ($q) => $q->today()->waiting(),
                'queues as serving_count' => fn ($q) => $q->today()->serving(),
            ])
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function totalWaiting(): int
    {
        return $this->consultationTypes->sum('waiting_count');
    }

    #[Computed]
    public function totalServing(): int
    {
        return $this->consultationTypes->sum('serving_count');
    }

    public function render(): View
    {
        return view('livewire.display.queue-display-selector', [
            'consultationTypes' => $this->consultationTypes,
            'totalWaiting' => $this->totalWaiting,
            'totalServing' => $this->totalServing,
        ])->layout('layouts.app');
    }
}
