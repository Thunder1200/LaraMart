<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Seller;
use App\Models\SubOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getAdminMetrics(): array
    {
        $totalRevenue = (float) Order::query()
            ->where('payment_status', PaymentStatus::Paid->value)
            ->sum('total');

        $todayRevenue = (float) Order::query()
            ->where('payment_status', PaymentStatus::Paid->value)
            ->whereDate('created_at', today())
            ->sum('total');

        $totalOrders = Order::query()
            ->where('payment_status', PaymentStatus::Paid->value)
            ->count();

        $newUsersCount = User::query()
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        $totalCommission = (float) SubOrder::query()
            ->whereHas('order', function ($query)
            {
                $query->where('payment_status', PaymentStatus::Paid->value);
            })
            ->sum('commission_amount');

        $topProducts = OrderItem::query()
            ->select('product_name', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(price * quantity) as total_amount'))
            ->groupBy('product_name')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();

        $dailySales = Order::query()
            ->select(DB::raw('DATE(created_at) as sale_date'), DB::raw('SUM(total) as daily_total'))
            ->where('payment_status', PaymentStatus::Paid->value)
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('sale_date')
            ->get();

        return [
            'total_revenue' => number_format($totalRevenue, 2, '.', ''),
            'today_revenue' => number_format($todayRevenue, 2, '.', ''),
            'total_orders' => $totalOrders,
            'new_users_count' => $newUsersCount,
            'total_commission' => number_format($totalCommission, 2, '.', ''),
            'top_products' => $topProducts,
            'daily_sales' => $dailySales,
        ];
    }

    public function getSellerMetrics(Seller $seller): array
    {
        $paidSubOrders = SubOrder::query()
            ->where('seller_id', $seller->id)
            ->whereHas('order', function ($query)
            {
                $query->where('payment_status', PaymentStatus::Paid->value);
            });

        $grossRevenue = (float) (clone $paidSubOrders)->sum('subtotal');
        $commissionTotal = (float) (clone $paidSubOrders)->sum('commission_amount');
        $netEarnings = round($grossRevenue - $commissionTotal, 2);

        $payouts = $seller->payouts()->latest('id')->get();

        return [
            'gross_revenue' => number_format($grossRevenue, 2, '.', ''),
            'commission_total' => number_format($commissionTotal, 2, '.', ''),
            'net_earnings' => number_format($netEarnings, 2, '.', ''),
            'sub_order_count' => (clone $paidSubOrders)->count(),
            'payouts' => $payouts,
        ];
    }

    public function exportOrdersCsv(): string
    {
        $orders = Order::query()
            ->with(['user', 'subOrders.seller'])
            ->latest('id')
            ->get();

        $lines = [];
        $lines[] = implode(',', [
            'Siparis No',
            'Musteri',
            'E-Posta',
            'Ara Toplam',
            'Indirim',
            'Kargo',
            'Genel Toplam',
            'Odeme Durumu',
            'Alt Siparis Sayisi',
            'Tarih',
        ]);

        foreach ($orders as $order)
        {
            $lines[] = implode(',', [
                $order->order_number,
                '"' . str_replace('"', '""', $order->user?->name ?? '') . '"',
                $order->user?->email ?? '',
                $order->subtotal,
                $order->discount,
                $order->shipping,
                $order->total,
                $order->payment_status->value,
                $order->subOrders->count(),
                $order->created_at?->format('Y-m-d H:i:s'),
            ]);
        }

        return implode("\n", $lines);
    }
}

