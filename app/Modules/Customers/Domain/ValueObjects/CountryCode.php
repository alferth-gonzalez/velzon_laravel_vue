<?php

declare(strict_types=1);

namespace App\Modules\Customers\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class CountryCode
{
    public function __construct(
        public string $value
    ) {
        $this->validate($value);
    }

    private function validate(string $code): void
    {
        if (empty($code)) {
            throw new InvalidArgumentException('El código de país no puede estar vacío');
        }

        if (strlen($code) !== 2) {
            throw new InvalidArgumentException('El código de país debe tener exactamente 2 caracteres');
        }

        if (!ctype_alpha($code)) {
            throw new InvalidArgumentException('El código de país debe contener solo letras');
        }

        // Lista básica de códigos ISO 3166-1 alpha-2
        $validCodes = [
            'CO', 'US', 'CA', 'MX', 'BR', 'AR', 'CL', 'PE', 'EC', 'VE',
            'ES', 'FR', 'DE', 'IT', 'GB', 'PT', 'NL', 'BE', 'CH', 'AT'
        ];

        $upperCode = strtoupper($code);
        if (!in_array($upperCode, $validCodes, true)) {
            throw new InvalidArgumentException('Código de país no válido: ' . $code);
        }
    }

    public function normalized(): string
    {
        return strtoupper($this->value);
    }

    public function __toString(): string
    {
        return $this->normalized();
    }

    public function equals(CountryCode $other): bool
    {
        return $this->normalized() === $other->normalized();
    }
}
