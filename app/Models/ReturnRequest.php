<?php

namespace App\Models;

use Database\Factories\ReturnRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnRequest extends Model
{
    /** @use HasFactory<ReturnRequestFactory> */
    use HasFactory;

    protected $table = 'returns';

    protected $fillable = [
        'order_item_id',
        'reason',
        'status',
        'refund_amount',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'refund_amount' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<OrderItem, $this> */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
