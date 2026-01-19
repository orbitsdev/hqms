<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $info = $this->personalInformation;

        return [
            'id' => $this->id,
            'email' => $this->email,
            'is_active' => $this->is_active,
            'email_verified_at' => $this->email_verified_at,
            'roles' => $this->roles->pluck('name'),

            // Personal Information
            'personal_information' => $info ? [
                'first_name' => $info->first_name,
                'middle_name' => $info->middle_name,
                'last_name' => $info->last_name,
                'full_name' => $info->full_name,
                'phone' => $info->phone,
                'date_of_birth' => $info->date_of_birth?->format('Y-m-d'),
                'gender' => $info->gender,
                'marital_status' => $info->marital_status,
                'province' => $info->province,
                'municipality' => $info->municipality,
                'barangay' => $info->barangay,
                'street' => $info->street,
                'full_address' => $info->full_address,
                'occupation' => $info->occupation,
                'emergency_contact_name' => $info->emergency_contact_name,
                'emergency_contact_phone' => $info->emergency_contact_phone,
            ] : null,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
