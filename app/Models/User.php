<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'profile_photo',
        'password',
        'status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
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

     public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', strtolower($roleName))->exists();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isOfficeStaff(): bool
    {
        return $this->hasRole('staff') || $this->officeStaff()->where('status', 'active')->exists();
    }

    public function officeStaff()
    {
        return $this->hasMany(OfficeStaff::class);
    }

    public function activeOfficeStaff()
    {
        return $this->hasOne(OfficeStaff::class)->where('status', 'active');
    }

    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function citizenProfile()
    {
        return $this->hasOne(CitizenProfile::class);
    }
    
}
