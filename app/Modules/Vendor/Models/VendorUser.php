<?php

namespace App\Modules\Vendor\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorUser extends Model
{
    protected $fillable = [
        'vendor_id', 'user_id', 'invited_email', 'staff_role', 'status', 'invited_by',
        'invitation_token_hash', 'invitation_expires_at', 'joined_at', 'accepted_at',
        'declined_at', 'revoked_at',
    ];

    protected $hidden = ['invitation_token_hash'];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'invitation_expires_at' => 'datetime',
            'accepted_at' => 'datetime',
            'declined_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function isPendingInvitation(): bool
    {
        return $this->status === 'invited'
            && $this->revoked_at === null
            && $this->invitation_token_hash !== null
            && $this->invitation_expires_at?->isFuture() === true;
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }
}
