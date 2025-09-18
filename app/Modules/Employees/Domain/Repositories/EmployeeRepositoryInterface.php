<?php

namespace App\Modules\Employees\Domain\Repositories;

interface EmployeeRepositoryInterface
{
    public function all();               // Obtener todos los empleados
    public function find($id);           // Obtener un empleado por ID
    public function create(array $data); // Crear un nuevo empleado
}