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

    // Inactivar empleados con apellido González
    public function inactivateGonzalezEmployees(): array
    {
        // 1. Buscar empleados con apellido "González"
        $employees = $this->employeeRepository->findByLastName('González');
        
        $results = [
            'inactivated' => 0,
            'errors' => [],
            'total_found' => count($employees)
        ];
        
        // 2. Inactivar cada empleado
        foreach ($employees as $employee) {
            try {
                // Lógica de negocio: verificar si se puede inactivar
                if ($employee->status()->value === 'inactive') {
                    $results['errors'][] = "Empleado {$employee->id()} ya está inactivo";
                    continue;
                }
                
                // Inactivar empleado
                $employee->inactivate();
                $this->employeeRepository->save($employee);
                $results['inactivated']++;
                
            } catch (\Exception $e) {
                $results['errors'][] = "Error con empleado {$employee->id()}: " . $e->getMessage();
            }
        }
        
        return $results;
    }

    // Obtener empleados por apellido (función auxiliar)
    public function getEmployeesByLastName(string $lastName): array
    {
        return $this->employeeRepository->findByLastName($lastName);
    }
}