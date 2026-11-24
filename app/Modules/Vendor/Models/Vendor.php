<?php

namespace App\Modules\Vendor\Models;

use App\Models\User;
use App\Modules\Vendor\Enums\VendorStatus;
use App\Modules\Vendor\Enums\VendorVerificationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_user_id', 'vendor_code', 'business_name', 'business_type', 'business_email', 'business_phone',
        'contact_person', 'business_address', 'district', 'upazila', 'postal_code',
        'business_description', 'verification_status', 'status', 'submitted_at',
        'approved_at', 'approved_by', 'rejected_at', 'rejected_by', 'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'verification_status' => VendorVerificationStatus::class,
            'status' => VendorStatus::class,
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function scopeAccessibleTo(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $query) use ($user) {
            $query->where('owner_user_id', $user->getKey())
                ->orWhereHas('staff', fn (Builder $staff) => $staff
                    ->where('user_id', $user->getKey())
                    ->where('status', 'active'));
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function staff(): HasMany
    {
        return $this->hasMany(VendorUser::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(\App\Modules\Product\Models\Product::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(VendorDocument::class);
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(VendorBankAccount::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(VendorStatusHistory::class);
    }
}
