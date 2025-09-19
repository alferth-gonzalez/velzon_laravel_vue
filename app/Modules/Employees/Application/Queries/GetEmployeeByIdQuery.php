<?php
namespace App\Modules\Employees\Application\Queries;
final class GetEmployeeByIdQuery {
    public function __construct(public string $id) {}
}