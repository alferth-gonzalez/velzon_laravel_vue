<?php

declare(strict_types=1);

namespace App\Modules\Customers\Application\Commands;

use App\Modules\Customers\Application\Contracts\CustomerRepositoryInterface;
use App\Modules\Customers\Domain\Services\MergeService;
use App\Modules\Customers\Domain\Entities\Customer;
use Illuminate\Support\Facades\Log;

class MergeCustomersCommand
{
    public function __construct(
        private MergeService $mergeService,
        private CustomerRepositoryInterface $customerRepository
    ) {}

    public function execute(
        int $sourceCustomerId,
        int $destinationCustomerId,
        string $idempotencyKey,
        ?int $actorId = null,
        ?string $reason = null
    ): Customer {
        Log::withContext([
            'module' => 'customers',
            'command' => 'merge_customers',
            'source_id' => $sourceCustomerId,
            'destination_id' => $destinationCustomerId,
            'idempotency_key' => $idempotencyKey,
            'actor_id' => $actorId
        ]);

        try {
            return $this->mergeService->mergeCustomers(
                sourceCustomerId: $sourceCustomerId,
                destinationCustomerId: $destinationCustomerId,
                idempotencyKey: $idempotencyKey,
                actorId: $actorId,
                reason: $reason
            );

        } catch (\Exception $e) {
            Log::error('Error al combinar clientes', [
                'source_id' => $sourceCustomerId,
                'destination_id' => $destinationCustomerId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    public function validateMerge(int $sourceCustomerId, int $destinationCustomerId): array
    {
        $sourceCustomer = $this->customerRepository->findById($sourceCustomerId);
        $destinationCustomer = $this->customerRepository->findById($destinationCustomerId);

        if (!$sourceCustomer || !$destinationCustomer) {
            return ['Uno o ambos clientes no existen'];
        }

        return $this->mergeService->validateMerge($sourceCustomer, $destinationCustomer);
    }

    public function previewMerge(int $sourceCustomerId, int $destinationCustomerId): array
    {
        $sourceCustomer = $this->customerRepository->findById($sourceCustomerId);
        $destinationCustomer = $this->customerRepository->findById($destinationCustomerId);

        if (!$sourceCustomer || !$destinationCustomer) {
            throw new \DomainException('Uno o ambos clientes no existen');
        }

        return $this->mergeService->previewMerge($sourceCustomer, $destinationCustomer);
    }
}
