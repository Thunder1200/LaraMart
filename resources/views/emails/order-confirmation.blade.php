<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>LaraMart Sipariş Onayı</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.6; padding: 20px;">
    <h2 style="color: #4338ca;">Merhaba {{ $order->user->name }}, siparişiniz alındı!</h2>
    <p>
        <strong>Sipariş Numaranız:</strong> {{ $order->order_number }}<br>
        <strong>Toplam Tutar:</strong> {{ number_format((float) $order->total, 2, ',', '.') }} TL
    </p>

    <h3>Satıcı Paketleriniz</h3>
    @foreach ($order->subOrders as $subOrder)
        <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; margin-bottom: 12px;">
            <strong>Satıcı: {{ $subOrder->seller->store_name }}</strong>
            <ul>
                @foreach ($subOrder->items as $item)
                    <li>{{ $item->quantity }}x {{ $item->product_name }} — {{ number_format((float) $item->price, 2, ',', '.') }} TL</li>
                @endforeach
            </ul>
        </div>
    @endforeach

    <p>Siparişinizi hesabım sayfanızdaki <strong>Siparişlerim</strong> bölümünden takip edebilirsiniz.</p>
    <p>Teşekkür ederiz,<br><strong>LaraMart Ekibi</strong></p>
</body>
</html>
