<?php

declare(strict_types=1);

namespace App\Modules\Customers\Application\Queries;

use App\Modules\Customers\Application\Contracts\CustomerRepositoryInterface;
use App\Modules\Customers\Domain\Entities\Customer;
use Illuminate\Support\Facades\Log;

class GetCustomerByIdQuery
{
    public function __construct(
        private CustomerRepositoryInterface $customerRepository
    ) {}

    public function execute(int $customerId): ?Customer
    {
        Log::withContext([
            'module' => 'customers',
            'query' => 'get_customer_by_id',
            'customer_id' => $customerId
        ]);

        try {
            $customer = $this->customerRepository->findById($customerId);

            if ($customer) {
                Log::info('Cliente encontrado', [
                    'customer_id' => $customerId,
                    'document' => $customer->getDocumentId()->__toString()
                ]);
            } else {
                Log::info('Cliente no encontrado', [
                    'customer_id' => $customerId
                ]);
            }

            return $customer;

        } catch (\Exception $e) {
            Log::error('Error al buscar cliente por ID', [
                'customer_id' => $customerId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
}

