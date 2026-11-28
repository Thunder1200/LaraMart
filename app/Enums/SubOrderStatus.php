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

    /**
     * Bu durumdan izin verilen bir sonraki durumlar.
     *
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::PendingApproval => [self::Preparing, self::Cancelled],
            self::Preparing => [self::Shipped, self::Cancelled],
            self::Shipped => [self::Delivered],
            self::Delivered => [self::Returned],
            self::Cancelled => [],
            self::Returned => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), true);
    }

    public function label(): string
    {
        return match ($this) {
            self::PendingApproval => 'Onay Bekliyor',
            self::Preparing => 'Hazırlanıyor',
            self::Shipped => 'Kargoya Verildi',
            self::Delivered => 'Teslim Edildi',
            self::Cancelled => 'İptal Edildi',
            self::Returned => 'İade Edildi',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PendingApproval => 'bg-amber-100 text-amber-800',
            self::Preparing => 'bg-indigo-100 text-indigo-800',
            self::Shipped => 'bg-sky-100 text-sky-800',
            self::Delivered => 'bg-emerald-100 text-emerald-800',
            self::Cancelled => 'bg-rose-100 text-rose-800',
            self::Returned => 'bg-purple-100 text-purple-800',
        };
    }

    public function progressStep(): int
    {
        return match ($this) {
            self::PendingApproval => 1,
            self::Preparing => 2,
            self::Shipped => 3,
            self::Delivered, self::Returned => 4,
            self::Cancelled => 0,
        };
    }

    /**
     * @return list<string>
     */
    public static function cargoCompanies(): array
    {
        return [
            'Yurtiçi Kargo',
            'Aras Kargo',
            'MNG Kargo',
            'PTT Kargo',
            'Sürat Kargo',
            'HepsiJET',
            'Trendyol Express',
        ];
    }
}
