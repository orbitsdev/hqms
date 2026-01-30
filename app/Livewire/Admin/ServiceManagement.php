<?php

namespace App\Livewire\Admin;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Support\Collection;
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

    public ?int $categoryFilter = null;

    public bool $showModal = false;

    public bool $isEditing = false;

    public ?int $editingId = null;

    #[Validate('required|string|max:255')]
    public string $serviceName = '';

    #[Validate('required|exists:service_categories,id')]
    public ?int $serviceCategoryId = null;

    public string $description = '';

    #[Validate('required|numeric|min:0')]
    public float $basePrice = 0;

    public bool $isActive = true;

    public int $displayOrder = 0;

    public function mount(): void
    {
        // Set default category to first active category
        $defaultCategory = ServiceCategory::active()->ordered()->first();
        $this->serviceCategoryId = $defaultCategory?->id;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function categories(): Collection
    {
        return ServiceCategory::active()->ordered()->get();
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
        $this->serviceCategoryId = $service->service_category_id;
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
        $defaultCategory = ServiceCategory::active()->ordered()->first();
        $this->serviceCategoryId = $defaultCategory?->id;
        $this->description = '';
        $this->basePrice = 0;
        $this->isActive = true;
        $this->displayOrder = 0;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        $this->validate();

        // Get category code for backward compatibility
        $category = ServiceCategory::find($this->serviceCategoryId);

        $data = [
            'service_name' => $this->serviceName,
            'service_category_id' => $this->serviceCategoryId,
            'category' => $category?->code ?? 'other',
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
            ->with('serviceCategory')
            ->when($this->search, fn ($q) => $q
                ->where('service_name', 'like', "%{$this->search}%"))
            ->when($this->categoryFilter, fn ($q) => $q
                ->where('service_category_id', $this->categoryFilter))
            ->orderBy('service_category_id')
            ->orderBy('display_order')
            ->orderBy('service_name')
            ->paginate(15);

        return view('livewire.admin.service-management', [
            'services' => $services,
        ])->layout('layouts.app');
    }
}
