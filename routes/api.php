<?php

use App\Http\Controllers\TestController;
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

Route::get('/user', function (Request $request) {
    return 'test';
});

    Route::prefix('test')->group(function (){
        Route::post('create',[TestController::class,'create']);
        Route::delete('/{id}',[TestController::class,'delete']);
        Route::get('/{id}',[TestController::class,'get']);
        Route::post('test-center',[TestController::class,'getTestsForTestCenter']);
        Route::post('check-in',[TestController::class,'checkin']);
        Route::post('result',[TestController::class,'getResult']);
        Route::post('test-between',[TestController::class,'getTestBetween']);
    });
