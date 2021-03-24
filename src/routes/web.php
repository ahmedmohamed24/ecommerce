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
    Route::post('login', [\App\Http\Controllers\User\Auth\LoginController::class, 'login']);
    Route::post('/password/request/reset', [\App\Http\Controllers\User\Auth\ResetPassword::class, 'sendTokenViaEmail']);
    Route::get('/reset/{email}/{token}', [\App\Http\Controllers\User\Auth\ResetPassword::class, 'sendTokenViaEmail'])->name('reset');
    Route::post('/password/reset', [\App\Http\Controllers\User\Auth\ResetPassword::class, 'createNewPassword']);
});
Route::group(['middleware' => 'auth'], function () {
    Route::post('/logout', [\App\Http\Controllers\User\Auth\LogoutController::class, 'logout']);
    Route::post('/token/refresh', [\App\Http\Controllers\User\Auth\UserController::class,'refreshToken']);
    Route::get('/me', [\App\Http\Controllers\User\Auth\UserController::class,'getAuthUser']);
});

Route::group(['prefix'=>'/product/'], function () {
    //product
    Route::get('/', [\App\Http\Controllers\ProductController::class,'getAll']);
    Route::get('trashed', [\App\Http\Controllers\ProductController::class,'getTrashed']);
    Route::get('random', [\App\Http\Controllers\ProductController::class,'getRandom']);
    Route::post('/', [\App\Http\Controllers\ProductController::class,'store']);
    Route::get('{product}', [\App\Http\Controllers\ProductController::class,'show']);
    Route::patch('{product}', [\App\Http\Controllers\ProductController::class,'update']);
    Route::delete('{product}', [\App\Http\Controllers\ProductController::class,'destory']);
    Route::post('{product}/restore', [\App\Http\Controllers\ProductController::class,'restore']);
});
Route::group(['prefix'=>'/category'], function () {
    //category
    Route::get('/', [\App\Http\Controllers\CategoryController::class,'getAll']);
    Route::get('/trashed', [\App\Http\Controllers\CategoryController::class,'getTrashed']);
    Route::post('/', [\App\Http\Controllers\CategoryController::class,'store']);
    Route::get('{category}', [\App\Http\Controllers\CategoryController::class,'show']);
    Route::post('{category}/products', [\App\Http\Controllers\CategoryController::class,'getProducts']);
    Route::put('{category}', [\App\Http\Controllers\CategoryController::class,'update']);
    Route::delete('{category}', [\App\Http\Controllers\CategoryController::class,'softDelete']);
    Route::delete('{category}/delete', [\App\Http\Controllers\CategoryController::class,'hardDelete']);
    Route::get('{category}/restore', [\App\Http\Controllers\CategoryController::class,'restore']);
    //subCategory routes
    Route::post('/{category}/create/sub', [\App\Http\Controllers\CategorySubController::class,'store']);
});
