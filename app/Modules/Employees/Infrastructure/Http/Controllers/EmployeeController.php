<?php

namespace App\Modules\Employees\Infrastructure\Http\Controllers;

use App\Modules\Employees\Domain\Services\EmployeeService;  // Inyección del servicio de dominio
use Inertia\Inertia;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    protected $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService; // Inyección del servicio de dominio
    }

    // Obtener todos los empleados
    public function index()
    {  
        $employees = $this->employeeService->getAllEmployees(); // Llama al servicio de dominio
        dd($employees);
        return Inertia::render('Employees/Index', [
            'employees' => $employees,
        ]);
    }

    // Crear un nuevo empleado
    public function create()
    {
        return Inertia::render('Employees/Create');
    }

    // Editar un empleado
    public function edit($id)
    {
        $employee = $this->employeeService->getEmployeeById($id);  // Llama al servicio de dominio
        return Inertia::render('Employees/Edit', [
            'employee' => $employee,
        ]);
    }

    // Almacenar un nuevo empleado
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'document_type' => 'required|string',
            'document_number' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
        ]);

        $this->employeeService->createEmployee($validated);  // Llama al servicio de dominio

        return redirect()->route('employees.index');
    }

    // Actualizar un empleado
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'document_type' => 'required|string',
            'document_number' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
        ]);

        $this->employeeService->updateEmployee($id, $validated);  // Llama al servicio de dominio

        return redirect()->route('employees.index');
    }

    // Eliminar un empleado
    public function destroy($id)
    {
        $this->employeeService->deleteEmployee($id);  // Llama al servicio de dominio
        return redirect()->route('employees.index');
    }
}