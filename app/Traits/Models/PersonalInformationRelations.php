<?php
namespace App\Traits\Models;

use App\Models\User;


trait PersonalInformationRelations
{
     public function user() {
        return $this->belongsTo(User::class);
    }

     public function getFullNameAttribute() {
        $name = $this->first_name;
        if ($this->middle_name) {
            $name .= ' ' . $this->middle_name;
        }
        $name .= ' ' . $this->last_name;
        return $name;
    }

     public function getFullAddressAttribute() {
        $parts = array_filter([
            $this->street,
            $this->barangay,
            $this->municipality,
            $this->province,
        ]);
        return implode(', ', $parts);
    }
}
