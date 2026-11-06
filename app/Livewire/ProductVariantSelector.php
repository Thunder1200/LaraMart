<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\CalculateCartTotalsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class ProductVariantSelector extends Component
{
    public Product $product;

    public int $selectedVariantId = 0;

    public string $selectedColor = '';

    public string $selectedSize = '';

    public string $currentPrice = '0.00';

    public ?string $originalPrice = null;

    public int $currentStock = 0;

    public string $sku = '';

    public int $quantity = 1;

    public ?string $feedbackMessage = null;

    public ?string $errorMessage = null;

    public function mount(Product $product): void
    {
        $this->product = $product->loadMissing('variants');
        $firstVariant = $this->product->variants->first();

        if ($firstVariant)
        {
            $this->applyVariant($firstVariant->id);
        }
    }

    public function selectVariant(int $variantId): void
    {
        $this->feedbackMessage = null;
        $this->errorMessage = null;
        $this->applyVariant($variantId);
    }

    public function selectByAttributes(string $color, string $size): void
    {
        $this->feedbackMessage = null;
        $this->errorMessage = null;

        $variant = $this->product->variants
            ->first(fn ($item) => $item->color === $color && $item->size === $size)
            ?? $this->product->variants->firstWhere('color', $color)
            ?? $this->product->variants->first();

        if ($variant)
        {
            $this->applyVariant($variant->id);
        }
    }

    public function addToCart(CalculateCartTotalsService $cartService): void
    {
        $this->feedbackMessage = null;
        $this->errorMessage = null;

        $variant = $this->product->variants->firstWhere('id', $this->selectedVariantId);

        if (! $variant)
        {
            $this->errorMessage = 'Lütfen geçerli bir varyant seçiniz.';

            return;
        }

        try
        {
            $cart = $cartService->resolveCart(Auth::user(), Session::getId());
            $cartService->addItem($cart, $variant, $this->quantity);
            $this->feedbackMessage = 'Ürün sepetinize eklendi!';
            $this->dispatch('cart-updated');
        }
        catch (\Throwable $e)
        {
            $this->errorMessage = $e->getMessage();
        }
    }

    private function applyVariant(int $variantId): void
    {
        $variant = $this->product->variants->firstWhere('id', $variantId);

        if (! $variant)
        {
            return;
        }

        $this->selectedVariantId = $variant->id;
        $this->selectedColor = (string) $variant->color;
        $this->selectedSize = (string) $variant->size;
        $this->currentPrice = $variant->effectivePrice();
        $this->originalPrice = $variant->discount_price !== null ? (string) $variant->price : null;
        $this->currentStock = (int) $variant->stock;
        $this->sku = (string) $variant->sku;
    }

    public function render()
    {
        return view('livewire.product-variant-selector', [
            'variants' => $this->product->variants,
        ]);
    }
}
