<?php

declare(strict_types=1);

namespace App\Modules\Customers\Application\Contracts;

interface CustomerExportPortInterface
{
    public function exportToCSV(array $customerIds, array $fields = []): string;
    
    public function exportToExcel(array $customerIds, array $fields = []): string;
    
    public function getAvailableFields(): array;
    
    public function validateExportPermissions(array $customerIds, int $userId): bool;
    
    public function getExportableData(array $customerIds, array $fields = []): array;
}
