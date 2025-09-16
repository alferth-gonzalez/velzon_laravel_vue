<?php
namespace App\Modules\Employees\Application\Commands;

final class UpdateEmployeeCommand {
    public function __construct(
        public string $id,
        public string $firstName,
        public ?string $lastName,
        public ?string $email,
        public ?string $phone,
        public ?string $hireDate,
        public ?string $actorId
    ) {}
}