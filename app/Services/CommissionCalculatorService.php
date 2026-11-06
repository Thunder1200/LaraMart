<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Seller;

class CommissionCalculatorService
{
    public function calculateForSubOrder(float $subtotal, ?Category $category = null, ?Seller $seller = null): array
    {
        $rate = 10.00;

        if ($category !== null && (float) $category->commission_rate > 0)
        {
            $rate = (float) $category->commission_rate;
        }
        elseif ($seller !== null && (float) $seller->commission_rate > 0)
        {
            $rate = (float) $seller->commission_rate;
        }

        $commission = round($subtotal * ($rate / 100), 2);
        $net = round($subtotal - $commission, 2);

        return [
            'rate' => number_format($rate, 2, '.', ''),
            'gross' => number_format($subtotal, 2, '.', ''),
            'commission' => number_format($commission, 2, '.', ''),
            'net' => number_format($net, 2, '.', ''),
        ];
    }
}

