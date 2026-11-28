<?php

namespace App\Http\Controllers\Seller;

use App\Enums\SubOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\ReturnRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $seller = $request->user()?->seller?->loadCount(['products', 'subOrders']);
        $pendingOrdersCount = $seller !== null
            ? $seller->subOrders()->whereIn('status', [
                SubOrderStatus::PendingApproval->value,
                SubOrderStatus::Preparing->value,
            ])->count()
            : 0;
        $pendingReturnsCount = $seller !== null
            ? ReturnRequest::query()
                ->where('status', 'pending')
                ->whereHas('orderItem.subOrder', fn (Builder $q) => $q->where('seller_id', $seller->id))
                ->count()
            : 0;

        return view('seller.dashboard', [
            'seller' => $seller,
            'pendingOrdersCount' => $pendingOrdersCount,
            'pendingReturnsCount' => $pendingReturnsCount,
        ]);
    }
}
