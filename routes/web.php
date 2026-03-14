<?php

use App\Http\Controllers\AdjustmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\MoveController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/forgot-password', [AuthController::class, 'showForgot'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendOtp'])->name('password.email');
Route::get('/otp', [AuthController::class, 'showOtp'])->name('password.otp');
Route::post('/otp', [AuthController::class, 'verifyOtp'])->name('password.verify');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');

    Route::prefix('operations')->group(function () {
        Route::get('/receipts', [ReceiptController::class, 'index'])->name('receipts.index');
        Route::post('/receipts', [ReceiptController::class, 'store'])->name('receipts.store');
        Route::post('/receipts/{receipt}/validate', [ReceiptController::class, 'validateReceipt'])->name('receipts.validate');

        Route::get('/deliveries', [DeliveryController::class, 'index'])->name('deliveries.index');
        Route::post('/deliveries', [DeliveryController::class, 'store'])->name('deliveries.store');
        Route::post('/deliveries/{delivery}/validate', [DeliveryController::class, 'validateDelivery'])->name('deliveries.validate');

        Route::get('/adjustments', [AdjustmentController::class, 'index'])->name('adjustments.index');
        Route::post('/adjustments', [AdjustmentController::class, 'store'])->name('adjustments.store');

        Route::get('/moves', [MoveController::class, 'index'])->name('moves.index');
        Route::post('/moves', [MoveController::class, 'store'])->name('moves.store');
        Route::post('/moves/{move}/complete', [MoveController::class, 'complete'])->name('moves.complete');
    });

    Route::prefix('settings')->group(function () {
        Route::get('/warehouses', [WarehouseController::class, 'index'])->name('settings.warehouses');
        Route::post('/warehouses', [WarehouseController::class, 'store'])->name('settings.warehouses.store');
    });

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/ledger', [ReportController::class, 'exportLedger'])->name('reports.ledger');

    Route::view('/profile', 'profile')->name('profile');
});
