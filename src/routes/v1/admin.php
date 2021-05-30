<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['guest:admin']], function () {
    Route::group(['prefix' => 'admin'], function () {
        Route::post('/login', [\App\Http\Controllers\V1\Admin\AdminAuthController::class, 'login']);
        Route::post('/reset-password/request', [\App\Http\Controllers\V1\Admin\ResetPasswordController::class, 'sendTokenViaEmail']);
        Route::view('/reset-password/{email}/{token}', 'admin.password.create')->name('admin.reset');
        Route::post('/reset-password/create-password', [\App\Http\Controllers\V1\Admin\ResetPasswordController::class, 'createNewPassword']);
    });
});
Route::group(['middleware' => 'auth:admin'], function () {
    Route::group(['prefix' => 'admin'], function () {
        Route::get('/me', [\App\Http\Controllers\V1\Admin\AdminAuthController::class, 'getAuthUser']);
        Route::post('/logout', [\App\Http\Controllers\V1\Admin\AdminAuthController::class, 'logout']);
    });
    Route::group(['prefix' => '/category'], function () {
        //category
        Route::get('/trashed', [\App\Http\Controllers\V1\Admin\Category\CategoryController::class, 'getTrashed']);
        Route::post('/', [\App\Http\Controllers\V1\Admin\Category\CategoryController::class, 'store']);
        Route::put('{category}', [\App\Http\Controllers\V1\Admin\Category\CategoryController::class, 'update']);
        Route::delete('{category}', [\App\Http\Controllers\V1\Admin\Category\CategoryController::class, 'softDelete']);
        Route::delete('{category}/delete', [\App\Http\Controllers\V1\Admin\Category\CategoryController::class, 'hardDelete']);
        Route::post('{category}/restore', [\App\Http\Controllers\V1\Admin\Category\CategoryController::class, 'restore']);
        //subCategory routes
        Route::post('/{category}/attach/sub', [\App\Http\Controllers\V1\User\SubCategoryController::class, 'store']);
    });
    //store current balance in stripe
    Route::group(['prefix' => '/stripe'], function () {
        Route::get('/balance', [\App\Http\Controllers\V1\User\StripeController::class, 'getBalance']);
        Route::get('/balance/transactions', [\App\Http\Controllers\V1\User\StripeController::class, 'getBalanceTransactions']);
        Route::get('/charge/all', [\App\Http\Controllers\V1\User\StripeController::class, 'getAllCharges']);
        Route::get('/charge/{charge}', [\App\Http\Controllers\V1\User\StripeController::class, 'getCharge']);
    });
    Route::group(['prefix' => 'attribute'], function () {
        Route::post('/', [App\Http\Controllers\V1\Admin\Product\ProductAttributeController::class, 'storeAttribute'])->name('attribute.store');
        Route::post('/option', [App\Http\Controllers\V1\Admin\Product\ProductAttributeController::class, 'storeOption'])->name('attribute.option.store');
    });
});
