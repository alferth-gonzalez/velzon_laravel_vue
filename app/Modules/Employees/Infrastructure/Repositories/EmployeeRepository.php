<?php

namespace App\Modules\Employees\Infrastructure\Repositories;

use App\Modules\Employees\Domain\Models\Employee;
use App\Modules\Employees\Domain\Repositories\EmployeeRepositoryInterface; // Interfaz del repositorio

class EmployeeRepository implements EmployeeRepositoryInterface
{
    // Obtener todos los empleados
    public function all()
    {
        return Employee::all(); // Eloquent: obtiene todos los empleados
    }

    // Obtener un empleado por ID
    public function find($id)
    {
        return Employee::findOrFail($id); // Eloquent: encuentra el empleado por ID
    }

    // Crear un nuevo empleado
    public function create(array $data)
    {
        return Employee::create($data); // Eloquent: crea el empleado
    }
}