# Ejemplo: Flujo petición HTTP

## 📋 Índice
1. [Descripción del ejemplo](#descripción-del-ejemplo)
2. [Petición HTTP](#petición-http)
3. [Flujo completo paso a paso](#flujo-completo-paso-a-paso)
4. [Archivos involucrados](#archivos-involucrados)
5. [Transformaciones de datos](#transformaciones-de-datos)
6. [Implementación de código](#implementación-de-código)
7. [Testing del ejemplo](#testing-del-ejemplo)
8. [Mejores prácticas aplicadas](#mejores-prácticas-aplicadas)
9. [Extensiones posibles](#extensiones-posibles)
10. [Resumen](#resumen)

---

## 🎯 Descripción del ejemplo

### **Funcionalidad:**
Inactivar todos los empleados con apellido "González" en el sistema.

### **Caso de uso:**
- Un administrador necesita inactivar todos los empleados González
- La acción se ejecuta desde el frontend mediante un botón
- Se devuelve un resumen de la operación realizada

### **Arquitectura utilizada:**
- **DDD (Domain-Driven Design)**
- **Patrón Repository**
- **Service Layer**
- **Controller → Service → Repository**

---

## 🌐 Petición HTTP

### **Endpoint:**
```
POST /api/employees/inactivate-gonzalez
```

### **Headers:**
```http
Content-Type: application/json
Authorization: Bearer {token}
Accept: application/json
```

### **Body (opcional):**
```json
{
    "updated_by": "admin@empresa.com"
}
```

### **Respuesta exitosa (200):**
```json
{
    "message": "Se inactivaron 5 empleados González de 7 encontrados",
    "data": {
        "inactivated": 5,
        "errors": [
            "Empleado 123 ya está inactivo",
            "Empleado 456 ya está inactivo"
        ],
        "total_found": 7
    }
}
```

### **Respuesta de error (422):**
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "updated_by": ["The updated_by field must be a string."]
    }
}
```

---

## 🔄 Flujo completo paso a paso

### **1. Usuario inicia la acción**
```
Usuario hace clic en botón "Inactivar Empleados González"
    ↓
Frontend envía petición HTTP POST
```

### **2. Laravel recibe la petición**
```
HTTP Request
    ↓
Route (api.php) - POST /api/employees/inactivate-gonzalez
    ↓
Controller (EmployeeController) - método inactivateGonzalezEmployees
```

### **3. Controller procesa la petición**
```
Controller recibe EmployeeService por inyección de dependencias
    ↓
Controller llama a $employeeService->inactivateGonzalezEmployees()
    ↓
Controller retorna respuesta JSON
```

### **4. Service ejecuta lógica de negocio**
```
Service recibe EmployeeRepositoryInterface por inyección
    ↓
Service busca empleados con apellido "González"
    ↓
Service procesa cada empleado encontrado
    ↓
Service aplica reglas de negocio (verificar estado, inactivar, guardar)
    ↓
Service retorna array con resultados
```

### **5. Repository maneja persistencia**
```
Repository busca empleados en base de datos
    ↓
Repository convierte datos de DB a Entities (toDomain)
    ↓
Repository guarda cambios en base de datos
    ↓
Repository retorna datos transformados
```

### **6. Entity ejecuta lógica de dominio**
```
Entity recibe comando de inactivar
    ↓
Entity valida estado actual
    ↓
Entity cambia status a "inactive"
    ↓
Entity dispara eventos de dominio (si aplica)
    ↓
Entity retorna estado actualizado
```

### **7. Respuesta al usuario**
```
Controller formatea respuesta
    ↓
Laravel serializa a JSON
    ↓
HTTP Response enviada al frontend
    ↓
Frontend muestra resultado al usuario
```

---

## 📁 Archivos involucrados

### **1. Ruta (Infrastructure)**
```
app/Modules/Employees/Infrastructure/Http/Routes/api.php
```
**Función:** Define el endpoint HTTP

### **2. Controller (Infrastructure)**
```
app/Modules/Employees/Infrastructure/Http/Controllers/EmployeeController.php
```
**Función:** Maneja la petición HTTP y coordina la respuesta

### **3. Service (Domain)**
```
app/Modules/Employees/Domain/Services/EmployeeService.php
```
**Función:** Contiene la lógica de negocio para inactivar empleados

### **4. Repository Interface (Domain)**
```
app/Modules/Employees/Domain/Repositories/EmployeeRepositoryInterface.php
```
**Función:** Define el contrato para acceso a datos

### **5. Repository Implementation (Infrastructure)**
```
app/Modules/Employees/Infrastructure/Database/Repositories/EloquentEmployeeRepository.php
```
**Función:** Implementa el acceso a datos usando Eloquent

### **6. Entity (Domain)**
```
app/Modules/Employees/Domain/Entities/Employee.php
```
**Función:** Representa un empleado con su lógica de negocio

### **7. Database**
```
MySQL/PostgreSQL - tabla "employees"
```
**Función:** Almacena los datos persistentes

---

## 🔄 Transformaciones de datos

### **1. Entrada HTTP → Service**
```php
// Datos de entrada (opcionales)
{
    "updated_by": "admin@empresa.com"
}

// Service recibe parámetros
public function inactivateGonzalezEmployees(): array
{
    // No recibe parámetros externos en este caso
}
```

### **2. Service → Repository**
```php
// Service busca empleados
$employees = $this->employeeRepository->findByLastName('González');

// Repository retorna array de Employee Entities
[
    Employee { id: "123", lastName: "González", status: "active" },
    Employee { id: "456", lastName: "González", status: "inactive" },
    Employee { id: "789", lastName: "González", status: "active" }
]
```

### **3. Entity → Cambios en DB**
```php
// Entity cambia estado
$employee->inactivate(); // status: "active" → "inactive"

// Repository guarda cambios
$this->employeeRepository->save($employee);

// Base de datos actualizada
UPDATE employees SET status = 'inactive' WHERE id = '123'
```

### **4. Service → Resultados**
```php
// Service retorna array de resultados
[
    'inactivated' => 2,      // Empleados inactivados
    'errors' => [            // Errores encontrados
        "Empleado 456 ya está inactivo"
    ],
    'total_found' => 3       // Total de empleados González encontrados
]
```

### **5. Controller → Respuesta HTTP**
```php
// Controller formatea respuesta
return response()->json([
    'message' => "Se inactivaron {$results['inactivated']} empleados González de {$results['total_found']} encontrados",
    'data' => $results
]);

// Respuesta JSON final
{
    "message": "Se inactivaron 2 empleados González de 3 encontrados",
    "data": {
        "inactivated": 2,
        "errors": ["Empleado 456 ya está inactivo"],
        "total_found": 3
    }
}
```

---

## 💻 Implementación de código

### **1. Ruta (api.php)**
```php
<?php

use App\Modules\Employees\Infrastructure\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->prefix('api')->group(function () {
    Route::prefix('employees')->group(function () {
        Route::get('/', [EmployeeController::class, 'index']);
        Route::get('/{id}', [EmployeeController::class, 'show']);
        Route::post('/', [EmployeeController::class, 'store']);
        Route::put('/{id}', [EmployeeController::class, 'update']);
        Route::delete('/{id}', [EmployeeController::class, 'destroy']);
        
        // Función específica: Inactivar empleados González
        Route::post('/inactivate-gonzalez', [EmployeeController::class, 'inactivateGonzalezEmployees']);
    });
});
```

### **2. Controller (EmployeeController.php)**
```php
<?php

namespace App\Modules\Employees\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Employees\Domain\Services\EmployeeService;
use App\Modules\Employees\Domain\Repositories\EmployeeRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class EmployeeController extends Controller
{
    // ... otros métodos ...

    /**
     * Inactivar empleados con apellido González
     */
    public function inactivateGonzalezEmployees(EmployeeService $employeeService)
    {
        // 1. Ejecutar servicio de dominio
        $results = $employeeService->inactivateGonzalezEmployees();
        
        // 2. Formatear y retornar respuesta
        return response()->json([
            'message' => "Se inactivaron {$results['inactivated']} empleados González de {$results['total_found']} encontrados",
            'data' => $results
        ]);
    }
}
```

### **3. Service (EmployeeService.php)**
```php
<?php

namespace App\Modules\Employees\Domain\Services;

use App\Modules\Employees\Domain\Models\Employee;
use App\Modules\Employees\Domain\Repositories\EmployeeRepositoryInterface;

class EmployeeService
{
    protected $employeeRepository;

    public function __construct(EmployeeRepositoryInterface $employeeRepository)
    {
        $this->employeeRepository = $employeeRepository;
    }

    /**
     * Inactivar empleados con apellido González
     */
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

    /**
     * Obtener empleados por apellido (función auxiliar)
     */
    public function getEmployeesByLastName(string $lastName): array
    {
        return $this->employeeRepository->findByLastName($lastName);
    }
}
```

### **4. Repository Interface (EmployeeRepositoryInterface.php)**
```php
<?php

namespace App\Modules\Employees\Domain\Repositories;

use App\Modules\Employees\Domain\Entities\Employee;

interface EmployeeRepositoryInterface
{
    public function findById(string $id): ?Employee;
    public function findByDocument(?string $tenantId, DocumentId $document): ?Employee;
    public function findByLastName(string $lastName): array;
    public function save(Employee $e): void;
    public function delete(string $id): void;
    public function paginate(array $filters, int $page, int $perPage): array;
}
```

### **5. Repository Implementation (EloquentEmployeeRepository.php)**
```php
<?php

namespace App\Modules\Employees\Infrastructure\Database\Repositories;

use App\Modules\Employees\Domain\Entities\Employee;
use App\Modules\Employees\Domain\Repositories\EmployeeRepositoryInterface;
use App\Modules\Employees\Infrastructure\Database\Models\EmployeeModel;

final class EloquentEmployeeRepository implements EmployeeRepositoryInterface
{
    // ... otros métodos ...

    public function findByLastName(string $lastName): array
    {
        $rows = EmployeeModel::query()
            ->where('last_name', 'like', "%$lastName%")
            ->whereNull('deleted_at')
            ->get();
            
        return array_map(fn($m) => $this->toDomain($m), $rows->all());
    }

    public function save(Employee $e): void
    {
        $model = EmployeeModel::find($e->id()) ?? new EmployeeModel();
        
        $model->id = $e->id();
        $model->tenant_id = $e->tenantId();
        $model->first_name = $e->firstName();
        $model->last_name = $e->lastName();
        $model->document_type = $e->document()->type();
        $model->document_number = $e->document()->number();
        $model->email = $e->email()?->value();
        $model->phone = $e->phone()?->value();
        $model->hire_date = $e->hireDate()?->format('Y-m-d');
        $model->status = $e->status()->value;
        $model->created_by = $e->createdBy();
        $model->updated_by = $e->updatedBy();
        $model->created_at = $e->createdAt()?->format('Y-m-d H:i:s');
        $model->updated_at = $e->updatedAt()?->format('Y-m-d H:i:s');
        $model->deleted_at = $e->deletedAt()?->format('Y-m-d H:i:s');
        
        $model->save();
    }

    private function toDomain(EmployeeModel $m): Employee
    {
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
```

### **6. Entity (Employee.php)**
```php
<?php

namespace App\Modules\Employees\Domain\Entities;

use App\Modules\Employees\Domain\ValueObjects\Email;
use App\Modules\Employees\Domain\ValueObjects\Phone;
use App\Modules\Employees\Domain\ValueObjects\DocumentId;

final class Employee
{
    // ... constructor y otros métodos ...

    /**
     * Inactivar empleado
     */
    public function inactivate(?string $updatedBy = null): void
    {
        $this->status = EmployeeStatus::Inactive;
        $this->updatedBy = $updatedBy;
        $this->updatedAt = new \DateTimeImmutable();
        
        // Disparar evento de dominio
        $this->addDomainEvent(new EmployeeInactivatedEvent($this));
    }

    // ... otros métodos ...
}
```

---

## 🧪 Testing del ejemplo

### **Test del Controller**
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Modules\Employees\Domain\Entities\Employee;

class EmployeeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactivate_gonzalez_employees_success(): void
    {
        // 1. Crear empleados de prueba
        $this->createEmployee('Juan', 'González', 'active');
        $this->createEmployee('María', 'González', 'active');
        $this->createEmployee('Pedro', 'González', 'inactive');

        // 2. Hacer petición
        $response = $this->postJson('/api/employees/inactivate-gonzalez');

        // 3. Verificar respuesta
        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Se inactivaron 2 empleados González de 3 encontrados',
                    'data' => [
                        'inactivated' => 2,
                        'total_found' => 3,
                        'errors' => ['Empleado Pedro ya está inactivo']
                    ]
                ]);
    }

    public function test_inactivate_gonzalez_employees_no_employees(): void
    {
        // 1. No crear empleados

        // 2. Hacer petición
        $response = $this->postJson('/api/employees/inactivate-gonzalez');

        // 3. Verificar respuesta
        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Se inactivaron 0 empleados González de 0 encontrados',
                    'data' => [
                        'inactivated' => 0,
                        'total_found' => 0,
                        'errors' => []
                    ]
                ]);
    }

    private function createEmployee(string $firstName, string $lastName, string $status): void
    {
        // Implementar según tu estructura de testing
        Employee::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'status' => $status,
            // ... otros campos
        ]);
    }
}
```

### **Test del Service**
```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Modules\Employees\Domain\Services\EmployeeService;
use App\Modules\Employees\Domain\Repositories\EmployeeRepositoryInterface;
use Mockery;

class EmployeeServiceTest extends TestCase
{
    public function test_inactivate_gonzalez_employees(): void
    {
        // 1. Mock del repository
        $mockRepository = Mockery::mock(EmployeeRepositoryInterface::class);
        
        // 2. Mock de empleados
        $employees = [
            Mockery::mock(Employee::class),
            Mockery::mock(Employee::class),
        ];
        
        // 3. Configurar mocks
        $mockRepository->shouldReceive('findByLastName')
            ->with('González')
            ->andReturn($employees);
            
        $employees[0]->shouldReceive('status')->andReturn((object)['value' => 'active']);
        $employees[0]->shouldReceive('id')->andReturn('123');
        $employees[0]->shouldReceive('inactivate')->once();
        
        $employees[1]->shouldReceive('status')->andReturn((object)['value' => 'inactive']);
        $employees[1]->shouldReceive('id')->andReturn('456');
        
        $mockRepository->shouldReceive('save')->once();
        
        // 4. Ejecutar service
        $service = new EmployeeService($mockRepository);
        $result = $service->inactivateGonzalezEmployees();
        
        // 5. Verificar resultado
        $this->assertEquals(1, $result['inactivated']);
        $this->assertEquals(2, $result['total_found']);
        $this->assertCount(1, $result['errors']);
    }
}
```

---

## ✅ Mejores prácticas aplicadas

### **1. Separación de responsabilidades**
- **Controller:** Solo maneja HTTP, no lógica de negocio
- **Service:** Contiene lógica de negocio específica
- **Repository:** Solo maneja persistencia de datos
- **Entity:** Contiene comportamiento del dominio

### **2. Inyección de dependencias**
```php
// Controller recibe Service por inyección
public function inactivateGonzalezEmployees(EmployeeService $employeeService)

// Service recibe Repository por inyección
public function __construct(EmployeeRepositoryInterface $employeeRepository)
```

### **3. Manejo de errores**
```php
// Service maneja errores de forma granular
try {
    $employee->inactivate();
    $this->employeeRepository->save($employee);
    $results['inactivated']++;
} catch (\Exception $e) {
    $results['errors'][] = "Error con empleado {$employee->id()}: " . $e->getMessage();
}
```

### **4. Respuesta estructurada**
```php
// Controller retorna respuesta consistente
return response()->json([
    'message' => "Se inactivaron {$results['inactivated']} empleados González de {$results['total_found']} encontrados",
    'data' => $results
]);
```

### **5. Validación de estado**
```php
// Service valida estado antes de actuar
if ($employee->status()->value === 'inactive') {
    $results['errors'][] = "Empleado {$employee->id()} ya está inactivo";
    continue;
}
```

---

## 🚀 Extensiones posibles

### **1. Agregar validación de antigüedad**
```php
public function inactivateGonzalezEmployeesWithSeniority(int $minYears = 3): array
{
    $employees = $this->employeeRepository->findByLastName('González');
    
    foreach ($employees as $employee) {
        if (!$this->hasMinimumSeniority($employee, $minYears)) {
            $results['errors'][] = "Empleado {$employee->id()} no cumple con la antigüedad mínima";
            continue;
        }
        // ... resto de la lógica
    }
}
```

### **2. Agregar logging**
```php
use Illuminate\Support\Facades\Log;

public function inactivateGonzalezEmployees(): array
{
    Log::info('Iniciando inactivación de empleados González');
    
    // ... lógica ...
    
    Log::info("Inactivación completada: {$results['inactivated']} empleados inactivados");
    
    return $results;
}
```

### **3. Agregar eventos de dominio**
```php
use Illuminate\Support\Facades\Event;

public function inactivateGonzalezEmployees(): array
{
    // ... lógica ...
    
    // Disparar evento personalizado
    Event::dispatch(new EmployeesInactivatedEvent($results));
    
    return $results;
}
```

### **4. Agregar transacciones de base de datos**
```php
use Illuminate\Support\Facades\DB;

public function inactivateGonzalezEmployees(): array
{
    return DB::transaction(function () {
        // ... lógica de inactivación ...
        return $results;
    });
}
```

---

## 🎯 Resumen

### **✅ Lo que hemos implementado:**
1. **Endpoint HTTP** para inactivar empleados González
2. **Controller** que maneja la petición
3. **Service** con lógica de negocio
4. **Repository** para acceso a datos
5. **Entity** con comportamiento de dominio
6. **Testing** completo
7. **Manejo de errores** robusto

### **🔄 Flujo de datos:**
```
HTTP Request → Controller → Service → Repository → Database
     ↓
HTTP Response ← Controller ← Service ← Repository ← Database
```

### **📊 Ventajas de esta implementación:**
- **Mantenible:** Lógica separada en capas
- **Testeable:** Cada componente se puede probar independientemente
- **Reutilizable:** El Service se puede usar desde otros lugares
- **Escalable:** Fácil agregar nuevas funcionalidades
- **Robusto:** Manejo de errores en cada nivel

### ** Siguientes pasos:**
1. **Implementar** más funcionalidades siguiendo este patrón
2. **Agregar** más validaciones según necesidades del negocio
3. **Crear** más tests para cubrir casos edge
4. **Documentar** cada nueva funcionalidad

**¡Este ejemplo te sirve como plantilla para crear cualquier funcionalidad en tu aplicación Laravel con arquitectura DDD!** 🎯
