<?php

namespace App\Enums;

enum SubOrderStatus: string
{
    case PendingApproval = 'pending_approval';
    case Preparing = 'preparing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Returned = 'returned';

    public function label(): string
    {
        return match ($this)
        {
            self::PendingApproval => 'Onay Bekliyor',
            self::Preparing => 'Hazırlanıyor',
            self::Shipped => 'Kargoya Verildi',
            self::Delivered => 'Teslim Edildi',
            self::Cancelled => 'İptal',
            self::Returned => 'İade',
        };
    }

    public function allowedTransitions(): array
    {
        return match ($this)
        {
            self::PendingApproval => [self::Preparing, self::Cancelled],
            self::Preparing => [self::Shipped, self::Cancelled],
            self::Shipped => [self::Delivered],
            self::Delivered => [self::Returned],
            self::Cancelled => [],
            self::Returned => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }
}

