<?php

namespace App\Enums;

enum CouponType: string
{
    case Percentage = 'percentage';
    case Fixed = 'fixed';

    public function label(): string
    {
        return match ($this)
        {
            self::Percentage => 'Yüzde İndirimi',
            self::Fixed => 'Tutar İndirimi',
        };
    }
}
