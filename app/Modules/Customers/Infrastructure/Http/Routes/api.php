<?php

declare(strict_types=1);

use App\Modules\Customers\Infrastructure\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/customers')->middleware(['auth:sanctum'])->group(function () {
    // CRUD básico
    Route::get('/', [CustomerController::class, 'index'])->name('customers.api.index');
    Route::post('/', [CustomerController::class, 'storeSimple'])->name('customers.store');
    Route::get('/{id}', [CustomerController::class, 'show'])->name('customers.show');
    Route::put('/{id}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    
    // Funcionalidades especiales
    Route::get('/search', [CustomerController::class, 'search'])->name('customers.search');
    Route::post('/merge', [CustomerController::class, 'merge'])->name('customers.merge');
    Route::post('/{id}/blacklist', [CustomerController::class, 'blacklist'])->name('customers.blacklist');
    
    // Exportación (TODO: implementar)
    // Route::get('/export', [CustomerExportController::class, 'export'])->name('customers.export');
});
