<?php
// CreateEmployeeHandler.php
namespace App\Modules\Employees\Application\Handlers;

use App\Modules\Employees\Application\Commands\CreateEmployeeCommand;
use App\Modules\Employees\Application\DTOs\EmployeeDTO;
use App\Modules\Employees\Domain\Repositories\EmployeeRepository;
use App\Modules\Employees\Domain\Entities\Employee;
use App\Modules\Employees\Domain\ValueObjects\{DocumentId, Email, Phone};

final class CreateEmployeeHandler {
    public function __construct(private EmployeeRepository $repo) {}

    public function handle(CreateEmployeeCommand $c): EmployeeDTO {
        // Verificar duplicado por documento (por tenant si aplica)
        $doc = new DocumentId($c->documentType, $c->documentNumber);
        if ($this->repo->findByDocument($c->tenantId, $doc)) {
            throw new \DomainException('Empleado duplicado (documento ya existe).');
        }

        $id = (string) \Illuminate\Support\Str::ulid(); // o inyecta un IdGenerator
        $employee = Employee::create(
            id: $id,
            tenantId: $c->tenantId,
            firstName: $c->firstName,
            lastName: $c->lastName,
            document: $doc,
            email: $c->email ? new Email($c->email) : null,
            phone: $c->phone ? new Phone($c->phone) : null,
            hireDate: $c->hireDate ? new \DateTimeImmutable($c->hireDate) : null,
            createdBy: $c->actorId
        );

        $this->repo->save($employee);
        return EmployeeDTO::fromDomain($employee);
    }
}