<?php

declare(strict_types=1);

namespace App\Modules\Customers\Application\Contracts;

interface IdempotencyRepositoryInterface
{
    public function exists(string $key): bool;
    
    public function store(string $key, array $data, int $ttlSeconds = 3600): void;
    
    public function get(string $key): ?array;
    
    public function delete(string $key): bool;
    
    public function cleanup(): int;
}
