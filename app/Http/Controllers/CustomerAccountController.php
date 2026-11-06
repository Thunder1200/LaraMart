<?php

namespace App\Http\Controllers;

use App\Actions\PostOrderInteractionAction;
use App\Actions\SellerManagementAction;
use App\Http\Requests\SellerApplicationRequest;
use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\StoreReturnRequest;
use App\Http\Requests\StoreReviewRequest;
use App\Models\Address;
use App\Models\Favorite;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CustomerAccountController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $orders = $user->orders()->with(['subOrders.seller', 'subOrders.items'])->latest('id')->get();
        $addresses = $user->addresses()->orderByDesc('is_default')->get();
        $favorites = $user->favoriteProducts()->with(['seller', 'variants', 'brand'])->get();
        $notifications = $user->notifications()->latest()->take(15)->get();

        return view('account.dashboard', [
            'user' => $user,
            'orders' => $orders,
            'addresses' => $addresses,
            'favorites' => $favorites,
            'notifications' => $notifications,
        ]);
    }

    public function showOrder(Order $order): View
    {
        Gate::authorize('view', $order);

        $order->load(['subOrders.seller', 'subOrders.items.variant.product', 'subOrders.items.returnRequest', 'subOrders.items.review', 'payments']);

        return view('account.order-detail', [
            'order' => $order,
        ]);
    }

    public function storeAddress(StoreAddressRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if (! empty($data['is_default']))
        {
            $user->addresses()->update(['is_default' => false]);
        }

        $user->addresses()->create([
            'title' => $data['title'],
            'city' => $data['city'],
            'district' => $data['district'],
            'full_address' => $data['full_address'],
            'is_default' => (bool) ($data['is_default'] ?? false),
        ]);

        return back()->with('success', 'Yeni teslimat adresi eklendi.');
    }

    public function deleteAddress(Address $address): RedirectResponse
    {
        Gate::authorize('delete', $address);
        $address->delete();

        return back()->with('success', 'Adres silindi.');
    }

    public function toggleFavorite(Product $product, Request $request): RedirectResponse
    {
        $user = $request->user();
        $existing = Favorite::where('user_id', $user->id)->where('product_id', $product->id)->first();

        if ($existing)
        {
            $existing->delete();

            return back()->with('success', 'Ürün favorilerinizden çıkarıldı.');
        }

        Favorite::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        return back()->with('success', 'Ürün favorilerinize eklendi.');
    }

    public function submitSellerApplication(SellerApplicationRequest $request, SellerManagementAction $action): RedirectResponse
    {
        $action->apply($request->user(), $request->validated());

        return back()->with('success', 'Satıcı başvurunuz alındı. Yönetici onayından sonra mağazanız aktif olacaktır.');
    }

    public function requestReturn(OrderItem $orderItem, StoreReturnRequest $request, PostOrderInteractionAction $action): RedirectResponse
    {
        try
        {
            $action->createReturnRequest($request->user(), $orderItem, $request->validated('reason'));

            return back()->with('success', 'İade talebiniz satıcıya iletildi.');
        }
        catch (\Throwable $e)
        {
            return back()->withErrors(['return' => $e->getMessage()]);
        }
    }

    public function submitReview(OrderItem $orderItem, StoreReviewRequest $request, PostOrderInteractionAction $action): RedirectResponse
    {
        try
        {
            $action->createReview(
                $request->user(),
                $orderItem,
                (int) $request->validated('rating'),
                (string) $request->validated('comment')
            );

            return back()->with('success', 'Yorumunuz kaydedildi. Yönetici onayından sonra yayınlanacaktır.');
        }
        catch (\Throwable $e)
        {
            return back()->withErrors(['review' => $e->getMessage()]);
        }
    }
}

