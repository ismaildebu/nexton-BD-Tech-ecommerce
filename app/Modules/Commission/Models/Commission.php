<?php

namespace App\Modules\Commission\Models;

use App\Modules\Order\Models\Order;
use App\Modules\Vendor\Models\Vendor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commission extends Model
{
    protected $fillable = ['vendor_id', 'order_id', 'amount', 'rate'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'rate' => 'decimal:2'];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
