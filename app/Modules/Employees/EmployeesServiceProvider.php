<?php
declare(strict_types=1);

namespace App\Modules\Employees;

use Illuminate\Support\ServiceProvider;
use App\Modules\Employees\Domain\Repositories\EmployeeRepositoryInterface;
use App\Modules\Employees\Infrastructure\Database\Repositories\EloquentEmployeeRepository;

final class EmployeesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(EmployeeRepositoryInterface::class, EloquentEmployeeRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/Infrastructure/Http/Routes/api.php');
        $this->loadMigrationsFrom(__DIR__.'/Infrastructure/Database/Migrations');
        // $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'employees');
        // Event::listen(...);
        // Gate::policy(...);
    }
}