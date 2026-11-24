<?php

namespace App\Models;

use App\Modules\Identity\Enums\UserStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Mass assignable attributes.
     *
     * status, last_login_at, is_protected and the verification timestamps are
     * deliberately NOT fillable. They are only changed by services via forceFill().
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
            'is_admin' => 'boolean',
            'is_protected' => 'boolean',
        ];
    }

    public function customerProfile(): HasOne
    {
        return $this->hasOne(CustomerProfile::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function hasVerifiedPhone(): bool
    {
        return $this->phone !== null && $this->phone_verified_at !== null;
    }

    public function hasVerifiedContact(): bool
    {
        return ($this->email !== null && $this->hasVerifiedEmail())
            || $this->hasVerifiedPhone();
    }

    public function canAuthenticate(): bool
    {
        $status = $this->status instanceof UserStatus
            ? $this->status
            : UserStatus::tryFrom((string) $this->status);

        return ($status?->canAuthenticate() ?? false) && $this->hasVerifiedContact();
    }

    public function vendor(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Modules\Vendor\Models\Vendor::class, 'owner_user_id');
    }
}
