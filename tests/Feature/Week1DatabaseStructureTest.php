<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Schema;

test('all 20 required marketplace tables exist after migration', function () {
    $requiredTables = [
        'users',
        'addresses',
        'sellers',
        'categories',
        'brands',
        'products',
        'product_variants',
        'media',
        'carts',
        'cart_items',
        'favorites',
        'coupons',
        'orders',
        'sub_orders',
        'order_items',
        'payments',
        'returns',
        'reviews',
        'seller_payouts',
        'banners',
    ];

    foreach ($requiredTables as $table) {
        expect(Schema::hasTable($table))->toBeTrue("Missing table: {$table}");
    }
});

test('database seeder populates expected week 1 sample data', function () {
    $this->seed(DatabaseSeeder::class);

    expect(User::role('admin')->count())->toBe(1);
    expect(User::role('seller')->count())->toBe(5);
    expect(Seller::count())->toBe(5);
    expect(User::role('customer')->count())->toBe(20);
    expect(Category::count())->toBe(15);
    expect(Product::count())->toBe(100);
    expect(Product::with(['seller', 'category', 'brand', 'variants'])->first()->variants->count())->toBeGreaterThanOrEqual(2);
});
