<?php

namespace App\Models;

use App\Enums\SubOrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'seller_id',
        'status',
        'cargo_company',
        'tracking_number',
        'subtotal',
        'shipping',
        'commission_amount',
    ];

    protected function casts(): array
    {
        return [
            'status' => SubOrderStatus::class,
            'subtotal' => 'decimal:2',
            'shipping' => 'decimal:2',
            'commission_amount' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}

