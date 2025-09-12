<?php

declare(strict_types=1);

namespace App\Modules\Customers\Domain\ValueObjects;

use InvalidArgumentException;

enum CustomerStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';
    case BLACKLISTED = 'blacklisted';
    case PROSPECT = 'prospect';

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function canBeUpdated(): bool
    {
        return $this !== self::BLACKLISTED;
    }

    public function canBeDeleted(): bool
    {
        return $this !== self::BLACKLISTED;
    }

    public function description(): string
    {
        return match ($this) {
            self::ACTIVE => 'Cliente activo',
            self::INACTIVE => 'Cliente inactivo',
            self::SUSPENDED => 'Cliente suspendido',
            self::BLACKLISTED => 'Cliente en lista negra',
            self::PROSPECT => 'Cliente prospecto',
        };
    }

    public static function fromString(string $status): self
    {
        return self::tryFrom($status) ?? throw new InvalidArgumentException(
            'Estado de cliente inválido: ' . $status
        );
    }
}
