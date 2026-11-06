<?php

namespace App\Filament\Resources;

use App\Enums\SellerStatus;
use App\Models\Seller;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SellerResource extends Resource
{
    protected static ?string $model = Seller::class;

    protected static ?string $navigationLabel = 'Satıcılar';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('store_name')->searchable()->label('Mağaza Adı'),
                TextColumn::make('tax_number')->label('Vergi No'),
                TextColumn::make('iban')->label('IBAN'),
                TextColumn::make('status')
                    ->label('Durum')
                    ->formatStateUsing(fn (SellerStatus $state): string => $state->label()),
                TextColumn::make('commission_rate')->label('Komisyon (%)'),
            ]);
    }
}
