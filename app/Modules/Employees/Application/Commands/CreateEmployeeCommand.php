<?php
namespace App\Modules\Employees\Application\Commands;

final class CreateEmployeeCommand {
    public function __construct(
        public ?string $tenantId,
        public string $firstName,
        public ?string $lastName,
        public string $documentType,
        public string $documentNumber,
        public ?string $email,
        public ?string $phone,
        public ?string $hireDate,
        public ?string $actorId
    ) {}
}
