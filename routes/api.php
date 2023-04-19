<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MerchantSettingsController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('users/register', [AuthController::class, 'register'])->name('users.register');
Route::post('users/login', [AuthController::class, 'login'])->name('users.login');

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('payments/create/{merchant}', [PaymentController::class, 'createPayment'])->name('payments.create');

    Route::group(['middleware' => ['merchant.access']], function () {
        Route::patch('merchant-settings/update/{merchantSettings}', [MerchantSettingsController::class, 'update'])->name('merchant-settings.update');
    });
});
