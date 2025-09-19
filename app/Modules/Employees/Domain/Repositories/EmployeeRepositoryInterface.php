<?php

namespace App\Modules\Employees\Domain\Repositories;

use App\Modules\Employees\Domain\Entities\Employee;
use App\Modules\Employees\Domain\ValueObjects\DocumentId;

interface EmployeeRepositoryInterface
{
    public function findById(string $id): ?Employee;
    public function findByDocument(?string $tenantId, DocumentId $document): ?Employee;
    public function findByLastName(string $lastName): array;
    public function save(Employee $e): void;
    public function delete(string $id): void;
    public function paginate(array $filters, int $page, int $perPage): array;
}