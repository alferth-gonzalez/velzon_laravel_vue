<?php

declare(strict_types=1);

namespace App\Modules\Customers\Application\DTOs;

use Carbon\Carbon;

final readonly class CustomerFiltersDTO
{
    public function __construct(
        public ?string $tenantId = null,
        public ?string $status = null,
        public ?string $type = null,
        public ?string $segment = null,
        public ?string $search = null,
        public ?Carbon $createdFrom = null,
        public ?Carbon $createdTo = null,
        public ?Carbon $updatedFrom = null,
        public ?Carbon $updatedTo = null,
        public bool $includeDeleted = false,
        public ?string $documentType = null,
        public ?string $countryCode = null
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'tenant_id' => $this->tenantId,
            'status' => $this->status,
            'type' => $this->type,
            'segment' => $this->segment,
            'search' => $this->search,
            'created_from' => $this->createdFrom?->toDateString(),
            'created_to' => $this->createdTo?->toDateString(),
            'updated_from' => $this->updatedFrom?->toDateString(),
            'updated_to' => $this->updatedTo?->toDateString(),
            'include_deleted' => $this->includeDeleted,
            'document_type' => $this->documentType,
            'country_code' => $this->countryCode,
        ], fn($value) => $value !== null);
    }
}

