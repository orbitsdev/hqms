<?php

namespace App\Livewire\Admin;

use App\Models\ConsultationType;
use App\Models\PersonalInformation;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;
use Spatie\Permission\Models\Role;

class UserManagement extends Component
{
    use WithPagination;

    public string $search = '';

    public string $roleFilter = '';

    public string $statusFilter = 'active';

    // Create/Edit modal
    public bool $showUserModal = false;

    public ?int $editingUserId = null;

    #[Validate('required|email|max:255')]
    public string $email = '';

    public string $password = '';

    public string $passwordConfirmation = '';

    #[Validate('required|string')]
    public string $role = 'patient';

    // Personal Information
    #[Validate('required|string|max:100')]
    public string $firstName = '';

    #[Validate('required|string|max:100')]
    public string $lastName = '';

    #[Validate('nullable|string|max:100')]
    public string $middleName = '';

    #[Validate('nullable|date')]
    public ?string $dateOfBirth = null;

    #[Validate('nullable|in:male,female')]
    public string $gender = '';

    #[Validate('nullable|string|max:20')]
    public string $phoneNumber = '';

    // Doctor specific
    public array $selectedConsultationTypes = [];

    // Delete confirmation
    public bool $showDeleteModal = false;

    public ?int $deletingUserId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetUserForm();
        $this->showUserModal = true;
    }

    public function openEditModal(int $userId): void
    {
        $user = User::with(['personalInformation', 'consultationTypes', 'roles'])->find($userId);

        if (! $user) {
            return;
        }

        $this->editingUserId = $user->id;
        $this->email = $user->email;
        $this->role = $user->roles->first()?->name ?? 'patient';

        // Get data from user table
        $this->firstName = $user->first_name ?? '';
        $this->lastName = $user->last_name ?? '';
        $this->middleName = $user->middle_name ?? '';
        $this->phoneNumber = $user->phone ?? '';

        // Get additional data from personal information
        if ($user->personalInformation) {
            $this->dateOfBirth = $user->personalInformation->date_of_birth?->format('Y-m-d');
            $this->gender = $user->personalInformation->gender ?? '';
        }

        $this->selectedConsultationTypes = $user->consultationTypes->pluck('id')->toArray();

        $this->showUserModal = true;
    }

    public function closeUserModal(): void
    {
        $this->showUserModal = false;
        $this->resetUserForm();
    }

    public function resetUserForm(): void
    {
        $this->editingUserId = null;
        $this->email = '';
        $this->password = '';
        $this->passwordConfirmation = '';
        $this->role = 'patient';
        $this->firstName = '';
        $this->lastName = '';
        $this->middleName = '';
        $this->dateOfBirth = null;
        $this->gender = '';
        $this->phoneNumber = '';
        $this->selectedConsultationTypes = [];
        $this->resetValidation();
    }

    public function saveUser(): void
    {
        $rules = [
            'email' => 'required|email|max:255|unique:users,email'.($this->editingUserId ? ','.$this->editingUserId : ''),
            'role' => 'required|string|exists:roles,name',
            'firstName' => 'required|string|max:100',
            'lastName' => 'required|string|max:100',
            'middleName' => 'nullable|string|max:100',
            'dateOfBirth' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'phoneNumber' => 'nullable|string|max:20',
        ];

        // Password required for new users
        if (! $this->editingUserId) {
            $rules['password'] = ['required', 'string', Password::min(6)];
            $rules['passwordConfirmation'] = 'required|same:password';
        } elseif ($this->password) {
            $rules['password'] = ['string', Password::min(6)];
            $rules['passwordConfirmation'] = 'required|same:password';
        }

        // Consultation types required for doctors
        if ($this->role === 'doctor') {
            $rules['selectedConsultationTypes'] = 'required|array|min:1';
        }

        $this->validate($rules);

        if ($this->editingUserId) {
            $user = User::find($this->editingUserId);

            $user->update([
                'first_name' => $this->firstName,
                'middle_name' => $this->middleName ?: null,
                'last_name' => $this->lastName,
                'email' => $this->email,
                'phone' => $this->phoneNumber ?: null,
            ]);

            if ($this->password) {
                $user->update(['password' => Hash::make($this->password)]);
            }

            // Update role
            $user->syncRoles([$this->role]);

            Toaster::success(__('User updated successfully.'));
        } else {
            $user = User::create([
                'first_name' => $this->firstName,
                'middle_name' => $this->middleName ?: null,
                'last_name' => $this->lastName,
                'email' => $this->email,
                'phone' => $this->phoneNumber ?: null,
                'password' => Hash::make($this->password),
                'email_verified_at' => now(),
            ]);

            $user->assignRole($this->role);

            Toaster::success(__('User created successfully.'));
        }

        // Update or create personal information
        PersonalInformation::updateOrCreate(
            ['user_id' => $user->id],
            [
                'first_name' => $this->firstName,
                'last_name' => $this->lastName,
                'middle_name' => $this->middleName ?: null,
                'date_of_birth' => $this->dateOfBirth ?: null,
                'gender' => $this->gender ?: null,
                'phone_number' => $this->phoneNumber ?: null,
            ]
        );

        // Sync consultation types for doctors
        if ($this->role === 'doctor') {
            $user->consultationTypes()->sync($this->selectedConsultationTypes);
        } else {
            $user->consultationTypes()->detach();
        }

        $this->closeUserModal();
    }

    public function openDeleteModal(int $userId): void
    {
        $this->deletingUserId = $userId;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingUserId = null;
    }

    public function deleteUser(): void
    {
        if (! $this->deletingUserId) {
            return;
        }

        $user = User::find($this->deletingUserId);

        if (! $user) {
            $this->closeDeleteModal();

            return;
        }

        // Soft delete - just deactivate
        $user->delete();

        Toaster::success(__('User deactivated successfully.'));
        $this->closeDeleteModal();
    }

    public function restoreUser(int $userId): void
    {
        $user = User::withTrashed()->find($userId);

        if ($user) {
            $user->restore();
            Toaster::success(__('User restored successfully.'));
        }
    }

    #[Computed]
    public function roles(): \Illuminate\Database\Eloquent\Collection
    {
        return Role::orderBy('name')->get();
    }

    #[Computed]
    public function consultationTypes(): \Illuminate\Database\Eloquent\Collection
    {
        return ConsultationType::where('is_active', true)->orderBy('name')->get();
    }

    #[Computed]
    public function roleCounts(): array
    {
        return [
            'all' => User::when($this->statusFilter === 'inactive', fn ($q) => $q->onlyTrashed())->count(),
            'patient' => User::role('patient')->when($this->statusFilter === 'inactive', fn ($q) => $q->onlyTrashed())->count(),
            'doctor' => User::role('doctor')->when($this->statusFilter === 'inactive', fn ($q) => $q->onlyTrashed())->count(),
            'nurse' => User::role('nurse')->when($this->statusFilter === 'inactive', fn ($q) => $q->onlyTrashed())->count(),
            'cashier' => User::role('cashier')->when($this->statusFilter === 'inactive', fn ($q) => $q->onlyTrashed())->count(),
            'admin' => User::role('admin')->when($this->statusFilter === 'inactive', fn ($q) => $q->onlyTrashed())->count(),
        ];
    }

    public function render(): View
    {
        $users = User::query()
            ->with(['roles', 'personalInformation', 'consultationTypes'])
            ->when($this->statusFilter === 'inactive', fn ($q) => $q->onlyTrashed())
            ->when($this->roleFilter, fn ($q) => $q->role($this->roleFilter))
            ->when($this->search, fn ($q) => $q
                ->where(function ($query) {
                    $query->where('first_name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%")
                        ->orWhere('middle_name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                }))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.admin.user-management', [
            'users' => $users,
        ])->layout('layouts.app');
    }
}
