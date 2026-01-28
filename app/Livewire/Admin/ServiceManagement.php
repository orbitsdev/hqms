<?php

namespace App\Livewire\Admin;

use App\Models\Service;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

class ServiceManagement extends Component
{
    use WithPagination;

    public string $search = '';

    public string $categoryFilter = '';

    public bool $showModal = false;

    public bool $isEditing = false;

    public ?int $editingId = null;

    #[Validate('required|string|max:255')]
    public string $serviceName = '';

    #[Validate('required|in:ultrasound,consultation,procedure,laboratory,other')]
    public string $category = 'consultation';

    public string $description = '';

    #[Validate('required|numeric|min:0')]
    public float $basePrice = 0;

    public bool $isActive = true;

    public int $displayOrder = 0;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function categories(): array
    {
        return [
            'consultation' => 'Consultation / Professional Fee',
            'ultrasound' => 'Ultrasound',
            'procedure' => 'Procedure',
            'laboratory' => 'Laboratory',
            'other' => 'Other',
        ];
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $service = Service::findOrFail($id);

        $this->editingId = $service->id;
        $this->serviceName = $service->service_name;
        $this->category = $service->category;
        $this->description = $service->description ?? '';
        $this->basePrice = (float) $service->base_price;
        $this->isActive = $service->is_active;
        $this->displayOrder = $service->display_order;

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
        $this->serviceName = '';
        $this->category = 'consultation';
        $this->description = '';
        $this->basePrice = 0;
        $this->isActive = true;
        $this->displayOrder = 0;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'service_name' => $this->serviceName,
            'category' => $this->category,
            'description' => $this->description ?: null,
            'base_price' => $this->basePrice,
            'is_active' => $this->isActive,
            'display_order' => $this->displayOrder,
        ];

        if ($this->isEditing && $this->editingId) {
            $service = Service::findOrFail($this->editingId);
            $service->update($data);
            Toaster::success(__('Service updated successfully.'));
        } else {
            Service::create($data);
            Toaster::success(__('Service created successfully.'));
        }

        $this->closeModal();
    }

    public function toggleActive(int $id): void
    {
        $service = Service::findOrFail($id);
        $service->update(['is_active' => ! $service->is_active]);

        $status = $service->is_active ? 'activated' : 'deactivated';
        Toaster::success(__("Service {$status}."));
    }

    public function delete(int $id): void
    {
        $service = Service::findOrFail($id);
        $service->delete();

        Toaster::success(__('Service deleted.'));
    }

    public function render(): View
    {
        $services = Service::query()
            ->when($this->search, fn ($q) => $q
                ->where('service_name', 'like', "%{$this->search}%"))
            ->when($this->categoryFilter, fn ($q) => $q
                ->where('category', $this->categoryFilter))
            ->orderBy('category')
            ->orderBy('display_order')
            ->orderBy('service_name')
            ->paginate(15);

        return view('livewire.admin.service-management', [
            'services' => $services,
        ])->layout('layouts.app');
    }
}
