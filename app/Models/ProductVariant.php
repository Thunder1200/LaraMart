<?php

namespace App\Models;

use Database\Factories\ProductVariantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    /** @use HasFactory<ProductVariantFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'color',
        'size',
        'image_path',
        'price',
        'discount_price',
        'stock',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'discount_price' => 'decimal:2',
            'stock' => 'integer',
        ];
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @return HasMany<CartItem, $this> */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'variant_id');
    }

    /** @return HasMany<OrderItem, $this> */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'variant_id');
    }

    public function effectivePrice(): float
    {
        return $this->hasDiscount()
            ? (float) $this->discount_price
            : (float) $this->price;
    }

    public function hasDiscount(): bool
    {
        return $this->discount_price !== null && (float) $this->discount_price < (float) $this->price;
    }

    public function label(): string
    {
        return collect([$this->color, $this->size])
            ->filter()
            ->implode(' / ') ?: $this->sku;
    }

    public function hasUniqueImageFile(): bool
    {
        if (! is_string($this->image_path) || $this->image_path === '') {
            return false;
        }

        return self::publicFileExists($this->image_path);
    }

    public function imageUrl(): ?string
    {
        if (! $this->hasUniqueImageFile()) {
            return null;
        }

        return asset((string) $this->image_path);
    }

    public function thumbnailUrl(): ?string
    {
        if (! $this->hasUniqueImageFile()) {
            return null;
        }

        $path = (string) $this->image_path;
        $dir = dirname($path);
        $base = basename($path);
        $thumbRel = "{$dir}/thumbs/{$base}";

        if (self::publicFileExists($thumbRel)) {
            return asset($thumbRel);
        }

        return asset($path);
    }

    private static function publicFileExists(string $relativePath): bool
    {
        $normalized = ltrim(str_replace('\\', '/', $relativePath), '/');
        $request = request();

        /** @var array<string, bool> $memo */
        $memo = $request->attributes->get('laramart.public_file_exists', []);

        if (array_key_exists($normalized, $memo)) {
            return $memo[$normalized];
        }

        $dir = dirname($normalized);
        $dirCacheKey = 'laramart.dir_index.'.$dir;

        if (! $request->attributes->has($dirCacheKey)) {
            $absDir = public_path($dir);
            $entries = is_dir($absDir) ? (scandir($absDir) ?: []) : [];
            $map = [];
            foreach ($entries as $entry) {
                if ($entry !== '.' && $entry !== '..') {
                    $map[$entry] = true;
                }
            }
            $request->attributes->set($dirCacheKey, $map);
        }

        /** @var array<string, bool> $dirMap */
        $dirMap = $request->attributes->get($dirCacheKey, []);
        $exists = isset($dirMap[basename($normalized)]) || is_file(public_path($normalized));

        $memo[$normalized] = $exists;
        $request->attributes->set('laramart.public_file_exists', $memo);

        return $exists;
    }

    /**
     * Renk adına karşılık gelen arayüz renk kodu (swatch gösterimi için).
     */
    public function swatchHex(): string
    {
        $color = mb_strtolower((string) $this->color);

        return match (true) {
            str_contains($color, 'siyah') || str_contains($color, 'antrasit') => '#18181b',
            str_contains($color, 'beyaz') => '#f8fafc',
            str_contains($color, 'gümüş') || str_contains($color, 'gri') || str_contains($color, 'titanyum') || str_contains($color, 'inoks') || str_contains($color, 'krom') => '#94a3b8',
            str_contains($color, 'lacivert') || str_contains($color, 'mavi') => '#1e3a8a',
            str_contains($color, 'bej') || str_contains($color, 'taş') => '#d6c7b2',
            str_contains($color, 'yeşil') || str_contains($color, 'haki') => '#3f6212',
            str_contains($color, 'kırmızı') => '#dc2626',
            str_contains($color, 'amber') || str_contains($color, 'pirinç') || str_contains($color, 'meşe') || str_contains($color, 'altın') => '#b45309',
            str_contains($color, 'turuncu') => '#ea580c',
            default => '#4f46e5',
        };
    }
}
