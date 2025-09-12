<?php

declare(strict_types=1);

use App\Modules\Customers\Infrastructure\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

// Nota: La ruta web se registra en routes/web.php para evitar conflictos de orden
// Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
//     Route::get('/customers', [CustomerController::class, 'indexView'])->name('customers.index');
// });
