<?php

namespace App\Filament\Resources;

use App\Models\Order;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationLabel = 'Siparişler';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')->searchable()->label('Sipariş No'),
                TextColumn::make('user.name')->label('Müşteri'),
                TextColumn::make('subtotal')->label('Ara Toplam'),
                TextColumn::make('discount')->label('İndirim'),
                TextColumn::make('shipping')->label('Kargo'),
                TextColumn::make('total')->label('Genel Toplam'),
                TextColumn::make('created_at')->dateTime('d.m.Y H:i')->label('Tarih'),
            ]);
    }
}
