<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['guest:vendor']], function () {
    Route::post('register', [\App\Http\Controllers\Vendor\VendorAuthController::class, 'register']);
    Route::post('login', [\App\Http\Controllers\Vendor\VendorAuthController::class, 'login']);
    Route::post('reset-password/request', [\App\Http\Controllers\Vendor\VendorResetPasswordController::class, 'sendTokenViaEmail']);
    Route::get('/reset-password/{email}/{token}', [\App\Http\Controllers\Admin\ResetPasswordController::class, 'sendTokenViaEmail'])->name('vendor.reset');
    Route::post('reset-password', [\App\Http\Controllers\Vendor\VendorResetPasswordController::class, 'createNewPassword']);
});
Route::group(['middleware' => ['auth:vendor']], function () {
    Route::post('logout', [\App\Http\Controllers\Vendor\VendorAuthController::class, 'logout']);
    Route::get('me', [\App\Http\Controllers\Vendor\VendorAuthController::class, 'getAuthUser']);
    Route::post('/attach-phone', [\App\Http\Controllers\Vendor\PhoneController::class, 'store']);
    Route::get('refresh-token', [\App\Http\Controllers\Vendor\VendorAuthController::class, 'refreshToken']);
});
