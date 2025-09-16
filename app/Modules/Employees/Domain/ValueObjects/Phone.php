<?php
declare(strict_types=1);

namespace App\Modules\Employees\Domain\ValueObjects;

final class Phone {
    public function __construct(private readonly string $value) {
        // Valida formato básico E.164 o sanitiza a tu gusto
        $v = preg_replace('/\s+/', '', $value);
        if ($v === '' || strlen($v) > 30) {
            throw new \InvalidArgumentException('Teléfono inválido');
        }
        $this->value = $v;
    }
    public function value(): string { return $this->value; }
    public function __toString(): string { return $this->value; }
}