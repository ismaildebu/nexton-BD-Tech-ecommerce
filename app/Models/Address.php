<?php

namespace App\Models;

use App\Modules\Identity\Enums\AddressType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    /**
     * user_id and is_default are never mass assignable.
     * Ownership comes from the relationship; the default flag is managed by AddressService.
     *
     * @var list<string>
     */
    protected $fillable = [
        'type',
        'full_name',
        'phone',
        'address_line',
        'division',
        'district',
        'upazila',
        'postal_code',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'default_owner_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => AddressType::class,
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
