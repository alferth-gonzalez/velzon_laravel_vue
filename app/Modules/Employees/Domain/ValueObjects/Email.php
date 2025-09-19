<?php
declare(strict_types=1);

namespace App\Modules\Employees\Domain\ValueObjects;

use App\Modules\Employees\Domain\Exceptions\InvalidEmployeeDataException;

final class Email {
    public function __construct(private readonly string $value) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmployeeDataException('Email inválido');
        }
    }
    public function value(): string { return strtolower($this->value); }
    public function __toString(): string { return $this->value(); }
}