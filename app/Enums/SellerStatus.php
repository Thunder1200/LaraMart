<?php

namespace App\Enums;

enum SellerStatus: string
{
    case Pending = 'beklemede';
    case Approved = 'onayli';
    case Rejected = 'reddedildi';

    public function label(): string
    {
        return match ($this)
        {
            self::Pending => 'Beklemede',
            self::Approved => 'Onaylı',
            self::Rejected => 'Reddedildi',
        };
    }
}
