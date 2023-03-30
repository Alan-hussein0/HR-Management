<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProfileController;
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

Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/register', 'register');
});

Route::middleware(['api','auth'])
    ->prefix('user/profile')
    ->as('user.profile.')
    ->group(function(){
        Route::get('/{user}',[ProfileController::class, 'show'])->name('show');
        Route::patch('/{profile}',[ProfileController::class, 'update'])->name('update');        
        
    }
);