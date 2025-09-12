<?php

declare(strict_types=1);

namespace App\Modules\Customers\Application\DTOs;

final readonly class CreateCustomerDTO
{
    public function __construct(
        public ?string $tenantId,
        public string $type,
        public string $documentType,
        public string $documentNumber,
        public string $businessName,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $email = null,
        public ?string $phone = null,
        public string $status = 'prospect',
        public ?string $segment = null,
        public ?string $notes = null,
        public ?array $contacts = null,
        public ?array $addresses = null,
        public ?array $taxProfile = null
    ) {}

    public function toArray(): array
    {
        return [
            'tenant_id' => $this->tenantId,
            'type' => $this->type,
            'document_type' => $this->documentType,
            'document_number' => $this->documentNumber,
            'business_name' => $this->businessName,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,
            'segment' => $this->segment,
            'notes' => $this->notes,
            'contacts' => $this->contacts,
            'addresses' => $this->addresses,
            'tax_profile' => $this->taxProfile,
        ];
    }
}
