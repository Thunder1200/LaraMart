<?php

namespace App\Actions;

use App\Enums\ReturnStatus;
use App\Enums\SubOrderStatus;
use App\Models\OrderItem;
use App\Models\ReturnRequest;
use App\Models\Review;
use App\Models\User;
use InvalidArgumentException;

class PostOrderInteractionAction
{
    public function createReturnRequest(User $user, OrderItem $orderItem, string $reason): ReturnRequest
    {
        $subOrder = $orderItem->subOrder;

        if ((int) $subOrder->order->user_id !== (int) $user->id)
        {
            throw new InvalidArgumentException('Bu siparişe ait ürün için iade talebi oluşturma yetkiniz yok.');
        }

        if ($subOrder->status !== SubOrderStatus::Delivered)
        {
            throw new InvalidArgumentException('Yalnızca teslim edilmiş ürünler için iade talebi açılabilir.');
        }

        if ($subOrder->updated_at->diffInDays(now()) > 14)
        {
            throw new InvalidArgumentException('Teslimattan itibaren 14 günlük iade süresi dolmuştur.');
        }

        $refundAmount = bcmul((string) $orderItem->price, (string) $orderItem->quantity, 2);

        return ReturnRequest::updateOrCreate(
            ['order_item_id' => $orderItem->id],
            [
                'reason' => $reason,
                'status' => ReturnStatus::Pending,
                'refund_amount' => $refundAmount,
            ]
        );
    }

    public function decideReturnRequest(ReturnRequest $returnRequest, ReturnStatus $decision): ReturnRequest
    {
        $returnRequest->update(['status' => $decision]);

        if ($decision === ReturnStatus::Approved)
        {
            $subOrder = $returnRequest->orderItem->subOrder;
            if ($subOrder->status === SubOrderStatus::Delivered)
            {
                $subOrder->update(['status' => SubOrderStatus::Returned]);
            }
        }

        return $returnRequest->fresh();
    }

    public function createReview(User $user, OrderItem $orderItem, int $rating, string $comment): Review
    {
        $subOrder = $orderItem->subOrder;

        if ((int) $subOrder->order->user_id !== (int) $user->id)
        {
            throw new InvalidArgumentException('Yalnızca kendi satın aldığınız ürünü değerlendirebilirsiniz.');
        }

        if ($subOrder->status !== SubOrderStatus::Delivered)
        {
            throw new InvalidArgumentException('Yalnızca teslim edilmiş ürünler için yorum yazabilirsiniz.');
        }

        return Review::updateOrCreate(
            ['order_item_id' => $orderItem->id],
            [
                'user_id' => $user->id,
                'product_id' => $orderItem->variant->product_id,
                'rating' => $rating,
                'comment' => $comment,
                'is_approved' => false,
            ]
        );
    }
}

