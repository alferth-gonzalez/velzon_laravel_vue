# Documentación: Relación entre Controller y Service

## 📋 Índice
1. [Introducción](#introducción)
2. [Arquitectura Controller-Service](#arquitectura-controller-service)
3. [Responsabilidades de cada capa](#responsabilidades-de-cada-capa)
4. [Cuándo usar Service vs Controller](#cuándo-usar-service-vs-controller)
5. [Patrones y Mejores Prácticas](#patrones-y-mejores-prácticas)
6. [Ejemplos Prácticos](#ejemplos-prácticos)
7. [Casos de Uso Comunes](#casos-de-uso-comunes)
8. [Ventajas y Beneficios](#ventajas-y-beneficios)

---

## 🎯 Introducción

La relación entre **Controller** y **Service** es fundamental en la arquitectura de aplicaciones Laravel modulares. Esta separación permite mantener un código limpio, mantenible y escalable, siguiendo el principio de **separación de responsabilidades**.

### ¿Por qué separar Controller y Service?
- **Controller**: Se enfoca en manejar peticiones HTTP
- **Service**: Se enfoca en la lógica de negocio
- **Código más limpio**: Cada clase tiene una responsabilidad específica
- **Fácil testing**: Puedes probar la lógica de negocio independientemente
- **Reutilización**: Los Services pueden ser usados desde múltiples Controllers

---

## 🏗️ Arquitectura Controller-Service

```
┌─────────────────────────────────────┐
│           Controller                │
│  (HTTP, Validación, Respuestas)     │
└─────────────┬───────────────────────┘
              │
              │ usa
              ▼
┌─────────────────────────────────────┐
│            Service                  │
│     (Lógica de Negocio)            │
└─────────────┬───────────────────────┘
              │
              │ usa
              ▼
┌─────────────────────────────────────┐
│           Repository                │
│        (Acceso a Datos)             │
└─────────────────────────────────────┘
```

### **Flujo de datos:**
1. **Request** → Controller
2. **Controller** → Service (lógica de negocio)
3. **Service** → Repository (datos)
4. **Repository** → Database
5. **Response** ← Controller

---

## 📋 Responsabilidades de cada capa

### **Controller - Responsabilidades:**

#### **✅ SÍ debe hacer:**
- Recibir peticiones HTTP (GET, POST, PUT, DELETE)
- Validar datos de entrada (usando Form Requests)
- Llamar a Services para procesar la lógica
- Formatear respuestas HTTP (JSON, códigos de estado)
- Manejar errores HTTP y excepciones
- Coordinar entre diferentes Services

#### **❌ NO debe hacer:**
- Lógica de negocio compleja
- Consultas directas a la base de datos
- Validaciones de reglas de negocio
- Transformaciones de datos complejas
- Cálculos o procesamientos

### **Service - Responsabilidades:**

#### **✅ SÍ debe hacer:**
- Implementar lógica de negocio
- Validar reglas de negocio
- Coordinar entre múltiples Repositories
- Aplicar transformaciones de datos
- Manejar transacciones complejas
- Procesar comandos y queries

#### **❌ NO debe hacer:**
- Manejar peticiones HTTP directamente
- Formatear respuestas HTTP
- Acceso directo a la base de datos (usa Repository)
- Lógica de presentación

---

## 🤔 Cuándo usar Service vs Controller

### **✅ Controller - Cuando la lógica es:**

#### **Simple (1-3 líneas):**
```php
// ✅ BIEN - Lógica simple en Controller
public function show(string $id) {
    $employee = $this->employeeService->getEmployeeById($id);
    return response()->json(['data' => $employee]);
}
```

#### **Específica para una función:**
```php
// ✅ BIEN - Función específica, no reutilizable
public function getEmployeeCount() {
    $count = $this->employeeRepository->count();
    return response()->json(['count' => $count]);
}
```

### **✅ Service - Cuando la lógica es:**

#### **Compleja (más de 3 líneas):**
```php
// ✅ BIEN - Lógica compleja en Service
public function inactivateGonzalezEmployees(): array {
    $employees = $this->employeeRepository->findByLastName('González');
    
    $results = ['inactivated' => 0, 'errors' => []];
    
    foreach ($employees as $employee) {
        try {
            if ($employee->status()->value === 'inactive') {
                $results['errors'][] = "Empleado {$employee->id()} ya está inactivo";
                continue;
            }
            
            $employee->inactivate();
            $this->employeeRepository->save($employee);
            $results['inactivated']++;
            
        } catch (\Exception $e) {
            $results['errors'][] = "Error: " . $e->getMessage();
        }
    }
    
    return $results;
}
```

#### **Reutilizable:**
```php
// ✅ BIEN - Lógica reutilizable en Service
public function getActiveEmployeesForReport(string $tenantId): array {
    $employees = $this->employeeRepository->findByTenant($tenantId);
    
    return array_filter($employees, function($employee) {
        return $employee->status() === EmployeeStatus::ACTIVE 
            && $employee->getYearsOfService() >= 1;
    });
}
```

---

## 🎨 Patrones y Mejores Prácticas

### **✅ Patrones Recomendados:**

#### **1. Inyección de Dependencias**
```php
class EmployeeController extends Controller
{
    public function __construct(
        private EmployeeService $employeeService
    ) {}
    
    public function index() {
        $employees = $this->employeeService->getAllEmployees();
        return response()->json(['data' => $employees]);
    }
}
```

#### **2. Manejo de Errores**
```php
// Controller
public function show(string $id) {
    try {
        $employee = $this->employeeService->getEmployeeById($id);
        return response()->json(['data' => $employee]);
    } catch (EmployeeNotFoundException $e) {
        return response()->json(['error' => 'Empleado no encontrado'], 404);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Error interno'], 500);
    }
}

// Service
public function getEmployeeById(string $id): Employee {
    $employee = $this->employeeRepository->findById($id);
    
    if (!$employee) {
        throw new EmployeeNotFoundException("Empleado con ID {$id} no encontrado");
    }
    
    return $employee;
}
```

#### **3. Validación de Datos**
```php
// Controller - Validación de entrada
public function store(CreateEmployeeRequest $request) {
    $employee = $this->employeeService->createEmployee($request->validated());
    return response()->json(['data' => $employee], 201);
}

// Service - Validación de negocio
public function createEmployee(array $data): Employee {
    // Validar reglas de negocio
    if ($this->employeeRepository->findByEmail($data['email'])) {
        throw new DuplicateEmailException('El email ya existe');
    }
    
    $employee = new Employee(/* ... */);
    $this->employeeRepository->save($employee);
    
    return $employee;
}
```

### **❌ Anti-patrones (Evitar):**

#### **1. Lógica de negocio en Controller**
```php
// ❌ MAL - Lógica compleja en Controller
public function inactivateGonzalezEmployees() {
    $employees = $this->employeeRepository->findByLastName('González');
    
    foreach ($employees as $employee) {
        if ($employee->status() !== 'inactive') {
            $employee->inactivate();
            $this->employeeRepository->save($employee);
        }
    }
    
    return response()->json(['message' => 'Empleados inactivados']);
}
```

#### **2. Acceso directo a Repository desde Controller**
```php
// ❌ MAL - Controller accede directamente al Repository
public function index() {
    $employees = $this->employeeRepository->paginate($filters, $page, $perPage);
    return response()->json(['data' => $employees]);
}
```

#### **3. Services que manejan HTTP**
```php
// ❌ MAL - Service maneja HTTP
class EmployeeService {
    public function getEmployeeAndReturnJson($id) {
        $employee = $this->employeeRepository->findById($id);
        return response()->json(['data' => $employee]); // ❌ NO aquí
    }
}
```

---

## 💡 Ejemplos Prácticos

### **Ejemplo 1: Función Simple (Controller)**
```php
// Controller
class EmployeeController extends Controller
{
    public function __construct(
        private EmployeeService $employeeService
    ) {}

    public function getEmployeeCount() {
        $count = $this->employeeService->getEmployeeCount();
        return response()->json(['count' => $count]);
    }
}

// Service
class EmployeeService
{
    public function getEmployeeCount(): int {
        return $this->employeeRepository->count();
    }
}
```

### **Ejemplo 2: Función Compleja (Service)**
```php
// Controller
class EmployeeController extends Controller
{
    public function inactivateGonzalezEmployees() {
        $results = $this->employeeService->inactivateGonzalezEmployees();
        
        return response()->json([
            'message' => "Se inactivaron {$results['inactivated']} empleados",
            'data' => $results
        ]);
    }
}

// Service
class EmployeeService
{
    public function inactivateGonzalezEmployees(): array {
        $employees = $this->employeeRepository->findByLastName('González');
        
        $results = ['inactivated' => 0, 'errors' => []];
        
        foreach ($employees as $employee) {
            try {
                if ($employee->status()->value === 'inactive') {
                    $results['errors'][] = "Empleado {$employee->id()} ya está inactivo";
                    continue;
                }
                
                $employee->inactivate();
                $this->employeeRepository->save($employee);
                $results['inactivated']++;
                
            } catch (\Exception $e) {
                $results['errors'][] = "Error: " . $e->getMessage();
            }
        }
        
        return $results;
    }
}
```

### **Ejemplo 3: Función Reutilizable (Service)**
```php
// Controller 1
class EmployeeController extends Controller
{
    public function getActiveEmployees() {
        $employees = $this->employeeService->getActiveEmployees();
        return response()->json(['data' => $employees]);
    }
}

// Controller 2
class ReportController extends Controller
{
    public function generateEmployeeReport() {
        $employees = $this->employeeService->getActiveEmployees();
        // Generar reporte...
    }
}

// Service (reutilizable)
class EmployeeService
{
    public function getActiveEmployees(): array {
        return $this->employeeRepository->findByStatus('active');
    }
}
```

---

## 🔧 Casos de Uso Comunes

### **1. CRUD Básico**
```php
// Controller
class EmployeeController extends Controller
{
    public function index() {
        $employees = $this->employeeService->getAllEmployees();
        return response()->json(['data' => $employees]);
    }
    
    public function store(CreateEmployeeRequest $request) {
        $employee = $this->employeeService->createEmployee($request->validated());
        return response()->json(['data' => $employee], 201);
    }
    
    public function show(string $id) {
        $employee = $this->employeeService->getEmployeeById($id);
        return response()->json(['data' => $employee]);
    }
    
    public function update(UpdateEmployeeRequest $request, string $id) {
        $employee = $this->employeeService->updateEmployee($id, $request->validated());
        return response()->json(['data' => $employee]);
    }
    
    public function destroy(string $id) {
        $this->employeeService->deleteEmployee($id);
        return response()->noContent();
    }
}
```

### **2. Funciones Específicas**
```php
// Controller
class EmployeeController extends Controller
{
    public function inactivateGonzalezEmployees() {
        $results = $this->employeeService->inactivateGonzalezEmployees();
        return response()->json($results);
    }
    
    public function getEmployeesByDepartment(string $department) {
        $employees = $this->employeeService->getEmployeesByDepartment($department);
        return response()->json(['data' => $employees]);
    }
    
    public function sendBirthdayEmails() {
        $results = $this->employeeService->sendBirthdayEmails();
        return response()->json(['message' => 'Emails enviados', 'data' => $results]);
    }
}
```

### **3. Operaciones Masivas**
```php
// Controller
class EmployeeController extends Controller
{
    public function bulkUpdate(BulkUpdateRequest $request) {
        $results = $this->employeeService->bulkUpdateEmployees($request->validated());
        return response()->json(['data' => $results]);
    }
    
    public function importEmployees(ImportEmployeesRequest $request) {
        $results = $this->employeeService->importEmployees($request->file('file'));
        return response()->json(['data' => $results]);
    }
}
```

---

## 🚀 Ventajas y Beneficios

### **1. Separación de Responsabilidades**
- **Controller**: Solo HTTP
- **Service**: Solo lógica de negocio
- **Repository**: Solo datos

### **2. Código Más Limpio**
```php
// ✅ BIEN - Responsabilidades claras
class EmployeeController {
    public function show($id) {
        $employee = $this->employeeService->getEmployeeById($id);
        return response()->json(['data' => $employee]);
    }
}

// ❌ MAL - Todo mezclado
class EmployeeController {
    public function show($id) {
        $employee = DB::table('employees')->where('id', $id)->first();
        if (!$employee) {
            return response()->json(['error' => 'No encontrado'], 404);
        }
        if ($employee->status !== 'active') {
            return response()->json(['error' => 'Inactivo'], 403);
        }
        return response()->json(['data' => $employee]);
    }
}
```

### **3. Fácil Testing**
```php
// Test del Service (sin HTTP)
public function test_inactivate_gonzalez_employees() {
    $mockRepository = Mockery::mock(EmployeeRepositoryInterface::class);
    $service = new EmployeeService($mockRepository);
    
    // Test de la lógica de negocio
    $results = $service->inactivateGonzalezEmployees();
    
    $this->assertEquals(2, $results['inactivated']);
}
```

### **4. Reutilización**
```php
// El mismo Service se puede usar en:
// - Controller HTTP
// - Comando de consola
// - Job de cola
// - Otro Service
```

### **5. Mantenibilidad**
- Cambios en lógica de negocio → Solo Service
- Cambios en HTTP → Solo Controller
- Cambios en datos → Solo Repository

---

## 📝 Resumen

### **Controller:**
- **Responsabilidad**: Manejar HTTP
- **Cuándo usar**: Para lógica simple y específica
- **NO debe**: Contener lógica de negocio compleja

### **Service:**
- **Responsabilidad**: Lógica de negocio
- **Cuándo usar**: Para lógica compleja y reutilizable
- **NO debe**: Manejar HTTP directamente

### **Regla práctica:**
- **¿Es simple y específica?** → Controller
- **¿Es compleja o reutilizable?** → Service
- **¿Tienes dudas?** → Service (más mantenible)

Esta arquitectura hace que tu código sea más limpio, testeable y escalable.
