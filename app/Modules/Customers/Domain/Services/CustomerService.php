<?php

declare(strict_types=1);

namespace App\Modules\Customers\Domain\Services;

use App\Modules\Customers\Domain\Entities\Customer;
use App\Modules\Customers\Domain\ValueObjects\CustomerStatus;
use App\Modules\Customers\Domain\ValueObjects\CustomerType;
use App\Modules\Customers\Domain\ValueObjects\DocumentId;
use App\Modules\Customers\Domain\ValueObjects\Email;
use App\Modules\Customers\Domain\Events\CustomerBlacklisted;
use Illuminate\Support\Facades\Log;

class CustomerService
{
    public function __construct(
        private DedupService $dedupService
    ) {}

    public function createCustomer(
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
    ): Customer {
        Log::withContext([
            'module' => 'customers',
            'action' => 'create_customer',
            'tenant_id' => $tenantId,
            'document' => $documentId->type . ':' . $documentId->normalized()
        ]);

        // Validar que el documento no exista para este tenant
        $this->validateUniqueDocument($tenantId, $documentId);

        // Validar tipo de documento según tipo de cliente
        $this->validateDocumentTypeForCustomerType($type, $documentId);

        $customer = Customer::create(
            tenantId: $tenantId,
            type: $type,
            documentId: $documentId,
            businessName: $businessName,
            firstName: $firstName,
            lastName: $lastName,
            email: $email,
            phone: $phone,
            status: $status,
            segment: $segment,
            notes: $notes
        );

        Log::info('Cliente creado exitosamente', [
            'customer_id' => $customer->getId(),
            'document' => $documentId->__toString()
        ]);

        return $customer;
    }

    public function blacklistCustomer(
        Customer $customer,
        string $reason,
        ?int $actorId = null
    ): void {
        Log::withContext([
            'module' => 'customers',
            'action' => 'blacklist_customer',
            'customer_id' => $customer->getId(),
            'tenant_id' => $customer->getTenantId()
        ]);

        if ($customer->getStatus() === CustomerStatus::BLACKLISTED) {
            Log::warning('Intento de blacklistear cliente ya en lista negra');
            return;
        }

        $customer->changeStatus(CustomerStatus::BLACKLISTED, $reason);
        $customer->recordEvent(new CustomerBlacklisted($customer, $reason, $actorId));

        Log::warning('Cliente agregado a lista negra', [
            'reason' => $reason,
            'actor_id' => $actorId
        ]);
    }

    public function validateBusinessRules(Customer $customer): array
    {
        $violations = [];

        // Validar que persona natural tenga nombres
        if ($customer->getType()->isNatural()) {
            if (empty($customer->getFirstName()) && empty($customer->getLastName())) {
                $violations[] = 'Una persona natural debe tener al menos nombre o apellido';
            }
        }

        // Validar que persona jurídica tenga razón social
        if ($customer->getType()->isJuridical()) {
            if (empty($customer->getBusinessName())) {
                $violations[] = 'Una persona jurídica debe tener razón social';
            }
        }

        // Validar que tenga al menos un medio de contacto
        if (empty($customer->getEmail()) && empty($customer->getPhone())) {
            $violations[] = 'El cliente debe tener al menos un email o teléfono';
        }

        return $violations;
    }

    private function validateUniqueDocument(?string $tenantId, DocumentId $documentId): void
    {
        // Esta validación se implementará en el repository
        // Aquí validamos las reglas de negocio
        if ($documentId->type === 'NIT' && strlen($documentId->normalized()) < 8) {
            throw new \DomainException('Un NIT debe tener al menos 8 dígitos');
        }
    }

    private function validateDocumentTypeForCustomerType(CustomerType $type, DocumentId $documentId): void
    {
        $validTypes = $type->validDocumentTypes();
        
        if (!in_array($documentId->type, $validTypes, true)) {
            throw new \DomainException(
                sprintf(
                    'El tipo de documento %s no es válido para %s. Tipos válidos: %s',
                    $documentId->type,
                    $type->description(),
                    implode(', ', $validTypes)
                )
            );
        }
    }

    public function canCustomerBeDeleted(Customer $customer): bool
    {
        return $customer->getStatus()->canBeDeleted();
    }

    public function getCustomerMetrics(Customer $customer): array
    {
        return [
            'contacts_count' => $customer->getContacts()->count(),
            'addresses_count' => $customer->getAddresses()->count(),
            'has_tax_profile' => $customer->getTaxProfile() !== null,
            'days_since_creation' => $customer->getCreatedAt()?->diffInDays() ?? 0,
            'days_since_update' => $customer->getUpdatedAt()?->diffInDays() ?? 0,
            'is_complete' => $this->isCustomerDataComplete($customer),
        ];
    }

    private function isCustomerDataComplete(Customer $customer): bool
    {
        // Un cliente está completo si tiene al menos:
        // - Información básica (ya validada en creación)
        // - Al menos un contacto o email/teléfono principal
        // - Al menos una dirección
        
        $hasContact = $customer->getEmail() !== null || 
                     $customer->getPhone() !== null || 
                     $customer->getContacts()->isNotEmpty();
                     
        $hasAddress = $customer->getAddresses()->isNotEmpty();
        
        return $hasContact && $hasAddress;
    }
}
