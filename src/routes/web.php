<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/token', function () {
    return csrf_token();
});

//auth routes
Route::group(['middleware' => ['api', 'isAuth']], function () {
    Route::post('register', [\App\Http\Controllers\User\Auth\UserAuthController::class, 'register']);
    Route::post('login', [\App\Http\Controllers\User\Auth\UserAuthController::class, 'login'])->name('login');
    Route::post('/password/request/reset', [\App\Http\Controllers\User\Auth\ResetPassword::class, 'sendTokenViaEmail']);
    Route::get('/reset/{email}/{token}', [\App\Http\Controllers\User\Auth\ResetPassword::class, 'sendTokenViaEmail'])->name('api.reset');
    Route::post('/password/reset', [\App\Http\Controllers\User\Auth\ResetPassword::class, 'createNewPassword']);
});
Route::group(['middleware' => 'auth'], function () {
    Route::post('/logout', [\App\Http\Controllers\User\Auth\UserAuthController::class, 'logout']);
    Route::post('/token/refresh', [\App\Http\Controllers\User\Auth\UserAuthController::class, 'refreshToken']);
    Route::get('/me', [\App\Http\Controllers\User\Auth\UserAuthController::class, 'getAuthUser'])->name('user');
    Route::post('/email/verification-notification', [\App\Http\Controllers\User\Auth\VerifyUserEmail::class, 'requestEmailVerification'])->middleware(['throttle:6,1'])->name('verification.send');
    Route::get('/email/verify/{id}/{hash}', [\App\Http\Controllers\User\Auth\VerifyUserEmail::class, 'verifyEmail'])->name('verification.verify');
    Route::post('/phone/add', [\App\Http\Controllers\User\Auth\PhoneVerificationController::class, 'attachPhone']);
    Route::view('/email/verify', 'auth.verify-email')->middleware('auth')->name('verification.notice');
});

Route::group(['middleware' => 'verified'], function () {
    Route::group(['prefix' => '/product/'], function () {
        //product
        Route::get('/', [\App\Http\Controllers\User\ProductController::class, 'getAll']);
        Route::get('trashed', [\App\Http\Controllers\User\ProductController::class, 'getTrashed']);
        Route::get('random', [\App\Http\Controllers\User\ProductController::class, 'getRandom']);
        Route::get('{product}', [\App\Http\Controllers\User\ProductController::class, 'show']);
        Route::post('/', [\App\Http\Controllers\User\ProductController::class, 'store']);
        Route::post('{product}/restore', [\App\Http\Controllers\User\ProductController::class, 'restore']);
        Route::put('{product}', [\App\Http\Controllers\User\ProductController::class, 'update']);
        Route::delete('{product}', [\App\Http\Controllers\User\ProductController::class, 'destory']);
    });
    Route::group(['prefix' => '/category'], function () {
        //category
        Route::get('/', [\App\Http\Controllers\User\CategoryController::class, 'getAll']);
        Route::get('/trashed', [\App\Http\Controllers\User\CategoryController::class, 'getTrashed']);
        Route::post('/', [\App\Http\Controllers\User\CategoryController::class, 'store']);
        Route::get('{category}', [\App\Http\Controllers\User\CategoryController::class, 'show']);
        Route::get('{category}/products', [\App\Http\Controllers\User\CategoryController::class, 'getProducts']);
        Route::put('{category}', [\App\Http\Controllers\User\CategoryController::class, 'update']);
        Route::delete('{category}', [\App\Http\Controllers\User\CategoryController::class, 'softDelete']);
        Route::delete('{category}/delete', [\App\Http\Controllers\User\CategoryController::class, 'hardDelete']);
        Route::post('{category}/restore', [\App\Http\Controllers\User\CategoryController::class, 'restore']);
        //subCategory routes
        Route::post('/{category}/attach/sub', [\App\Http\Controllers\User\SubCategoryController::class, 'store']);
        Route::get('/{category}/sub-categories', [\App\Http\Controllers\User\SubCategoryController::class, 'getSubCategories']);
    });

    //cart Routes
    Route::group(['prefix' => '/cart'], function () {
        Route::get('/', [\App\Http\Controllers\User\CartController::class, 'content']);
        Route::get('/count', [\App\Http\Controllers\User\CartController::class, 'count']);
        Route::get('/empty', [\App\Http\Controllers\User\CartController::class, 'empty']);
        Route::post('/', [\App\Http\Controllers\User\CartController::class, 'store']);
        Route::post('/remove', [\App\Http\Controllers\User\CartController::class, 'remove']);
    });
    //orders
    Route::group(['prefix' => 'order'], function () {
        Route::post('/', [\App\Http\Controllers\User\OrderController::class, 'createOrder']);
        Route::post('/{orderNumber}/checkout', [\App\Http\Controllers\User\OrderController::class, 'checkout']);
        Route::get('success', [\App\Http\Controllers\User\OrderController::class, 'paypalOrderSuccess'])->name('paypal.success');
        Route::get('cancelled', [\App\Http\Controllers\User\OrderController::class, 'paypalOrderCancelled'])->name('paypal.cancel');
    });

    Route::group(['prefix' => '/stripe'], function () {
        Route::get('/balance', [\App\Http\Controllers\User\StripeController::class, 'getBalance']);
        Route::get('/balance/transactions', [\App\Http\Controllers\User\StripeController::class, 'getBalanceTransactions']);
        Route::get('/charge/all', [\App\Http\Controllers\User\StripeController::class, 'getAllCharges']);
        Route::get('/charge/{charge}', [\App\Http\Controllers\User\StripeController::class, 'getCharge']);
    });
});
Route::group(['prefix' => 'auth', 'middleware' => ['api', 'isAuth']], function () {
    Route::get('/{driver}/login', [\App\Http\Controllers\User\Auth\SocialAuthLogin::class, 'redirectToProvider']);
    Route::get('/{driver}/callback', [\App\Http\Controllers\User\Auth\SocialAuthLogin::class, 'handleProviderCallback'])->name('success.callback');
});
