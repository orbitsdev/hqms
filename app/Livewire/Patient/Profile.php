<?php

namespace App\Livewire\Patient;

use App\Models\PersonalInformation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class Profile extends Component
{
    public $user;
    public $personalInfo;

    public $first_name;
    public $middle_name;
    public $last_name;
    public $date_of_birth;
    public $gender;
    public $phone;
    public $occupation;
    public $province;
    public $municipality;
    public $barangay;
    public $street;
    public $emergency_contact_name;
    public $emergency_contact_phone;

    public function mount(): void
    {
        $this->user = Auth::user();
        $this->personalInfo = $this->user->personalInformation;

        if ($this->personalInfo) {
            $this->first_name = $this->personalInfo->first_name;
            $this->middle_name = $this->personalInfo->middle_name;
            $this->last_name = $this->personalInfo->last_name;
            $this->date_of_birth = $this->personalInfo->date_of_birth?->toDateString();
            $this->gender = $this->personalInfo->gender;
            $this->phone = $this->personalInfo->phone;
            $this->occupation = $this->personalInfo->occupation;
            $this->province = $this->personalInfo->province;
            $this->municipality = $this->personalInfo->municipality;
            $this->barangay = $this->personalInfo->barangay;
            $this->street = $this->personalInfo->street;
            $this->emergency_contact_name = $this->personalInfo->emergency_contact_name;
            $this->emergency_contact_phone = $this->personalInfo->emergency_contact_phone;
        }
    }

    public function savePersonalInfo(): void
    {
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'date_of_birth' => 'required|date|before:today',
            'gender' => ['required', Rule::in(['male', 'female'])],
            'phone' => 'nullable|string|max:20',
            'occupation' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'municipality' => 'nullable|string|max:255',
            'barangay' => 'nullable|string|max:255',
            'street' => 'nullable|string|max:500',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
        ]);

        if ($this->personalInfo) {
            $this->personalInfo->update([
                'first_name' => $this->first_name,
                'middle_name' => $this->middle_name,
                'last_name' => $this->last_name,
                'date_of_birth' => $this->date_of_birth,
                'gender' => $this->gender,
                'phone' => $this->phone,
                'occupation' => $this->occupation,
                'province' => $this->province,
                'municipality' => $this->municipality,
                'barangay' => $this->barangay,
                'street' => $this->street,
                'emergency_contact_name' => $this->emergency_contact_name,
                'emergency_contact_phone' => $this->emergency_contact_phone,
            ]);
        } else {
            $this->personalInfo = PersonalInformation::create([
                'user_id' => $this->user->id,
                'first_name' => $this->first_name,
                'middle_name' => $this->middle_name,
                'last_name' => $this->last_name,
                'date_of_birth' => $this->date_of_birth,
                'gender' => $this->gender,
                'phone' => $this->phone,
                'occupation' => $this->occupation,
                'province' => $this->province,
                'municipality' => $this->municipality,
                'barangay' => $this->barangay,
                'street' => $this->street,
                'emergency_contact_name' => $this->emergency_contact_name,
                'emergency_contact_phone' => $this->emergency_contact_phone,
            ]);
        }

        $this->dispatch('saved', 'Profile updated successfully!');
    }

    public function render(): View
    {
        return view('livewire.patient.profile')
            ->layout('layouts.app');
    }
}
