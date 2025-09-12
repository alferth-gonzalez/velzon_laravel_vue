<?php

declare(strict_types=1);

namespace App\Modules\Customers\Domain\Entities;

use App\Modules\Customers\Domain\ValueObjects\CustomerStatus;
use App\Modules\Customers\Domain\ValueObjects\CustomerType;
use App\Modules\Customers\Domain\ValueObjects\DocumentId;
use App\Modules\Customers\Domain\ValueObjects\Email;
use App\Modules\Customers\Domain\Events\CustomerCreated;
use App\Modules\Customers\Domain\Events\CustomerUpdated;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class Customer
{
    private array $domainEvents = [];

    public function __construct(
        private ?int $id,
        private ?string $tenantId,
        private CustomerType $type,
        private DocumentId $documentId,
        private string $businessName,
        private ?string $firstName,
        private ?string $lastName,
        private ?Email $email,
        private ?string $phone,
        private CustomerStatus $status,
        private ?string $segment,
        private ?string $notes,
        private ?Carbon $createdAt = null,
        private ?Carbon $updatedAt = null,
        private ?Carbon $deletedAt = null,
        private ?string $blacklistReason = null,
        private Collection $contacts = new Collection(),
        private Collection $addresses = new Collection(),
        private ?TaxProfile $taxProfile = null
    ) {
        $this->contacts = $contacts instanceof Collection ? $contacts : new Collection();
        $this->addresses = $addresses instanceof Collection ? $addresses : new Collection();
    }

    public static function create(
        ?string $tenantId,
        CustomerType $type,
        DocumentId $documentId,
        string $businessName,
        ?string $firstName = null,
        ?string $lastName = null,
        ?Email $email = null,
        ?string $phone = null,
        CustomerStatus $status = CustomerStatus::PROSPECT,
        ?string $segment = null,
        ?string $notes = null
    ): self {
        $customer = new self(
            id: null,
            tenantId: $tenantId,
            type: $type,
            documentId: $documentId,
            businessName: trim($businessName),
            firstName: $firstName ? trim($firstName) : null,
            lastName: $lastName ? trim($lastName) : null,
            email: $email,
            phone: $phone,
            status: $status,
            segment: $segment,
            notes: $notes,
            createdAt: Carbon::now(),
            updatedAt: Carbon::now()
        );

        $customer->recordEvent(new CustomerCreated($customer));

        return $customer;
    }

    public function update(
        string $businessName,
        ?string $firstName = null,
        ?string $lastName = null,
        ?Email $email = null,
        ?string $phone = null,
        ?string $segment = null,
        ?string $notes = null
    ): void {
        if (!$this->status->canBeUpdated()) {
            throw new \DomainException('No se puede actualizar un cliente con estado: ' . $this->status->value);
        }

        $oldData = $this->toArray();

        $this->businessName = trim($businessName);
        $this->firstName = $firstName ? trim($firstName) : null;
        $this->lastName = $lastName ? trim($lastName) : null;
        $this->email = $email;
        $this->phone = $phone;
        $this->segment = $segment;
        $this->notes = $notes;
        $this->updatedAt = Carbon::now();

        $this->recordEvent(new CustomerUpdated($this, $oldData, $this->toArray()));
    }

    public function changeStatus(CustomerStatus $newStatus, ?string $reason = null): void
    {
        if ($this->status === $newStatus) {
            return;
        }

        $oldStatus = $this->status;
        $this->status = $newStatus;
        $this->updatedAt = Carbon::now();

        if ($newStatus === CustomerStatus::BLACKLISTED) {
            $this->blacklistReason = $reason ?? 'Sin razón especificada';
        }

        $this->recordEvent(new CustomerUpdated(
            $this,
            ['status' => $oldStatus->value],
            ['status' => $newStatus->value, 'blacklist_reason' => $this->blacklistReason]
        ));
    }

    public function addContact(Contact $contact): void
    {
        $this->contacts->push($contact);
        $this->updatedAt = Carbon::now();
    }

    public function addAddress(Address $address): void
    {
        $this->addresses->push($address);
        $this->updatedAt = Carbon::now();
    }

    public function setTaxProfile(TaxProfile $taxProfile): void
    {
        $this->taxProfile = $taxProfile;
        $this->updatedAt = Carbon::now();
    }

    public function softDelete(?string $reason = null): void
    {
        if (!$this->status->canBeDeleted()) {
            throw new \DomainException(
                'No se puede eliminar un cliente en lista negra. Razón actual: ' . $this->blacklistReason
            );
        }

        $this->deletedAt = Carbon::now();
        $this->updatedAt = Carbon::now();
    }

    public function getFullName(): string
    {
        if ($this->type->isNatural()) {
            return trim(($this->firstName ?? '') . ' ' . ($this->lastName ?? ''));
        }

        return $this->businessName;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    public function getDomainEvents(): array
    {
        return $this->domainEvents;
    }

    public function clearDomainEvents(): void
    {
        $this->domainEvents = [];
    }

    private function recordEvent(object $event): void
    {
        $this->domainEvents[] = $event;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenantId,
            'type' => $this->type->value,
            'document_type' => $this->documentId->type,
            'document_number' => $this->documentId->number,
            'business_name' => $this->businessName,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email?->value,
            'phone' => $this->phone,
            'status' => $this->status->value,
            'segment' => $this->segment,
            'notes' => $this->notes,
            'blacklist_reason' => $this->blacklistReason,
            'created_at' => $this->createdAt?->toISOString(),
            'updated_at' => $this->updatedAt?->toISOString(),
            'deleted_at' => $this->deletedAt?->toISOString(),
        ];
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): ?string { return $this->tenantId; }
    public function getType(): CustomerType { return $this->type; }
    public function getDocumentId(): DocumentId { return $this->documentId; }
    public function getBusinessName(): string { return $this->businessName; }
    public function getFirstName(): ?string { return $this->firstName; }
    public function getLastName(): ?string { return $this->lastName; }
    public function getEmail(): ?Email { return $this->email; }
    public function getPhone(): ?string { return $this->phone; }
    public function getStatus(): CustomerStatus { return $this->status; }
    public function getSegment(): ?string { return $this->segment; }
    public function getNotes(): ?string { return $this->notes; }
    public function getBlacklistReason(): ?string { return $this->blacklistReason; }
    public function getCreatedAt(): ?Carbon { return $this->createdAt; }
    public function getUpdatedAt(): ?Carbon { return $this->updatedAt; }
    public function getDeletedAt(): ?Carbon { return $this->deletedAt; }
    public function getContacts(): Collection { return $this->contacts; }
    public function getAddresses(): Collection { return $this->addresses; }
    public function getTaxProfile(): ?TaxProfile { return $this->taxProfile; }
}
