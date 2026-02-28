<?php

namespace App\Livewire\Patient;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class Profile extends Component
{
    public string $first_name = '';

    public ?string $middle_name = null;

    public string $last_name = '';

    public string $phone = '';

    public ?string $date_of_birth = null;

    public ?string $gender = null;

    public ?string $marital_status = null;

    public ?string $province = null;

    public ?string $municipality = null;

    public ?string $barangay = null;

    public ?string $street = null;

    public ?string $occupation = null;

    public ?string $emergency_contact_name = null;

    public ?string $emergency_contact_phone = null;

    public bool $showCompletionNotice = false;

    public function mount(): void
    {
        $user = Auth::user();

        $info = $user?->personalInformation;

        if ($info) {
            $this->first_name = $info->first_name;
            $this->middle_name = $info->middle_name;
            $this->last_name = $info->last_name;
            $this->phone = (string) ($info->phone ?? '');
            $this->date_of_birth = optional($info->date_of_birth)?->format('Y-m-d');
            $this->gender = $info->gender;
            $this->marital_status = $info->marital_status;
            $this->province = $info->province;
            $this->municipality = $info->municipality;
            $this->barangay = $info->barangay;
            $this->street = $info->street;
            $this->occupation = $info->occupation;
            $this->emergency_contact_name = $info->emergency_contact_name;
            $this->emergency_contact_phone = (string) ($info->emergency_contact_phone ?? '');
        }

        $this->showCompletionNotice = ! $user?->hasCompletePersonalInformation();
    }

    public function save(): void
    {
        $user = Auth::user();

        $validated = $this->validate($this->rules());

        $user->personalInformation()->updateOrCreate(
            ['user_id' => $user->id],
            $validated,
        );

        $this->showCompletionNotice = ! $user->fresh()->hasCompletePersonalInformation();

        Toaster::success(__('Profile saved. You can continue to the patient dashboard.'));
    }

    protected function rules(): array
    {
        $user = Auth::user();
        $infoId = $user?->personalInformation?->id;

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', Rule::unique('personal_information', 'phone')->ignore($infoId)],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'marital_status' => ['nullable', Rule::in(['child', 'single', 'married', 'widow'])],
            'province' => ['required', 'string', 'max:255'],
            'municipality' => ['required', 'string', 'max:255'],
            'barangay' => ['required', 'string', 'max:255'],
            'street' => ['required', 'string', 'max:500'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'emergency_contact_name' => ['required', 'string', 'max:255'],
            'emergency_contact_phone' => ['required', 'string', 'max:20'],
        ];
    }

    public function render(): View
    {
        return view('livewire.patient.profile')
            ->layout('layouts.app');
    }
}
