<?php

declare(strict_types=1);

namespace App\Modules\Customers\Application\Commands;

use App\Modules\Customers\Application\Contracts\CustomerRepositoryInterface;
use App\Modules\Customers\Domain\Services\CustomerService;
use App\Modules\Customers\Domain\Entities\Customer;
use Illuminate\Support\Facades\Log;

class BlacklistCustomerCommand
{
    public function __construct(
        private CustomerService $customerService,
        private CustomerRepositoryInterface $customerRepository
    ) {}

    public function execute(int $customerId, string $reason, ?int $actorId = null): Customer
    {
        Log::withContext([
            'module' => 'customers',
            'command' => 'blacklist_customer',
            'customer_id' => $customerId,
            'actor_id' => $actorId
        ]);

        try {
            $customer = $this->customerRepository->findById($customerId);
            
            if (!$customer) {
                throw new \DomainException('Cliente no encontrado con ID: ' . $customerId);
            }

            // Aplicar blacklist
            $this->customerService->blacklistCustomer($customer, $reason, $actorId);

            // Guardar cambios
            $updatedCustomer = $this->customerRepository->save($customer);

            Log::warning('Cliente agregado a lista negra', [
                'customer_id' => $customerId,
                'reason' => $reason,
                'actor_id' => $actorId
            ]);

            return $updatedCustomer;

        } catch (\Exception $e) {
            Log::error('Error al agregar cliente a lista negra', [
                'customer_id' => $customerId,
                'error' => $e->getMessage(),
                'reason' => $reason
            ]);
            
            throw $e;
        }
    }
}

