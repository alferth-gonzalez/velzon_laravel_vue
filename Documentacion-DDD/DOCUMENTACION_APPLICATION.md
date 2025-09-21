# Documentación: Carpeta Application - Guía Completa

## 📋 Índice
1. [¿Qué es la carpeta Application?](#qué-es-la-carpeta-application)
2. [Propósito y Función](#propósito-y-función)
3. [Estructura de la carpeta Application](#estructura-de-la-carpeta-application)
4. [Elementos principales](#elementos-principales)
5. [Relación con otras capas](#relación-con-otras-capas)
6. [Ciclo de vida de una petición](#ciclo-de-vida-de-una-petición)
7. [Mejores prácticas](#mejores-prácticas)
8. [Ejemplos prácticos](#ejemplos-prácticos)
9. [Ventajas de esta arquitectura](#ventajas-de-esta-arquitectura)
10. [Resumen](#resumen)

---

## 🎯 ¿Qué es la carpeta Application?

### **Definición:**
La carpeta **Application** es la **capa de aplicación** en la arquitectura DDD (Domain-Driven Design). Es como el **"coordinador"** que conecta la interfaz de usuario (Controllers) con la lógica de negocio (Domain).

### **Características principales:**
- **Orquesta** las operaciones de la aplicación
- **Coordina** entre diferentes capas
- **Implementa** casos de uso específicos
- **Maneja** el flujo de datos entre capas
- **Es independiente** de la interfaz de usuario
- **Contiene** la lógica de aplicación, no de negocio

### **Analogía de la Vida Real:**
La carpeta **Application** es como un **"maestro de ceremonias"** en un evento:

- **Recibe** las solicitudes de los invitados (Controllers)
- **Coordina** con los diferentes servicios (Domain)
- **Organiza** el flujo de actividades
- **Asegura** que todo funcione correctamente
- **Retorna** respuestas organizadas

---

## 🎯 Propósito y Función

### **1. Orquestación:**
```php
// La Application coordina diferentes elementos
class CreateEmployeeHandler
{
    public function handle(CreateEmployeeCommand $command): EmployeeDTO
    {
        // 1. Validar datos
        $this->validateCommand($command);
        
        // 2. Crear entidad de dominio
        $employee = new Employee(/* ... */);
        
        // 3. Aplicar lógica de negocio
        $this->employeeService->processEmployee($employee);
        
        // 4. Persistir datos
        $this->employeeRepository->save($employee);
        
        // 5. Retornar DTO
        return $this->convertToDTO($employee);
    }
}
```

### **2. Coordinación entre capas:**
```php
// Application conecta Controllers con Domain
class EmployeeController extends Controller
{
    public function store(Request $request)
    {
        // 1. Controller recibe petición HTTP
        $command = new CreateEmployeeCommand(/* ... */);
        
        // 2. Application procesa la petición
        $handler = app(CreateEmployeeHandler::class);
        $dto = $handler->handle($command);
        
        // 3. Controller retorna respuesta HTTP
        return response()->json(['data' => $dto->toArray()]);
    }
}
```

### **3. Implementación de casos de uso:**
```php
// Cada Handler implementa un caso de uso específico
class ListEmployeesHandler
{
    public function handle(ListEmployeesQuery $query): EmployeeListDTO
    {
        // Caso de uso: Listar empleados con filtros
        $filters = $this->buildFilters($query);
        $employees = $this->employeeRepository->paginate($filters);
        
        return $this->convertToDTO($employees);
    }
}
```

---

## 🏗️ Estructura de la carpeta Application

### **Estructura típica:**
```
app/Modules/Employees/Application/
├── Commands/          # Comandos (acciones)
│   ├── CreateEmployeeCommand.php
│   ├── UpdateEmployeeCommand.php
│   └── DeleteEmployeeCommand.php
├── Queries/           # Consultas (lectura)
│   ├── GetEmployeeByIdQuery.php
│   ├── ListEmployeesQuery.php
│   └── SearchEmployeesQuery.php
├── Handlers/          # Manejadores
│   ├── CreateEmployeeHandler.php
│   ├── UpdateEmployeeHandler.php
│   ├── DeleteEmployeeHandler.php
│   ├── GetEmployeeByIdHandler.php
│   ├── ListEmployeesHandler.php
│   └── SearchEmployeesHandler.php
├── DTOs/              # Objetos de transferencia
│   ├── EmployeeDTO.php
│   ├── EmployeeListDTO.php
│   └── EmployeeSummaryDTO.php
├── Services/          # Servicios de aplicación
│   ├── EmployeeApplicationService.php
│   └── EmployeeNotificationService.php
├── Events/            # Eventos de aplicación
│   ├── EmployeeCreatedEvent.php
│   ├── EmployeeUpdatedEvent.php
│   └── EmployeeDeletedEvent.php
└── Validators/        # Validadores
    ├── CreateEmployeeValidator.php
    └── UpdateEmployeeValidator.php
```

### **Estructura en tu proyecto actual:**
```
app/Modules/Employees/Application/
├── Commands/
│   ├── CreateEmployeeCommand.php
│   └── DeleteEmployeeCommand.php
├── Queries/
│   ├── GetEmployeeByIdQuery.php
│   └── ListEmployeesQuery.php
├── Handlers/
│   └── CreateEmployeeHandler.php
└── DTOs/
    └── EmployeeDTO.php
```

---

## 🧩 Elementos principales

### **1. Commands (Comandos):**
```php
// Representan acciones que modifican datos
final class CreateEmployeeCommand
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly string $phone
    ) {}
}
```

### **2. Queries (Consultas):**
```php
// Representan consultas de lectura
final class ListEmployeesQuery
{
    public function __construct(
        public readonly ?string $tenantId = null,
        public readonly ?string $status = null,
        public readonly int $page = 1,
        public readonly int $perPage = 15
    ) {}
}
```

### **3. Handlers (Manejadores):**
```php
// Procesan Commands y Queries
class CreateEmployeeHandler
{
    public function handle(CreateEmployeeCommand $command): EmployeeDTO
    {
        // Lógica de aplicación
    }
}
```

### **4. DTOs (Data Transfer Objects):**
```php
// Transportan datos entre capas
final class EmployeeDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email
    ) {}
}
```

### **5. Services (Servicios de aplicación):**
```php
// Contienen lógica de aplicación compleja
class EmployeeApplicationService
{
    public function processEmployeeCreation(Employee $employee): void
    {
        // Lógica de aplicación
    }
}
```

---

## 🔗 Relación con otras capas

### **1. Con Controllers (Infrastructure):**
```php
// Controllers usan Application
class EmployeeController extends Controller
{
    public function store(Request $request)
    {
        $command = new CreateEmployeeCommand(/* ... */);
        $handler = app(CreateEmployeeHandler::class);
        $dto = $handler->handle($command);
        return response()->json(['data' => $dto->toArray()]);
    }
}
```

### **2. Con Domain:**
```php
// Application usa Domain
class CreateEmployeeHandler
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository,
        private EmployeeService $employeeService
    ) {}

    public function handle(CreateEmployeeCommand $command): EmployeeDTO
    {
        // Usa entidades del Domain
        $employee = new Employee(/* ... */);
        
        // Usa servicios del Domain
        $this->employeeService->validateEmployee($employee);
        
        // Usa repositorios del Domain
        $this->employeeRepository->save($employee);
    }
}
```

### **3. Con Infrastructure:**
```php
// Application coordina con Infrastructure
class CreateEmployeeHandler
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository, // Interface del Domain
        private EmployeeRepository $employeeRepositoryImpl      // Implementación de Infrastructure
    ) {}
}
```

---

## 🔄 Ciclo de vida de una petición

### **1. Petición HTTP:**
```php
// Usuario hace petición POST /api/employees
POST /api/employees
{
    "first_name": "Juan",
    "last_name": "Pérez",
    "email": "juan@email.com"
}
```

### **2. Controller recibe petición:**
```php
class EmployeeController extends Controller
{
    public function store(Request $request)
    {
        // 1. Crear Command con datos de la petición
        $command = new CreateEmployeeCommand(
            firstName: $request->input('first_name'),
            lastName: $request->input('last_name'),
            email: $request->input('email')
        );
        
        // 2. Pasar Command al Handler
        $handler = app(CreateEmployeeHandler::class);
        $dto = $handler->handle($command);
        
        // 3. Retornar respuesta HTTP
        return response()->json(['data' => $dto->toArray()]);
    }
}
```

### **3. Handler procesa Command:**
```php
class CreateEmployeeHandler
{
    public function handle(CreateEmployeeCommand $command): EmployeeDTO
    {
        // 1. Validar Command
        $this->validateCommand($command);
        
        // 2. Crear entidad de dominio
        $employee = new Employee(
            id: Str::uuid(),
            firstName: $command->firstName,
            lastName: $command->lastName,
            email: new Email($command->email)
        );
        
        // 3. Aplicar lógica de negocio
        $this->employeeService->processEmployee($employee);
        
        // 4. Persistir en base de datos
        $this->employeeRepository->save($employee);
        
        // 5. Crear DTO de respuesta
        return new EmployeeDTO(
            id: $employee->id(),
            firstName: $employee->firstName(),
            lastName: $employee->lastName(),
            email: $employee->email()->value()
        );
    }
}
```

### **4. Respuesta HTTP:**
```json
{
    "data": {
        "id": "123e4567-e89b-12d3-a456-426614174000",
        "first_name": "Juan",
        "last_name": "Pérez",
        "email": "juan@email.com"
    }
}
```

---

## ✅ Mejores prácticas

### **1. Separación de responsabilidades:**
```php
// ✅ BIEN - Cada Handler tiene una responsabilidad
class CreateEmployeeHandler
{
    public function handle(CreateEmployeeCommand $command): EmployeeDTO
    {
        // Solo maneja la creación de empleados
    }
}

// ❌ MAL - Handler con múltiples responsabilidades
class EmployeeHandler
{
    public function handle($command): mixed
    {
        // Maneja creación, actualización, eliminación, etc.
    }
}
```

### **2. Commands y Queries separados:**
```php
// ✅ BIEN - Separación clara
CreateEmployeeCommand    // Modifica datos
ListEmployeesQuery      // Lee datos

// ❌ MAL - Mezclar responsabilidades
EmployeeCommand         // ¿Modifica o lee?
```

### **3. Handlers específicos:**
```php
// ✅ BIEN - Un Handler por Command/Query
CreateEmployeeHandler
UpdateEmployeeHandler
DeleteEmployeeHandler

// ❌ MAL - Handler genérico
EmployeeHandler
```

### **4. DTOs inmutables:**
```php
// ✅ BIEN - DTO inmutable
final class EmployeeDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $firstName
    ) {}
}

// ❌ MAL - DTO mutable
final class EmployeeDTO
{
    public string $id;
    public string $firstName;
}
```

### **5. Validación en Commands:**
```php
// ✅ BIEN - Validación en el Command
final class CreateEmployeeCommand
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email
    ) {
        $this->validate();
    }
    
    private function validate(): void
    {
        if (empty($this->firstName)) {
            throw new InvalidArgumentException('First name is required');
        }
    }
}
```

---

## 💻 Ejemplos prácticos

### **Ejemplo 1: Command completo**
```php
// app/Modules/Employees/Application/Commands/CreateEmployeeCommand.php
final class CreateEmployeeCommand
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly ?string $phone = null,
        public readonly ?string $department = null
    ) {
        $this->validate();
    }
    
    private function validate(): void
    {
        if (empty($this->firstName)) {
            throw new InvalidArgumentException('First name is required');
        }
        
        if (empty($this->lastName)) {
            throw new InvalidArgumentException('Last name is required');
        }
        
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format');
        }
    }
    
    public function toArray(): array
    {
        return [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'phone' => $this->phone,
            'department' => $this->department,
        ];
    }
}
```

### **Ejemplo 2: Handler completo**
```php
// app/Modules/Employees/Application/Handlers/CreateEmployeeHandler.php
class CreateEmployeeHandler
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository,
        private EmployeeService $employeeService,
        private EventDispatcher $eventDispatcher
    ) {}
    
    public function handle(CreateEmployeeCommand $command): EmployeeDTO
    {
        // 1. Crear entidad de dominio
        $employee = new Employee(
            id: Str::uuid(),
            firstName: $command->firstName,
            lastName: $command->lastName,
            email: new Email($command->email),
            phone: $command->phone ? new Phone($command->phone) : null,
            department: $command->department
        );
        
        // 2. Aplicar lógica de negocio
        $this->employeeService->validateEmployee($employee);
        $this->employeeService->assignDefaultRole($employee);
        
        // 3. Persistir en base de datos
        $this->employeeRepository->save($employee);
        
        // 4. Disparar eventos
        $this->eventDispatcher->dispatch(new EmployeeCreatedEvent($employee));
        
        // 5. Crear DTO de respuesta
        return new EmployeeDTO(
            id: $employee->id(),
            firstName: $employee->firstName(),
            lastName: $employee->lastName(),
            email: $employee->email()->value(),
            phone: $employee->phone()?->value(),
            department: $employee->department(),
            createdAt: $employee->createdAt()->format('Y-m-d H:i:s')
        );
    }
}
```

### **Ejemplo 3: Query y Handler de consulta**
```php
// app/Modules/Employees/Application/Queries/ListEmployeesQuery.php
final class ListEmployeesQuery
{
    public function __construct(
        public readonly ?string $tenantId = null,
        public readonly ?string $status = null,
        public readonly ?string $department = null,
        public readonly ?string $search = null,
        public readonly int $page = 1,
        public readonly int $perPage = 15,
        public readonly ?string $sortBy = null,
        public readonly ?string $sortDirection = 'asc'
    ) {
        $this->validate();
    }
    
    private function validate(): void
    {
        if ($this->page < 1) {
            throw new InvalidArgumentException('Page must be greater than 0');
        }
        
        if ($this->perPage < 1 || $this->perPage > 100) {
            throw new InvalidArgumentException('Per page must be between 1 and 100');
        }
    }
}

// app/Modules/Employees/Application/Handlers/ListEmployeesHandler.php
class ListEmployeesHandler
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository
    ) {}
    
    public function handle(ListEmployeesQuery $query): EmployeeListDTO
    {
        // 1. Construir filtros
        $filters = [
            'tenant_id' => $query->tenantId,
            'status' => $query->status,
            'department' => $query->department,
            'search' => $query->search,
        ];
        
        // 2. Buscar empleados
        $result = $this->employeeRepository->paginate(
            $filters,
            $query->page,
            $query->perPage
        );
        
        // 3. Convertir a DTOs
        $employeeDTOs = array_map(
            fn($employee) => $this->convertToDTO($employee),
            $result['data']
        );
        
        // 4. Crear DTO de lista
        return new EmployeeListDTO(
            employees: $employeeDTOs,
            total: $result['total'],
            page: $query->page,
            perPage: $query->perPage,
            totalPages: ceil($result['total'] / $query->perPage)
        );
    }
    
    private function convertToDTO(Employee $employee): EmployeeDTO
    {
        return new EmployeeDTO(
            id: $employee->id(),
            firstName: $employee->firstName(),
            lastName: $employee->lastName(),
            email: $employee->email()->value(),
            phone: $employee->phone()?->value(),
            department: $employee->department(),
            status: $employee->status()->value(),
            createdAt: $employee->createdAt()->format('Y-m-d H:i:s')
        );
    }
}
```

---

## 🚀 Ventajas de esta arquitectura

### **1. Separación de responsabilidades:**
- **Controllers** solo manejan HTTP
- **Application** coordina operaciones
- **Domain** contiene lógica de negocio
- **Infrastructure** maneja persistencia

### **2. Testabilidad:**
```php
// Fácil de probar cada capa por separado
class CreateEmployeeHandlerTest extends TestCase
{
    public function test_creates_employee_successfully(): void
    {
        $command = new CreateEmployeeCommand(/* ... */);
        $handler = new CreateEmployeeHandler(/* ... */);
        
        $dto = $handler->handle($command);
        
        $this->assertInstanceOf(EmployeeDTO::class, $dto);
    }
}
```

### **3. Mantenibilidad:**
- **Cambios aislados** en cada capa
- **Fácil modificación** de lógica
- **Reutilización** de componentes
- **Escalabilidad** del código

### **4. Flexibilidad:**
- **Intercambio** de implementaciones
- **Múltiples interfaces** de usuario
- **Diferentes fuentes** de datos
- **Evolución** independiente

---

## 🎯 Resumen

### **🎯 Application es:**
- **Capa de coordinación** entre Controllers y Domain
- **Implementación** de casos de uso específicos
- **Orquestación** de operaciones de la aplicación
- **Manejo** del flujo de datos entre capas

### **🎯 Para qué sirve:**
- Coordinar operaciones de la aplicación
- Implementar casos de uso específicos
- Manejar el flujo de datos entre capas
- Separar lógica de aplicación de lógica de negocio

### **📋 Elementos principales:**
- **Commands**: Acciones que modifican datos
- **Queries**: Consultas de lectura
- **Handlers**: Procesan Commands y Queries
- **DTOs**: Transportan datos entre capas
- **Services**: Lógica de aplicación compleja

### **🔄 En el ciclo de vida:**
- Recibe peticiones de Controllers
- Procesa lógica de aplicación
- Coordina con Domain
- Maneja persistencia de datos
- Retorna respuestas estructuradas

### **✅ Mejores prácticas:**
- Separación clara de responsabilidades
- Commands y Queries separados
- Handlers específicos
- DTOs inmutables
- Validación en Commands

**La carpeta Application es esencial para mantener una arquitectura limpia y mantenible, separando la lógica de aplicación de la lógica de negocio.**
