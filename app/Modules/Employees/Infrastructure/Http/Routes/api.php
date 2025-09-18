<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Employees\Infrastructure\Http\Controllers\EmployeeController;

Route::middleware('api')->prefix('api')->group(function () {
    Route::prefix('employees')->group(function () {
        Route::get('/', [EmployeeController::class, 'index']);
        Route::get('/{id}', [EmployeeController::class, 'show']);
        Route::post('/', [EmployeeController::class, 'store']);
        Route::put('/{id}', [EmployeeController::class, 'update']);
        Route::delete('/{id}', [EmployeeController::class, 'destroy']);
    });
});
// TEMPORAL: Auth middleware removido para testing - agregar de vuelta: ->middleware(['auth:sanctum'])