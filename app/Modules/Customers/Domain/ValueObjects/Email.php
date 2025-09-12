<?php

declare(strict_types=1);

namespace App\Modules\Customers\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class Email
{
    public function __construct(
        public string $value
    ) {
        $this->validate($value);
    }

    private function validate(string $email): void
    {
        if (empty($email)) {
            throw new InvalidArgumentException('El email no puede estar vacío');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('El formato del email es inválido');
        }

        if (strlen($email) > 255) {
            throw new InvalidArgumentException('El email no puede tener más de 255 caracteres');
        }
    }

    public function normalized(): string
    {
        return strtolower(trim($this->value));
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(Email $other): bool
    {
        return $this->normalized() === $other->normalized();
    }
}
