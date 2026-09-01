<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Booking (Public)
|--------------------------------------------------------------------------
*/
Route::get('/', [BookingController::class, 'index'])->name('booking.index');
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
Route::get('/payment', [BookingController::class, 'payment'])->name('payment.page');
Route::post('/payment', [BookingController::class, 'processPayment'])->name('payment.process');
// MOCK PAYMENT
Route::get('/mock/checkout', [PaymentController::class, 'mockCheckout'])->name('mock.checkout');
Route::post('/mock/confirm', [PaymentController::class, 'mockConfirm'])->name('mock.confirm');
Route::match(['GET', 'POST'], '/mock/expire', [PaymentController::class, 'mockExpire'])->name('mock.expire');
// Invoice Download
Route::get('/invoice/download/{id}', [PaymentController::class, 'downloadInvoice'])
    ->name('invoice.download');

/*
|--------------------------------------------------------------------------
| Admin Login (Public)
|--------------------------------------------------------------------------
*/
Route::get('/admin/login', [AdminController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login.submit');

/*
|--------------------------------------------------------------------------
| Admin Panel (Protected)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:admin')->prefix('admin')->name('admin.')->group(function () {

    // Dashboard & Reports
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/report', [AdminController::class, 'invoiceReport'])->name('report');
    Route::get('/invoice/pdf', [AdminController::class, 'exportInvoicePdf'])->name('report.pdf');
    Route::post('/logout', [AdminController::class, 'logout'])->name('logout');

    // User & Points
    Route::get('/users/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::post('/users/update-price-per-point', [AdminController::class, 'updatePricePerPoint'])->name('users.updatePricePerPoint');
    Route::post('/users/{id}/update-points', [AdminController::class, 'updateUserPoints'])->name('users.updatePoints');

    // Schedule & Discounts
    Route::get('/schedule/edit', [AdminController::class, 'editSchedule'])->name('schedule.edit');
    Route::post('/schedule/update', [AdminController::class, 'updateSchedule'])->name('schedule.update');
    Route::post('/schedule/bulkprice/update', [AdminController::class, 'updateBulkPrice'])->name('schedule.bulkprice.update');

    Route::post('/schedule/discount/store', [AdminController::class, 'storeDiscount'])->name('schedule.discount.store');
    Route::get('/manage-discounts', [AdminController::class, 'manageDiscounts'])->name('manage.discounts');
    Route::delete('/manage-discounts/{id}', [AdminController::class, 'deleteDiscount'])->name('schedule.discount.delete');
});
