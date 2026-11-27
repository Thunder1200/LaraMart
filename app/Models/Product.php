<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'seller_id',
        'category_id',
        'brand_id',
        'name',
        'slug',
        'description',
        'status',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('products')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->nonQueued()
            ->fit(Fit::Crop, 400, 400);

        $this->addMediaConversion('gallery')
            ->nonQueued()
            ->fit(Fit::Contain, 900, 900);
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /** @return BelongsTo<Seller, $this> */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return BelongsTo<Brand, $this> */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /** @return HasMany<ProductVariant, $this> */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /** @return HasMany<Favorite, $this> */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /** @return BelongsToMany<User, $this> */
    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    /** @return HasMany<Review, $this> */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /** @return HasMany<Review, $this> */
    public function approvedReviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('is_approved', true);
    }

    public function displayVariant(): ?ProductVariant
    {
        $variants = $this->relationLoaded('variants') ? $this->variants : $this->variants()->get();

        return $variants->sortBy(fn (ProductVariant $variant): float => $variant->effectivePrice())->first();
    }

    public function totalStock(): int
    {
        $variants = $this->relationLoaded('variants') ? $this->variants : $this->variants()->get();

        return (int) $variants->sum('stock');
    }

    public function thumbnailUrl(): string
    {
        $media = $this->getMedia('products')->first();

        if ($media !== null) {
            return $media->hasGeneratedConversion('thumb')
                ? $media->getUrl('thumb')
                : $media->getUrl();
        }

        return $this->placeholderSvgDataUri($this->name, 0);
    }

    /**
     * @return list<array{id: int, url: string, thumb: string}>
     */
    public function galleryImages(): array
    {
        $mediaItems = $this->getMedia('products');

        if ($mediaItems->isNotEmpty()) {
            return $mediaItems->values()->map(fn (Media $media, int $index): array => [
                'id' => $index,
                'url' => $media->hasGeneratedConversion('gallery') ? $media->getUrl('gallery') : $media->getUrl(),
                'thumb' => $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl(),
            ])->all();
        }

        return [
            [
                'id' => 0,
                'url' => $this->placeholderSvgDataUri($this->name, 0),
                'thumb' => $this->placeholderSvgDataUri($this->name, 0),
            ],
            [
                'id' => 1,
                'url' => $this->placeholderSvgDataUri($this->name.' — Detay', 1),
                'thumb' => $this->placeholderSvgDataUri($this->name.' — Detay', 1),
            ],
        ];
    }

    private function placeholderSvgDataUri(string $label, int $variantIndex): string
    {
        $palettes = [
            ['#eef2ff', '#4f46e5', '#312e81'],
            ['#ecfdf5', '#059669', '#064e3b'],
            ['#fff7ed', '#ea580c', '#7c2d12'],
            ['#f5f3ff', '#7c3aed', '#4c1d95'],
            ['#f0f9ff', '#0284c7', '#0c4a6e'],
        ];

        $palette = $palettes[($this->id + $variantIndex) % count($palettes)];
        $safeTitle = htmlspecialchars(mb_strimwidth($label, 0, 28, '…'), ENT_QUOTES, 'UTF-8');

        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="600" height="600" viewBox="0 0 600 600"><rect width="600" height="600" fill="%s"/><circle cx="300" cy="245" r="95" fill="%s" opacity="0.15"/><rect x="245" y="190" width="110" height="110" rx="22" fill="%s" opacity="0.85"/><text x="300" y="257" text-anchor="middle" font-family="Arial,sans-serif" font-size="44" font-weight="700" fill="#ffffff">LM</text><text x="300" y="395" text-anchor="middle" font-family="Arial,sans-serif" font-size="22" font-weight="600" fill="%s">%s</text></svg>',
            $palette[0],
            $palette[1],
            $palette[1],
            $palette[2],
            $safeTitle
        );

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
