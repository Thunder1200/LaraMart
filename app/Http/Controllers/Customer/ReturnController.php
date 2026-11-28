<?php

namespace App\Http\Controllers\Customer;

use App\Actions\Returns\CreateReturnRequestAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreReturnRequest;
use App\Models\OrderItem;
use DomainException;
use Illuminate\Http\RedirectResponse;

class ReturnController extends Controller
{
    public function store(
        StoreReturnRequest $request,
        OrderItem $orderItem,
        CreateReturnRequestAction $action
    ): RedirectResponse {
        $orderItem->loadMissing('subOrder.order');

        try {
            $action->execute($request->user(), $orderItem, (string) $request->validated()['reason']);
        } catch (DomainException $exception) {
            return redirect()
                ->route('customer.orders.show', $orderItem->subOrder->order)
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('customer.orders.show', $orderItem->subOrder->order)
            ->with('status', 'İade talebiniz satıcıya iletildi. Değerlendirme sonucunda bildirim alacaksınız.');
    }
}
