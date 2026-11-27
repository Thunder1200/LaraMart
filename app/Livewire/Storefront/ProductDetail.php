<?php

namespace App\Livewire\Storefront;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ProductDetail extends Component
{
    public Product $product;

    public ?int $selectedVariantId = null;

    public int $activeImageIndex = 0;

    public int $quantity = 1;

    public ?string $cartNotice = null;

    public function mount(Product $product): void
    {
        $this->product = $product->loadMissing(['seller', 'category.parent', 'brand', 'variants', 'media']);

        $defaultVariant = $this->product->variants
            ->first(fn (ProductVariant $variant): bool => $variant->stock > 0)
            ?? $this->product->variants->first();

        $this->selectedVariantId = $defaultVariant?->id;
    }

    public function selectVariant(int $variantId): void
    {
        $variant = $this->product->variants->firstWhere('id', $variantId);

        if (! $variant) {
            return;
        }

        $this->selectedVariantId = $variant->id;
        $this->quantity = 1;
        $this->cartNotice = null;
    }

    public function selectImage(int $index): void
    {
        $this->activeImageIndex = $index;
    }

    public function addToCart(): void
    {
        $variant = $this->selectedVariant();

        if (! $variant || $variant->stock < 1) {
            $this->cartNotice = 'Seçilen varyant şu an stokta bulunmuyor.';

            return;
        }

        if ($this->quantity > $variant->stock) {
            $this->quantity = $variant->stock;
        }

        $this->cartNotice = sprintf(
            '%s (%s) — %d adet sepete eklenmek üzere seçildi. (Tam sepet ve ödeme akışı Hafta 5–6’da aktif edilecektir.)',
            $this->product->name,
            $variant->label(),
            $this->quantity
        );
    }

    #[Computed]
    public function selectedVariant(): ?ProductVariant
    {
        if ($this->selectedVariantId === null) {
            return $this->product->variants->first();
        }

        return $this->product->variants->firstWhere('id', $this->selectedVariantId)
            ?? $this->product->variants->first();
    }

    /**
     * @return list<array{id: int, url: string, thumb: string}>
     */
    #[Computed]
    public function gallery(): array
    {
        return $this->product->galleryImages();
    }

    public function render(): View
    {
        return view('livewire.storefront.product-detail');
    }
}
