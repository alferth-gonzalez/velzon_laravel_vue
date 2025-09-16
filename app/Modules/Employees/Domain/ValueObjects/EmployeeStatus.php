<?php
declare(strict_types=1);

namespace App\Modules\Employees\Domain\ValueObjects;

enum EmployeeStatus: string {
    case Active = 'active';
    case Inactive = 'inactive';
}