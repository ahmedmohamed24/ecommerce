<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'vendor'], function () {
    Route::group(['middleware' => ['guest:vendor']], function () {
        Route::post('register', [\App\Http\Controllers\V1\Vendor\VendorAuthController::class, 'register']);
        Route::post('login', [\App\Http\Controllers\V1\Vendor\VendorAuthController::class, 'login']);
        Route::post('reset-password/request', [\App\Http\Controllers\V1\Vendor\VendorResetPasswordController::class, 'sendTokenViaEmail']);
        Route::get('/reset-password/{email}/{token}', [\App\Http\Controllers\V1\Admin\ResetPasswordController::class, 'sendTokenViaEmail'])->name('vendor.reset');
        Route::post('reset-password', [\App\Http\Controllers\V1\Vendor\VendorResetPasswordController::class, 'createNewPassword']);
    });
    Route::group(['middleware' => ['auth:vendor']], function () {
        Route::post('logout', [\App\Http\Controllers\V1\Vendor\VendorAuthController::class, 'logout']);
        Route::get('me', [\App\Http\Controllers\V1\Vendor\VendorAuthController::class, 'getAuthUser']);
        Route::post('/email/verification-notification', [\App\Http\Controllers\V1\Vendor\VerifyEmailController::class, 'requestEmailVerification'])->middleware(['throttle:6,1'])->name('verification.send');
        Route::get('/email/verify/{id}/{hash}', [\App\Http\Controllers\V1\Vendor\VerifyEmailController::class, 'verifyEmail'])->name('vendor.verification.verify');
        Route::get('refresh-token', [\App\Http\Controllers\V1\Vendor\VendorAuthController::class, 'refreshToken']);
        Route::post('/attach-phone', [\App\Http\Controllers\V1\Vendor\PhoneController::class, 'store']);
        Route::post('/verify-phone', [\App\Http\Controllers\V1\Vendor\PhoneController::class, 'verify']);
    });
});
Route::group(['middleware' => 'auth:vendor'], function () {
    Route::group(['prefix' => '/product/'], function () {
        //product
        Route::get('trashed', [\App\Http\Controllers\V1\User\ProductController::class, 'getTrashed']);
        Route::post('/', [\App\Http\Controllers\V1\User\ProductController::class, 'store']);
        Route::post('{product}/restore', [\App\Http\Controllers\V1\User\ProductController::class, 'restore']);
        Route::put('{product}', [\App\Http\Controllers\V1\User\ProductController::class, 'update']);
        Route::delete('{product}', [\App\Http\Controllers\V1\User\ProductController::class, 'destroy']);
    });
});
