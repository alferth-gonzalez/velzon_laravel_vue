<?php
// DeleteEmployeeHandler.php
namespace App\Modules\Employees\Application\Handlers;

use App\Modules\Employees\Application\Commands\DeleteEmployeeCommand;
use App\Modules\Employees\Domain\Repositories\EmployeeRepositoryInterface;

final class DeleteEmployeeHandler {
    public function __construct(private EmployeeRepositoryInterface $repo) {}
    public function handle(DeleteEmployeeCommand $c): void {
        $this->repo->delete($c->id); // soft delete
    }
}