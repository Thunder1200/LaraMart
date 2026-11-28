<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table): void {
            $table->index(['product_id', 'is_approved', 'rating'], 'reviews_product_approved_rating_idx');
        });

        Schema::table('product_variants', function (Blueprint $table): void {
            $table->index(['product_id', 'color', 'size'], 'variants_product_color_size_idx');
            $table->index(['product_id', 'price', 'discount_price'], 'variants_product_price_idx');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->index(['status', 'brand_id'], 'products_status_brand_idx');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table): void {
            $table->dropIndex('reviews_product_approved_rating_idx');
        });

        Schema::table('product_variants', function (Blueprint $table): void {
            $table->dropIndex('variants_product_color_size_idx');
            $table->dropIndex('variants_product_price_idx');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex('products_status_brand_idx');
        });
    }
};
