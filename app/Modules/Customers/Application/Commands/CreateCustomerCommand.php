<?php

declare(strict_types=1);

namespace App\Modules\Customers\Application\Commands;

use App\Modules\Customers\Application\DTOs\CreateCustomerDTO;
use App\Modules\Customers\Application\Contracts\CustomerRepositoryInterface;
use App\Modules\Customers\Domain\Services\CustomerService;
use App\Modules\Customers\Domain\ValueObjects\CustomerType;
use App\Modules\Customers\Domain\ValueObjects\CustomerStatus;
use App\Modules\Customers\Domain\ValueObjects\DocumentId;
use App\Modules\Customers\Domain\ValueObjects\Email;
use App\Modules\Customers\Domain\Entities\Customer;
use Illuminate\Support\Facades\Log;

class CreateCustomerCommand
{
    public function __construct(
        private CustomerService $customerService,
        private CustomerRepositoryInterface $customerRepository
    ) {}

    public function execute(CreateCustomerDTO $dto): Customer
    {
        Log::withContext([
            'module' => 'customers',
            'command' => 'create_customer',
            'tenant_id' => $dto->tenantId,
            'document' => $dto->documentType . ':' . $dto->documentNumber
        ]);

        try {
            // Validar que no exista el documento
            $documentId = new DocumentId($dto->documentType, $dto->documentNumber);
            
            if ($this->customerRepository->exists($dto->tenantId, $documentId)) {
                throw new \DomainException(
                    'Ya existe un cliente con el documento ' . $dto->documentType . ':' . $dto->documentNumber
                );
            }

            // Crear el cliente
            $customer = $this->customerService->createCustomer(
                tenantId: $dto->tenantId,
                type: CustomerType::fromString($dto->type),
                documentId: $documentId,
                businessName: $dto->businessName,
                firstName: $dto->firstName,
                lastName: $dto->lastName,
                email: $dto->email ? new Email($dto->email) : null,
                phone: $dto->phone,
                status: CustomerStatus::fromString($dto->status),
                segment: $dto->segment,
                notes: $dto->notes
            );

            // Validar reglas de negocio
            $violations = $this->customerService->validateBusinessRules($customer);
            if (!empty($violations)) {
                throw new \DomainException('Violaciones de reglas de negocio: ' . implode(', ', $violations));
            }

            // Guardar el cliente
            $savedCustomer = $this->customerRepository->save($customer);

            Log::info('Cliente creado exitosamente', [
                'customer_id' => $savedCustomer->getId()
            ]);

            return $savedCustomer;

        } catch (\Exception $e) {
            Log::error('Error al crear cliente', [
                'error' => $e->getMessage(),
                'dto' => $dto->toArray()
            ]);
            
            throw $e;
        }
    }
}
