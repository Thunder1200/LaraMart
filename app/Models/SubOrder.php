<?php

namespace App\Models;

use App\Enums\SubOrderStatus;
use Database\Factories\SubOrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property SubOrderStatus $status
 * @property Carbon|null $delivered_at
 */
class SubOrder extends Model
{
    /** @use HasFactory<SubOrderFactory> */
    use HasFactory;

    protected $fillable = [
        'order_id',
        'seller_id',
        'status',
        'cargo_company',
        'tracking_number',
        'delivered_at',
        'subtotal',
        'shipping_amount',
        'commission_amount',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => SubOrderStatus::class,
            'delivered_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'shipping_amount' => 'decimal:2',
            'commission_amount' => 'decimal:2',
        ];
    }

    public function isReturnWindowOpen(): bool
    {
        if ($this->status !== SubOrderStatus::Delivered) {
            return false;
        }

        $deliveredAt = $this->delivered_at ?? $this->updated_at;
        if (! $deliveredAt instanceof Carbon) {
            return false;
        }

        return now()->lessThanOrEqualTo($deliveredAt->copy()->addDays(14));
    }

    public function returnDaysRemaining(): int
    {
        if (! $this->isReturnWindowOpen()) {
            return 0;
        }

        $deliveredAt = $this->delivered_at ?? $this->updated_at;
        if (! $deliveredAt instanceof Carbon) {
            return 0;
        }

        $deadline = $deliveredAt->copy()->addDays(14);

        return max(0, (int) now()->diffInDays($deadline, false));
    }

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** @return BelongsTo<Seller, $this> */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    /** @return HasMany<OrderItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
