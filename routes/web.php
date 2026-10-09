<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Customer\DashboardController;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Route;

/* ---------- Publik ---------- */
Route::get('/', [CatalogController::class, 'index'])->name('home');

Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'show'])->name('show');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::post('/change', [CartController::class, 'change'])->name('change');
});

/* ---------- Auth ---------- */
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/masuk', [AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::post('/daftar', [AuthController::class, 'register'])->name('register')->middleware('throttle:6,1');
});
Route::post('/keluar', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/* ---------- Checkout (show menangani tamu sendiri agar diarahkan ke tab Daftar) ---------- */
Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
Route::middleware('auth')->group(function () {
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/selesai/{order}', [CheckoutController::class, 'success'])->name('checkout.success');

    /* ---------- Dashboard pelanggan ---------- */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/pesanan/{order}/batal', [DashboardController::class, 'cancel'])->name('orders.cancel');
    Route::post('/pesanan/{order}/beli-lagi', [DashboardController::class, 'reorder'])->name('orders.reorder');
    Route::put('/profil', [DashboardController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profil/password', [DashboardController::class, 'updatePassword'])->name('profile.password');
});

/* ---------- Admin ---------- */
Route::middleware(['auth', EnsureAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\PanelController::class, 'index'])->name('index');

    Route::get('/pesanan/export', [Admin\OrderController::class, 'export'])->name('orders.export');
    Route::patch('/pesanan/{order}/status', [Admin\OrderController::class, 'updateStatus'])->name('orders.status');
    Route::patch('/pesanan/{order}/resi', [Admin\OrderController::class, 'updateResi'])->name('orders.resi');
    Route::delete('/pesanan/{order}', [Admin\OrderController::class, 'destroy'])->name('orders.destroy');

    Route::post('/produk', [Admin\ProductController::class, 'store'])->name('products.store');
    Route::put('/produk/{product}', [Admin\ProductController::class, 'update'])->name('products.update');
    Route::patch('/produk/{product}/tayang', [Admin\ProductController::class, 'toggle'])->name('products.toggle');
    Route::delete('/produk/{product}', [Admin\ProductController::class, 'destroy'])->name('products.destroy');
});

Route::get('/run-migrate-secret', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true, '--seed' => true]);
        return 'Migration and Seeding Success!';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});