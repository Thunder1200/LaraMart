<?php

namespace App\Models;

use Database\Factories\SellerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seller extends Model
{
    /** @use HasFactory<SellerFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'store_name',
        'slug',
        'tax_number',
        'iban',
        'status',
        'commission_rate',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<Product, $this> */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /** @return HasMany<SubOrder, $this> */
    public function subOrders(): HasMany
    {
        return $this->hasMany(SubOrder::class);
    }

    /** @return HasMany<SellerPayout, $this> */
    public function payouts(): HasMany
    {
        return $this->hasMany(SellerPayout::class);
    }
}
