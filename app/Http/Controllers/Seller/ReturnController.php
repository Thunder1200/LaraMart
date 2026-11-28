<?php

namespace App\Http\Controllers\Seller;

use App\Actions\Returns\DecideReturnRequestAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\DecideReturnRequest;
use App\Models\ReturnRequest;
use App\Models\Seller;
use App\Models\SubOrder;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ReturnController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', SubOrder::class);

        /** @var Seller $seller */
        $seller = $request->user()->seller;

        $returnRequests = ReturnRequest::query()
            ->whereHas('orderItem.subOrder', fn (Builder $query) => $query->where('seller_id', $seller->id))
            ->with(['orderItem.subOrder.order.user', 'orderItem.variant.product'])
            ->latest('id')
            ->paginate(15);

        return view('seller.returns.index', [
            'seller' => $seller,
            'returnRequests' => $returnRequests,
        ]);
    }

    public function decide(
        DecideReturnRequest $request,
        ReturnRequest $returnRequest,
        DecideReturnRequestAction $action
    ): RedirectResponse {
        $decision = (string) $request->validated()['decision'];

        try {
            $action->execute($returnRequest, $decision);
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with(
            'status',
            $decision === 'approved'
                ? 'İade talebi onaylandı, stok güncellendi ve müşteriye bildirim gönderildi.'
                : 'İade talebi reddedildi ve müşteriye bildirim gönderildi.'
        );
    }
}
