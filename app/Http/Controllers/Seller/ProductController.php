<?php

namespace App\Http\Controllers\Seller;

use App\Actions\Products\CreateSellerProductAction;
use App\Actions\Products\DeleteSellerProductAction;
use App\Actions\Products\ToggleProductStatusAction;
use App\Actions\Products\UpdateSellerProductAction;
use App\Actions\Products\UpdateVariantStockAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\StoreProductRequest;
use App\Http\Requests\Seller\UpdateProductRequest;
use App\Http\Requests\Seller\UpdateVariantStockRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Product::class);

        /** @var Seller $seller */
        $seller = $request->user()->seller;

        $products = Product::query()
            ->where('seller_id', $seller->id)
            ->with(['category', 'brand', 'variants', 'media'])
            ->latest('id')
            ->paginate(15);

        return view('seller.products.index', [
            'seller' => $seller,
            'products' => $products,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Product::class);

        return view('seller.products.create', [
            'categories' => Category::query()->orderBy('sort')->orderBy('name')->get(),
            'brands' => Brand::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreProductRequest $request, CreateSellerProductAction $action): RedirectResponse
    {
        /** @var Seller $seller */
        $seller = $request->user()->seller;

        /** @var list<UploadedFile> $images */
        $images = array_values($request->file('images', []));

        $action->execute($seller, $request->validated(), $images);

        return redirect()
            ->route('seller.products.index')
            ->with('status', 'Ürün başarıyla eklendi.');
    }

    public function edit(Product $product): View
    {
        Gate::authorize('update', $product);

        $product->load(['variants', 'media']);

        return view('seller.products.edit', [
            'product' => $product,
            'primaryVariant' => $product->variants->first(),
            'categories' => Category::query()->orderBy('sort')->orderBy('name')->get(),
            'brands' => Brand::query()->orderBy('name')->get(),
        ]);
    }

    public function update(
        UpdateProductRequest $request,
        Product $product,
        UpdateSellerProductAction $action
    ): RedirectResponse {
        /** @var list<UploadedFile> $images */
        $images = array_values($request->file('images', []));

        $action->execute($product, $request->validated(), $images);

        return redirect()
            ->route('seller.products.index')
            ->with('status', 'Ürün başarıyla güncellendi.');
    }

    public function destroy(Product $product, DeleteSellerProductAction $action): RedirectResponse
    {
        Gate::authorize('delete', $product);

        $action->execute($product);

        return redirect()
            ->route('seller.products.index')
            ->with('status', 'Ürün başarıyla silindi.');
    }

    public function toggleStatus(Product $product, ToggleProductStatusAction $action): RedirectResponse
    {
        Gate::authorize('update', $product);

        $updated = $action->execute($product);

        return redirect()
            ->route('seller.products.index')
            ->with('status', $updated->status === 'active' ? 'Ürün aktif yapıldı.' : 'Ürün pasif yapıldı.');
    }

    public function updateStock(
        UpdateVariantStockRequest $request,
        Product $product,
        UpdateVariantStockAction $action
    ): RedirectResponse {
        $validated = $request->validated();

        $action->execute($product, (int) $validated['variant_id'], (int) $validated['stock']);

        return redirect()
            ->route('seller.products.index')
            ->with('status', 'Stok başarıyla güncellendi.');
    }
}
