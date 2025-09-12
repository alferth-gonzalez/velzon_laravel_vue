<?php

declare(strict_types=1);

namespace App\Modules\Customers\Domain\Entities;

use App\Modules\Customers\Domain\ValueObjects\CountryCode;
use Carbon\Carbon;

class Address
{
    public function __construct(
        private ?int $id,
        private int $customerId,
        private string $type,
        private string $addressLine1,
        private ?string $addressLine2 = null,
        private string $city,
        private string $state,
        private string $postalCode,
        private CountryCode $countryCode,
        private bool $isDefault = false,
        private ?string $notes = null,
        private ?Carbon $createdAt = null,
        private ?Carbon $updatedAt = null
    ) {
        $this->validateType($type);
        $this->createdAt = $createdAt ?? Carbon::now();
        $this->updatedAt = $updatedAt ?? Carbon::now();
    }

    public static function create(
        int $customerId,
        string $type,
        string $addressLine1,
        string $city,
        string $state,
        string $postalCode,
        CountryCode $countryCode,
        ?string $addressLine2 = null,
        bool $isDefault = false,
        ?string $notes = null
    ): self {
        return new self(
            id: null,
            customerId: $customerId,
            type: $type,
            addressLine1: trim($addressLine1),
            addressLine2: $addressLine2 ? trim($addressLine2) : null,
            city: trim($city),
            state: trim($state),
            postalCode: trim($postalCode),
            countryCode: $countryCode,
            isDefault: $isDefault,
            notes: $notes,
            createdAt: Carbon::now(),
            updatedAt: Carbon::now()
        );
    }

    private function validateType(string $type): void
    {
        $validTypes = ['billing', 'shipping', 'legal', 'office', 'home'];
        
        if (!in_array($type, $validTypes, true)) {
            throw new \InvalidArgumentException(
                'Tipo de dirección inválido. Tipos válidos: ' . implode(', ', $validTypes)
            );
        }
    }

    public function update(
        string $type,
        string $addressLine1,
        string $city,
        string $state,
        string $postalCode,
        CountryCode $countryCode,
        ?string $addressLine2 = null,
        bool $isDefault = false,
        ?string $notes = null
    ): void {
        $this->validateType($type);
        
        $this->type = $type;
        $this->addressLine1 = trim($addressLine1);
        $this->addressLine2 = $addressLine2 ? trim($addressLine2) : null;
        $this->city = trim($city);
        $this->state = trim($state);
        $this->postalCode = trim($postalCode);
        $this->countryCode = $countryCode;
        $this->isDefault = $isDefault;
        $this->notes = $notes;
        $this->updatedAt = Carbon::now();
    }

    public function setDefault(bool $isDefault): void
    {
        $this->isDefault = $isDefault;
        $this->updatedAt = Carbon::now();
    }

    public function getFullAddress(): string
    {
        $parts = [
            $this->addressLine1,
            $this->addressLine2,
            $this->city,
            $this->state,
            $this->postalCode,
            $this->countryCode->value
        ];

        return implode(', ', array_filter($parts));
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customerId,
            'type' => $this->type,
            'address_line_1' => $this->addressLine1,
            'address_line_2' => $this->addressLine2,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postalCode,
            'country_code' => $this->countryCode->value,
            'is_default' => $this->isDefault,
            'notes' => $this->notes,
            'created_at' => $this->createdAt?->toISOString(),
            'updated_at' => $this->updatedAt?->toISOString(),
        ];
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getCustomerId(): int { return $this->customerId; }
    public function getType(): string { return $this->type; }
    public function getAddressLine1(): string { return $this->addressLine1; }
    public function getAddressLine2(): ?string { return $this->addressLine2; }
    public function getCity(): string { return $this->city; }
    public function getState(): string { return $this->state; }
    public function getPostalCode(): string { return $this->postalCode; }
    public function getCountryCode(): CountryCode { return $this->countryCode; }
    public function isDefault(): bool { return $this->isDefault; }
    public function getNotes(): ?string { return $this->notes; }
    public function getCreatedAt(): ?Carbon { return $this->createdAt; }
    public function getUpdatedAt(): ?Carbon { return $this->updatedAt; }
}
