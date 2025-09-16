<?php
// UpdateEmployeeHandler.php
namespace App\Modules\Employees\Application\Handlers;

use App\Modules\Employees\Application\Commands\UpdateEmployeeCommand;
use App\Modules\Employees\Application\DTOs\EmployeeDTO;
use App\Modules\Employees\Domain\Repositories\EmployeeRepository;
use App\Modules\Employees\Domain\ValueObjects\{Email, Phone};
use App\Modules\Employees\Domain\Exceptions\EmployeeNotFoundException;

final class UpdateEmployeeHandler {
    public function __construct(private EmployeeRepository $repo) {}

    public function handle(UpdateEmployeeCommand $c): EmployeeDTO {
        $e = $this->repo->findById($c->id);
        if (!$e) throw new EmployeeNotFoundException('Empleado no encontrado');

        $e->update(
            firstName: $c->firstName,
            lastName: $c->lastName,
            email: $c->email ? new Email($c->email) : null,
            phone: $c->phone ? new Phone($c->phone) : null,
            hireDate: $c->hireDate ? new \DateTimeImmutable($c->hireDate) : null,
            updatedBy: $c->actorId
        );

        $this->repo->save($e);
        return EmployeeDTO::fromDomain($e);
    }
}