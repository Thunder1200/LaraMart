<?php

namespace App\Filament\Resources;

use App\Models\Category;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationLabel = 'Kategoriler';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->label('Kategori Adı'),
                TextColumn::make('slug')->label('Slug'),
                TextColumn::make('commission_rate')->label('Komisyon Oranı (%)'),
                TextColumn::make('sort')->sortable()->label('Sıralama'),
            ]);
    }
}

