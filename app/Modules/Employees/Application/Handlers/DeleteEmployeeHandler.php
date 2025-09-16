<?php
// DeleteEmployeeHandler.php
namespace App\Modules\Employees\Application\Handlers;

use App\Modules\Employees\Application\Commands\DeleteEmployeeCommand;
use App\Modules\Employees\Domain\Repositories\EmployeeRepository;

final class DeleteEmployeeHandler {
    public function __construct(private EmployeeRepository $repo) {}
    public function handle(DeleteEmployeeCommand $c): void {
        $this->repo->delete($c->id); // soft delete
    }
}