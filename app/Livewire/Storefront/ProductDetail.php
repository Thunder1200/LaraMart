<?php

namespace App\Livewire\Storefront;

use App\Actions\Favorites\ToggleFavoriteAction;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use DomainException;
use Illuminate\Support\Facades\Auth;
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

    public ?string $cartError = null;

    public bool $isFavorited = false;

    public function mount(Product $product): void
    {
        $this->product = $product->loadMissing(['seller', 'category.parent', 'brand', 'variants', 'media']);

        $defaultVariant = $this->product->variants
            ->first(fn (ProductVariant $variant): bool => $variant->stock > 0)
            ?? $this->product->variants->first();

        $this->selectedVariantId = $defaultVariant?->id;

        $user = Auth::user();
        $this->isFavorited = $user !== null
            && $user->favorites()->where('product_id', $this->product->id)->exists();
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
        $this->cartError = null;
    }

    public function selectImage(int $index): void
    {
        $this->activeImageIndex = $index;
    }

    public function addToCart(CartService $cartService): void
    {
        $this->cartNotice = null;
        $this->cartError = null;

        $variant = $this->selectedVariant();

        if (! $variant || $variant->stock < 1) {
            $this->cartError = 'Seçilen varyant şu an stokta bulunmuyor.';

            return;
        }

        try {
            $cartService->addItem(Auth::user(), $variant, max(1, $this->quantity));
            $this->cartNotice = sprintf(
                '%s (%s) — %d adet sepetinize eklendi.',
                $this->product->name,
                $variant->label(),
                max(1, $this->quantity)
            );
        } catch (DomainException $exception) {
            $this->cartError = $exception->getMessage();
        }
    }

    public function toggleFavorite(ToggleFavoriteAction $action): void
    {
        $user = Auth::user();

        if ($user === null) {
            $this->redirect(route('login'));

            return;
        }

        $this->isFavorited = $action->execute($user, $this->product);
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
