<?php

use App\Http\Controllers\AdminPanelController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartAndCheckoutController;
use App\Http\Controllers\CustomerAccountController;
use App\Http\Controllers\SellerPanelController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'home'])->name('home');
Route::get('/arama', [StorefrontController::class, 'search'])->name('catalog.search');
Route::get('/kategori/{slug}', [StorefrontController::class, 'category'])->name('catalog.category');
Route::get('/urun/{slug}', [StorefrontController::class, 'product'])->name('catalog.product');
Route::get('/magaza/{slug}', [StorefrontController::class, 'sellerStore'])->name('catalog.seller');

Route::get('/sepet', [CartAndCheckoutController::class, 'showCart'])->name('cart.index');
Route::post('/sepet/ekle', [CartAndCheckoutController::class, 'addToCart'])->name('cart.add');
Route::post('/sepet/kupon', [CartAndCheckoutController::class, 'applyCoupon'])->name('cart.coupon');

Route::middleware('guest')->group(function ()
{
    Route::get('/giris', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/giris', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/kayit', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/kayit', [AuthController::class, 'register']);
});

Route::post('/cikis', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function ()
{
    Route::get('/odeme', [CartAndCheckoutController::class, 'showCheckout'])->name('checkout.index');
    Route::post('/odeme', [CartAndCheckoutController::class, 'processCheckout'])->name('checkout.process');
    Route::get('/odeme/hata', [CartAndCheckoutController::class, 'paymentError'])->name('checkout.error');

    Route::prefix('hesabim')->group(function ()
    {
        Route::get('/', [CustomerAccountController::class, 'index'])->name('account.index');
        Route::get('/siparisler/{order}', [CustomerAccountController::class, 'showOrder'])->name('account.orders.show');
        Route::post('/adresler', [CustomerAccountController::class, 'storeAddress'])->name('account.addresses.store');
        Route::delete('/adresler/{address}', [CustomerAccountController::class, 'deleteAddress'])->name('account.addresses.delete');
        Route::post('/favoriler/{product}', [CustomerAccountController::class, 'toggleFavorite'])->name('account.favorites.toggle');
        Route::post('/satici-basvuru', [CustomerAccountController::class, 'submitSellerApplication'])->name('account.seller.apply');
        Route::post('/siparis-kalemi/{orderItem}/iade', [CustomerAccountController::class, 'requestReturn'])->name('account.returns.store');
        Route::post('/siparis-kalemi/{orderItem}/yorum', [CustomerAccountController::class, 'submitReview'])->name('account.reviews.store');
    });

    Route::prefix('satici')->middleware('seller.approved')->group(function ()
    {
        Route::get('/', [SellerPanelController::class, 'dashboard'])->name('seller.dashboard');
        Route::post('/urunler', [SellerPanelController::class, 'storeProduct'])->name('seller.products.store');
        Route::get('/urunler/{product}/duzenle', [SellerPanelController::class, 'editProduct'])->name('seller.products.edit');
        Route::put('/urunler/{product}', [SellerPanelController::class, 'updateProduct'])->name('seller.products.update');
        Route::delete('/urunler/{product}', [SellerPanelController::class, 'deleteProduct'])->name('seller.products.delete');
        Route::patch('/alt-siparisler/{subOrder}/durum', [SellerPanelController::class, 'updateSubOrderStatus'])->name('seller.suborders.status');
        Route::patch('/iadeler/{returnRequest}', [SellerPanelController::class, 'handleReturn'])->name('seller.returns.handle');
    });

    Route::prefix('admin')->middleware('admin.only')->group(function ()
    {
        Route::get('/', [AdminPanelController::class, 'dashboard'])->name('admin.dashboard');
        Route::post('/saticilar/{seller}/onayla', [AdminPanelController::class, 'approveSeller'])->name('admin.sellers.approve');
        Route::post('/saticilar/{seller}/reddet', [AdminPanelController::class, 'rejectSeller'])->name('admin.sellers.reject');
        Route::post('/kategoriler', [AdminPanelController::class, 'storeCategory'])->name('admin.categories.store');
        Route::delete('/kategoriler/{category}', [AdminPanelController::class, 'deleteCategory'])->name('admin.categories.delete');
        Route::post('/yorumlar/{review}/onayla', [AdminPanelController::class, 'approveReview'])->name('admin.reviews.approve');
        Route::get('/siparisler/disa-aktar', [AdminPanelController::class, 'exportOrders'])->name('admin.orders.export');
    });
});
