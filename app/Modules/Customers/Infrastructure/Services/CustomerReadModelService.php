<?php

declare(strict_types=1);

namespace App\Modules\Customers\Infrastructure\Services;

use App\Modules\Customers\Application\Contracts\CustomerReadModelInterface;
use App\Modules\Customers\Infrastructure\Models\CustomerModel;

class CustomerReadModelService implements CustomerReadModelInterface
{
    public function getCustomerById(int $customerId): ?array
    {
        $customer = CustomerModel::find($customerId);
        
        return $customer ? [
            'id' => $customer->id,
            'business_name' => $customer->business_name,
            'full_name' => $customer->full_name,
            'document_type' => $customer->document_type,
            'document_number' => $customer->document_number,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'status' => $customer->status,
            'type' => $customer->type,
        ] : null;
    }

    public function getCustomerByDocument(string $documentType, string $documentNumber, ?string $tenantId = null): ?array
    {
        $query = CustomerModel::where('document_type', $documentType)
            ->where('document_number', $documentNumber);
            
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }
        
        $customer = $query->first();
        
        return $customer ? $this->getCustomerById($customer->id) : null;
    }

    public function getCustomerBasicInfo(int $customerId): ?array
    {
        $customer = CustomerModel::find($customerId);
        
        return $customer ? [
            'id' => $customer->id,
            'business_name' => $customer->business_name,
            'full_name' => $customer->full_name,
            'status' => $customer->status,
        ] : null;
    }

    public function getCustomersByIds(array $customerIds): array
    {
        return CustomerModel::whereIn('id', $customerIds)
            ->get()
            ->map(fn($customer) => $this->getCustomerById($customer->id))
            ->toArray();
    }

    public function searchCustomers(string $query, ?string $tenantId = null, int $limit = 10): array
    {
        $builder = CustomerModel::search($query);
        
        if ($tenantId) {
            $builder->byTenant($tenantId);
        }
        
        return $builder->limit($limit)
            ->get()
            ->map(fn($customer) => $this->getCustomerBasicInfo($customer->id))
            ->toArray();
    }

    public function getCustomerContactInfo(int $customerId): ?array
    {
        $customer = CustomerModel::with(['contacts', 'addresses'])->find($customerId);
        
        return $customer ? [
            'id' => $customer->id,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'contacts' => $customer->contacts->toArray(),
            'addresses' => $customer->addresses->toArray(),
        ] : null;
    }

    public function isCustomerActive(int $customerId): bool
    {
        return CustomerModel::where('id', $customerId)
            ->where('status', 'active')
            ->exists();
    }

    public function getCustomerTaxInfo(int $customerId): ?array
    {
        $customer = CustomerModel::with('taxProfile')->find($customerId);
        
        return $customer && $customer->taxProfile ? [
            'customer_id' => $customer->id,
            'tax_regime' => $customer->taxProfile->tax_regime,
            'tax_responsibilities' => $customer->taxProfile->tax_responsibilities,
            'is_retention_agent' => $customer->taxProfile->is_retention_agent,
            'is_self_retainer' => $customer->taxProfile->is_self_retainer,
        ] : null;
    }
}
