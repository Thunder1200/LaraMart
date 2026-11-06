<?php

namespace App\Models;

use App\Enums\CouponType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_total',
        'starts_at',
        'ends_at',
        'usage_limit',
        'used_count',
    ];

    protected function casts(): array
    {
        return [
            'type' => CouponType::class,
            'value' => 'decimal:2',
            'min_total' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
        ];
    }

    public function isExpired(): bool
    {
        if ($this->ends_at !== null && $this->ends_at->isPast())
        {
            return true;
        }

        return false;
    }

    public function isNotStarted(): bool
    {
        if ($this->starts_at !== null && $this->starts_at->isFuture())
        {
            return true;
        }

        return false;
    }

    public function isUsageLimitReached(): bool
    {
        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit)
        {
            return true;
        }

        return false;
    }
}

