<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'commission_rate',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:2',
            'sort' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function ()
        {
            Cache::forget('mega_menu_categories');
            Cache::forget('home_featured_categories');
        });

        static::deleted(function ()
        {
            Cache::forget('mega_menu_categories');
            Cache::forget('home_featured_categories');
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}

