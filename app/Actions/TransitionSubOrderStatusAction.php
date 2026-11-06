<?php

namespace App\Actions;

use App\Enums\SubOrderStatus;
use App\Models\SubOrder;
use App\Notifications\SubOrderStatusChangedNotification;
use InvalidArgumentException;

class TransitionSubOrderStatusAction
{
    public function execute(
        SubOrder $subOrder,
        SubOrderStatus $targetStatus,
        ?string $cargoCompany = null,
        ?string $trackingNumber = null
    ): SubOrder {
        $currentStatus = $subOrder->status;

        if (! $currentStatus->canTransitionTo($targetStatus))
        {
            throw new InvalidArgumentException(
                "Geçersiz sipariş durumu geçişi: {$currentStatus->label()} durumundan {$targetStatus->label()} durumuna geçilemez."
            );
        }

        $attributes = [
            'status' => $targetStatus,
        ];

        if ($cargoCompany !== null)
        {
            $attributes['cargo_company'] = $cargoCompany;
        }

        if ($trackingNumber !== null)
        {
            $attributes['tracking_number'] = $trackingNumber;
        }

        $subOrder->update($attributes);
        $subOrder->loadMissing(['order.user', 'seller']);

        if ($subOrder->order && $subOrder->order->user)
        {
            $subOrder->order->user->notify(new SubOrderStatusChangedNotification($subOrder));
        }

        return $subOrder->fresh();
    }
}
