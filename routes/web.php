<?php

use App\Http\Controllers\AccessControlController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OnlineOrderController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\SystemSyncController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Real-Time Sync & Authoritative Thai Time Endpoint
Route::get('/system/sync-state', [SystemSyncController::class, 'syncState'])->name('system.sync_state');
Route::get('/system/audit-logs', [SystemSyncController::class, 'auditLogs'])->name('system.audit_logs')->middleware('auth');

// Landing redirect to POS or Login
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('pos.index');
    }
    return redirect()->route('login');
});

// Authentication Routes (Login, Logout, User Switcher)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout.get');
Route::match(['get', 'post'], '/switch-user/{userId}', [AuthController::class, 'switchUser'])->name('switch_user')->middleware('auth');
Route::post('/register-request', [AccessControlController::class, 'submitRegistrationRequest'])->name('register.request');

// 1. บริการหน้าร้าน (In-Store POS & Kitchen Display) - ต้องเข้าสู่ระบบ (Requires Auth)
Route::prefix('pos')->name('pos.')->middleware('auth')->group(function () {
    Route::get('/', [PosController::class, 'index'])->name('index');
    Route::post('/order', [PosController::class, 'storeOrder'])->name('order.store');
    Route::get('/kitchen', [PosController::class, 'kitchen'])->name('kitchen');
    Route::post('/orders/{order}/status', [PosController::class, 'updateStatus'])->name('order.status');
});

// 2. บริการออนไลน์ สั่งออเดอร์ ชำระในแอปทันที สั่งมารับเองหน้าร้าน (Grab Food 100% Style)
Route::prefix('order')->name('online.')->group(function () {
    Route::get('/', [OnlineOrderController::class, 'index'])->name('index');
    Route::get('/checkout', [OnlineOrderController::class, 'checkout'])->name('checkout');
    Route::post('/submit', [OnlineOrderController::class, 'storeOrder'])->name('submit');
    Route::get('/track/{order_no}', [OnlineOrderController::class, 'track'])->name('track');
    Route::get('/status/{order_no}', [OnlineOrderController::class, 'statusJson'])->name('status');

    // 2.1 หน้าแชทลูกค้า (Shopee-Style Customer Chat Synced with Orders)
    Route::get('/chat', [OnlineOrderController::class, 'chat'])->name('chat');
    Route::get('/chat/messages', [OnlineOrderController::class, 'getChatMessages'])->name('chat.messages');
    Route::post('/chat/send', [OnlineOrderController::class, 'sendChatMessage'])->name('chat.send');
    Route::post('/chat/upload-image', [OnlineOrderController::class, 'uploadChatImage'])->name('chat.upload_image');

    // 2.2 หน้าเช็คแต้มสะสม & รีวิวอาหาร (Loyalty Points: ซื้อ 1 ครั้ง = 1 แต้ม, ครบ 5 ฟรี 1 ไม้, รีวิว = +1 แต้ม)
    Route::get('/rewards', [OnlineOrderController::class, 'rewards'])->name('rewards');
    Route::post('/rewards/lookup', [OnlineOrderController::class, 'lookupRewards'])->name('rewards.lookup');
    Route::post('/rewards/review', [OnlineOrderController::class, 'submitReview'])->name('rewards.review');
    Route::post('/rewards/redeem', [OnlineOrderController::class, 'redeemReward'])->name('rewards.redeem');

    // 2.3 ตั้งค่าภาพปกหน้าร้าน (Hero Banner Settings for Mobile)
    Route::post('/hero-banner', [OnlineOrderController::class, 'updateHeroBanner'])->name('hero_banner.update');
    Route::post('/hero-banner/upload', [OnlineOrderController::class, 'uploadHeroBannerImage'])->name('hero_banner.upload');
    Route::delete('/hero-banner/history', [OnlineOrderController::class, 'deleteHeroBannerHistory'])->name('hero_banner.delete_history');
});

// 3. ระบบหลังบ้าน (Backend Admin & Financial / Inventory Systems) - ต้องเข้าสู่ระบบ (Requires Auth)
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/settings/toggle-online-mode', [DashboardController::class, 'toggleOnlineMode'])->name('settings.toggle_online');

    // 3.1 ระบบสแกนใบเสร็จ & ตรวจจับราคาขึ้นลง แปลงเป็นชิ้นหรือไม้
    Route::get('/receipts', [ReceiptController::class, 'index'])->name('receipts.index');
    Route::post('/receipts', [ReceiptController::class, 'store'])->name('receipts.store');
    Route::delete('/receipts/{receipt}', [ReceiptController::class, 'destroy'])->name('receipts.destroy');
    Route::post('/receipts/upload-image', [ReceiptController::class, 'uploadImage'])->name('receipts.upload_image');
    Route::post('/receipts/scan-ocr', [ReceiptController::class, 'scanOcr'])->name('receipts.scan_ocr');
    Route::post('/receipts/save-gemini-key', [ReceiptController::class, 'saveGeminiKey'])->name('receipts.save_gemini_key');
    Route::get('/price-tracker', [ReceiptController::class, 'priceTracker'])->name('receipts.price_tracker');
    Route::redirect('/receipts/price-tracker', '/admin/price-tracker');
    Route::post('/price-tracker/materials', [ReceiptController::class, 'storePriceTrackerMaterial'])->name('receipts.price_tracker.store_material');
    Route::post('/price-tracker/materials/batch-delete', [ReceiptController::class, 'destroyPriceTrackerMaterials'])->name('receipts.price_tracker.batch_delete');
    Route::delete('/price-tracker/materials', [ReceiptController::class, 'destroyPriceTrackerMaterials'])->name('receipts.price_tracker.destroy_materials');
    Route::delete('/price-tracker/materials/{rawMaterial}', [ReceiptController::class, 'destroyPriceTrackerMaterial'])->name('receipts.price_tracker.destroy_material');
    Route::post('/price-tracker/materials/{rawMaterial}', [ReceiptController::class, 'updatePriceTrackerMaterial'])->name('receipts.price_tracker.update_material');
    Route::post('/price-tracker/materials/{rawMaterial}/toggle-approval', [ReceiptController::class, 'toggleMaterialApproval'])->name('receipts.price_tracker.toggle_material_approval');
    Route::post('/price-tracker/materials/{rawMaterial}/toggle-recommended', [ReceiptController::class, 'toggleMaterialRecommended'])->name('receipts.price_tracker.toggle_material_recommended');
    Route::post('/price-tracker/upload-image', [ReceiptController::class, 'uploadMaterialImage'])->name('receipts.price_tracker.upload_image');
    Route::post('/price-tracker/hero-banner', [ReceiptController::class, 'updatePriceTrackerHero'])->name('receipts.price_tracker.update_hero');
    Route::post('/price-tracker/sync-today', [ReceiptController::class, 'syncTodayReceipts'])->name('receipts.price_tracker.sync_today');
    Route::post('/categories', [ReceiptController::class, 'storeCategory'])->name('categories.store');
    Route::post('/categories/{category}/update', [ReceiptController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{category}', [ReceiptController::class, 'destroyCategory'])->name('categories.destroy');
    Route::post('/categories/{category}/delete', [ReceiptController::class, 'destroyCategory'])->name('categories.destroy.post');
    Route::post('/categories/{category}/toggle-approval', [ReceiptController::class, 'toggleCategoryApproval'])->name('categories.toggle_approval');

    // 3.2 ระบบบันทึกฟิกคอส รายรับรายจ่ายทั่วไป
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

    // 3.3 ระบบสรุปยอดไม้คงเหลือของทุกรายการ ยกมา บวกวันนี้ หักยอดขาย
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
    Route::post('/inventory/reconcile', [InventoryController::class, 'reconcile'])->name('inventory.reconcile');

    // 3.4 ระบบจัดการพนักงาน ร้านหม่าล่า (สแกนหน้าเข้างาน & สรุปยอดเงินเดือน)
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::post('/employees/clock-in', [EmployeeController::class, 'clockIn'])->name('employees.clock_in');
    Route::post('/employees/store-location', [EmployeeController::class, 'updateStoreLocation'])->name('employees.update_location');
    Route::get('/employees/payslip/{month?}', [EmployeeController::class, 'payslip'])->name('employees.payslip');

    // 3.5 ระบบการเข้าถึง & สิทธิ์การใช้งาน (Access Control & Permissions)
    Route::get('/access', [AccessControlController::class, 'index'])->name('access.index');
    Route::post('/access/save', [AccessControlController::class, 'save'])->name('access.save');
    Route::post('/access/employee/save', [AccessControlController::class, 'saveEmployeeData'])->name('access.save_employee');
    Route::post('/access/users/store', [AccessControlController::class, 'storeUser'])->name('access.store_user');
    Route::post('/access/users/delete/{id}', [AccessControlController::class, 'deleteUser'])->name('access.delete_user');
    Route::post('/access/users/{id}/toggle-status', [AccessControlController::class, 'toggleStatus'])->name('access.users.toggle_status');
    Route::post('/access/users/clear-test', [AccessControlController::class, 'clearTestUsers'])->name('access.clear_test_users');
    Route::post('/access/settings', [AccessControlController::class, 'updateSettings'])->name('access.update_settings');
    Route::post('/access/register-requests/{id}/approve', [AccessControlController::class, 'approveRegistrationRequest'])->name('access.register_requests.approve');
    Route::post('/access/register-requests/{id}/reject', [AccessControlController::class, 'rejectRegistrationRequest'])->name('access.register_requests.reject');
});

// Shortcut alias for employees & access - ต้องเข้าสู่ระบบ (Requires Auth)
Route::get('/employees', function () {
    return redirect()->route('admin.employees.index');
})->middleware('auth');
Route::get('/access', function () {
    return redirect()->route('admin.access.index');
})->middleware('auth');
