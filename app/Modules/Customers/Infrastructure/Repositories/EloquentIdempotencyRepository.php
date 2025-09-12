<?php

declare(strict_types=1);

namespace App\Modules\Customers\Infrastructure\Repositories;

use App\Modules\Customers\Application\Contracts\IdempotencyRepositoryInterface;
use App\Modules\Customers\Infrastructure\Models\CustomerProcessedEventModel;
use Carbon\Carbon;

class EloquentIdempotencyRepository implements IdempotencyRepositoryInterface
{
    public function exists(string $key): bool
    {
        return CustomerProcessedEventModel::where('idempotency_key', $key)
            ->notExpired()
            ->exists();
    }

    public function store(string $key, array $data, int $ttlSeconds = 3600): void
    {
        CustomerProcessedEventModel::create([
            'idempotency_key' => $key,
            'event_type' => $data['event_type'] ?? 'unknown',
            'payload' => $data,
            'result' => null,
            'processed_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addSeconds($ttlSeconds),
        ]);
    }

    public function get(string $key): ?array
    {
        $event = CustomerProcessedEventModel::where('idempotency_key', $key)
            ->notExpired()
            ->first();

        return $event ? [
            'payload' => $event->payload,
            'result' => $event->result,
            'processed_at' => $event->processed_at,
        ] : null;
    }

    public function delete(string $key): bool
    {
        return CustomerProcessedEventModel::where('idempotency_key', $key)
            ->delete() > 0;
    }

    public function cleanup(): int
    {
        return CustomerProcessedEventModel::expired()->delete();
    }
}

