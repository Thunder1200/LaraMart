<?php

namespace App\Actions;

use App\Enums\PaymentStatus;
use App\Enums\SubOrderStatus;
use App\Mail\OrderConfirmedMail;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\SubOrder;
use App\Models\User;
use App\Services\CalculateCartTotalsService;
use App\Services\CommissionCalculatorService;
use App\Services\IyzicoPaymentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;

class CreateOrderAction
{
    public function __construct(
        private readonly CalculateCartTotalsService $cartTotalsService,
        private readonly CommissionCalculatorService $commissionService,
        private readonly IyzicoPaymentService $paymentService,
    ) {
    }

    public function execute(User $user, Cart $cart, Address $address, array $paymentPayload = []): Order
    {
        $idempotencyKey = $paymentPayload['idempotency_key'] ?? null;

        if ($idempotencyKey)
        {
            $existingOrder = Order::where('idempotency_key', $idempotencyKey)->first();
            if ($existingOrder)
            {
                return $existingOrder->load(['subOrders.items', 'payments']);
            }
        }

        $cart->load(['items.variant.product.seller', 'items.variant.product.category']);

        if ($cart->items->isEmpty())
        {
            throw new RuntimeException('Sepetiniz boş olduğu için sipariş oluşturulamaz.');
        }

        $totals = $this->cartTotalsService->calculate($cart);

        $paymentResult = $this->paymentService->chargeSandbox([
            'amount' => $totals['total'],
            'basket_id' => 'CART-' . $cart->id,
            'card_number' => $paymentPayload['card_number'] ?? '5528790000000008',
            'simulate_failure' => $paymentPayload['simulate_failure'] ?? false,
        ]);

        if (! $paymentResult['success'])
        {
            throw new RuntimeException($paymentResult['error_message'] ?? 'Ödeme başarısız oldu.');
        }

        $order = DB::transaction(function () use ($user, $cart, $address, $totals, $paymentResult, $idempotencyKey)
        {
            if ($idempotencyKey)
            {
                $alreadyCreated = Order::where('idempotency_key', $idempotencyKey)->lockForUpdate()->first();
                if ($alreadyCreated)
                {
                    return $alreadyCreated;
                }
            }

            $sortedItems = $cart->items->sortBy('variant_id')->values();
            $lockedVariants = [];

            foreach ($sortedItems as $cartItem)
            {
                $variant = ProductVariant::where('id', $cartItem->variant_id)
                    ->lockForUpdate()
                    ->first();

                if (! $variant || $variant->stock < $cartItem->quantity)
                {
                    $productTitle = $variant?->product?->name ?? 'Ürün';
                    throw new RuntimeException("{$productTitle} için yeterli stok kalmadı (Kalan: " . ($variant?->stock ?? 0) . ').');
                }

                $lockedVariants[$variant->id] = $variant;
            }

            foreach ($sortedItems as $cartItem)
            {
                $variant = $lockedVariants[$cartItem->variant_id];
                $variant->decrement('stock', $cartItem->quantity);
            }

            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'LM-' . strtoupper(Str::random(10)),
                'idempotency_key' => $idempotencyKey,
                'address' => $address->toSnapshot(),
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'shipping' => $totals['shipping'],
                'total' => $totals['total'],
                'payment_status' => PaymentStatus::Paid,
                'coupon_code' => $totals['coupon_code'],
            ]);

            $itemsBySeller = $cart->items->groupBy(fn ($item) => $item->variant->product->seller_id);

            foreach ($itemsBySeller as $sellerId => $sellerCartItems)
            {
                $sellerSubtotal = 0.00;
                $totalCommission = 0.00;
                $sellerModel = $sellerCartItems->first()->variant->product->seller;

                foreach ($sellerCartItems as $sellerItem)
                {
                    $lockedVariant = $lockedVariants[$sellerItem->variant_id];
                    $unitPrice = (float) $lockedVariant->effectivePrice();
                    $lineTotal = round($unitPrice * $sellerItem->quantity, 2);
                    $sellerSubtotal = round($sellerSubtotal + $lineTotal, 2);

                    $commissionData = $this->commissionService->calculateForSubOrder(
                        $lineTotal,
                        $lockedVariant->product->category,
                        $sellerModel
                    );

                    $totalCommission = round($totalCommission + (float) $commissionData['commission'], 2);
                }

                $sellerShipping = $sellerSubtotal >= CalculateCartTotalsService::FREE_SHIPPING_THRESHOLD
                    ? 0.00
                    : CalculateCartTotalsService::STANDARD_SHIPPING_FEE;

                $subOrder = SubOrder::create([
                    'order_id' => $order->id,
                    'seller_id' => $sellerId,
                    'status' => SubOrderStatus::PendingApproval,
                    'subtotal' => number_format($sellerSubtotal, 2, '.', ''),
                    'shipping' => number_format($sellerShipping, 2, '.', ''),
                    'commission_amount' => number_format($totalCommission, 2, '.', ''),
                ]);

                foreach ($sellerCartItems as $sellerItem)
                {
                    $lockedVariant = $lockedVariants[$sellerItem->variant_id];
                    $unitPrice = $lockedVariant->effectivePrice();

                    OrderItem::create([
                        'sub_order_id' => $subOrder->id,
                        'variant_id' => $lockedVariant->id,
                        'product_name' => $lockedVariant->product->name,
                        'price' => $unitPrice,
                        'quantity' => $sellerItem->quantity,
                    ]);
                }
            }

            Payment::create([
                'order_id' => $order->id,
                'provider' => 'iyzico',
                'transaction_id' => $paymentResult['transaction_id'],
                'amount' => $totals['total'],
                'status' => PaymentStatus::Paid,
                'raw_response' => $paymentResult['raw_response'],
            ]);

            if ($totals['coupon_code'])
            {
                Coupon::where('code', $totals['coupon_code'])->increment('used_count');
            }

            $cart->items()->delete();
            $cart->update(['coupon_code' => null]);

            return $order->load(['subOrders.items', 'payments']);
        });

        Mail::to($user->email)->queue(new OrderConfirmedMail($order));

        return $order;
    }
}

