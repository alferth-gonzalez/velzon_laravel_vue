<?php
declare(strict_types=1);

namespace App\Modules\Employees\Domain\Repositories;

use App\Modules\Employees\Domain\Entities\Employee;
use App\Modules\Employees\Domain\ValueObjects\DocumentId;

interface EmployeeRepository {
    public function findById(string $id): ?Employee;
    public function findByDocument(?string $tenantId, DocumentId $document): ?Employee;
    public function save(Employee $employee): void;
    public function delete(string $id): void; // soft delete
    /** @return array{data:Employee[],total:int} */
    public function paginate(array $filters, int $page, int $perPage): array;
}