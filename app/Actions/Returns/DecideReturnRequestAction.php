<?php

namespace App\Actions\Returns;

use App\Actions\Orders\UpdateSubOrderStatusAction;
use App\Enums\SubOrderStatus;
use App\Models\ProductVariant;
use App\Models\ReturnRequest;
use App\Notifications\ReturnRequestDecidedNotification;
use DomainException;
use Illuminate\Support\Facades\DB;

class DecideReturnRequestAction
{
    public function __construct(private readonly UpdateSubOrderStatusAction $updateSubOrderStatus) {}

    public function execute(ReturnRequest $returnRequest, string $decision): ReturnRequest
    {
        if (! in_array($decision, ['approved', 'rejected'], true)) {
            throw new DomainException('Geçersiz iade kararı.');
        }

        if ($returnRequest->status !== 'pending') {
            throw new DomainException('Bu iade talebi zaten sonuçlandırılmış.');
        }

        $subOrderToMarkReturned = null;

        $updated = DB::transaction(function () use ($returnRequest, $decision, &$subOrderToMarkReturned): ReturnRequest {
            $returnRequest->loadMissing(['orderItem.subOrder.items.returnRequest', 'orderItem.subOrder.order.user']);

            $returnRequest->update([
                'status' => $decision,
            ]);

            if ($decision === 'approved') {
                $orderItem = $returnRequest->orderItem;
                $variant = ProductVariant::query()->lockForUpdate()->find($orderItem->variant_id);
                if ($variant !== null) {
                    $variant->increment('stock', $orderItem->quantity);
                }

                $subOrder = $orderItem->subOrder;
                if ($subOrder->status === SubOrderStatus::Delivered) {
                    $allReturned = $subOrder->items->every(
                        fn ($item): bool => $item->id === $orderItem->id || $item->returnRequest?->status === 'approved'
                    );

                    if ($allReturned) {
                        $subOrderToMarkReturned = $subOrder;
                    }
                }
            }

            return $returnRequest->refresh();
        });

        if ($subOrderToMarkReturned !== null) {
            $this->updateSubOrderStatus->execute($subOrderToMarkReturned, SubOrderStatus::Returned);
        }

        $customer = $updated->orderItem->subOrder->order->user;
        if ($customer !== null) {
            $customer->notify(new ReturnRequestDecidedNotification($updated));
        }

        return $updated;
    }
}
