<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\Models\UserRelations;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;
    use HasRoles;
    use UserRelations;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
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
            return strtoupper(substr($info->first_name, 0, 1) . substr($info->last_name, 0, 1));
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
}
