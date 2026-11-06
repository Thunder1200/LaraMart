<?php

namespace App\Http\Controllers;

use App\Actions\SellerManagementAction;
use App\Http\Requests\StoreCategoryRequest;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\Seller;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminPanelController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService,
        private readonly SellerManagementAction $sellerAction,
    ) {
    }

    public function dashboard(): View
    {
        $metrics = $this->reportService->getAdminMetrics();
        $sellers = Seller::with('user')->latest('id')->get();
        $categories = Category::with('parent')->orderBy('sort')->get();
        $orders = Order::with(['user', 'subOrders.seller'])->latest('id')->take(20)->get();
        $reviews = Review::with(['user', 'product'])->latest('id')->take(20)->get();
        $coupons = Coupon::latest('id')->get();
        $banners = Banner::orderBy('sort')->get();
        $usersCount = User::count();
        $productsCount = Product::count();

        return view('admin.dashboard', [
            'metrics' => $metrics,
            'sellers' => $sellers,
            'categories' => $categories,
            'orders' => $orders,
            'reviews' => $reviews,
            'coupons' => $coupons,
            'banners' => $banners,
            'usersCount' => $usersCount,
            'productsCount' => $productsCount,
        ]);
    }

    public function approveSeller(Seller $seller): RedirectResponse
    {
        $this->sellerAction->approve($seller);

        return back()->with('success', "{$seller->store_name} mağazası onaylandı ve satıcıya e-posta gönderildi.");
    }

    public function rejectSeller(Seller $seller): RedirectResponse
    {
        $this->sellerAction->reject($seller);

        return back()->with('success', "{$seller->store_name} başvurusu reddedildi.");
    }

    public function storeCategory(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Category::create([
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'],
            'slug' => Str::slug($data['name']) . '-' . Str::lower(Str::random(3)),
            'commission_rate' => number_format((float) $data['commission_rate'], 2, '.', ''),
            'sort' => (int) ($data['sort'] ?? 10),
        ]);

        return back()->with('success', 'Kategori başarıyla oluşturuldu.');
    }

    public function deleteCategory(Category $category): RedirectResponse
    {
        $category->delete();

        return back()->with('success', 'Kategori silindi.');
    }

    public function approveReview(Review $review): RedirectResponse
    {
        $review->update(['is_approved' => true]);

        return back()->with('success', 'Yorum onaylandı ve yayına alındı.');
    }

    public function exportOrders(): Response
    {
        $csv = $this->reportService->exportOrdersCsv();

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="laramart-siparisler.csv"',
        ]);
    }
}

