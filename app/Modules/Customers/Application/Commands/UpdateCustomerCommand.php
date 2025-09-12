<?php

declare(strict_types=1);

namespace App\Modules\Customers\Application\Commands;

use App\Modules\Customers\Application\DTOs\UpdateCustomerDTO;
use App\Modules\Customers\Application\Contracts\CustomerRepositoryInterface;
use App\Modules\Customers\Domain\Services\CustomerService;
use App\Modules\Customers\Domain\ValueObjects\Email;
use App\Modules\Customers\Domain\Entities\Customer;
use Illuminate\Support\Facades\Log;

class UpdateCustomerCommand
{
    public function __construct(
        private CustomerService $customerService,
        private CustomerRepositoryInterface $customerRepository
    ) {}

    public function execute(int $customerId, UpdateCustomerDTO $dto): Customer
    {
        Log::withContext([
            'module' => 'customers',
            'command' => 'update_customer',
            'customer_id' => $customerId
        ]);

        try {
            $customer = $this->customerRepository->findById($customerId);
            
            if (!$customer) {
                throw new \DomainException('Cliente no encontrado con ID: ' . $customerId);
            }

            // Actualizar el cliente
            $customer->update(
                businessName: $dto->businessName,
                firstName: $dto->firstName,
                lastName: $dto->lastName,
                email: $dto->email ? new Email($dto->email) : null,
                phone: $dto->phone,
                segment: $dto->segment,
                notes: $dto->notes
            );

            // Validar reglas de negocio
            $violations = $this->customerService->validateBusinessRules($customer);
            if (!empty($violations)) {
                throw new \DomainException('Violaciones de reglas de negocio: ' . implode(', ', $violations));
            }

            // Guardar cambios
            $updatedCustomer = $this->customerRepository->save($customer);

            Log::info('Cliente actualizado exitosamente', [
                'customer_id' => $customerId
            ]);

            return $updatedCustomer;

        } catch (\Exception $e) {
            Log::error('Error al actualizar cliente', [
                'customer_id' => $customerId,
                'error' => $e->getMessage(),
                'dto' => $dto->toArray()
            ]);
            
            throw $e;
        }
    }
}
