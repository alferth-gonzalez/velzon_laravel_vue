# Documentación: DTOs (Data Transfer Objects)

## 📋 Índice
1. [Introducción](#introducción)
2. [¿Qué son los DTOs?](#qué-son-los-dtos)
3. [Estructura de un DTO](#estructura-de-un-dto)
4. [Ciclo de Vida del DTO](#ciclo-de-vida-del-dto)
5. [Para qué sirven los DTOs](#para-qué-sirven-los-dtos)
6. [Casos de Uso para DTOs](#casos-de-uso-para-dtos)
7. [Interacción con el Ciclo de Vida del Proyecto](#interacción-con-el-ciclo-de-vida-del-proyecto)
8. [Mejores Prácticas](#mejores-prácticas)
9. [Ejemplos Completos](#ejemplos-completos)
10. [Patrones y Anti-patrones](#patrones-y-anti-patrones)
11. [Testing de DTOs](#testing-de-dtos)
12. [Resumen y Recomendaciones](#resumen-y-recomendaciones)

---

## 🎯 Introducción

Los **DTOs** (Data Transfer Objects) son una pieza fundamental en la arquitectura DDD (Domain-Driven Design). Representan los **datos de salida** que el sistema devuelve al usuario después de procesar una acción.

### **¿Por qué usar DTOs?**
- **Estandarización** del formato de datos
- **Serialización** para APIs
- **Deserialización** de datos externos
- **Separación** entre lógica de negocio y presentación
- **Mantenibilidad** del código

---

## 🎯 ¿Qué son los DTOs?

### **Definición:**
Un **DTO** (Data Transfer Object) es un objeto que se usa para **transferir datos** entre diferentes capas o procesos de la aplicación. Es como un "contenedor" que lleva información de un lugar a otro.

### **Características principales:**
- **Solo datos**: No tiene lógica de negocio
- **Serializable**: Se puede convertir a JSON/XML
- **Inmutable**: No cambia después de ser creado
- **Ligero**: Solo contiene propiedades públicas
- **Estructurado**: Tiene un formato específico y consistente

### **Analogía de la Vida Real:**
Imagina que los **DTOs** son como **reportes de trabajo** en una empresa:

- **Reporte de Ventas**: Contiene datos de ventas, clientes, productos, etc.
- **Solo datos**: No procesa las ventas, solo presenta la información
- **Estructurado**: Tiene secciones específicas y ordenadas
- **Serializable**: Se puede convertir a PDF, Excel, etc.

---

## 🏗️ Estructura de un DTO

### **Ejemplo básico:**
```php
// app/Modules/Employees/Application/DTOs/EmployeeDTO.php
final class EmployeeDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $tenantId,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $documentType,
        public readonly string $documentNumber,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?string $hireDate,
        public readonly string $status,
        public readonly string $createdAt,
        public readonly string $updatedAt
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenantId,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'document_type' => $this->documentType,
            'document_number' => $this->documentNumber,
            'email' => $this->email,
            'phone' => $this->phone,
            'hire_date' => $this->hireDate,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            tenantId: $data['tenant_id'],
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            documentType: $data['document_type'],
            documentNumber: $data['document_number'],
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            hireDate: $data['hire_date'] ?? null,
            status: $data['status'],
            createdAt: $data['created_at'],
            updatedAt: $data['updated_at']
        );
    }
}
```

### **¿Por qué `readonly`?**
- **Inmutabilidad**: Los datos no pueden cambiar después de crear el DTO
- **Thread-safe**: Múltiples hilos pueden acceder sin problemas
- **Claridad**: Es obvio que es solo para transportar datos
- **Prevención de errores**: Evita modificaciones accidentales

### **¿Por qué `final`?**
- **Prevención de herencia**: No se puede extender accidentalmente
- **Claridad**: Indica que es una clase completa
- **Optimización**: El compilador puede optimizar mejor

---

## 🔄 Ciclo de Vida del DTO

### **1. Creación en el Handler:**
```php
class CreateEmployeeHandler
{
    public function handle(CreateEmployeeCommand $command): EmployeeDTO
    {
        // 1. Crear entidad
        $employee = new Employee(/* ... */);
        
        // 2. Guardar en base de datos
        $this->employeeRepository->save($employee);
        
        // 3. Crear DTO para respuesta
        $dto = new EmployeeDTO(
            id: $employee->id(),
            tenantId: $employee->tenantId(),
            firstName: $employee->firstName(),
            lastName: $employee->lastName(),
            documentType: $employee->document()->type(),
            documentNumber: $employee->document()->number(),
            email: $employee->email()?->value(),
            phone: $employee->phone()?->value(),
            hireDate: $employee->hireDate()?->format('Y-m-d'),
            status: $employee->status()->value,
            createdAt: $employee->createdAt()->format('Y-m-d H:i:s'),
            updatedAt: $employee->updatedAt()->format('Y-m-d H:i:s')
        );
        
        return $dto;
    }
}
```

### **2. Uso en el Controller:**
```php
class EmployeeController extends Controller
{
    public function store(CreateEmployeeRequest $request) {
        $command = new CreateEmployeeCommand(/* ... */);
        $handler = app(CreateEmployeeHandler::class);
        
        // 1. Obtener DTO del Handler
        $dto = $handler->handle($command);
        
        // 2. Convertir DTO a array para JSON
        return response()->json(['data' => $dto->toArray()], 201);
    }
}
```

### **3. Serialización para API:**
```php
// El DTO se convierte automáticamente a JSON
{
    "data": {
        "id": "123e4567-e89b-12d3-a456-426614174000",
        "tenant_id": "tenant-1",
        "first_name": "Juan",
        "last_name": "Pérez",
        "document_type": "CC",
        "document_number": "12345678",
        "email": "juan@email.com",
        "phone": "+573001234567",
        "hire_date": "2023-01-01",
        "status": "active",
        "created_at": "2023-01-01 10:00:00",
        "updated_at": "2023-01-01 10:00:00"
    }
}
```

### **4. Destrucción:**
- El DTO se destruye automáticamente al salir del scope
- No persiste en la base de datos
- Solo existe durante la transferencia de datos

---

## 🎯 Para qué sirven los DTOs

### **1. Transferir datos entre capas:**
```php
// Handler → Controller
class CreateEmployeeHandler
{
    public function handle(CreateEmployeeCommand $command): EmployeeDTO
    {
        $employee = $this->createEmployee($command);
        return $this->convertToDTO($employee);
    }
}

// Controller → API
class EmployeeController
{
    public function store(CreateEmployeeRequest $request) {
        $dto = $this->handler->handle($command);
        return response()->json(['data' => $dto->toArray()]);
    }
}
```

### **2. Serializar datos para APIs:**
```php
// DTO → JSON
$dto = new EmployeeDTO(/* ... */);
$json = json_encode($dto->toArray());

// DTO → XML
$dto = new EmployeeDTO(/* ... */);
$xml = $this->convertToXML($dto->toArray());
```

### **3. Deserializar datos de APIs:**
```php
// JSON → DTO
$data = json_decode($json, true);
$dto = EmployeeDTO::fromArray($data);

// Array → DTO
$dto = EmployeeDTO::fromArray($request->all());
```

### **4. Estandarizar formato de datos:**
```php
// Diferentes fuentes → Mismo formato
$dtoFromDatabase = EmployeeDTO::fromArray($dbData);
$dtoFromAPI = EmployeeDTO::fromArray($apiData);
$dtoFromFile = EmployeeDTO::fromArray($fileData);

// Todas tienen el mismo formato
```

---

## 📋 Casos de Uso para DTOs

### **1. Respuestas de API:**
```php
// GET /api/employees/123
{
    "data": {
        "id": "123",
        "first_name": "Juan",
        "last_name": "Pérez",
        "email": "juan@email.com"
    }
}
```

### **2. Listas paginadas:**
```php
// GET /api/employees
{
    "data": [
        {
            "id": "123",
            "first_name": "Juan",
            "last_name": "Pérez"
        },
        {
            "id": "124",
            "first_name": "María",
            "last_name": "González"
        }
    ],
    "meta": {
        "total": 2,
        "page": 1,
        "per_page": 15
    }
}
```

### **3. Reportes:**
```php
// GET /api/employees/report
{
    "data": {
        "total_employees": 150,
        "active_employees": 120,
        "inactive_employees": 30,
        "average_salary": 50000
    }
}
```

### **4. Exportación de datos:**
```php
// Exportar a CSV
$employees = $this->getEmployees();
$csvData = [];

foreach ($employees as $employee) {
    $dto = new EmployeeDTO(/* ... */);
    $csvData[] = $dto->toArray();
}

$this->exportToCSV($csvData);
```

### **5. Respuestas de búsqueda:**
```php
// GET /api/employees/search?q=Juan
{
    "data": [
        {
            "id": "123",
            "first_name": "Juan",
            "last_name": "Pérez",
            "email": "juan@email.com",
            "department": "IT"
        }
    ],
    "meta": {
        "query": "Juan",
        "total": 1,
        "page": 1
    }
}
```

---

## 🔄 Interacción con el Ciclo de Vida del Proyecto

### **1. En el Módulo:**
```php
// app/Modules/Employees/Application/DTOs/
├── EmployeeDTO.php
├── EmployeeListDTO.php
├── EmployeeReportDTO.php
├── EmployeeSummaryDTO.php
└── EmployeeSearchDTO.php
```

### **2. En el Service Provider:**
```php
class EmployeesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Los DTOs no necesitan registro especial
        // Se instancian directamente cuando se necesitan
    }
}
```

### **3. En los Handlers:**
```php
class CreateEmployeeHandler
{
    public function handle(CreateEmployeeCommand $command): EmployeeDTO
    {
        $employee = $this->createEmployee($command);
        return $this->convertToDTO($employee);
    }

    private function convertToDTO(Employee $employee): EmployeeDTO
    {
        return new EmployeeDTO(
            id: $employee->id(),
            tenantId: $employee->tenantId(),
            firstName: $employee->firstName(),
            lastName: $employee->lastName(),
            // ... más campos
        );
    }
}
```

### **4. En los Controllers:**
```php
class EmployeeController extends Controller
{
    public function index() {
        $employees = $this->employeeService->getAllEmployees();
        
        $dtos = array_map(function($employee) {
            return $this->convertToDTO($employee);
        }, $employees);
        
        return response()->json([
            'data' => array_map(fn($dto) => $dto->toArray(), $dtos)
        ]);
    }
}
```

### **5. En Commands de Consola:**
```php
class ExportEmployeesCommand extends Command
{
    public function handle() {
        $employees = $this->employeeService->getAllEmployees();
        
        foreach ($employees as $employee) {
            $dto = new EmployeeDTO(/* ... */);
            $this->line($dto->toArray());
        }
    }
}
```

---

## 🎨 Mejores Prácticas

### **1. DTOs específicos para cada caso:**
```php
// ✅ BIEN - DTOs específicos
EmployeeDTO           // Para empleado individual
EmployeeListDTO       // Para lista de empleados
EmployeeSummaryDTO    // Para resumen de empleado
EmployeeReportDTO     // Para reportes

// ❌ MAL - DTO genérico
GenericDTO
DataDTO
ResponseDTO
```

### **2. DTOs inmutables:**
```php
// ✅ BIEN - Inmutable
final class EmployeeDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $firstName
    ) {}
}

// ❌ MAL - Mutable
final class EmployeeDTO
{
    public string $id;
    public string $firstName;
    
    public function setId(string $id): void {
        $this->id = $id; // ❌ Puede cambiar
    }
}
```

### **3. Métodos de conversión:**
```php
// ✅ BIEN - Métodos de conversión
final class EmployeeDTO
{
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->firstName,
            // ... más campos
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            firstName: $data['first_name'],
            // ... más campos
        );
    }
}
```

### **4. Validación de datos:**
```php
// ✅ BIEN - Validación en fromArray
public static function fromArray(array $data): self
{
    if (!isset($data['id'])) {
        throw new InvalidArgumentException('ID is required');
    }
    
    if (!isset($data['first_name'])) {
        throw new InvalidArgumentException('First name is required');
    }
    
    return new self(
        id: $data['id'],
        firstName: $data['first_name'],
        // ... más campos
    );
}
```

### **5. Naming consistente:**
```php
// ✅ BIEN - Naming consistente
EmployeeDTO
EmployeeListDTO
EmployeeReportDTO
EmployeeSummaryDTO

// ❌ MAL - Naming inconsistente
EmployeeDTO
EmployeeListData
EmployeeReportObject
EmployeeSummaryData
```

---

## 💡 Ejemplos Completos

### **Ejemplo 1: DTO Simple**
```php
// app/Modules/Employees/Application/DTOs/EmployeeSummaryDTO.php
final class EmployeeSummaryDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $fullName,
        public readonly string $status,
        public readonly string $department
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->fullName,
            'status' => $this->status,
            'department' => $this->department,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            fullName: $data['full_name'],
            status: $data['status'],
            department: $data['department']
        );
    }
}
```

### **Ejemplo 2: DTO Complejo**
```php
// app/Modules/Employees/Application/DTOs/EmployeeReportDTO.php
final class EmployeeReportDTO
{
    public function __construct(
        public readonly string $period,
        public readonly int $totalEmployees,
        public readonly int $activeEmployees,
        public readonly int $inactiveEmployees,
        public readonly float $averageSalary,
        public readonly array $departments,
        public readonly array $topPerformers
    ) {}

    public function toArray(): array
    {
        return [
            'period' => $this->period,
            'total_employees' => $this->totalEmployees,
            'active_employees' => $this->activeEmployees,
            'inactive_employees' => $this->inactiveEmployees,
            'average_salary' => $this->averageSalary,
            'departments' => $this->departments,
            'top_performers' => $this->topPerformers,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            period: $data['period'],
            totalEmployees: $data['total_employees'],
            activeEmployees: $data['active_employees'],
            inactiveEmployees: $data['inactive_employees'],
            averageSalary: $data['average_salary'],
            departments: $data['departments'],
            topPerformers: $data['top_performers']
        );
    }
}
```

### **Ejemplo 3: DTO con Validación**
```php
// app/Modules/Employees/Application/DTOs/EmployeeDTO.php
final class EmployeeDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $tenantId,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $documentType,
        public readonly string $documentNumber,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?string $hireDate,
        public readonly string $status,
        public readonly string $createdAt,
        public readonly string $updatedAt
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenantId,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'document_type' => $this->documentType,
            'document_number' => $this->documentNumber,
            'email' => $this->email,
            'phone' => $this->phone,
            'hire_date' => $this->hireDate,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    public static function fromArray(array $data): self
    {
        $this->validateData($data);
        
        return new self(
            id: $data['id'],
            tenantId: $data['tenant_id'],
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            documentType: $data['document_type'],
            documentNumber: $data['document_number'],
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            hireDate: $data['hire_date'] ?? null,
            status: $data['status'],
            createdAt: $data['created_at'],
            updatedAt: $data['updated_at']
        );
    }

    private static function validateData(array $data): void
    {
        $required = ['id', 'tenant_id', 'first_name', 'last_name', 'status'];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new InvalidArgumentException("Field '{$field}' is required");
            }
        }
    }
}
```

---

## 🚫 Patrones y Anti-patrones

### **✅ Patrones Recomendados:**

#### **1. DTOs específicos:**
```php
// ✅ BIEN - DTOs específicos
EmployeeDTO
EmployeeListDTO
EmployeeReportDTO
```

#### **2. DTOs inmutables:**
```php
// ✅ BIEN - Inmutable con readonly
final class EmployeeDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $firstName
    ) {}
}
```

#### **3. Métodos de conversión:**
```php
// ✅ BIEN - Métodos de conversión
final class EmployeeDTO
{
    public function toArray(): array { /* ... */ }
    public static function fromArray(array $data): self { /* ... */ }
}
```

### **❌ Anti-patrones (Evitar):**

#### **1. DTOs genéricos:**
```php
// ❌ MAL - DTO genérico
final class GenericDTO
{
    public function __construct(
        public readonly array $data
    ) {}
}
```

#### **2. DTOs mutables:**
```php
// ❌ MAL - DTO mutable
final class EmployeeDTO
{
    public string $id;
    public string $firstName;
    
    public function setId(string $id): void {
        $this->id = $id; // ❌ Puede cambiar
    }
}
```

#### **3. DTOs con lógica de negocio:**
```php
// ❌ MAL - DTO con lógica
final class EmployeeDTO
{
    public function calculateSalary(): float {
        // ❌ NO debe tener lógica de negocio
        return $this->baseSalary * 1.1;
    }
}
```

---

## 🧪 Testing de DTOs

### **Test de conversión:**
```php
// tests/Unit/DTOs/EmployeeDTOTest.php
class EmployeeDTOTest extends TestCase
{
    public function test_to_array_returns_correct_data(): void
    {
        $dto = new EmployeeDTO(
            id: '123',
            tenantId: 'tenant-1',
            firstName: 'Juan',
            lastName: 'Pérez',
            documentType: 'CC',
            documentNumber: '12345678',
            email: 'juan@email.com',
            phone: '+573001234567',
            hireDate: '2023-01-01',
            status: 'active',
            createdAt: '2023-01-01 10:00:00',
            updatedAt: '2023-01-01 10:00:00'
        );

        $expected = [
            'id' => '123',
            'tenant_id' => 'tenant-1',
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'document_type' => 'CC',
            'document_number' => '12345678',
            'email' => 'juan@email.com',
            'phone' => '+573001234567',
            'hire_date' => '2023-01-01',
            'status' => 'active',
            'created_at' => '2023-01-01 10:00:00',
            'updated_at' => '2023-01-01 10:00:00',
        ];

        $this->assertEquals($expected, $dto->toArray());
    }

    public function test_from_array_creates_dto_correctly(): void
    {
        $data = [
            'id' => '123',
            'tenant_id' => 'tenant-1',
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'document_type' => 'CC',
            'document_number' => '12345678',
            'email' => 'juan@email.com',
            'phone' => '+573001234567',
            'hire_date' => '2023-01-01',
            'status' => 'active',
            'created_at' => '2023-01-01 10:00:00',
            'updated_at' => '2023-01-01 10:00:00',
        ];

        $dto = EmployeeDTO::fromArray($data);

        $this->assertEquals('123', $dto->id);
        $this->assertEquals('tenant-1', $dto->tenantId);
        $this->assertEquals('Juan', $dto->firstName);
        $this->assertEquals('Pérez', $dto->lastName);
    }

    public function test_from_array_throws_exception_with_missing_required_field(): void
    {
        $data = [
            'id' => '123',
            // 'tenant_id' => 'tenant-1', // ❌ Faltante
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'status' => 'active',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Field 'tenant_id' is required");

        EmployeeDTO::fromArray($data);
    }
}
```

---

## 📝 Resumen y Recomendaciones

### **🎯 DTOs son:**
- **Objetos de transferencia** de datos
- **Solo datos**, sin lógica de negocio
- **Serializables** para APIs
- **Inmutables** para prevenir cambios

### **📋 Para qué sirven:**
- Transferir datos entre capas
- Serializar para APIs
- Deserializar de APIs
- Estandarizar formato de datos

### **🎯 Casos de uso:**
- Respuestas de API
- Listas paginadas
- Reportes
- Exportación de datos
- Respuestas de búsqueda

### **🔄 En el ciclo de vida:**
- Se crean en Handlers
- Se usan en Controllers
- Se serializan para APIs
- Se destruyen automáticamente

### **✅ Mejores prácticas:**
- DTOs específicos para cada caso
- DTOs inmutables
- Métodos de conversión
- Validación de datos
- Naming consistente

### **❌ Evitar:**
- DTOs genéricos
- DTOs mutables
- DTOs con lógica de negocio
- Naming inconsistente

**Los DTOs son esenciales para una arquitectura limpia, permitiendo transferir datos de forma estructurada y consistente entre diferentes capas de la aplicación.**
