<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['guest:admin']], function () {
    Route::post('/password/reset', [\App\Http\Controllers\Admin\ResetPasswordController::class, 'reset']);
    Route::get('/password/reset/{token}', [\App\Http\Controllers\Admin\ResetPasswordController::class, 'getResetFormData'])->name('password.reset');
    Route::post('/login', [\App\Http\Controllers\Admin\AdminAuthController::class, 'login']);
    Route::post('/register', [\App\Http\Controllers\Admin\AdminAuthController::class, 'register']);
    Route::post('/password/email', [\App\Http\Controllers\Admin\ForgotPasswordController::class, 'sendResetLinkEmail']);
});

Route::group(['middleware' => ['auth:admin']], function () {
    Route::get('/me', [\App\Http\Controllers\Admin\AdminAuthController::class, 'getAuthUser']);
    Route::post('/logout', [\App\Http\Controllers\Admin\AdminAuthController::class, 'logout']);
});
