<?php

declare(strict_types=1);

namespace App\Modules\Customers\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class DocumentId
{
    public function __construct(
        public string $type,
        public string $number
    ) {
        $this->validateType($type);
        $this->validateNumber($number);
    }

    private function validateType(string $type): void
    {
        $validTypes = ['CC', 'NIT', 'CE', 'PA', 'TI', 'RC'];
        
        if (!in_array($type, $validTypes, true)) {
            throw new InvalidArgumentException(
                'Tipo de documento inválido. Tipos válidos: ' . implode(', ', $validTypes)
            );
        }
    }

    private function validateNumber(string $number): void
    {
        if (empty($number)) {
            throw new InvalidArgumentException('El número de documento no puede estar vacío');
        }

        $numbersOnly = preg_replace('/[^0-9]/', '', $number);
        
        if (empty($numbersOnly)) {
            throw new InvalidArgumentException('El número de documento debe contener al menos un dígito');
        }

        if (strlen($numbersOnly) < 5 || strlen($numbersOnly) > 15) {
            throw new InvalidArgumentException('El número de documento debe tener entre 5 y 15 dígitos');
        }

        // Validación específica para NIT (dígito de verificación)
        if ($this->type === 'NIT') {
            $this->validateNIT($numbersOnly);
        }
    }

    private function validateNIT(string $nit): void
    {
        // Algoritmo de validación de NIT colombiano simplificado
        if (strlen($nit) < 8) {
            throw new InvalidArgumentException('El NIT debe tener al menos 8 dígitos');
        }

        $nitNumber = substr($nit, 0, -1);
        $checkDigit = (int) substr($nit, -1);
        
        $factors = [3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71];
        $sum = 0;
        
        for ($i = 0; $i < strlen($nitNumber); $i++) {
            $sum += (int) $nitNumber[$i] * $factors[$i % count($factors)];
        }
        
        $calculatedDigit = $sum % 11;
        $calculatedDigit = $calculatedDigit < 2 ? $calculatedDigit : 11 - $calculatedDigit;
        
        if ($calculatedDigit !== $checkDigit) {
            throw new InvalidArgumentException('El dígito de verificación del NIT es incorrecto');
        }
    }

    public function normalized(): string
    {
        return preg_replace('/[^0-9]/', '', $this->number);
    }

    public function formatted(): string
    {
        $normalized = $this->normalized();
        
        return match ($this->type) {
            'NIT' => $this->formatNIT($normalized),
            'CC' => $this->formatCC($normalized),
            default => $normalized
        };
    }

    private function formatNIT(string $nit): string
    {
        if (strlen($nit) >= 9) {
            $number = substr($nit, 0, -1);
            $checkDigit = substr($nit, -1);
            return number_format((int) $number, 0, '', '.') . '-' . $checkDigit;
        }
        
        return $nit;
    }

    private function formatCC(string $cc): string
    {
        return number_format((int) $cc, 0, '', '.');
    }

    public function __toString(): string
    {
        return $this->type . ':' . $this->number;
    }

    public function equals(DocumentId $other): bool
    {
        return $this->type === $other->type && 
               $this->normalized() === $other->normalized();
    }
}
