<?php

declare(strict_types=1);

namespace App\Modules\Customers\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class Phone
{
    public function __construct(
        public string $value
    ) {
        $this->validate($value);
    }

    private function validate(string $phone): void
    {
        if (empty($phone)) {
            throw new InvalidArgumentException('El teléfono no puede estar vacío');
        }

        // Remover todos los caracteres no numéricos para validación
        $numbersOnly = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($numbersOnly) < 7 || strlen($numbersOnly) > 15) {
            throw new InvalidArgumentException('El teléfono debe tener entre 7 y 15 dígitos');
        }
    }

    /**
     * Normaliza el teléfono al formato E.164 (ejemplo simplificado)
     */
    public function normalized(): string
    {
        $numbersOnly = preg_replace('/[^0-9]/', '', $this->value);
        
        // Si comienza con 57 (Colombia) y tiene 12 dígitos, es +57
        if (strlen($numbersOnly) === 12 && str_starts_with($numbersOnly, '57')) {
            return '+' . $numbersOnly;
        }
        
        // Si comienza con 3 (celular Colombia) y tiene 10 dígitos, agregar +57
        if (strlen($numbersOnly) === 10 && str_starts_with($numbersOnly, '3')) {
            return '+57' . $numbersOnly;
        }
        
        // Si no tiene código de país, asumir Colombia
        if (strlen($numbersOnly) >= 7 && strlen($numbersOnly) <= 10) {
            return '+57' . $numbersOnly;
        }
        
        // Para otros casos, agregar + si no lo tiene
        return str_starts_with($this->value, '+') ? $this->value : '+' . $numbersOnly;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(Phone $other): bool
    {
        return $this->normalized() === $other->normalized();
    }
}
