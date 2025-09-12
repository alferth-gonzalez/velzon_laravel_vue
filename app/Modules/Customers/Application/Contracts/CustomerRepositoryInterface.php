<?php

declare(strict_types=1);

namespace App\Modules\Customers\Application\Contracts;

use App\Modules\Customers\Domain\Entities\Customer;
use App\Modules\Customers\Domain\ValueObjects\DocumentId;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CustomerRepositoryInterface
{
    public function save(Customer $customer): Customer;
    
    public function findById(int $id): ?Customer;
    
    public function findByDocumentId(?string $tenantId, DocumentId $documentId): Collection;
    
    public function findByEmail(?string $tenantId, string $email): Collection;
    
    public function findByPhone(?string $tenantId, string $phone): Collection;
    
    public function findBySimilarNames(?string $tenantId, array $searchTerms): Collection;
    
    public function list(
        ?string $tenantId = null,
        array $filters = [],
        int $page = 1,
        int $perPage = 15
    ): LengthAwarePaginator;
    
    public function search(
        ?string $tenantId,
        string $query,
        array $filters = [],
        int $limit = 20
    ): Collection;
    
    public function delete(int $id): bool;
    
    public function exists(?string $tenantId, DocumentId $documentId): bool;
    
    public function getMetrics(?string $tenantId, array $filters = []): array;
}
