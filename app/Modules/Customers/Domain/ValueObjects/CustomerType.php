<?php

declare(strict_types=1);

namespace App\Modules\Customers\Domain\ValueObjects;

use InvalidArgumentException;

enum CustomerType: string
{
    case NATURAL = 'natural';
    case JURIDICAL = 'juridical';

    public function isNatural(): bool
    {
        return $this === self::NATURAL;
    }

    public function isJuridical(): bool
    {
        return $this === self::JURIDICAL;
    }

    public function description(): string
    {
        return match ($this) {
            self::NATURAL => 'Persona Natural',
            self::JURIDICAL => 'Persona Jurídica',
        };
    }

    public function validDocumentTypes(): array
    {
        return match ($this) {
            self::NATURAL => ['CC', 'CE', 'PA', 'TI', 'RC'],
            self::JURIDICAL => ['NIT'],
        };
    }

    public static function fromString(string $type): self
    {
        return self::tryFrom($type) ?? throw new InvalidArgumentException(
            'Tipo de cliente inválido: ' . $type
        );
    }
}
