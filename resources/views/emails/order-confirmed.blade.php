<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sipariş Onayı</title>
</head>
<body>
    <h1>LaraMart Siparişiniz Alındı (#{{ $order->order_number }})</h1>
    <p>Toplam Tutar: {{ $order->total }} TL</p>
    <p>Alt Sipariş Sayısı: {{ $order->subOrders->count() }}</p>
</body>
</html>

