<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CountUserController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Enquiry\EnquiryController;
use App\Http\Controllers\EsewaPaymentController;
use App\Http\Controllers\Membership\MembershipController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\PendingAmountController;
use App\Http\Controllers\RecentMembersController;
use Rats\Zkteco\Lib\Helper\Attendance;
use Rats\Zkteco\Lib\ZKTeco;
use App\Http\Controllers\KhaltiPaymentController;
Route::post('/register', [RegisteredUserController::class, 'store'])
    ->middleware('guest')
    ->name('register');
Route::post('/register/{id}',[RegisteredUserController::class,'update']);

Route::get('/users', [RegisteredUserController::class, 'index']);
Route::middleware('auth:sanctum')->get('/user', [RegisteredUserController::class, 'getLoggedInUserDetails']);

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest')
    ->name('login');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware('guest')
    ->name('password.email');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('password.store');

Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['auth', 'signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

// Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
//     ->middleware('auth')
//     ->name('logout');
    Route::middleware('auth:sanctum')->post('/logout', [AuthenticatedSessionController::class, 'destroy']);

//Blog
Route::post('/blog', [BlogController::class, 'store']);
Route::get('/blog-table', [BlogController::class, 'index']);
Route::post('/blog/{id}',[BlogController::class,'update']);
Route::delete('/blog/{blog}',[BlogController::class, 'destroy']);
Route::get('/blog/{slug}', [BlogController::class, 'show']);

//Dashboard Count
Route::get('/dashboardCount',[DashboardController::class,'dashboardCounts']);
//Membership
Route::post('/membership',[MembershipController::class, 'store']);
Route::get('/membership',[MembershipController::class, 'index']);
Route::patch('/membership/{id}',[MembershipController::class,'update']);
//Payments
Route::post('/payments',[PaymentController::class,'store']);
Route::get('/payments',[PaymentController::class,'index']);
Route::patch('/payments/{payment_id}',[PaymentController::class,'update']);
Route::delete('/payments/{payment_id}',[PaymentController::class,'destroy']);
//Enquiry
Route::post('/enquiries', [EnquiryController::class, 'store']); // Save enquiry
Route::post('/enquiries/reply/{id}', [EnquiryController::class, 'reply']); 
Route::delete('/enquiries',[EnquiryController::class,'destroy']);
Route::delete('/enquiries/{id}',[EnquiryController::class,'destroy']);
Route::get('/enquiries',[EnquiryController::class,'index']);
Route::get('/test-zkteco', [AttendanceController::class,'connect']);
Route::get('/getusers', [AttendanceController::class,'getUsers']);
Route::get('/attendance', [AttendanceController::class,'showAttendance']);

Route::post('/khalti/payment', [KhaltiPaymentController::class, 'purchase']);
// In routes/web.php
Route::post('/khalti/payment/verify', [KhaltiPaymentController::class, 'verify']);



