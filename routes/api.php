<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ResetPasswordController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});*/

//Route::group([
//    'middleware' => 'api',
//    'prefix' => 'auth'
//], function ($router) {
//    Route::post('/login', [AuthController::class, 'login']);
//    Route::post('/register', [AuthController::class, 'register']);
//    Route::post('/logout', [AuthController::class, 'logout']);
//    Route::post('/refresh', [AuthController::class, 'refresh']);
//    Route::get('/user-profile', [AuthController::class, 'userProfile']);
//});


Route::group(['prefix' => 'auth'], function () {
    Route::group([
        'controller' => AuthController::class
    ],
        function () {
            Route::post('/login', 'login');
            Route::post('/logout', 'logout');
            Route::get('user_profile', 'user_profile');
        }
    );

    Route::group(['controller' => RegisterController::class], function () {
        Route::post('register', 'register');
        Route::post('verify_user/{id}', 'verify_user');
    });

});

Route::group(['controller' => ResetPasswordController::class], function () {
    Route::post('forgot_password', 'forgot_password');
    Route::post('check_reset_code', 'check_reset_code');
    Route::post('reset_password', 'reset_password');
});


