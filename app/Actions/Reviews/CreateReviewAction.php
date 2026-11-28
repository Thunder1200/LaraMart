<?php

namespace App\Actions\Reviews;

use App\Enums\SubOrderStatus;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use DomainException;

class CreateReviewAction
{
    public function execute(User $user, Product $product, OrderItem $orderItem, int $rating, string $comment): Review
    {
        $orderItem->loadMissing(['subOrder.order', 'variant', 'review']);

        if ($orderItem->subOrder->order->user_id !== $user->id) {
            throw new DomainException('Yalnızca kendi siparişleriniz için değerlendirme yapabilirsiniz.');
        }

        if ($orderItem->subOrder->status !== SubOrderStatus::Delivered) {
            throw new DomainException('Yalnızca teslim edilmiş ürünler için yorum yazabilirsiniz.');
        }

        if ($orderItem->variant === null || $orderItem->variant->product_id !== $product->id) {
            throw new DomainException('Seçilen sipariş kalemi bu ürüne ait değildir.');
        }

        if ($orderItem->review !== null) {
            throw new DomainException('Bu sipariş kalemi için zaten bir değerlendirme gönderdiniz.');
        }

        return Review::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_item_id' => $orderItem->id,
            'rating' => $rating,
            'comment' => trim($comment),
            'is_approved' => false,
        ]);
    }
}
