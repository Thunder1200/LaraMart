<?php

namespace App\Actions\Orders;

use App\Enums\SubOrderStatus;
use App\Models\ProductVariant;
use App\Models\SubOrder;
use App\Notifications\SubOrderStatusUpdatedNotification;
use DomainException;
use Illuminate\Support\Facades\DB;

class UpdateSubOrderStatusAction
{
    public function execute(
        SubOrder $subOrder,
        SubOrderStatus $nextStatus,
        ?string $cargoCompany = null,
        ?string $trackingNumber = null
    ): SubOrder {
        $currentStatus = $subOrder->status;

        if (! $currentStatus->canTransitionTo($nextStatus)) {
            throw new DomainException(sprintf(
                'Geçersiz durum geçişi: "%s" durumundan "%s" durumuna geçilemez.',
                $currentStatus->label(),
                $nextStatus->label()
            ));
        }

        if ($nextStatus === SubOrderStatus::Shipped) {
            $cargoCompany = trim((string) $cargoCompany);
            $trackingNumber = trim((string) $trackingNumber);

            if ($cargoCompany === '' || $trackingNumber === '') {
                throw new DomainException('Kargoya verildi durumuna geçmek için kargo firması ve takip numarası zorunludur.');
            }
        }

        $updated = DB::transaction(function () use ($subOrder, $nextStatus, $cargoCompany, $trackingNumber): SubOrder {
            $subOrder->loadMissing(['items', 'order.user', 'seller']);

            $updates = [
                'status' => $nextStatus,
            ];

            if ($nextStatus === SubOrderStatus::Shipped) {
                $updates['cargo_company'] = $cargoCompany;
                $updates['tracking_number'] = $trackingNumber;
            }

            if ($nextStatus === SubOrderStatus::Delivered) {
                $updates['delivered_at'] = now();
            }

            if ($nextStatus === SubOrderStatus::Cancelled) {
                foreach ($subOrder->items as $item) {
                    $variant = ProductVariant::query()->lockForUpdate()->find($item->variant_id);
                    if ($variant !== null) {
                        $variant->increment('stock', $item->quantity);
                    }
                }
            }

            $subOrder->update($updates);

            return $subOrder->refresh();
        });

        $customer = $updated->order->user;
        if ($customer !== null) {
            $customer->notify(new SubOrderStatusUpdatedNotification($updated, $currentStatus, $nextStatus));
        }

        return $updated;
    }
}
