<?php

declare(strict_types=1);

namespace App\Modules\Customers\Domain\Events;

use App\Modules\Customers\Domain\Entities\Customer;
use Carbon\Carbon;

final readonly class CustomerBlacklisted
{
    public function __construct(
        public Customer $customer,
        public string $reason,
        public ?int $actorId = null,
        public Carbon $occurredAt = new Carbon()
    ) {
        $this->occurredAt = $occurredAt ?? Carbon::now();
    }

    public function toArray(): array
    {
        return [
            'event_type' => 'customer.blacklisted',
            'customer_id' => $this->customer->getId(),
            'tenant_id' => $this->customer->getTenantId(),
            'reason' => $this->reason,
            'actor_id' => $this->actorId,
            'customer_data' => $this->customer->toArray(),
            'occurred_at' => $this->occurredAt->toISOString(),
        ];
    }
}
