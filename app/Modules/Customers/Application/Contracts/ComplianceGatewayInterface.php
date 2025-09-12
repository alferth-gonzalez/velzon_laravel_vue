<?php

declare(strict_types=1);

namespace App\Modules\Customers\Application\Contracts;

interface ComplianceGatewayInterface
{
    public function reportDataDeletion(int $customerId, string $reason): void;
    
    public function reportDataUpdate(int $customerId, array $changes): void;
    
    public function validateDataRetentionPolicy(int $customerId): bool;
    
    public function getDataPortabilityReport(int $customerId): array;
    
    public function processForgetMeRequest(int $customerId): bool;
    
    public function auditDataAccess(int $customerId, int $userId, string $purpose): void;
}

