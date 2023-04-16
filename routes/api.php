<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
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

//Route::post('users/register', 'AuthController@register')->name('users.register');

Route::post('users/register', [AuthController::class, 'register'])->name('users.register');
Route::post('users/login', [AuthController::class, 'login'])->name('users.login');
Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('users.verify');


//Route::get('/user', function (Request $request) {
//    return $request->user();
//});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
