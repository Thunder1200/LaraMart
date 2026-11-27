<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\Orders\CreateOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StartCheckoutRequest;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use App\Services\CartCalculator;
use App\Services\CartService;
use App\Services\Payment\IyzicoSandboxService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function show(Request $request, CartService $cartService, CartCalculator $calculator): View|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $cart = $cartService->findCurrentCart($user);

        $items = $cart !== null
            ? $cart->items()
                ->with(['variant.product.seller', 'variant.product.brand', 'variant.product.media'])
                ->get()
            : collect();

        if ($items->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Ödeme adımına geçmek için sepetinizde en az bir ürün olmalıdır.');
        }

        $couponCode = session()->get(CartService::APPLIED_COUPON_SESSION_KEY);
        $coupon = is_string($couponCode) && $couponCode !== ''
            ? Coupon::query()->where('code', $couponCode)->first()
            : null;

        try {
            $summary = $calculator->calculate($items, $coupon);
        } catch (DomainException) {
            $coupon = null;
            session()->forget(CartService::APPLIED_COUPON_SESSION_KEY);
            $summary = $calculator->calculate($items);
        }

        $addresses = $user->addresses()
            ->orderByDesc('is_default')
            ->latest('id')
            ->get();

        $idempotencyKey = (string) session()->get('checkout_idempotency_key', '');
        if ($idempotencyKey === '') {
            $idempotencyKey = (string) Str::uuid();
            session()->put('checkout_idempotency_key', $idempotencyKey);
        }

        return view('storefront.checkout.index', [
            'summary' => $summary,
            'appliedCoupon' => $coupon,
            'addresses' => $addresses,
            'idempotencyKey' => $idempotencyKey,
        ]);
    }

    public function start(
        StartCheckoutRequest $request,
        CartService $cartService,
        CartCalculator $calculator,
        IyzicoSandboxService $iyzico
    ): RedirectResponse|Response {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validated();
        $idempotencyKey = (string) $validated['idempotency_key'];

        // Aynı checkout formu iki kez gönderilirse mevcut siparişe yönlendir (idempotency)
        $existingOrder = Order::query()
            ->where('user_id', $user->id)
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if ($existingOrder !== null) {
            return redirect()
                ->route('customer.orders.show', $existingOrder)
                ->with('status', 'Siparişiniz zaten alınmıştı; mevcut sipariş detayınız gösteriliyor.');
        }

        $cart = $cartService->findCurrentCart($user);
        $items = $cart !== null
            ? $cart->items()->with(['variant.product.seller', 'variant.product.category'])->get()
            : collect();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Sepetiniz boş.');
        }

        $couponCode = session()->get(CartService::APPLIED_COUPON_SESSION_KEY);
        $coupon = is_string($couponCode) && $couponCode !== ''
            ? Coupon::query()->where('code', $couponCode)->first()
            : null;

        try {
            $summary = $calculator->calculate($items, $coupon);
        } catch (DomainException $exception) {
            return redirect()->route('checkout.error')->with('payment_error', $exception->getMessage());
        }

        $address = Address::query()
            ->where('user_id', $user->id)
            ->findOrFail($validated['address_id']);

        $initResult = $iyzico->initializeThreeDs([
            'user_id' => $user->id,
            'idempotency_key' => $idempotencyKey,
            'subtotal' => $summary['subtotal'],
            'total' => $summary['total'],
            'callback_url' => route('checkout.iyzico.callback'),
            'card_holder' => $validated['card_holder'],
            'card_number' => $validated['card_number'],
            'expire_month' => $validated['expire_month'],
            'expire_year' => $validated['expire_year'],
            'cvc' => $validated['cvc'],
            'buyer' => [
                'id' => (string) $user->id,
                'name' => $user->name,
                'surname' => $user->name,
                'email' => $user->email,
                'identityNumber' => '11111111111',
                'registrationAddress' => $address->full_address,
                'ip' => $request->ip() ?? '127.0.0.1',
                'city' => $address->city,
                'country' => 'Turkey',
            ],
            'shipping_address' => [
                'contactName' => $user->name,
                'city' => $address->city,
                'country' => 'Turkey',
                'address' => $address->full_address,
            ],
            'basket_items' => $items->map(fn ($item): array => [
                'id' => (string) $item->variant_id,
                'name' => $item->variant->product->name,
                'category1' => $item->variant->product->category->name,
                'itemType' => 'PHYSICAL',
                'price' => number_format($item->variant->effectivePrice() * $item->quantity, 2, '.', ''),
            ])->values()->all(),
        ]);

        if ($initResult['status'] !== 'success') {
            return redirect()
                ->route('checkout.error')
                ->with('payment_error', $initResult['error_message'] ?? 'Ödeme başlatılamadı.');
        }

        $token = $initResult['token'];

        Cache::put('checkout_3ds:'.$token, [
            'user_id' => $user->id,
            'address_id' => $address->id,
            'idempotency_key' => $idempotencyKey,
            'coupon_code' => $coupon?->code,
            'total' => $summary['total'],
            'card_last4' => substr((string) $validated['card_number'], -4),
        ], now()->addMinutes(15));

        if ($initResult['mode'] === 'iyzico_sandbox' && is_string($initResult['three_ds_html'])) {
            return response($initResult['three_ds_html']);
        }

        return redirect()->route('checkout.3ds', ['token' => $token]);
    }

    public function threeDsChallenge(string $token): View|RedirectResponse
    {
        $context = Cache::get('checkout_3ds:'.$token);

        if (! is_array($context)) {
            return redirect()
                ->route('checkout.error')
                ->with('payment_error', '3D Secure oturum süresi dolmuş veya geçersiz.');
        }

        return view('storefront.checkout.three-ds', [
            'token' => $token,
            'total' => (float) $context['total'],
            'cardLast4' => (string) $context['card_last4'],
        ]);
    }

    public function callback(
        Request $request,
        IyzicoSandboxService $iyzico,
        CreateOrderAction $createOrderAction
    ): RedirectResponse {
        $token = (string) ($request->input('token') ?? $request->input('paymentId') ?? '');
        $context = $token !== '' ? Cache::get('checkout_3ds:'.$token) : null;

        if (! is_array($context)) {
            return redirect()
                ->route('checkout.error')
                ->with('payment_error', 'Ödeme doğrulama oturumu bulunamadı veya süresi doldu.');
        }

        $actionChoice = (string) $request->input('action', 'approve');
        $smsCode = trim((string) $request->input('sms_code', '123456'));

        if ($actionChoice === 'reject' || ($request->has('sms_code') && $smsCode !== '123456')) {
            Cache::forget('checkout_3ds:'.$token);

            return redirect()
                ->route('checkout.error')
                ->with('payment_error', '3D Secure doğrulaması başarısız oldu veya iptal edildi. Hiçbir kayıt değiştirilmedi.');
        }

        $callbackPayload = $request->all();
        if (! $request->has('status')) {
            $callbackPayload['status'] = 'success';
            $callbackPayload['mdStatus'] = '1';
            $callbackPayload['paymentId'] = $token;
        }

        $verification = $iyzico->verifyThreeDsCallback($callbackPayload);

        if ($verification['status'] !== 'success') {
            Cache::forget('checkout_3ds:'.$token);

            return redirect()
                ->route('checkout.error')
                ->with('payment_error', $verification['error_message'] ?? 'Ödeme reddedildi.');
        }

        $user = User::query()->findOrFail($context['user_id']);
        $address = Address::query()->findOrFail($context['address_id']);

        try {
            $order = $createOrderAction->execute(
                user: $user,
                address: $address,
                idempotencyKey: (string) $context['idempotency_key'],
                paymentData: [
                    'provider' => 'iyzico',
                    'transaction_id' => $verification['transaction_id'],
                    'raw_response' => $verification['raw_response'],
                ],
                couponCode: is_string($context['coupon_code']) ? $context['coupon_code'] : null,
            );
        } catch (DomainException $exception) {
            Cache::forget('checkout_3ds:'.$token);

            return redirect()
                ->route('checkout.error')
                ->with('payment_error', $exception->getMessage());
        }

        Cache::forget('checkout_3ds:'.$token);
        session()->forget([
            CartService::APPLIED_COUPON_SESSION_KEY,
            'checkout_idempotency_key',
        ]);

        return redirect()
            ->route('customer.orders.show', $order)
            ->with('status', "Siparişiniz başarıyla oluşturuldu! Sipariş numaranız: {$order->order_number}");
    }

    public function error(): View
    {
        return view('storefront.checkout.error', [
            'errorMessage' => session('payment_error', 'Ödeme işlemi tamamlanamadı. Sepetiniz ve stoklarınız etkilenmedi.'),
        ]);
    }
}
