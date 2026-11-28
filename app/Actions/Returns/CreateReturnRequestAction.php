<?php

namespace App\Actions\Returns;

use App\Enums\SubOrderStatus;
use App\Models\OrderItem;
use App\Models\ReturnRequest;
use App\Models\User;
use DomainException;

class CreateReturnRequestAction
{
    public function execute(User $user, OrderItem $orderItem, string $reason): ReturnRequest
    {
        $orderItem->loadMissing(['subOrder.order', 'returnRequest']);

        if ($orderItem->subOrder->order->user_id !== $user->id) {
            throw new DomainException('Bu sipariş kalemi hesabınıza ait değildir.');
        }

        if ($orderItem->subOrder->status !== SubOrderStatus::Delivered) {
            throw new DomainException('Yalnızca teslim edilmiş ürünler için iade talebi oluşturulabilir.');
        }

        if (! $orderItem->subOrder->isReturnWindowOpen()) {
            throw new DomainException('Teslim tarihinden itibaren 14 günlük iade süresi dolmuştur.');
        }

        if ($orderItem->returnRequest !== null) {
            throw new DomainException('Bu ürün için zaten aktif bir iade talebiniz bulunuyor.');
        }

        return ReturnRequest::create([
            'order_item_id' => $orderItem->id,
            'reason' => trim($reason),
            'status' => 'pending',
            'refund_amount' => round((float) $orderItem->price * $orderItem->quantity, 2),
        ]);
    }
}
