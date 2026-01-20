<?php

namespace App\Livewire\Patient;

use App\Models\MedicalRecord;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class MedicalRecords extends Component
{
    use WithPagination;

    public $search = '';
    public $dateFilter = '';

    protected $paginationTheme = 'tailwind';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedDateFilter(): void
    {
        $this->resetPage();
    }

    public function getRecords(): LengthAwarePaginator
    {
        $user = Auth::user();
        
        $query = $user->medicalRecords()
            ->with(['consultationType', 'prescriptions'])
            ->where('is_pre_visit', false)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('diagnosis', 'like', '%' . $this->search . '%')
                      ->orWhere('chief_complaints_initial', 'like', '%' . $this->search . '%')
                      ->orWhere('chief_complaints_updated', 'like', '%' . $this->search . '%')
                      ->orWhere('plan', 'like', '%' . $this->search . '%')
                      ->orWhereHas('consultationType', function ($subQuery) {
                          $subQuery->where('name', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->dateFilter, function ($query) {
                $date = \Carbon\Carbon::parse($this->dateFilter);
                $query->whereDate('created_at', $date);
            });

        return $query->orderBy('created_at', 'desc')
                     ->paginate(10);
    }

    public function render(): View
    {
        return view('livewire.patient.medical-records', [
            'records' => $this->getRecords(),
        ])->layout('layouts.app');
    }
}
