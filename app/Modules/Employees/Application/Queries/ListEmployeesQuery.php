<?php
namespace App\Modules\Employees\Application\Queries;
final class ListEmployeesQuery {
    public function __construct(
        public ?string $tenantId,
        public ?string $status,
        public ?string $search,
        public int $page = 1,
        public int $perPage = 15
    ) {}
}