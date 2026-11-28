<?php

namespace App\Models;

use App\Support\CategoryFilterSchema;
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
        'image_key',
        'description',
        'attributes',
        'status',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'attributes' => 'array',
        ];
    }

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

    /**
     * @return array<string, string>
     */
    public function formattedAttributes(): array
    {
        $raw = $this->getAttribute('attributes');

        if (! is_array($raw) || $raw === []) {
            return [];
        }

        $formatted = [];
        foreach ($raw as $key => $value) {
            if (! is_string($key) || $value === null || $value === '') {
                continue;
            }
            $formatted[CategoryFilterSchema::labelFor($key)] = (string) $value;
        }

        return $formatted;
    }

    public function thumbnailUrl(): string
    {
        $media = $this->getMedia('products')->first();

        if ($media !== null) {
            return $media->hasGeneratedConversion('thumb')
                ? $media->getUrl('thumb')
                : $media->getUrl();
        }

        $displayVariant = $this->displayVariant();
        $displayThumb = $displayVariant?->thumbnailUrl();
        if ($displayThumb !== null) {
            return $displayThumb;
        }

        $variants = $this->relationLoaded('variants') ? $this->variants : $this->variants()->get();
        foreach ($variants as $variant) {
            $thumb = $variant->thumbnailUrl();
            if ($thumb !== null) {
                return $thumb;
            }
        }

        $studioImages = $this->resolveStudioImages();
        if ($studioImages !== []) {
            return $studioImages[0]['thumb'];
        }

        return $this->placeholderSvgDataUri($this->name, 0);
    }

    /**
     * Ürün kartlarında anlık renk değişimi için benzersiz renk seçenekleri ve görsellerini döndürür.
     *
     * @return list<array{color: string, hex: string, image: string, variant_id: int}>
     */
    public function colorSwatches(): array
    {
        $variants = $this->relationLoaded('variants') ? $this->variants : $this->variants()->get();
        $fallbackImage = $this->thumbnailUrl();
        $seen = [];
        $swatches = [];

        foreach ($variants as $variant) {
            $colorName = trim((string) ($variant->color ?? ''));
            if ($colorName === '' || isset($seen[$colorName])) {
                continue;
            }

            $seen[$colorName] = true;
            $swatches[] = [
                'color' => $colorName,
                'hex' => $variant->swatchHex(),
                'image' => $variant->thumbnailUrl() ?? $fallbackImage,
                'variant_id' => $variant->id,
            ];
        }

        return $swatches;
    }

    /**
     * @return list<array{id: int, url: string, thumb: string, color?: string, variant_id?: int}>
     */
    public function galleryImages(?ProductVariant $selectedVariant = null): array
    {
        $mediaItems = $this->getMedia('products');

        if ($mediaItems->isNotEmpty()) {
            return $mediaItems->values()->map(fn (Media $media, int $index): array => [
                'id' => $index,
                'url' => $media->hasGeneratedConversion('gallery') ? $media->getUrl('gallery') : $media->getUrl(),
                'thumb' => $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl(),
            ])->all();
        }

        // Renk varyantlarına özel benzersiz görseller varsa galeri bunlardan oluşur
        $variants = $this->relationLoaded('variants') ? $this->variants : $this->variants()->get();
        $variantImages = [];
        $seenPaths = [];

        foreach ($variants as $variant) {
            $url = $variant->imageUrl();
            if ($url === null || isset($seenPaths[$url])) {
                continue;
            }

            $seenPaths[$url] = true;
            $variantImages[] = [
                'id' => count($variantImages),
                'url' => $url,
                'thumb' => $variant->thumbnailUrl() ?? $url,
                'color' => (string) ($variant->color ?? ''),
                'variant_id' => $variant->id,
            ];
        }

        if ($variantImages !== []) {
            if ($selectedVariant !== null && $selectedVariant->imageUrl() !== null) {
                $selectedUrl = $selectedVariant->imageUrl();
                usort($variantImages, fn (array $a, array $b): int => ($b['url'] === $selectedUrl ? 1 : 0) <=> ($a['url'] === $selectedUrl ? 1 : 0));
                $variantImages = array_map(function (array $item, int $idx): array {
                    $item['id'] = $idx;

                    return $item;
                }, $variantImages, array_keys($variantImages));
            }

            return $variantImages;
        }

        $studioImages = $this->resolveStudioImages();
        if ($studioImages !== []) {
            return $studioImages;
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

    /**
     * @return list<array{id: int, url: string, thumb: string}>
     */
    private function resolveStudioImages(): array
    {
        $key = $this->resolveImageKey();
        if ($key === null) {
            return [];
        }

        $primaryIndex = ($this->id % 2 === 0) ? 2 : 1;
        $secondaryIndex = $primaryIndex === 1 ? 2 : 1;

        $images = [];
        foreach ([$primaryIndex, $secondaryIndex] as $order => $angle) {
            foreach (['jpg', 'png', 'webp'] as $ext) {
                $relativePath = "images/products/{$key}-{$angle}.{$ext}";
                if (is_file(public_path($relativePath))) {
                    $url = asset($relativePath);
                    $images[] = [
                        'id' => $order,
                        'url' => $url,
                        'thumb' => $url,
                    ];
                    break;
                }
            }
        }

        return $images;
    }

    private function resolveImageKey(): ?string
    {
        if (is_string($this->image_key) && $this->image_key !== '') {
            return $this->image_key;
        }

        $name = mb_strtolower($this->name);

        return match (true) {
            str_contains($name, 'kulakl') => 'kulaklik',
            str_contains($name, 'saat') => 'akilli-saat',
            str_contains($name, 'laptop') || str_contains($name, 'dizüstü') || str_contains($name, 'notebook') || str_contains($name, 'tablet') => 'laptop',
            str_contains($name, 'klavye') => 'klavye',
            str_contains($name, 'ceket') || str_contains($name, 'trençkot') || str_contains($name, 'blazer') || str_contains($name, 'kaban') => 'ceket',
            str_contains($name, 'tişört') || str_contains($name, 'tisort') || str_contains($name, 'polo') => 'tisort',
            str_contains($name, 'ayakkab') || str_contains($name, 'sneaker') => 'ayakkabi',
            str_contains($name, 'kahve') || str_contains($name, 'espresso') => 'kahve-makinesi',
            str_contains($name, 'lamba') || str_contains($name, 'abajur') => 'masa-lambasi',
            str_contains($name, 'çanta') || str_contains($name, 'canta') || str_contains($name, 'kamp') => 'sirt-cantasi',
            str_contains($name, 'serum') || str_contains($name, 'krem') || str_contains($name, 'cilt') => 'cilt-bakim',
            str_contains($name, 'parfüm') || str_contains($name, 'parfum') || str_contains($name, 'edp') => 'parfum',
            default => null,
        };
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
