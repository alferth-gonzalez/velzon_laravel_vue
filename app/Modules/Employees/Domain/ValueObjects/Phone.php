<?php
declare(strict_types=1);

namespace App\Modules\Employees\Domain\ValueObjects;

final class Phone {
    public function __construct(private readonly string $value) {
        // Validamos el valor original
        if ($value === '' || strlen($value) > 30) {
            throw new \InvalidArgumentException('Teléfono inválido');
        }
    }
    
    public static function fromString(string $value): self {
        $sanitized = preg_replace('/\s+/', '', $value);
        return new self($sanitized);
    }
    public function value(): string { return $this->value; }
    public function __toString(): string { return $this->value; }
}