<?php

declare(strict_types=1);

namespace App\Modules\Customers\Application\Queries;

use App\Modules\Customers\Application\Contracts\CustomerRepositoryInterface;
use App\Modules\Customers\Application\DTOs\CustomerFiltersDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class ListCustomersQuery
{
    public function __construct(
        private CustomerRepositoryInterface $customerRepository
    ) {}

    public function execute(CustomerFiltersDTO $filters, int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        Log::withContext([
            'module' => 'customers',
            'query' => 'list_customers',
            'filters' => $filters->toArray(),
            'page' => $page,
            'per_page' => $perPage
        ]);

        try {
            $result = $this->customerRepository->list(
                tenantId: $filters->tenantId,
                filters: $filters->toArray(),
                page: $page,
                perPage: $perPage
            );

            Log::info('Lista de clientes obtenida', [
                'total' => $result->total(),
                'current_page' => $result->currentPage(),
                'per_page' => $result->perPage()
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Error al listar clientes', [
                'filters' => $filters->toArray(),
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
}

