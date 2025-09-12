<?php

declare(strict_types=1);

namespace App\Modules\Customers\Infrastructure\Repositories;

use App\Modules\Customers\Application\Contracts\CustomerRepositoryInterface;
use App\Modules\Customers\Domain\Entities\Customer;
use App\Modules\Customers\Domain\ValueObjects\DocumentId;
use App\Modules\Customers\Infrastructure\Models\CustomerModel;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class EloquentCustomerRepository implements CustomerRepositoryInterface
{
    public function save(Customer $customer): Customer
    {
        $model = $customer->getId() 
            ? CustomerModel::findOrFail($customer->getId())
            : new CustomerModel();

        $model->fill([
            'tenant_id' => $customer->getTenantId(),
            'type' => $customer->getType()->value,
            'document_type' => $customer->getDocumentId()->type,
            'document_number' => $customer->getDocumentId()->number,
            'business_name' => $customer->getBusinessName(),
            'first_name' => $customer->getFirstName(),
            'last_name' => $customer->getLastName(),
            'email' => $customer->getEmail()?->value,
            'phone' => $customer->getPhone(),
            'status' => $customer->getStatus()->value,
            'segment' => $customer->getSegment(),
            'notes' => $customer->getNotes(),
            'blacklist_reason' => $customer->getBlacklistReason(),
        ]);

        if ($customer->getDeletedAt()) {
            $model->deleted_at = $customer->getDeletedAt();
        }

        $model->save();

        // Convertir el modelo guardado de vuelta a entidad
        return $this->modelToEntity($model);
    }

    public function findById(int $id): ?Customer
    {
        $model = CustomerModel::with(['contacts', 'addresses', 'taxProfile'])->find($id);
        
        return $model ? $this->modelToEntity($model) : null;
    }

    public function findByDocumentId(?string $tenantId, DocumentId $documentId): Collection
    {
        $query = CustomerModel::where('document_type', $documentId->type)
            ->where('document_number', $documentId->number);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        return $query->get()->map(fn($model) => $this->modelToEntity($model));
    }

    public function findByEmail(?string $tenantId, string $email): Collection
    {
        $query = CustomerModel::where('email', $email);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        return $query->get()->map(fn($model) => $this->modelToEntity($model));
    }

    public function findByPhone(?string $tenantId, string $phone): Collection
    {
        // Normalizar teléfono para búsqueda
        $normalizedPhone = preg_replace('/[^0-9]/', '', $phone);
        
        $query = CustomerModel::where(function ($q) use ($phone, $normalizedPhone) {
            $q->where('phone', $phone)
              ->orWhere('phone', 'like', '%' . $normalizedPhone . '%');
        });

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        return $query->get()->map(fn($model) => $this->modelToEntity($model));
    }

    public function findBySimilarNames(?string $tenantId, array $searchTerms): Collection
    {
        $query = CustomerModel::query();

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $query->where(function ($q) use ($searchTerms) {
            foreach ($searchTerms as $term) {
                $q->orWhere('business_name', 'like', '%' . $term . '%')
                  ->orWhere('first_name', 'like', '%' . $term . '%')
                  ->orWhere('last_name', 'like', '%' . $term . '%');
            }
        });

        return $query->limit(50)->get()->map(fn($model) => $this->modelToEntity($model));
    }

    public function list(
        ?string $tenantId = null,
        array $filters = [],
        int $page = 1,
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = CustomerModel::query();

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        // Aplicar filtros
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['segment'])) {
            $query->where('segment', $filters['segment']);
        }

        if (!empty($filters['document_type'])) {
            $query->where('document_type', $filters['document_type']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'like', '%' . $search . '%')
                  ->orWhere('first_name', 'like', '%' . $search . '%')
                  ->orWhere('last_name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('document_number', 'like', '%' . $search . '%');
            });
        }

        if (!empty($filters['created_from'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['created_from']));
        }

        if (!empty($filters['created_to'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['created_to'])->endOfDay());
        }

        if (!empty($filters['updated_from'])) {
            $query->where('updated_at', '>=', Carbon::parse($filters['updated_from']));
        }

        if (!empty($filters['updated_to'])) {
            $query->where('updated_at', '<=', Carbon::parse($filters['updated_to'])->endOfDay());
        }

        // Incluir eliminados si se especifica
        if (!empty($filters['include_deleted'])) {
            $query->withTrashed();
        }

        // Ordenamiento por defecto
        $query->orderBy('created_at', 'desc');

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function search(
        ?string $tenantId,
        string $query,
        array $filters = [],
        int $limit = 20
    ): Collection {
        $builder = CustomerModel::query();

        if ($tenantId) {
            $builder->where('tenant_id', $tenantId);
        }

        // Búsqueda en múltiples campos
        $builder->where(function ($q) use ($query) {
            $q->where('business_name', 'like', '%' . $query . '%')
              ->orWhere('first_name', 'like', '%' . $query . '%')
              ->orWhere('last_name', 'like', '%' . $query . '%')
              ->orWhere('email', 'like', '%' . $query . '%')
              ->orWhere('document_number', 'like', '%' . $query . '%')
              ->orWhere('phone', 'like', '%' . $query . '%');
        });

        // Aplicar filtros adicionales
        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $builder->where('type', $filters['type']);
        }

        return $builder->limit($limit)->get()->map(fn($model) => $this->modelToEntity($model));
    }

    public function delete(int $id): bool
    {
        $model = CustomerModel::find($id);
        return $model ? $model->delete() : false;
    }

    public function exists(?string $tenantId, DocumentId $documentId): bool
    {
        $query = CustomerModel::where('document_type', $documentId->type)
            ->where('document_number', $documentId->number);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        return $query->exists();
    }

    public function getMetrics(?string $tenantId, array $filters = []): array
    {
        $query = CustomerModel::query();

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        // Aplicar filtros de fecha si se proporcionan
        if (!empty($filters['from'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['from']));
        }

        if (!empty($filters['to'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['to'])->endOfDay());
        }

        return [
            'total_customers' => $query->count(),
            'active_customers' => $query->where('status', 'active')->count(),
            'inactive_customers' => $query->where('status', 'inactive')->count(),
            'blacklisted_customers' => $query->where('status', 'blacklisted')->count(),
            'prospect_customers' => $query->where('status', 'prospect')->count(),
            'natural_persons' => $query->where('type', 'natural')->count(),
            'juridical_persons' => $query->where('type', 'juridical')->count(),
            'customers_with_email' => $query->whereNotNull('email')->count(),
            'customers_with_phone' => $query->whereNotNull('phone')->count(),
            'deleted_customers' => CustomerModel::onlyTrashed()->count(),
        ];
    }

    private function modelToEntity(CustomerModel $model): Customer
    {
        // Por simplicidad, creamos una instancia básica
        // En una implementación completa, incluiríamos contactos, direcciones, etc.
        return new Customer(
            id: $model->id,
            tenantId: $model->tenant_id,
            type: \App\Modules\Customers\Domain\ValueObjects\CustomerType::fromString($model->type),
            documentId: new DocumentId($model->document_type, $model->document_number),
            businessName: $model->business_name,
            firstName: $model->first_name,
            lastName: $model->last_name,
            email: $model->email ? new \App\Modules\Customers\Domain\ValueObjects\Email($model->email) : null,
            phone: $model->phone,
            status: \App\Modules\Customers\Domain\ValueObjects\CustomerStatus::fromString($model->status),
            segment: $model->segment,
            notes: $model->notes,
            createdAt: $model->created_at ? Carbon::parse($model->created_at) : null,
            updatedAt: $model->updated_at ? Carbon::parse($model->updated_at) : null,
            deletedAt: $model->deleted_at ? Carbon::parse($model->deleted_at) : null,
            blacklistReason: $model->blacklist_reason
        );
    }
}
