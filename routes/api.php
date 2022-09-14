<?php

use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'auth:sanctum'], function(){
    Route::post('add-event', [\App\Http\Controllers\EventController::class, 'addEvent'])->name('add');
    Route::put('update-event/{event_id}', [\App\Http\Controllers\EventController::class, 'updateEvent'])->name('update');
    Route::delete('delete-event/{event_id}', [\App\Http\Controllers\EventController::class, 'deleteEvent'])->name('delete');
    Route::get('view-event/{event_id}', [\App\Http\Controllers\EventController::class, 'viewEvent'])->name('view');
    Route::get('view-events', [\App\Http\Controllers\EventController::class, 'viewEvents'])->name('viewall');
});


Route::post('register', [\App\Http\Controllers\UserController::class, 'register'])->name('register');
Route::post('login', [\App\Http\Controllers\UserController::class, 'login'])->name('login');
Route::post('account-recovery', [\App\Http\Controllers\UserController::class, 'recovery'])->name('account-recovery');
Route::post('valid-reset-pin', [\App\Http\Controllers\UserController::class, 'checkResetPin'])->name('valid-reset-pin');
Route::post('reset-password', [\App\Http\Controllers\UserController::class, 'resetPassword'])->name('reset-password');
