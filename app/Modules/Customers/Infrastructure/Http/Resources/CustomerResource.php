<?php

declare(strict_types=1);

namespace App\Modules\Customers\Infrastructure\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Customers\Domain\Entities\Customer;

class CustomerResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Customer $customer */
        $customer = $this->resource;

        return [
            'id' => $customer->getId(),
            'tenant_id' => $customer->getTenantId(),
            'type' => [
                'value' => $customer->getType()->value,
                'description' => $customer->getType()->description()
            ],
            'document' => [
                'type' => $customer->getDocumentId()->type,
                'number' => $customer->getDocumentId()->number,
                'formatted' => $customer->getDocumentId()->formatted()
            ],
            'business_name' => $customer->getBusinessName(),
            'first_name' => $customer->getFirstName(),
            'last_name' => $customer->getLastName(),
            'full_name' => $customer->getFullName(),
            'email' => $customer->getEmail()?->value,
            'phone' => $customer->getPhone(),
            'status' => [
                'value' => $customer->getStatus()->value,
                'description' => $customer->getStatus()->description()
            ],
            'segment' => $customer->getSegment(),
            'notes' => $customer->getNotes(),
            'blacklist_reason' => $customer->getBlacklistReason(),
            'contacts_count' => $customer->getContacts()->count(),
            'addresses_count' => $customer->getAddresses()->count(),
            'has_tax_profile' => $customer->getTaxProfile() !== null,
            'is_deleted' => $customer->isDeleted(),
            'created_at' => $customer->getCreatedAt()?->toISOString(),
            'updated_at' => $customer->getUpdatedAt()?->toISOString(),
            'deleted_at' => $customer->getDeletedAt()?->toISOString(),
        ];
    }
}

