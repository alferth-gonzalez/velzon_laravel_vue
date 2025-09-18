<?php

namespace App\Modules\Employees\Domain\Services;

use App\Modules\Employees\Domain\Models\Employee;
use App\Modules\Employees\Domain\Repositories\EmployeeRepositoryInterface; // Repositorio de dominio

class EmployeeService
{
    protected $employeeRepository;

    public function __construct(EmployeeRepositoryInterface $employeeRepository)
    {
        $this->employeeRepository = $employeeRepository;
    }

    // Obtener todos los empleados
    public function getAllEmployees()
    {
        return $this->employeeRepository->all(); // Llama al repositorio para obtener todos los empleados
    }

    // Obtener un empleado por ID
    public function getEmployeeById($id)
    {
        return $this->employeeRepository->find($id); // Llama al repositorio para encontrar el empleado por ID
    }

    // Crear un nuevo empleado
    public function createEmployee(array $data)
    {
        return $this->employeeRepository->create($data); // Llama al repositorio para crear el empleado
    }

    // Actualizar un empleado
    public function updateEmployee($id, array $data)
    {
        $employee = $this->employeeRepository->find($id); // Encuentra el empleado
        $employee->update($data); // Actualiza el empleado
        return $employee;
    }

    // Eliminar un empleado
    public function deleteEmployee($id)
    {
        $employee = $this->employeeRepository->find($id); // Encuentra el empleado
        return $employee->delete(); // Elimina el empleado
    }
}