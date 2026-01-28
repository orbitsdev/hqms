<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\Models\UserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, TwoFactorAuthenticatable;
    use HasRoles;
    use UserRelations;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'phone',
        'password',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials from personal information
     */
    public function initials(): string
    {
        $info = $this->personalInformation;
        if ($info) {
            return strtoupper(substr($info->first_name, 0, 1).substr($info->last_name, 0, 1));
        }

        // Fallback: use email username (e.g., "admin@test.com" -> "AD")
        $username = Str::before($this->email, '@');

        return strtoupper(substr($username, 0, 2));
    }

    /**
     * Get display name from personal information
     */
    public function getNameAttribute(): string
    {
        return $this->personalInformation?->full_name ?? $this->email;
    }

    /**
     * Role helper methods
     */
    public function isPatient(): bool
    {
        return $this->hasRole('patient');
    }

    public function isDoctor(): bool
    {
        return $this->hasRole('doctor');
    }

    public function isNurse(): bool
    {
        return $this->hasRole('nurse');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isCashier(): bool
    {
        return $this->hasRole('cashier');
    }

    public function hasCompletePersonalInformation(): bool
    {
        $info = $this->personalInformation;

        if (! $info) {
            return false;
        }

        $required = [
            $info->first_name,
            $info->last_name,
            $info->phone,
            $info->date_of_birth,
            $info->gender,
            $info->province,
            $info->municipality,
            $info->barangay,
            $info->street,
            $info->emergency_contact_name,
            $info->emergency_contact_phone,
        ];

        foreach ($required as $value) {
            if (! filled($value)) {
                return false;
            }
        }

        return true;
    }
}
