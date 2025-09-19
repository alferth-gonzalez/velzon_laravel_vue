<?php
namespace App\Modules\Employees\Application\Commands;
final class DeleteEmployeeCommand {
    public function __construct(public string $id) {}
}