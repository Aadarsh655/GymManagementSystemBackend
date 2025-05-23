<?php
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Enquiry\EnquiryController;
use App\Http\Controllers\Membership\MembershipController;
use App\Http\Controllers\Payment\PaymentController;
use Rats\Zkteco\Lib\ZKTeco;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\KhaltiPaymentController;
use App\Http\Controllers\Notice\NoticeController;
use App\Http\Controllers\TestimonialController;

Route::post('/register', [RegisteredUserController::class, 'store'])
    ->middleware('guest')
    ->name('register');
Route::put('/register/{id}',[RegisteredUserController::class,'update']);

Route::get('/users', [RegisteredUserController::class, 'index']);
Route::middleware('auth:sanctum')->get('/user', [RegisteredUserController::class, 'getLoggedInUserDetails']);
Route::patch('/users/{id}/archive', [RegisteredUserController::class, 'archive']);

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest')
    ->name('login');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware('guest')
    ->name('password.email');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('password.store');

Route::middleware('auth:sanctum')->post('/password/change', [NewPasswordController::class, 'changePassword']);


Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['auth', 'signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

Route::middleware('auth:sanctum')->post('/logout', [AuthenticatedSessionController::class, 'destroy']);

Route::post('/blog', [BlogController::class, 'store']);
Route::get('/blog-table', [BlogController::class, 'index']);
Route::post('/blog/{id}',[BlogController::class,'update']);
Route::delete('/blog/{blog}', [BlogController::class, 'destroy']);
Route::get('/blog/{slug}', [BlogController::class, 'show']);

Route::get('/dashboardCount',[DashboardController::class,'dashboardCounts']);

Route::post('/membership',[MembershipController::class, 'store']);
Route::get('/membership',[MembershipController::class, 'index']);
Route::patch('/membership/{id}',[MembershipController::class,'update']);
Route::delete('/membership/delete',[MembershipController::class,'destroy']);

Route::post('/payments',[PaymentController::class,'store']);
Route::get('/payments',[PaymentController::class,'index']);
Route::patch('/payments/{payment_id}',[PaymentController::class,'update']);
Route::delete('/payments/{payment_id}',[PaymentController::class,'destroy']);
Route::middleware('auth:sanctum')->get('/userpayments', [PaymentController::class, 'getUserPayments']);

Route::post('/enquiries', [EnquiryController::class, 'store']); 
Route::post('/enquiries/reply/{id}', [EnquiryController::class, 'reply']); 
Route::delete('/enquiries',[EnquiryController::class,'destroy']);
Route::delete('/enquiries/{id}',[EnquiryController::class,'destroy']);
Route::get('/enquiries',[EnquiryController::class,'index']);

Route::get('/notices',[NoticeController::class,'index']);
Route::post('/notices',[NoticeController::class,'store']);

Route::get('/test-zkteco', [AttendanceController::class,'checkConnection']);
Route::get('/getusers', [AttendanceController::class,'getUsers']);
Route::post('/attendance/filter', [AttendanceController::class, 'getAttendanceByDate']);
Route::get('/attendance/date', [AttendanceController::class, 'getAttendanceByDB']);
Route::post('/attendance/store', [AttendanceController::class, 'storeAttendance']);
Route::post('/khalti/payment', [KhaltiPaymentController::class, 'purchase']);

Route::post('/khalti/payment/verify', [KhaltiPaymentController::class, 'verify']);
Route::middleware('auth:sanctum')->post('/testimonials', [TestimonialController::class, 'store']);
Route::get('/testimonials', [TestimonialController::class, 'getTestimonials']);
Route::get('/publishedtestimonials', [TestimonialController::class, 'getPublishTestimonial']);
Route::patch('testimonials/{id}/publish', [TestimonialController::class, 'publish']);

