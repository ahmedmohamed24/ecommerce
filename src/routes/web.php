<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/token', function () {
    return csrf_token();
});

//auth routes
Route::group(['middleware' => ['api', 'isAuth']], function () {
    Route::post('register', [\App\Http\Controllers\User\Auth\RegisterController::class, 'register']);
    Route::post('login', [\App\Http\Controllers\User\Auth\LoginController::class, 'login'])->name('login');
    Route::post('/password/request/reset', [\App\Http\Controllers\User\Auth\ResetPassword::class, 'sendTokenViaEmail']);
    Route::get('/reset/{email}/{token}', [\App\Http\Controllers\User\Auth\ResetPassword::class, 'sendTokenViaEmail'])->name('reset');
    Route::post('/password/reset', [\App\Http\Controllers\User\Auth\ResetPassword::class, 'createNewPassword']);
});
Route::group(['middleware' => 'auth'], function () {
    Route::post('/logout', [\App\Http\Controllers\User\Auth\LogoutController::class, 'logout']);
    Route::post('/token/refresh', [\App\Http\Controllers\User\Auth\UserController::class, 'refreshToken']);
    Route::get('/me', [\App\Http\Controllers\User\Auth\UserController::class, 'getAuthUser'])->name('user');
    Route::post('/email/verification-notification', [\App\Http\Controllers\VerifyUserEmail::class, 'requestEmailVerification'])->middleware(['throttle:6,1'])->name('verification.send');
    Route::get('/email/verify/{id}/{hash}', [\App\Http\Controllers\VerifyUserEmail::class, 'verifyEmail'])->name('verification.verify');
    Route::post('/phone/add', [\App\Http\Controllers\PhoneVerificationController::class, 'attachPhone']);
});

Route::group(['middleware' => 'verified'], function () {
    Route::group(['prefix' => '/product/'], function () {
        //product
        Route::get('/', [\App\Http\Controllers\ProductController::class, 'getAll']);
        Route::get('trashed', [\App\Http\Controllers\ProductController::class, 'getTrashed']);
        Route::get('random', [\App\Http\Controllers\ProductController::class, 'getRandom']);
        Route::get('{product}', [\App\Http\Controllers\ProductController::class, 'show']);
        Route::post('/', [\App\Http\Controllers\ProductController::class, 'store']);
        Route::post('{product}/restore', [\App\Http\Controllers\ProductController::class, 'restore']);
        Route::put('{product}', [\App\Http\Controllers\ProductController::class, 'update']);
        Route::delete('{product}', [\App\Http\Controllers\ProductController::class, 'destory']);
    });
    Route::group(['prefix' => '/category'], function () {
        //category
        Route::get('/', [\App\Http\Controllers\CategoryController::class, 'getAll']);
        Route::get('/trashed', [\App\Http\Controllers\CategoryController::class, 'getTrashed']);
        Route::post('/', [\App\Http\Controllers\CategoryController::class, 'store']);
        Route::get('{category}', [\App\Http\Controllers\CategoryController::class, 'show']);
        Route::get('{category}/products', [\App\Http\Controllers\CategoryController::class, 'getProducts']);
        Route::put('{category}', [\App\Http\Controllers\CategoryController::class, 'update']);
        Route::delete('{category}', [\App\Http\Controllers\CategoryController::class, 'softDelete']);
        Route::delete('{category}/delete', [\App\Http\Controllers\CategoryController::class, 'hardDelete']);
        Route::post('{category}/restore', [\App\Http\Controllers\CategoryController::class, 'restore']);
        //subCategory routes
        Route::post('/{category}/attach/sub', [\App\Http\Controllers\SubCategoryController::class, 'store']);
        Route::get('/{category}/sub-categories', [\App\Http\Controllers\SubCategoryController::class, 'getSubCategories']);
    });

    //cart Routes
    Route::group(['prefix' => '/cart'], function () {
        Route::get('/', [\App\Http\Controllers\CartController::class, 'content']);
        Route::get('/count', [\App\Http\Controllers\CartController::class, 'count']);
        Route::get('/empty', [\App\Http\Controllers\CartController::class, 'empty']);
        Route::post('/', [\App\Http\Controllers\CartController::class, 'store']);
        Route::post('/remove', [\App\Http\Controllers\CartController::class, 'remove']);
    });
    //orders
    Route::group(['prefix' => 'order'], function () {
        Route::post('/', [\App\Http\Controllers\OrderController::class, 'createOrder']);
        Route::post('/{ordertNumber}/checkout', [\App\Http\Controllers\OrderController::class, 'checkout']);
        Route::get('success', [\App\Http\Controllers\OrderController::class, 'paypalOrderSuccess'])->name('paypal.success');
        Route::get('cancelled', [\App\Http\Controllers\OrderController::class, 'paypalOrderCancelled'])->name('paypal.cancel');
    });

    Route::group(['prefix' => '/stripe'], function () {
        Route::get('/balance', [\App\Http\Controllers\StripeController::class, 'getBalance']);
        Route::get('/balance/transactions', [\App\Http\Controllers\StripeController::class, 'getBalanceTransactions']);
        Route::get('/charge/all', [\App\Http\Controllers\StripeController::class, 'getAllCharges']);
        Route::get('/charge/{charge}', [\App\Http\Controllers\StripeController::class, 'getCharge']);
    });
});
Route::group(['prefix' => 'auth', 'middelware' => ['api', 'isAuth']], function () {
    Route::get('/{driver}/login', [\App\Http\Controllers\SocialAuthLogin::class, 'redirectToProvider']);
    Route::get('/{driver}/callback', [\App\Http\Controllers\SocialAuthLogin::class, 'handleProviderCallback'])->name('success.callback');
});
