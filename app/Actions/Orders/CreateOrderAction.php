<?php

namespace App\Actions\Orders;

use App\Mail\OrderConfirmationMail;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\SubOrder;
use App\Models\User;
use App\Services\CartCalculator;
use DomainException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CreateOrderAction
{
    public function __construct(private readonly CartCalculator $calculator) {}

    /**
     * Ödeme onaylandığında tek bir DB transaction içinde:
     * orders + satıcı başına sub_orders + order_items oluşturur,
     * lockForUpdate() ile stoğu düşer, kuponu kullanılmış sayar,
     * payment kaydını yazar, sepeti boşaltır ve onay e-postasını kuyruğa atar.
     *
     * @param  array{
     *     provider?: string,
     *     transaction_id: string,
     *     raw_response?: array<string, mixed>
     * }  $paymentData
     */
    public function execute(
        User $user,
        Address $address,
        string $idempotencyKey,
        array $paymentData,
        ?string $couponCode = null
    ): Order {
        if ($address->user_id !== $user->id) {
            throw new DomainException('Seçilen teslimat adresi hesabınıza ait değildir.');
        }

        $lock = Cache::lock('order-create:'.$idempotencyKey, 15);

        return $lock->block(5, function () use ($user, $address, $idempotencyKey, $paymentData, $couponCode): Order {
            $existingOrder = Order::query()
                ->where('idempotency_key', $idempotencyKey)
                ->with(['subOrders.items', 'payments'])
                ->first();

            if ($existingOrder !== null) {
                return $existingOrder;
            }

            $order = DB::transaction(function () use ($user, $address, $idempotencyKey, $paymentData, $couponCode): Order {
                $existingInTx = Order::query()
                    ->where('idempotency_key', $idempotencyKey)
                    ->first();

                if ($existingInTx !== null) {
                    return $existingInTx;
                }

                $cart = Cart::query()
                    ->where('user_id', $user->id)
                    ->with(['items.variant.product.seller', 'items.variant.product.category'])
                    ->first();

                if ($cart === null || $cart->items->isEmpty()) {
                    throw new DomainException('Sipariş oluşturmak için sepetinizde en az bir ürün olmalıdır.');
                }

                // Deadlock önlemek için varyantları ID sırasına göre lockForUpdate() ile kilitle
                $variantIds = $cart->items->pluck('variant_id')->unique()->sort()->values()->all();
                $lockedVariants = ProductVariant::query()
                    ->whereIn('id', $variantIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($cart->items as $cartItem) {
                    /** @var ProductVariant|null $lockedVariant */
                    $lockedVariant = $lockedVariants->get($cartItem->variant_id);

                    if ($lockedVariant === null) {
                        throw new DomainException('Sepetinizdeki ürün varyantı artık mevcut değil.');
                    }

                    if ($lockedVariant->stock < $cartItem->quantity) {
                        throw new DomainException(sprintf(
                            '"%s" (%s) için yeterli stok kalmadı. Mevcut stok: %d adet.',
                            $cartItem->variant->product->name,
                            $lockedVariant->label(),
                            $lockedVariant->stock
                        ));
                    }

                    // Güncel kilitli varyantı ilişkiye bağla
                    $lockedVariant->setRelation('product', $cartItem->variant->product);
                    $cartItem->setRelation('variant', $lockedVariant);
                }

                $coupon = null;
                if ($couponCode !== null && $couponCode !== '') {
                    $coupon = Coupon::query()
                        ->where('code', strtoupper($couponCode))
                        ->lockForUpdate()
                        ->first();

                    if ($coupon === null) {
                        throw new DomainException('Uygulanan kupon artık geçerli değil.');
                    }
                }

                $summary = $this->calculator->calculate($cart->items, $coupon);

                // Adres kopyası (snapshot)
                $addressSnapshot = [
                    'title' => $address->title,
                    'city' => $address->city,
                    'district' => $address->district,
                    'full_address' => $address->full_address,
                    'recipient_name' => $user->name,
                    'recipient_phone' => $user->phone,
                ];

                $order = Order::create([
                    'user_id' => $user->id,
                    'coupon_id' => $coupon?->id,
                    'order_number' => $this->generateOrderNumber(),
                    'idempotency_key' => $idempotencyKey,
                    'address' => $addressSnapshot,
                    'subtotal' => $summary['subtotal'],
                    'discount' => $summary['discount'],
                    'shipping' => $summary['shipping'],
                    'total' => $summary['total'],
                    'payment_status' => 'paid',
                ]);

                // Satıcı başına 1 alt sipariş (sub_orders) + order_items (ürün adı ve fiyat kopya)
                foreach ($summary['groups'] as $group) {
                    $commissionTotal = 0.00;

                    /** @var CartItem $groupItem */
                    foreach ($group['items'] as $groupItem) {
                        $product = $groupItem->variant->product;
                        $rate = (float) ($product->seller->commission_rate ?? $product->category->commission_rate ?? 10.00);
                        $lineTotal = $groupItem->variant->effectivePrice() * $groupItem->quantity;
                        $commissionTotal += $lineTotal * ($rate / 100);
                    }

                    $subOrder = SubOrder::create([
                        'order_id' => $order->id,
                        'seller_id' => $group['seller_id'],
                        'status' => 'pending_approval',
                        'subtotal' => $group['subtotal'],
                        'shipping_amount' => $group['shipping'],
                        'commission_amount' => round($commissionTotal, 2),
                    ]);

                    foreach ($group['items'] as $groupItem) {
                        $variant = $groupItem->variant;
                        $unitPrice = $variant->effectivePrice();

                        $subOrder->items()->create([
                            'variant_id' => $variant->id,
                            'product_name' => $variant->product->name.' ('.$variant->label().')',
                            'price' => $unitPrice,
                            'quantity' => $groupItem->quantity,
                        ]);

                        // Stok düşümü (transaction + lockForUpdate altında)
                        $variant->decrement('stock', $groupItem->quantity);
                    }
                }

                // Ödeme kaydı
                Payment::create([
                    'order_id' => $order->id,
                    'provider' => $paymentData['provider'] ?? 'iyzico',
                    'transaction_id' => $paymentData['transaction_id'],
                    'amount' => $summary['total'],
                    'status' => 'success',
                    'raw_response' => $paymentData['raw_response'] ?? [],
                ]);

                // Kuponu kullanılmış say
                if ($coupon !== null) {
                    $coupon->increment('used_count');
                }

                // Sepeti boşalt
                $cart->items()->delete();

                return $order->load(['subOrders.seller', 'subOrders.items', 'payments']);
            });

            // Sipariş onay e-postasını kuyruğa (queue) at
            Mail::to($user->email)->queue(new OrderConfirmationMail($order));

            return $order;
        });
    }

    private function generateOrderNumber(): string
    {
        return 'LM-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
    }
}
