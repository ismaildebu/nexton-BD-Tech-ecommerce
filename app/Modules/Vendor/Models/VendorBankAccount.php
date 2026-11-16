<?php

namespace App\Modules\Vendor\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class VendorBankAccount extends Model
{
    protected $fillable = [
        'vendor_id', 'account_holder_name', 'bank_name', 'branch_name',
        'account_number_encrypted', 'account_number_last_four', 'routing_number',
        'account_type', 'verification_status', 'verified_by', 'verified_at', 'is_default',
    ];

    protected function casts(): array
    {
        return ['verified_at' => 'datetime', 'is_default' => 'boolean'];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function setAccountNumberAttribute(string $value): void
    {
        $this->attributes['account_number_encrypted'] = Crypt::encryptString($value);
        $this->attributes['account_number_last_four'] = substr($value, -4);
    }

    public function getMaskedAccountNumberAttribute(): string
    {
        return '****'.($this->account_number_last_four ?? '');
    }
}
