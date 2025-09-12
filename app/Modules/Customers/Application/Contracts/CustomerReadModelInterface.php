<?php

declare(strict_types=1);

namespace App\Modules\Customers\Application\Contracts;

/**
 * Contrato para exponer información de clientes a otros módulos
 * Solo información básica y segura para consumo externo
 */
interface CustomerReadModelInterface
{
    public function getCustomerById(int $customerId): ?array;
    
    public function getCustomerByDocument(string $documentType, string $documentNumber, ?string $tenantId = null): ?array;
    
    public function getCustomerBasicInfo(int $customerId): ?array;
    
    public function getCustomersByIds(array $customerIds): array;
    
    public function searchCustomers(string $query, ?string $tenantId = null, int $limit = 10): array;
    
    public function getCustomerContactInfo(int $customerId): ?array;
    
    public function isCustomerActive(int $customerId): bool;
    
    public function getCustomerTaxInfo(int $customerId): ?array;
}
