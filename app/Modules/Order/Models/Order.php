<?php

namespace App\Modules\Order\Models;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'address_id', 'shipping_address', 'order_number', 'subtotal',
        'shipping_total', 'discount_total', 'total', 'status', 'payment_status', 'idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'shipping_address' => 'array',
            'subtotal' => 'decimal:2',
            'shipping_total' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(\App\Modules\Payment\Models\Payment::class);
    }

    public function shippings(): HasMany
    {
        return $this->hasMany(\App\Modules\Shipping\Models\Shipping::class);
    }
}
