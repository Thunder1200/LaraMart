<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Customer\AccountController;
use App\Http\Controllers\Customer\AddressController;
use App\Http\Controllers\Customer\FavoriteController;
use App\Http\Controllers\Customer\NotificationController as CustomerNotificationController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\ReturnController as CustomerReturnController;
use App\Http\Controllers\Customer\ReviewController as CustomerReviewController;
use App\Http\Controllers\Seller\ApplicationController as SellerApplicationController;
use App\Http\Controllers\Seller\DashboardController as SellerDashboardController;
use App\Http\Controllers\Seller\OrderController as SellerOrderController;
use App\Http\Controllers\Seller\ProductController as SellerProductController;
use App\Http\Controllers\Seller\ReturnController as SellerReturnController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CategoryController as StorefrontCategoryController;
use App\Http\Controllers\Storefront\CheckoutController;
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
    Route::post('/kupon', [CartController::class, 'applyCoupon'])->name('coupon.apply');
    Route::delete('/kupon', [CartController::class, 'removeCoupon'])->name('coupon.remove');
    Route::patch('/{cartItem}', [CartController::class, 'update'])->whereNumber('cartItem')->name('update');
    Route::delete('/{cartItem}', [CartController::class, 'destroy'])->whereNumber('cartItem')->name('destroy');
});

// 3D Secure iyzico dönüş adresi ve hata ekranı
Route::post('/odeme/iyzico/callback', [CheckoutController::class, 'callback'])->name('checkout.iyzico.callback');
Route::get('/odeme/hata', [CheckoutController::class, 'error'])->name('checkout.error');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/odeme', [CheckoutController::class, 'show'])->name('checkout.index');
    Route::post('/odeme', [CheckoutController::class, 'start'])->name('checkout.start');
    Route::get('/odeme/3d-secure/{token}', [CheckoutController::class, 'threeDsChallenge'])->name('checkout.3ds');

    Route::get('/hesabim', AccountController::class)->name('customer.account');
    Route::get('/hesabim/favoriler', [FavoriteController::class, 'index'])->name('customer.favorites.index');
    Route::post('/favoriler/{product}/degistir', [FavoriteController::class, 'toggle'])->name('customer.favorites.toggle');

    Route::get('/hesabim/siparisler', [CustomerOrderController::class, 'index'])->name('customer.orders.index');
    Route::get('/hesabim/siparisler/{order}', [CustomerOrderController::class, 'show'])->name('customer.orders.show');
    Route::post('/hesabim/siparis-kalemleri/{orderItem}/iade', [CustomerReturnController::class, 'store'])->name('customer.returns.store');
    Route::post('/urun/{product:slug}/yorum', [CustomerReviewController::class, 'store'])->name('customer.reviews.store');

    Route::get('/hesabim/bildirimler', [CustomerNotificationController::class, 'index'])->name('customer.notifications.index');
    Route::post('/hesabim/bildirimler/tumunu-oku', [CustomerNotificationController::class, 'markAllRead'])->name('customer.notifications.read-all');
    Route::post('/hesabim/bildirimler/{notificationId}/oku', [CustomerNotificationController::class, 'markRead'])->name('customer.notifications.read');

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
        Route::get('siparisler', [SellerOrderController::class, 'index'])->name('orders.index');
        Route::get('siparisler/{subOrder}', [SellerOrderController::class, 'show'])->name('orders.show');
        Route::patch('siparisler/{subOrder}/durum', [SellerOrderController::class, 'updateStatus'])->name('orders.update-status');
        Route::get('iadeler', [SellerReturnController::class, 'index'])->name('returns.index');
        Route::patch('iadeler/{returnRequest}/karar', [SellerReturnController::class, 'decide'])->name('returns.decide');
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
        Route::get('yorumlar', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::patch('yorumlar/{review}/onayla', [AdminReviewController::class, 'approve'])->name('reviews.approve');
        Route::delete('yorumlar/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');
    });

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
