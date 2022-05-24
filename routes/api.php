<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LoginController;
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

// Route::group(['prefix' => 'auth'], function () {
//     Route::group(
//         ['controller' => AuthController::class],
//         function () {
//             Route::post('/login', 'login');
//             Route::post('/logout', 'logout');
//             Route::get('user_profile', 'user_profile');
//         }
//     );

    Route::group(['controller' => RegisterController::class], function () {
        Route::post('register', 'register');
        Route::post('verify_user', 'verify_user');
        Route::post('Resend_code', 'Resend_verification_code');
    });
// });

Route::group(['controller' => LoginController::class], function () {
    Route::post('/login','login');
});

Route::group(['controller' => ResetPasswordController::class], function () {
    Route::post('forgot_password', 'forgot_password');
    Route::post('check_reset_code', 'check_reset_code');
    Route::post('reset_password', 'reset_password');
});
