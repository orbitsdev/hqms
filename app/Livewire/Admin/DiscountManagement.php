<?php

namespace App\Livewire\Admin;

use App\Models\Discount;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

class DiscountManagement extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'active';

    public bool $showModal = false;

    public bool $isEditing = false;

    public ?int $editingId = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string|max:50|alpha_dash')]
    public string $code = '';

    #[Validate('required|numeric|min:0|max:100')]
    public float $percentage = 0;

    public string $description = '';

    public bool $isActive = true;

    public int $sortOrder = 0;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedName(): void
    {
        // Auto-generate code from name if not editing
        if (! $this->isEditing && empty($this->code)) {
            $this->code = str($this->name)->slug()->toString();
        }
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $discount = Discount::findOrFail($id);

        $this->editingId = $discount->id;
        $this->name = $discount->name;
        $this->code = $discount->code;
        $this->percentage = (float) $discount->percentage;
        $this->description = $discount->description ?? '';
        $this->isActive = $discount->is_active;
        $this->sortOrder = $discount->sort_order;

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
        $this->name = '';
        $this->code = '';
        $this->percentage = 0;
        $this->description = '';
        $this->isActive = true;
        $this->sortOrder = 0;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        $rules = [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|alpha_dash|unique:discounts,code'.($this->editingId ? ','.$this->editingId : ''),
            'percentage' => 'required|numeric|min:0|max:100',
        ];

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'code' => $this->code,
            'percentage' => $this->percentage,
            'description' => $this->description ?: null,
            'is_active' => $this->isActive,
            'sort_order' => $this->sortOrder,
        ];

        if ($this->isEditing && $this->editingId) {
            $discount = Discount::findOrFail($this->editingId);
            $discount->update($data);
            Toaster::success(__('Discount updated successfully.'));
        } else {
            Discount::create($data);
            Toaster::success(__('Discount created successfully.'));
        }

        $this->closeModal();
    }

    public function toggleActive(int $id): void
    {
        $discount = Discount::findOrFail($id);
        $discount->update(['is_active' => ! $discount->is_active]);

        $status = $discount->is_active ? 'activated' : 'deactivated';
        Toaster::success(__("Discount {$status}."));
    }

    public function delete(int $id): void
    {
        $discount = Discount::findOrFail($id);
        $discount->delete();

        Toaster::success(__('Discount deleted.'));
    }

    public function render(): View
    {
        $discounts = Discount::query()
            ->when($this->statusFilter === 'active', fn ($q) => $q->where('is_active', true))
            ->when($this->statusFilter === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($this->search, fn ($q) => $q
                ->where(function ($query) {
                    $query->where('name', 'like', "%{$this->search}%")
                        ->orWhere('code', 'like', "%{$this->search}%");
                }))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.admin.discount-management', [
            'discounts' => $discounts,
        ])->layout('layouts.app');
    }
}
