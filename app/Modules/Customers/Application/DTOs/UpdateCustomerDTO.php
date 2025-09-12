<?php

declare(strict_types=1);

namespace App\Modules\Customers\Application\DTOs;

final readonly class UpdateCustomerDTO
{
    public function __construct(
        public string $businessName,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $segment = null,
        public ?string $notes = null
    ) {}

    public function toArray(): array
    {
        return [
            'business_name' => $this->businessName,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'phone' => $this->phone,
            'segment' => $this->segment,
            'notes' => $this->notes,
        ];
    }
}
