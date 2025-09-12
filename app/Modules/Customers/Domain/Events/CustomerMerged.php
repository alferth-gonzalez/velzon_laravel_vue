<?php

declare(strict_types=1);

namespace App\Modules\Customers\Domain\Events;

use App\Modules\Customers\Domain\Entities\Customer;
use Carbon\Carbon;

final readonly class CustomerMerged
{
    public function __construct(
        public Customer $sourceCustomer,
        public Customer $destinationCustomer,
        public string $mergeId,
        public Carbon $occurredAt = new Carbon()
    ) {
        $this->occurredAt = $occurredAt ?? Carbon::now();
    }

    public function toArray(): array
    {
        return [
            'event_type' => 'customer.merged',
            'merge_id' => $this->mergeId,
            'source_customer_id' => $this->sourceCustomer->getId(),
            'destination_customer_id' => $this->destinationCustomer->getId(),
            'tenant_id' => $this->destinationCustomer->getTenantId(),
            'source_data' => $this->sourceCustomer->toArray(),
            'destination_data' => $this->destinationCustomer->toArray(),
            'occurred_at' => $this->occurredAt->toISOString(),
        ];
    }
}
