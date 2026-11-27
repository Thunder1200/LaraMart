<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = $request->user()
            ->orders()
            ->with(['subOrders.seller', 'subOrders.items'])
            ->latest('id')
            ->paginate(10);

        return view('customer.orders.index', [
            'orders' => $orders,
        ]);
    }

    public function show(Order $order): View
    {
        Gate::authorize('view', $order);

        $order->load(['coupon', 'payments', 'subOrders.seller', 'subOrders.items']);

        return view('customer.orders.show', [
            'order' => $order,
        ]);
    }
}
