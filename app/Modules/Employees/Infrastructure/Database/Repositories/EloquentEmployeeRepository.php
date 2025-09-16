<?php
declare(strict_types=1);

namespace App\Modules\Employees\Infrastructure\Database\Repositories;

use App\Modules\Employees\Domain\Repositories\EmployeeRepository;
use App\Modules\Employees\Domain\Entities\Employee;
use App\Modules\Employees\Domain\ValueObjects\{DocumentId, Email, Phone, EmployeeStatus};
use App\Modules\Employees\Infrastructure\Database\Models\EmployeeModel;

final class EloquentEmployeeRepository implements EmployeeRepository
{
    public function findById(string $id): ?Employee {
        $m = EmployeeModel::find($id);
        return $m ? $this->toDomain($m) : null;
    }

    public function findByDocument(?string $tenantId, DocumentId $document): ?Employee {
        $q = EmployeeModel::query()
            ->where('document_type', $document->type())
            ->where('document_number', $document->number());
        if ($tenantId) $q->where('tenant_id', $tenantId);
        $m = $q->first();
        return $m ? $this->toDomain($m) : null;
    }

    public function save(Employee $e): void {
        $m = EmployeeModel::find($e->id()) ?? new EmployeeModel(['id' => $e->id()]);
        $m->tenant_id       = $e->tenantId();
        $m->first_name      = $e->firstName();
        $m->last_name       = $e->lastName();
        $m->document_type   = $e->document()->type();
        $m->document_number = $e->document()->number();
        $m->email           = $e->email()?->value();
        $m->phone           = $e->phone()?->value();
        $m->hire_date       = $e->hireDate()?->format('Y-m-d');
        $m->status          = $e->status()->value;
        $m->save();
    }

    public function delete(string $id): void {
        EmployeeModel::where('id', $id)->delete();
    }

    public function paginate(array $filters, int $page, int $perPage): array {
        $q = EmployeeModel::query();

        if (!empty($filters['tenant_id']))    $q->where('tenant_id', $filters['tenant_id']);
        if (!empty($filters['status']))       $q->where('status', $filters['status']);
        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $q->where(function($w) use ($s) {
                $w->where('first_name','like',"%$s%")
                  ->orWhere('last_name','like',"%$s%")
                  ->orWhere('document_number','like',"%$s%");
            });
        }
        $total = (clone $q)->count();
        $rows = $q->orderByDesc('created_at')
                  ->forPage($page, $perPage)->get();

        return [
            'data' => array_map(fn($m) => $this->toDomain($m), $rows->all()),
            'total' => $total,
        ];
    }

    private function toDomain(EmployeeModel $m): Employee {
        return new Employee(
            id: (string)$m->id,
            tenantId: $m->tenant_id,
            firstName: $m->first_name,
            lastName: $m->last_name,
            document: new DocumentId($m->document_type, $m->document_number),
            email: $m->email ? new Email($m->email) : null,
            phone: $m->phone ? new Phone($m->phone) : null,
            hireDate: $m->hire_date ? new \DateTimeImmutable($m->hire_date->format('Y-m-d')) : null,
            status: EmployeeStatus::from($m->status),
            createdBy: $m->created_by,
            updatedBy: $m->updated_by,
            createdAt: new \DateTimeImmutable($m->created_at?->toAtomString() ?? 'now'),
            updatedAt: new \DateTimeImmutable($m->updated_at?->toAtomString() ?? 'now'),
            deletedAt: $m->deleted_at ? new \DateTimeImmutable($m->deleted_at->toAtomString()) : null
        );
    }
}