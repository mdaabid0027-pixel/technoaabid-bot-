<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminCategoryController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/telegram/webhook', [TelegramController::class, 'handle']);
Route::get('/telegram/webapp', [TelegramController::class, 'webApp'])->name('telegram.webapp');
Route::get('/telegram/spin-wheel', [TelegramController::class, 'spinWheelView'])->name('telegram.spin-wheel');
Route::post('/telegram/spin-wheel/spin', [TelegramController::class, 'spinWheelApi'])->name('telegram.spin-wheel.api');
Route::post('/telegram/spin-wheel/notify', [TelegramController::class, 'spinWheelNotifyApi'])->name('telegram.spin-wheel.notify');

// Redirect /admin to dashboard
Route::redirect('/admin', '/admin/dashboard');

// Admin Authentication (Guest)
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::get('/forgot-password', [AdminAuthController::class, 'showForgotPassword'])->name('admin.password.request');
    Route::post('/forgot-password', [AdminAuthController::class, 'sendResetLinkEmail'])->name('admin.password.email');
    Route::get('/reset-password/{token}', [AdminAuthController::class, 'showResetPassword'])->name('admin.password.reset');
    Route::post('/reset-password', [AdminAuthController::class, 'resetPassword'])->name('admin.password.store');
});

// Admin Dashboard & Management (Protected with strict Auth Middleware)
Route::prefix('admin')->middleware('auth')->group(function () {
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
    Route::post('/password/update', [AdminAuthController::class, 'updatePassword'])->name('admin.password.update');
    
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    
    // Customers & VIP Resellers Management
    Route::get('/resellers', [AdminUserController::class, 'resellers'])->name('admin.resellers.index');
    Route::post('/resellers/add', [AdminUserController::class, 'addReseller'])->name('admin.resellers.add');
    Route::get('/customers', [AdminUserController::class, 'customers'])->name('admin.customers.index');
    Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::match(['delete', 'post'], '/users/bulk-delete', [AdminUserController::class, 'bulkDelete'])->name('admin.users.bulk-delete');
    Route::get('/users/{id}', [AdminUserController::class, 'show'])->name('admin.users.show');
    Route::match(['delete', 'post'], '/users/{id}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
    Route::post('/users/{id}/adjust-balance', [AdminUserController::class, 'adjustBalance'])->name('admin.users.adjust-balance');
    Route::post('/users/{id}/toggle-block', [AdminUserController::class, 'toggleBlock'])->name('admin.users.toggle-block');
    Route::post('/users/{id}/toggle-reseller', [AdminUserController::class, 'toggleReseller'])->name('admin.users.toggle-reseller');
    Route::post('/users/{id}/notify-bitaim-activated', [AdminUserController::class, 'notifyBitaimActivated'])->name('admin.users.notify-bitaim-activated');
    
    // Stock & Product Management
    Route::get('/products', [AdminProductController::class, 'index'])->name('admin.products.index');
    Route::post('/products', [AdminProductController::class, 'store'])->name('admin.products.store');
    Route::put('/products/{id}', [AdminProductController::class, 'update'])->name('admin.products.update');
    Route::delete('/products/{id}', [AdminProductController::class, 'destroy'])->name('admin.products.destroy');
    Route::post('/products/{id}/add-keys', [AdminProductController::class, 'addKeys'])->name('admin.products.add-keys');
    
    // Key Inventory & Pricing Management (with Available/History tabs, WhatsApp Link Generator, and VIP Custom Pricing)
    Route::get('/keys', [App\Http\Controllers\Admin\AdminKeyController::class, 'index'])->name('admin.keys.index');
    Route::get('/keys/batch-keys', [App\Http\Controllers\Admin\AdminKeyController::class, 'getBatchKeys'])->name('admin.keys.batch_keys');
    Route::post('/keys', [App\Http\Controllers\Admin\AdminKeyController::class, 'store'])->name('admin.keys.store');
    Route::delete('/keys/{id}', [App\Http\Controllers\Admin\AdminKeyController::class, 'destroy'])->name('admin.keys.destroy');
    Route::post('/keys/bulk-delete', [App\Http\Controllers\Admin\AdminKeyController::class, 'bulkDelete'])->name('admin.keys.bulk-delete');
    Route::post('/keys/clear', [App\Http\Controllers\Admin\AdminKeyController::class, 'clearHistory'])->name('admin.keys.clear');
    Route::post('/keys/update-price', [App\Http\Controllers\Admin\AdminKeyController::class, 'updateCustomPrice'])->name('admin.keys.update_price');
    Route::delete('/keys/custom-price/{id}', [App\Http\Controllers\Admin\AdminKeyController::class, 'deleteCustomPrice'])->name('admin.keys.delete_custom_price');
    // Dedicated Pricing Matrix Manager (Customer Base Rates & Reseller Custom Rates)
    Route::get('/pricing', [App\Http\Controllers\Admin\AdminPricingController::class, 'index'])->name('admin.pricing.index');
    Route::put('/pricing/customer/{id}', [App\Http\Controllers\Admin\AdminPricingController::class, 'updateCustomerPrices'])->name('admin.pricing.update_customer');
    Route::put('/pricing/standard-reseller/{id}', [App\Http\Controllers\Admin\AdminPricingController::class, 'updateStandardResellerPrices'])->name('admin.pricing.update_standard_reseller');
    Route::post('/pricing/standard-reseller/batch', [App\Http\Controllers\Admin\AdminPricingController::class, 'updateStandardResellerBatch'])->name('admin.pricing.update_standard_reseller_batch');
    Route::post('/pricing/reseller', [App\Http\Controllers\Admin\AdminPricingController::class, 'updateResellerPrice'])->name('admin.pricing.update_reseller');
    Route::put('/pricing/reseller/{id}/batch', [App\Http\Controllers\Admin\AdminPricingController::class, 'updateBatchResellerPrices'])->name('admin.pricing.update_reseller_batch');
    Route::delete('/pricing/reseller/{id}', [App\Http\Controllers\Admin\AdminPricingController::class, 'deleteResellerPrice'])->name('admin.pricing.delete_reseller');

    // Transaction Management & User History API
    Route::delete('/transactions/{id}', [App\Http\Controllers\Admin\AdminTransactionController::class, 'destroy'])->name('admin.transactions.destroy');
    Route::post('/transactions/bulk-delete', [App\Http\Controllers\Admin\AdminTransactionController::class, 'bulkDelete'])->name('admin.transactions.bulk-delete');
    Route::get('/users/{id}/history-api', [App\Http\Controllers\Admin\AdminTransactionController::class, 'userHistory'])->name('admin.users.history-api');

    // Categories CRUD Management
    Route::get('/categories', [AdminCategoryController::class, 'index'])->name('admin.categories.index');
    Route::post('/categories', [AdminCategoryController::class, 'store'])->name('admin.categories.store');
    Route::put('/categories/{id}', [AdminCategoryController::class, 'update'])->name('admin.categories.update');
    Route::delete('/categories/{id}', [AdminCategoryController::class, 'destroy'])->name('admin.categories.destroy');

    // In-Game Items & Posters Management
    Route::get('/ingame-items', [App\Http\Controllers\Admin\AdminInGameItemController::class, 'index'])->name('admin.ingame-items.index');
    Route::put('/ingame-items/{id}', [App\Http\Controllers\Admin\AdminInGameItemController::class, 'update'])->name('admin.ingame-items.update');

    // Sound Effects & Memes Soundboard
    Route::get('/sounds', [App\Http\Controllers\Admin\AdminSoundController::class, 'index'])->name('admin.sounds.index');
    Route::post('/sounds/save-events', [App\Http\Controllers\Admin\AdminSoundController::class, 'saveEvents'])->name('admin.sounds.save_events');
    Route::post('/sounds/assign-event', [App\Http\Controllers\Admin\AdminSoundController::class, 'assignEvent'])->name('admin.sounds.assign_event');
    Route::post('/sounds/test-telegram', [App\Http\Controllers\Admin\AdminSoundController::class, 'testTelegram'])->name('admin.sounds.test_telegram');
    Route::post('/sounds/upload', [App\Http\Controllers\Admin\AdminSoundController::class, 'upload'])->name('admin.sounds.upload');

    // Rewards & Claims Audit Management
    Route::get('/rewards-audit', [App\Http\Controllers\Admin\AdminRewardsAuditController::class, 'index'])->name('admin.rewards-audit.index');
    Route::delete('/rewards-audit/{id}', [App\Http\Controllers\Admin\AdminRewardsAuditController::class, 'destroy'])->name('admin.rewards-audit.destroy');
    Route::post('/rewards-audit/bulk-clear', [App\Http\Controllers\Admin\AdminRewardsAuditController::class, 'bulkClear'])->name('admin.rewards-audit.bulk-clear');
});
