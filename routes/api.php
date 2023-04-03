<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\EmployeeController;
use App\Http\Controllers\API\LogController;
use App\Http\Controllers\API\ProfileController;
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

//can send 10 request per minute maximum
Route::middleware([
    'auth:api',
    'throttle:api',
    ])
    ->prefix('user/profile')
    ->as('user.profile.')
    ->group(function () {
        Route::get('/{user}',[ProfileController::class, 'show'])->name('show');
        Route::patch('/{profile}',[ProfileController::class, 'update'])->name('update');        
    }
);

Route::middleware([
    'auth:api',
    'throttle:api',
    ])
    ->prefix('employees')
    ->as('employees.')
    ->group(function () {
        Route::get('/search',[EmployeeController::class, 'index'])->name('index');
        Route::patch('/{employee}',[EmployeeController::class, 'update'])->name('update');        
        Route::get('/{employee}', [EmployeeController::class, 'show']);
        Route::delete('/{employee}', [EmployeeController::class, 'destroy']);
        Route::get('/{employee}/managers', [EmployeeController::class, 'managers']);
        Route::get('/{employee}/managers-salary', [EmployeeController::class, 'managersSalary']);
        Route::get('/{date}/logs', [LogController::class,'show']);        
        Route::post('/import', [EmployeeController::class, 'importCSV']);
        Route::options('/export-csv', [EmployeeController::class, 'exportCsv']);
    }
);

