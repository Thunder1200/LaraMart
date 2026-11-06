<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image',
        'url',
        'sort',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function ()
        {
            Cache::forget('home_page_blocks');
        });

        static::deleted(function ()
        {
            Cache::forget('home_page_blocks');
        });
    }
}
