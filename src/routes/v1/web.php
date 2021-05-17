<?php

use Illuminate\Support\Facades\Route;

//user routes
Route::get('/', function () {
    return view('welcome');
});
Route::get('/token', function () {
    return csrf_token();
});

//auth routes
Route::group(['middleware' => ['api', 'isNotAuth']], function () {
    Route::post('register', [\App\Http\Controllers\V1\User\Auth\UserAuthController::class, 'register']);
    Route::post('login', [\App\Http\Controllers\V1\User\Auth\UserAuthController::class, 'login'])->name('login');
    Route::post('/password/request/reset', [\App\Http\Controllers\V1\User\Auth\ResetPassword::class, 'sendTokenViaEmail']);
    Route::get('/reset/{email}/{token}', [\App\Http\Controllers\V1\User\Auth\ResetPassword::class, 'sendTokenViaEmail'])->name('api.reset');
    Route::post('/password/reset', [\App\Http\Controllers\V1\User\Auth\ResetPassword::class, 'createNewPassword']);
});
Route::group(['middleware' => 'auth'], function () {
    Route::post('/logout', [\App\Http\Controllers\V1\User\Auth\UserAuthController::class, 'logout']);
    Route::post('/token/refresh', [\App\Http\Controllers\V1\User\Auth\UserAuthController::class, 'refreshToken']);
    Route::get('/me', [\App\Http\Controllers\V1\User\Auth\UserAuthController::class, 'getAuthUser'])->name('user');
    Route::post('/email/verification-notification', [\App\Http\Controllers\V1\User\Auth\VerifyUserEmail::class, 'requestEmailVerification'])->middleware(['throttle:6,1'])->name('verification.send');
    Route::get('/email/verify/{id}/{hash}', [\App\Http\Controllers\V1\User\Auth\VerifyUserEmail::class, 'verifyEmail'])->name('user.verification.verify');
    Route::view('/email/verify', 'auth.verify-email')->middleware('auth')->name('verification.notice');
    Route::post('/phone-add', [\App\Http\Controllers\V1\User\Auth\PhoneVerificationController::class, 'attachPhone']);
    Route::post('/phone-verify', [\App\Http\Controllers\V1\User\Auth\PhoneVerificationController::class, 'verify']);
});
//shopping routes
Route::group(['prefix' => '/category'], function () {
    //category
    Route::get('/', [\App\Http\Controllers\V1\User\CategoryController::class, 'getAll']);
    Route::get('/{category}/sub-categories', [\App\Http\Controllers\V1\User\SubCategoryController::class, 'getSubCategories']);
    Route::get('{category}/products', [\App\Http\Controllers\V1\User\CategoryController::class, 'getProducts']);
    Route::get('{category}', [\App\Http\Controllers\V1\User\CategoryController::class, 'show'])->where('category', '^((?!trashed).)*$');
});
Route::group(['prefix' => '/product/'], function () {
    //product
    Route::get('/', [\App\Http\Controllers\V1\User\ProductController::class, 'getAll']);
    Route::get('random', [\App\Http\Controllers\V1\User\ProductController::class, 'getRandom']);
    Route::get('{slug}/vendor', [\App\Http\Controllers\V1\User\ProductController::class, 'getOwnerInfo']);
    Route::get('{product}', [\App\Http\Controllers\V1\User\ProductController::class, 'show'])->where('product', '^((?!trashed).)*$');
});

Route::group(['middleware' => ['auth:api', 'verified']], function () {
    //cart Routes
    Route::group(['prefix' => '/cart'], function () {
        Route::get('/', [\App\Http\Controllers\V1\User\CartController::class, 'content']);
        Route::get('/count', [\App\Http\Controllers\V1\User\CartController::class, 'count']);
        Route::get('/empty', [\App\Http\Controllers\V1\User\CartController::class, 'empty']);
        Route::post('/', [\App\Http\Controllers\V1\User\CartController::class, 'store']);
        Route::post('/remove', [\App\Http\Controllers\V1\User\CartController::class, 'remove']);
    });
    //orders
    Route::group(['prefix' => 'order'], function () {
        Route::post('/', [\App\Http\Controllers\V1\User\OrderController::class, 'createOrder']);
        Route::post('/{orderNumber}/checkout', [\App\Http\Controllers\V1\User\OrderController::class, 'checkout']);
        Route::get('success', [\App\Http\Controllers\V1\User\OrderController::class, 'paypalOrderSuccess'])->name('paypal.success');
        Route::get('cancelled', [\App\Http\Controllers\V1\User\OrderController::class, 'paypalOrderCancelled'])->name('paypal.cancel');
    });
});
Route::group(['prefix' => 'auth', 'middleware' => ['web', 'api', 'isNotAuth']], function () {
    Route::get('/{driver}/login', [\App\Http\Controllers\V1\User\Auth\SocialAuthLogin::class, 'redirectToProvider']);
    Route::get('/{driver}/callback', [\App\Http\Controllers\V1\User\Auth\SocialAuthLogin::class, 'handleProviderCallback'])->name('success.callback');
});
