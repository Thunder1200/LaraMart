<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table)
        {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('city');
            $table->string('district');
            $table->text('full_address');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('sellers', function (Blueprint $table)
        {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('store_name');
            $table->string('slug')->unique()->index();
            $table->string('tax_number', 50);
            $table->string('iban', 50);
            $table->string('status')->default('beklemede')->index();
            $table->decimal('commission_rate', 10, 2)->default(10.00);
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table)
        {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique()->index();
            $table->decimal('commission_rate', 10, 2)->default(10.00);
            $table->integer('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('brands', function (Blueprint $table)
        {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique()->index();
            $table->string('logo')->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table)
        {
            $table->id();
            $table->foreignId('seller_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('brand_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique()->index();
            $table->text('description')->nullable();
            $table->string('status')->default('active')->index();
            $table->timestamps();
        });

        Schema::create('product_variants', function (Blueprint $table)
        {
            $table->id();
            $table->foreignId('product_id')->index()->constrained()->cascadeOnDelete();
            $table->string('sku')->unique();
            $table->string('color')->nullable();
            $table->string('size')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->timestamps();
        });

        Schema::create('carts', function (Blueprint $table)
        {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->cascadeOnDelete();
            $table->string('session_id')->nullable()->index();
            $table->string('coupon_code')->nullable();
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table)
        {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->timestamps();
            $table->unique(['cart_id', 'variant_id']);
        });

        Schema::create('favorites', function (Blueprint $table)
        {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'product_id']);
        });

        Schema::create('coupons', function (Blueprint $table)
        {
            $table->id();
            $table->string('code')->unique();
            $table->string('type')->default('percentage');
            $table->decimal('value', 10, 2);
            $table->decimal('min_total', 10, 2)->default(0.00);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('used_count')->default(0);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table)
        {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('order_number')->unique();
            $table->string('idempotency_key')->nullable()->unique();
            $table->json('address');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount', 10, 2)->default(0.00);
            $table->decimal('shipping', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2);
            $table->string('payment_status')->default('pending')->index();
            $table->string('coupon_code')->nullable();
            $table->timestamps();
        });

        Schema::create('sub_orders', function (Blueprint $table)
        {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_id')->index()->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending_approval')->index();
            $table->string('cargo_company')->nullable();
            $table->string('tracking_number')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->decimal('shipping', 10, 2)->default(0.00);
            $table->decimal('commission_amount', 10, 2)->default(0.00);
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table)
        {
            $table->id();
            $table->foreignId('sub_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->string('product_name');
            $table->decimal('price', 10, 2);
            $table->integer('quantity');
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table)
        {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('provider')->default('iyzico');
            $table->string('transaction_id')->nullable()->index();
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending')->index();
            $table->json('raw_response')->nullable();
            $table->timestamps();
        });

        Schema::create('returns', function (Blueprint $table)
        {
            $table->id();
            $table->foreignId('order_item_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('reason');
            $table->string('status')->default('pending')->index();
            $table->decimal('refund_amount', 10, 2);
            $table->timestamps();
        });

        Schema::create('reviews', function (Blueprint $table)
        {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment');
            $table->boolean('is_approved')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('seller_payouts', function (Blueprint $table)
        {
            $table->id();
            $table->foreignId('seller_id')->index()->constrained()->cascadeOnDelete();
            $table->string('period');
            $table->decimal('gross', 10, 2);
            $table->decimal('commission', 10, 2);
            $table->decimal('net', 10, 2);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('banners', function (Blueprint $table)
        {
            $table->id();
            $table->string('title');
            $table->string('image');
            $table->string('url')->nullable();
            $table->integer('sort')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table)
        {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('banners');
        Schema::dropIfExists('seller_payouts');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('returns');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('sub_orders');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('sellers');
        Schema::dropIfExists('addresses');
    }
};
