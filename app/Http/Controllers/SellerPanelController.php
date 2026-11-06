<?php

namespace App\Http\Controllers;

use App\Actions\PostOrderInteractionAction;
use App\Actions\TransitionSubOrderStatusAction;
use App\Enums\ReturnStatus;
use App\Enums\SubOrderStatus;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Requests\UpdateSubOrderStatusRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ReturnRequest;
use App\Models\SubOrder;
use App\Services\CatalogService;
use App\Services\ReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SellerPanelController extends Controller
{
    public function __construct(
        private readonly CatalogService $catalogService,
        private readonly ReportService $reportService,
    ) {
    }

    public function dashboard(Request $request): View
    {
        $seller = $request->user()->seller;
        $metrics = $this->reportService->getSellerMetrics($seller);
        $products = $seller->products()->with(['category', 'brand', 'variants'])->latest('id')->get();
        $subOrders = $seller->subOrders()
            ->with(['order.user', 'items.returnRequest'])
            ->latest('id')
            ->get();

        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();

        return view('seller.dashboard', [
            'seller' => $seller,
            'metrics' => $metrics,
            'products' => $products,
            'subOrders' => $subOrders,
            'categories' => $categories,
            'brands' => $brands,
        ]);
    }

    public function storeProduct(StoreProductRequest $request): RedirectResponse
    {
        Gate::authorize('create', Product::class);

        $seller = $request->user()->seller;
        $this->catalogService->createSellerProduct($seller->id, $request->validated());

        return redirect('/satici')->with('success', 'Yeni ürün başarıyla eklendi.');
    }

    public function editProduct(Product $product): View
    {
        Gate::authorize('update', $product);

        $product->load('variants');
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();

        return view('seller.edit-product', [
            'product' => $product,
            'categories' => $categories,
            'brands' => $brands,
        ]);
    }

    public function updateProduct(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        Gate::authorize('update', $product);

        $this->catalogService->updateSellerProduct($product, $request->validated());

        return redirect('/satici')->with('success', 'Ürün bilgileri güncellendi.');
    }

    public function deleteProduct(Product $product): RedirectResponse
    {
        Gate::authorize('delete', $product);

        $product->delete();

        return redirect('/satici')->with('success', 'Ürün silindi.');
    }

    public function updateSubOrderStatus(
        UpdateSubOrderStatusRequest $request,
        SubOrder $subOrder,
        TransitionSubOrderStatusAction $transitionAction
    ): RedirectResponse {
        Gate::authorize('update', $subOrder);

        try
        {
            $targetStatus = SubOrderStatus::from($request->validated('status'));

            $transitionAction->execute(
                $subOrder,
                $targetStatus,
                $request->validated('cargo_company'),
                $request->validated('tracking_number')
            );

            return back()->with('success', 'Alt sipariş durumu güncellendi ve müşteriye bildirim gönderildi.');
        }
        catch (\Throwable $e)
        {
            return back()->withErrors(['status' => $e->getMessage()]);
        }
    }

    public function handleReturn(Request $request, ReturnRequest $returnRequest, PostOrderInteractionAction $action): RedirectResponse
    {
        Gate::authorize('update', $returnRequest);

        $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
        ]);

        $decision = ReturnStatus::from($request->input('decision'));
        $action->decideReturnRequest($returnRequest, $decision);

        return back()->with('success', 'İade talebi durumu güncellendi.');
    }
}

