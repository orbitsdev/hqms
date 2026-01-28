<?php

namespace App\Livewire\Admin;

use App\Models\HospitalDrug;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

class HospitalDrugManagement extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public bool $isEditing = false;

    public ?int $editingId = null;

    #[Validate('required|string|max:255')]
    public string $drugName = '';

    public string $genericName = '';

    public string $dosageForm = '';

    public string $strength = '';

    #[Validate('required|numeric|min:0')]
    public float $unitPrice = 0;

    public int $stockQuantity = 0;

    public bool $isActive = true;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $drug = HospitalDrug::findOrFail($id);

        $this->editingId = $drug->id;
        $this->drugName = $drug->drug_name;
        $this->genericName = $drug->generic_name ?? '';
        $this->dosageForm = $drug->dosage_form ?? '';
        $this->strength = $drug->strength ?? '';
        $this->unitPrice = (float) $drug->unit_price;
        $this->stockQuantity = $drug->stock_quantity ?? 0;
        $this->isActive = $drug->is_active;

        $this->isEditing = true;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->drugName = '';
        $this->genericName = '';
        $this->dosageForm = '';
        $this->strength = '';
        $this->unitPrice = 0;
        $this->stockQuantity = 0;
        $this->isActive = true;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'drug_name' => $this->drugName,
            'generic_name' => $this->genericName ?: null,
            'dosage_form' => $this->dosageForm ?: null,
            'strength' => $this->strength ?: null,
            'unit_price' => $this->unitPrice,
            'stock_quantity' => $this->stockQuantity,
            'is_active' => $this->isActive,
        ];

        if ($this->isEditing && $this->editingId) {
            $drug = HospitalDrug::findOrFail($this->editingId);
            $drug->update($data);
            Toaster::success(__('Drug updated successfully.'));
        } else {
            HospitalDrug::create($data);
            Toaster::success(__('Drug created successfully.'));
        }

        $this->closeModal();
    }

    public function toggleActive(int $id): void
    {
        $drug = HospitalDrug::findOrFail($id);
        $drug->update(['is_active' => ! $drug->is_active]);

        $status = $drug->is_active ? 'activated' : 'deactivated';
        Toaster::success(__("Drug {$status}."));
    }

    public function delete(int $id): void
    {
        $drug = HospitalDrug::findOrFail($id);
        $drug->delete();

        Toaster::success(__('Drug deleted.'));
    }

    public function render(): View
    {
        $drugs = HospitalDrug::query()
            ->when($this->search, fn ($q) => $q
                ->where('drug_name', 'like', "%{$this->search}%")
                ->orWhere('generic_name', 'like', "%{$this->search}%"))
            ->orderBy('drug_name')
            ->paginate(15);

        return view('livewire.admin.hospital-drug-management', [
            'drugs' => $drugs,
        ])->layout('layouts.app');
    }
}
