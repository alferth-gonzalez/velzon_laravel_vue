<?php

declare(strict_types=1);

namespace App\Modules\Customers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Modules\Customers\Application\Contracts\CustomerRepositoryInterface;
use App\Modules\Customers\Application\Contracts\CustomerReadModelInterface;
use App\Modules\Customers\Application\Contracts\CustomerExportPortInterface;
use App\Modules\Customers\Application\Contracts\ComplianceGatewayInterface;
use App\Modules\Customers\Application\Contracts\IdempotencyRepositoryInterface;
use App\Modules\Customers\Infrastructure\Repositories\EloquentCustomerRepository;
use App\Modules\Customers\Infrastructure\Repositories\EloquentIdempotencyRepository;
use App\Modules\Customers\Infrastructure\Services\CustomerReadModelService;
use App\Modules\Customers\Infrastructure\Services\FakeCustomerExportService;
use App\Modules\Customers\Infrastructure\Services\FakeComplianceGateway;

class CustomersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Registrar bindings de contratos
        $this->app->bind(CustomerRepositoryInterface::class, EloquentCustomerRepository::class);
        $this->app->bind(IdempotencyRepositoryInterface::class, EloquentIdempotencyRepository::class);
        $this->app->bind(CustomerReadModelInterface::class, CustomerReadModelService::class);
        $this->app->bind(CustomerExportPortInterface::class, FakeCustomerExportService::class);
        $this->app->bind(ComplianceGatewayInterface::class, FakeComplianceGateway::class);
    }

    public function boot(): void
    {
        // Registrar rutas API
        $this->loadRoutesFrom(__DIR__ . '/Infrastructure/Http/Routes/api.php');
        
        // Registrar rutas web
        $this->loadRoutesFrom(__DIR__ . '/Infrastructure/Http/Routes/web.php');
        
        // Registrar migraciones
        $this->loadMigrationsFrom(__DIR__ . '/Infrastructure/Database/Migrations');
        
        // Registrar comandos de consola si los hay
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/Infrastructure/Database/Migrations' => database_path('migrations'),
            ], 'customers-migrations');
        }
    }
}
