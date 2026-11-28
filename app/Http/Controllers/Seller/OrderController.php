<?php

namespace App\Http\Controllers\Seller;

use App\Actions\Orders\UpdateSubOrderStatusAction;
use App\Enums\SubOrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\UpdateSubOrderStatusRequest;
use App\Models\Seller;
use App\Models\SubOrder;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', SubOrder::class);

        /** @var Seller $seller */
        $seller = $request->user()->seller;
        $statusFilter = (string) $request->query('durum', '');

        $query = SubOrder::query()
            ->where('seller_id', $seller->id)
            ->with(['order.user', 'items.returnRequest'])
            ->latest('id');

        if ($statusFilter !== '' && SubOrderStatus::tryFrom($statusFilter) !== null) {
            $query->where('status', $statusFilter);
        }

        return view('seller.orders.index', [
            'seller' => $seller,
            'subOrders' => $query->paginate(15)->withQueryString(),
            'statusFilter' => $statusFilter,
            'statuses' => SubOrderStatus::cases(),
        ]);
    }

    public function show(SubOrder $subOrder): View
    {
        Gate::authorize('view', $subOrder);

        $subOrder->load(['order.user', 'seller', 'items.returnRequest', 'items.variant.product']);

        return view('seller.orders.show', [
            'subOrder' => $subOrder,
            'allowedTransitions' => $subOrder->status->allowedTransitions(),
            'cargoCompanies' => SubOrderStatus::cargoCompanies(),
        ]);
    }

    public function updateStatus(
        UpdateSubOrderStatusRequest $request,
        SubOrder $subOrder,
        UpdateSubOrderStatusAction $action
    ): RedirectResponse {
        $validated = $request->validated();
        $nextStatus = SubOrderStatus::from((string) $validated['status']);

        try {
            $action->execute(
                $subOrder,
                $nextStatus,
                isset($validated['cargo_company']) ? (string) $validated['cargo_company'] : null,
                isset($validated['tracking_number']) ? (string) $validated['tracking_number'] : null,
            );
        } catch (DomainException $exception) {
            return redirect()
                ->route('seller.orders.show', $subOrder)
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('seller.orders.show', $subOrder)
            ->with('status', sprintf('Sipariş durumu "%s" olarak güncellendi ve müşteriye bildirim gönderildi.', $nextStatus->label()));
    }
}
