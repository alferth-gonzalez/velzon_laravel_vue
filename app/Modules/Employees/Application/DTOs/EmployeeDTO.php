<?php
declare(strict_types=1);

namespace App\Modules\Employees\Application\DTOs;

use App\Modules\Employees\Domain\Entities\Employee;

final class EmployeeDTO {
    public function __construct(
        public string $id,
        public ?string $tenant_id,
        public string $first_name,
        public ?string $last_name,
        public string $document_type,
        public string $document_number,
        public ?string $email,
        public ?string $phone,
        public ?string $hire_date,
        public string $status
    ) {}

    public static function fromDomain(Employee $e): self {
        return new self(
            id: $e->id(),
            tenant_id: $e->tenantId(),
            first_name: $e->firstName(),
            last_name: $e->lastName(),
            document_type: $e->document()->type(),
            document_number: $e->document()->number(),
            email: $e->email()?->value(),
            phone: $e->phone()?->value(),
            hire_date: $e->hireDate()?->format('Y-m-d'),
            status: $e->status()->value
        );
    }
}