<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['guest:admin']], function () {
    Route::post('/login', [\App\Http\Controllers\Admin\AdminAuthController::class, 'login']);
    Route::post('/register', [\App\Http\Controllers\Admin\AdminAuthController::class, 'register']);
    Route::post('/reset-password/request', [\App\Http\Controllers\Admin\ResetPasswordController::class, 'sendTokenViaEmail']);
    Route::get('/reset-password/{email}/{token}', [\App\Http\Controllers\Admin\ResetPasswordController::class, 'sendTokenViaEmail'])->name('reset');
    Route::post('/reset-password/create-password', [\App\Http\Controllers\Admin\ResetPasswordController::class, 'createNewPassword']);
});

Route::group(['middleware' => ['auth:admin']], function () {
    Route::get('/me', [\App\Http\Controllers\Admin\AdminAuthController::class, 'getAuthUser']);
    Route::post('/logout', [\App\Http\Controllers\Admin\AdminAuthController::class, 'logout']);
});
