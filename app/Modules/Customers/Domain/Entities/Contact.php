<?php

declare(strict_types=1);

namespace App\Modules\Customers\Domain\Entities;

use App\Modules\Customers\Domain\ValueObjects\Email;
use App\Modules\Customers\Domain\ValueObjects\Phone;
use Carbon\Carbon;

class Contact
{
    public function __construct(
        private ?int $id,
        private int $customerId,
        private string $role,
        private string $name,
        private ?Email $email = null,
        private ?Phone $phone = null,
        private bool $isPrimary = false,
        private ?string $notes = null,
        private ?Carbon $createdAt = null,
        private ?Carbon $updatedAt = null
    ) {
        $this->createdAt = $createdAt ?? Carbon::now();
        $this->updatedAt = $updatedAt ?? Carbon::now();
    }

    public static function create(
        int $customerId,
        string $role,
        string $name,
        ?Email $email = null,
        ?Phone $phone = null,
        bool $isPrimary = false,
        ?string $notes = null
    ): self {
        return new self(
            id: null,
            customerId: $customerId,
            role: trim($role),
            name: trim($name),
            email: $email,
            phone: $phone,
            isPrimary: $isPrimary,
            notes: $notes,
            createdAt: Carbon::now(),
            updatedAt: Carbon::now()
        );
    }

    public function update(
        string $role,
        string $name,
        ?Email $email = null,
        ?Phone $phone = null,
        bool $isPrimary = false,
        ?string $notes = null
    ): void {
        $this->role = trim($role);
        $this->name = trim($name);
        $this->email = $email;
        $this->phone = $phone;
        $this->isPrimary = $isPrimary;
        $this->notes = $notes;
        $this->updatedAt = Carbon::now();
    }

    public function setPrimary(bool $isPrimary): void
    {
        $this->isPrimary = $isPrimary;
        $this->updatedAt = Carbon::now();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customerId,
            'role' => $this->role,
            'name' => $this->name,
            'email' => $this->email?->value,
            'phone' => $this->phone?->value,
            'is_primary' => $this->isPrimary,
            'notes' => $this->notes,
            'created_at' => $this->createdAt?->toISOString(),
            'updated_at' => $this->updatedAt?->toISOString(),
        ];
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getCustomerId(): int { return $this->customerId; }
    public function getRole(): string { return $this->role; }
    public function getName(): string { return $this->name; }
    public function getEmail(): ?Email { return $this->email; }
    public function getPhone(): ?Phone { return $this->phone; }
    public function isPrimary(): bool { return $this->isPrimary; }
    public function getNotes(): ?string { return $this->notes; }
    public function getCreatedAt(): ?Carbon { return $this->createdAt; }
    public function getUpdatedAt(): ?Carbon { return $this->updatedAt; }
}
