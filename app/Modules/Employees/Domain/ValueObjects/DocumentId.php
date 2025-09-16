<?php
declare(strict_types=1);

namespace App\Modules\Employees\Domain\ValueObjects;

use App\Modules\Employees\Domain\Exceptions\InvalidEmployeeDataException;

final class DocumentId {
    public function __construct(
        private readonly string $type,
        private readonly string $number
    ) {
        if ($type === '' || $number === '') {
            throw new InvalidEmployeeDataException('Documento inválido');
        }
        if (strlen($number) > 32) {
            throw new InvalidEmployeeDataException('Documento demasiado largo');
        }
    }
    public function type(): string { return strtoupper($this->type); }
    public function number(): string { return $this->number; }
}