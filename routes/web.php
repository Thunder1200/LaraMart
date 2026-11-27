<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Customer\AccountController;
use App\Http\Controllers\Customer\AddressController;
use App\Http\Controllers\Customer\FavoriteController;
use App\Http\Controllers\Seller\ApplicationController as SellerApplicationController;
use App\Http\Controllers\Seller\DashboardController as SellerDashboardController;
use App\Http\Controllers\Seller\ProductController as SellerProductController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CategoryController as StorefrontCategoryController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\ProductController as StorefrontProductController;
use App\Http\Controllers\Storefront\SearchController;
use App\Http\Controllers\Storefront\StoreController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', HomeController::class)->name('home');
Route::get('/arama', SearchController::class)->name('search');
Route::get('/kategori/{category:slug}', [StorefrontCategoryController::class, 'show'])->name('categories.show');
Route::get('/urun/{product:slug}', [StorefrontProductController::class, 'show'])->name('products.show');
Route::get('/magaza/{seller:slug}', [StoreController::class, 'show'])->name('stores.show');

// Sepet (Misafir + Üye)
Route::prefix('sepet')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/ekle', [CartController::class, 'store'])->name('store');
    Route::patch('/{cartItem}', [CartController::class, 'update'])->name('update');
    Route::delete('/{cartItem}', [CartController::class, 'destroy'])->name('destroy');
    Route::post('/kupon', [CartController::class, 'applyCoupon'])->name('coupon.apply');
    Route::delete('/kupon', [CartController::class, 'removeCoupon'])->name('coupon.remove');
});

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/hesabim', AccountController::class)->name('customer.account');
    Route::get('/hesabim/favoriler', [FavoriteController::class, 'index'])->name('customer.favorites.index');
    Route::post('/favoriler/{product}/degistir', [FavoriteController::class, 'toggle'])->name('customer.favorites.toggle');

    Route::prefix('hesabim/adresler')->name('customer.addresses.')->group(function () {
        Route::get('/', [AddressController::class, 'index'])->name('index');
        Route::post('/', [AddressController::class, 'store'])->name('store');
        Route::get('/{address}/duzenle', [AddressController::class, 'edit'])->name('edit');
        Route::put('/{address}', [AddressController::class, 'update'])->name('update');
        Route::patch('/{address}/varsayilan', [AddressController::class, 'setDefault'])->name('default');
        Route::delete('/{address}', [AddressController::class, 'destroy'])->name('destroy');
    });

    Route::get('/satici-basvuru', [SellerApplicationController::class, 'create'])->name('seller.apply.create');
    Route::post('/satici-basvuru', [SellerApplicationController::class, 'store'])->name('seller.apply.store');
});

Route::prefix('satici')
    ->name('seller.')
    ->middleware(['auth', 'verified', 'approved.seller'])
    ->group(function () {
        Route::get('/', SellerDashboardController::class)->name('dashboard');
        Route::resource('urunler', SellerProductController::class)
            ->parameters(['urunler' => 'product'])
            ->names('products')
            ->except(['show']);
        Route::patch('urunler/{product}/durum', [SellerProductController::class, 'toggleStatus'])
            ->name('products.toggle-status');
        Route::patch('urunler/{product}/stok', [SellerProductController::class, 'updateStock'])
            ->name('products.update-stock');
    });

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified', 'role:admin'])
    ->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');
        Route::resource('kategoriler', AdminCategoryController::class)
            ->parameters(['kategoriler' => 'category'])
            ->names('categories')
            ->except(['show']);
    });

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
