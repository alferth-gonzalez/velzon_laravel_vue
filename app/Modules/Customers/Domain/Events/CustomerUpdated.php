<?php

declare(strict_types=1);

namespace App\Modules\Customers\Domain\Events;

use App\Modules\Customers\Domain\Entities\Customer;
use Carbon\Carbon;

final readonly class CustomerUpdated
{
    public function __construct(
        public Customer $customer,
        public array $oldData,
        public array $newData,
        public Carbon $occurredAt = new Carbon()
    ) {
        $this->occurredAt = $occurredAt ?? Carbon::now();
    }

    public function toArray(): array
    {
        return [
            'event_type' => 'customer.updated',
            'customer_id' => $this->customer->getId(),
            'tenant_id' => $this->customer->getTenantId(),
            'old_data' => $this->oldData,
            'new_data' => $this->newData,
            'occurred_at' => $this->occurredAt->toISOString(),
        ];
    }
}

