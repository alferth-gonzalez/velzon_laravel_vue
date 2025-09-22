# Documentación: Entities (Entidades) - Guía Completa

## 📋 Índice
1. [¿Qué son las Entities?](#qué-son-las-entities)
2. [Características principales](#características-principales)
3. [Estructura de una Entity](#estructura-de-una-entity)
4. [Casos de uso típicos](#casos-de-uso-típicos)
5. [Casos de uso específicos del negocio](#casos-de-uso-específicos-del-negocio)
6. [Flujo completo de un caso real](#flujo-completo-de-un-caso-real)
7. [Mejores prácticas](#mejores-prácticas)
8. [Testing de Entities](#testing-de-entities)
9. [Diferencias con otros elementos](#diferencias-con-otros-elementos)
10. [Ejemplos prácticos](#ejemplos-prácticos)
11. [Resumen](#resumen)

---

## 🎯 ¿Qué son las Entities?

### **Definición:**
Las **Entities** son objetos que representan los **conceptos principales** de tu negocio. Son como las "personas" o "cosas" más importantes de tu dominio.

### **Características principales:**
- **Tienen identidad única** (ID)
- **Pueden cambiar** a lo largo del tiempo
- **Contienen lógica de negocio**
- **Son mutables** (pueden modificarse)
- **Se identifican** por su ID, no por sus atributos

### **Analogía de la Vida Real:**
Las **Entities** son como **"personas"** en la vida real:

- **Juan Pérez** es la misma persona aunque cambie de trabajo, dirección, etc.
- **Su identidad** (nombre + documento) lo hace único
- **Puede cambiar** sus atributos (edad, dirección, trabajo)
- **Sigue siendo** la misma persona

---

## 🧩 Características principales

### **1. Identidad única:**
```php
class Employee
{
    private string $id; // Identidad única
    
    public function __construct(string $id, /* ... */)
    {
        $this->id = $id;
    }
    
    public function id(): string
    {
        return $this->id; // Siempre retorna el mismo ID
    }
}
```

**¿Por qué es importante?**
- **Distinguir** entre diferentes instancias
- **Identificar** la misma entidad en diferentes momentos
- **Comparar** entidades por identidad, no por atributos

### **2. Mutabilidad:**
```php
class Employee
{
    private string $firstName;
    private string $lastName;
    
    public function changeName(string $firstName, string $lastName): void
    {
        // Puede cambiar sus atributos
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }
}
```

**¿Por qué es importante?**
- **Reflejar** cambios en el mundo real
- **Mantener** la consistencia del estado
- **Permitir** evolución de la entidad

### **3. Lógica de negocio:**
```php
class Employee
{
    public function promote(Position $newPosition): void
    {
        // Lógica de negocio específica
        if (!$this->isEligibleForPromotion()) {
            throw new EmployeeNotEligibleForPromotionException();
        }
        
        $this->position = $newPosition;
        $this->promotionDate = new DateTimeImmutable();
    }
    
    private function isEligibleForPromotion(): bool
    {
        return $this->yearsInCurrentPosition() >= 2 && 
               $this->performanceRating() >= 4.0;
    }
}
```

**¿Por qué es importante?**
- **Encapsular** reglas de negocio
- **Mantener** la consistencia
- **Facilitar** el testing

---

## 🏗️ Estructura de una Entity

### **Ejemplo básico:**
```php
final class Employee
{
    public function __construct(
        private string $id,           // Identidad única
        private string $firstName,    // Atributos
        private string $lastName,     // Atributos
        private Email $email,         // Value Objects
        private EmployeeStatus $status // Value Objects
    ) {}
    
    // Métodos de negocio
    public function promote(Position $newPosition): void
    {
        // Lógica de negocio
    }
    
    // Getters
    public function id(): string { return $this->id; }
    public function firstName(): string { return $this->firstName; }
}
```

### **Elementos clave:**
1. **Identidad única** (ID)
2. **Atributos** (datos)
3. **Value Objects** (conceptos)
4. **Métodos de negocio** (comportamiento)
5. **Getters** (acceso a datos)

---

## 📋 Casos de uso típicos

### **1. Creación de empleados:**
```php
class Employee
{
    public function __construct(
        private string $id,
        private string $firstName,
        private string $lastName,
        private Email $email,
        private EmployeeStatus $status = EmployeeStatus::Active
    ) {
        $this->validate();
    }
    
    private function validate(): void
    {
        if (empty($this->firstName) || empty($this->lastName)) {
            throw new InvalidEmployeeDataException('Name cannot be empty');
        }
    }
}
```

### **2. Modificación de datos:**
```php
class Employee
{
    public function updatePersonalInfo(
        string $firstName,
        string $lastName,
        ?Email $email = null
    ): void {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        
        if ($email) {
            $this->email = $email;
        }
        
        $this->updatedAt = new DateTimeImmutable();
    }
}
```

### **3. Operaciones de negocio:**
```php
class Employee
{
    public function promote(Position $newPosition): void
    {
        if (!$this->isEligibleForPromotion()) {
            throw new EmployeeNotEligibleForPromotionException();
        }
        
        $this->position = $newPosition;
        $this->promotionDate = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }
    
    public function inactivate(?string $updatedBy = null): void
    {
        $this->status = EmployeeStatus::Inactive;
        $this->updatedBy = $updatedBy;
        $this->updatedAt = new DateTimeImmutable();
    }
}
```

### **4. Validaciones de negocio:**
```php
class Employee
{
    public function canBeDeleted(): bool
    {
        return $this->status === EmployeeStatus::Inactive &&
               !$this->hasActiveProjects() &&
               !$this->hasPendingPayments();
    }
    
    private function hasActiveProjects(): bool
    {
        // Lógica para verificar proyectos activos
        return false;
    }
}
```

---

## 🎯 Casos de uso específicos del negocio

### **1. Crear un empleado nuevo**
```php
// Caso: Usuario quiere registrar un nuevo empleado
class CreateEmployeeHandler
{
    public function handle(CreateEmployeeCommand $command): EmployeeDTO
    {
        // ✅ CREAR ENTITY - Representa el empleado real
        $employee = new Employee(
            id: Str::uuid(),
            tenantId: $command->tenantId,
            firstName: $command->firstName,
            lastName: $command->lastName,
            document: new DocumentId($command->documentType, $command->documentNumber),
            email: new Email($command->email),
            phone: $command->phone ? new Phone($command->phone) : null,
            hireDate: new DateTimeImmutable($command->hireDate)
        );
        
        // ✅ APLICAR LÓGICA DE NEGOCIO DE LA ENTITY
        $this->employeeService->validateEmployee($employee);
        $this->employeeService->assignDefaultRole($employee);
        
        // ✅ PERSISTIR LA ENTITY
        $this->employeeRepository->save($employee);
        
        // ✅ CREAR DTO PARA RESPUESTA
        return new EmployeeDTO(
            id: $employee->id(),
            firstName: $employee->firstName(),
            lastName: $employee->lastName(),
            email: $employee->email()->value()
        );
    }
}
```

### **2. Promover un empleado**
```php
// Caso: Jefe quiere promover a un empleado
class PromoteEmployeeHandler
{
    public function handle(PromoteEmployeeCommand $command): EmployeeDTO
    {
        // ✅ OBTENER ENTITY EXISTENTE
        $employee = $this->employeeRepository->findById($command->employeeId);
        
        if (!$employee) {
            throw new EmployeeNotFoundException();
        }
        
        $newPosition = $this->positionRepository->findById($command->newPositionId);
        
        // ✅ USAR LÓGICA DE NEGOCIO DE LA ENTITY
        $employee->promote($newPosition);
        
        // ✅ PERSISTIR CAMBIOS
        $this->employeeRepository->save($employee);
        
        // ✅ DISPARAR EVENTO
        $this->eventDispatcher->dispatch(new EmployeePromotedEvent($employee));
        
        return $this->convertToDTO($employee);
    }
}
```

### **3. Inactivar un empleado**
```php
// Caso: HR quiere inactivar un empleado
class InactivateEmployeeHandler
{
    public function handle(InactivateEmployeeCommand $command): EmployeeDTO
    {
        // ✅ OBTENER ENTITY EXISTENTE
        $employee = $this->employeeRepository->findById($command->employeeId);
        
        if (!$employee) {
            throw new EmployeeNotFoundException();
        }
        
        // ✅ USAR LÓGICA DE NEGOCIO DE LA ENTITY
        $employee->inactivate($command->updatedBy);
        
        // ✅ PERSISTIR CAMBIOS
        $this->employeeRepository->save($employee);
        
        return $this->convertToDTO($employee);
    }
}
```

### **4. Actualizar datos personales**
```php
// Caso: Empleado quiere actualizar sus datos
class UpdateEmployeeHandler
{
    public function handle(UpdateEmployeeCommand $command): EmployeeDTO
    {
        // ✅ OBTENER ENTITY EXISTENTE
        $employee = $this->employeeRepository->findById($command->employeeId);
        
        if (!$employee) {
            throw new EmployeeNotFoundException();
        }
        
        // ✅ USAR LÓGICA DE NEGOCIO DE LA ENTITY
        $employee->updatePersonalInfo(
            firstName: $command->firstName,
            lastName: $command->lastName,
            email: $command->email ? new Email($command->email) : null,
            phone: $command->phone ? new Phone($command->phone) : null
        );
        
        // ✅ PERSISTIR CAMBIOS
        $this->employeeRepository->save($employee);
        
        return $this->convertToDTO($employee);
    }
}
```

### **5. Validar si un empleado puede ser eliminado**
```php
// Caso: Sistema quiere verificar si se puede eliminar un empleado
class DeleteEmployeeHandler
{
    public function handle(DeleteEmployeeCommand $command): void
    {
        // ✅ OBTENER ENTITY EXISTENTE
        $employee = $this->employeeRepository->findById($command->employeeId);
        
        if (!$employee) {
            throw new EmployeeNotFoundException();
        }
        
        // ✅ USAR LÓGICA DE NEGOCIO DE LA ENTITY
        if (!$employee->canBeDeleted()) {
            throw new EmployeeCannotBeDeletedException(
                'Employee cannot be deleted due to active projects or pending payments'
            );
        }
        
        // ✅ ELIMINAR ENTITY
        $this->employeeRepository->delete($employee->id());
    }
}
```

### **6. Calcular salario de un empleado**
```php
// Caso: Sistema de nómina necesita calcular salario
class CalculateSalaryHandler
{
    public function handle(CalculateSalaryCommand $command): SalaryDTO
    {
        // ✅ OBTENER ENTITY EXISTENTE
        $employee = $this->employeeRepository->findById($command->employeeId);
        
        if (!$employee) {
            throw new EmployeeNotFoundException();
        }
        
        // ✅ USAR LÓGICA DE NEGOCIO DE LA ENTITY
        $salary = $this->salaryCalculationService->calculateSalary($employee);
        
        return new SalaryDTO(
            employeeId: $employee->id(),
            baseSalary: $salary->baseAmount(),
            bonus: $salary->bonusAmount(),
            deductions: $salary->deductionsAmount(),
            netSalary: $salary->netAmount()
        );
    }
}
```

---

## 🔄 Flujo completo de un caso real

### **Escenario: "Juan quiere actualizar su email"**

#### **1. Usuario hace petición:**
```http
PUT /api/employees/123
{
    "email": "juan.nuevo@email.com"
}
```

#### **2. Controller recibe petición:**
```php
class EmployeeController extends Controller
{
    public function update(Request $request, string $id)
    {
        // Command - Solo transporta datos
        $command = new UpdateEmployeeCommand(
            employeeId: $id,
            email: $request->input('email')
        );
        
        $handler = app(UpdateEmployeeHandler::class);
        $dto = $handler->handle($command);
        
        return response()->json(['data' => $dto->toArray()]);
    }
}
```

#### **3. Handler procesa Command:**
```php
class UpdateEmployeeHandler
{
    public function handle(UpdateEmployeeCommand $command): EmployeeDTO
    {
        // ✅ OBTENER ENTITY EXISTENTE
        $employee = $this->employeeRepository->findById($command->employeeId);
        
        if (!$employee) {
            throw new EmployeeNotFoundException();
        }
        
        // ✅ USAR LÓGICA DE NEGOCIO DE LA ENTITY
        $employee->updateEmail(new Email($command->email));
        
        // ✅ PERSISTIR CAMBIOS
        $this->employeeRepository->save($employee);
        
        // ✅ CREAR DTO PARA RESPUESTA
        return new EmployeeDTO(
            id: $employee->id(),
            firstName: $employee->firstName(),
            lastName: $employee->lastName(),
            email: $employee->email()->value()
        );
    }
}
```

#### **4. Entity aplica lógica de negocio:**
```php
class Employee
{
    public function updateEmail(Email $newEmail): void
    {
        // ✅ LÓGICA DE NEGOCIO EN LA ENTITY
        if ($this->email && $this->email->equals($newEmail)) {
            throw new SameEmailException('Email is the same as current');
        }
        
        $this->email = $newEmail;
        $this->updatedAt = new DateTimeImmutable();
        
        // ✅ DISPARAR EVENTO
        $this->addDomainEvent(new EmployeeEmailUpdatedEvent($this));
    }
}
```

#### **5. Respuesta al usuario:**
```json
{
    "data": {
        "id": "123",
        "first_name": "Juan",
        "last_name": "Pérez",
        "email": "juan.nuevo@email.com"
    }
}
```

---

## ✅ Mejores prácticas

### **1. Inmutabilidad de la identidad:**
```php
// ✅ BIEN - ID inmutable
class Employee
{
    private readonly string $id;
    
    public function __construct(string $id, /* ... */)
    {
        $this->id = $id; // Solo se asigna una vez
    }
}

// ❌ MAL - ID mutable
class Employee
{
    private string $id;
    
    public function setId(string $id): void
    {
        $this->id = $id; // ❌ Puede cambiar
    }
}
```

### **2. Encapsulación de datos:**
```php
// ✅ BIEN - Datos protegidos
class Employee
{
    private string $firstName;
    private string $lastName;
    
    public function firstName(): string
    {
        return $this->firstName; // Solo lectura
    }
    
    public function changeName(string $firstName, string $lastName): void
    {
        // Validación antes de cambiar
        if (empty($firstName) || empty($lastName)) {
            throw new InvalidEmployeeDataException('Name cannot be empty');
        }
        
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }
}

// ❌ MAL - Datos expuestos
class Employee
{
    public string $firstName; // ❌ Acceso directo
    public string $lastName;  // ❌ Acceso directo
}
```

### **3. Lógica de negocio en la entidad:**
```php
// ✅ BIEN - Lógica en la entidad
class Employee
{
    public function promote(Position $newPosition): void
    {
        if (!$this->isEligibleForPromotion()) {
            throw new EmployeeNotEligibleForPromotionException();
        }
        
        $this->position = $newPosition;
    }
}

// ❌ MAL - Lógica en el servicio
class EmployeeService
{
    public function promote(Employee $employee, Position $newPosition): void
    {
        // ❌ Lógica que debería estar en la entidad
        if ($employee->yearsInCurrentPosition() >= 2) {
            $employee->setPosition($newPosition);
        }
    }
}
```

### **4. Validación en el constructor:**
```php
// ✅ BIEN - Validación en constructor
class Employee
{
    public function __construct(
        private string $id,
        private string $firstName,
        private string $lastName,
        private Email $email
    ) {
        $this->validate();
    }
    
    private function validate(): void
    {
        if (empty($this->firstName) || empty($this->lastName)) {
            throw new InvalidEmployeeDataException('Name cannot be empty');
        }
    }
}
```

### **5. Métodos expresivos:**
```php
// ✅ BIEN - Nombres expresivos
class Employee
{
    public function isEligibleForPromotion(): bool
    {
        return $this->yearsInCurrentPosition() >= 2 && 
               $this->performanceRating() >= 4.0;
    }
    
    public function canBeDeleted(): bool
    {
        return $this->status === EmployeeStatus::Inactive &&
               !$this->hasActiveProjects();
    }
}

// ❌ MAL - Nombres genéricos
class Employee
{
    public function check(): bool
    {
        return $this->years >= 2 && $this->rating >= 4.0;
    }
}
```

---

## 🧪 Testing de Entities

### **Test básico:**
```php
// tests/Unit/Domain/Entities/EmployeeTest.php
class EmployeeTest extends TestCase
{
    public function test_creates_employee_with_valid_data(): void
    {
        $employee = new Employee(
            id: '123',
            tenantId: 'tenant-123',
            firstName: 'Juan',
            lastName: 'Pérez',
            document: new DocumentId('CC', '12345678'),
            email: new Email('juan@email.com')
        );
        
        $this->assertEquals('123', $employee->id());
        $this->assertEquals('Juan', $employee->firstName());
        $this->assertEquals('Pérez', $employee->lastName());
    }
    
    public function test_throws_exception_with_empty_name(): void
    {
        $this->expectException(InvalidEmployeeDataException::class);
        $this->expectExceptionMessage('Name cannot be empty');
        
        new Employee(
            id: '123',
            tenantId: 'tenant-123',
            firstName: '',
            lastName: 'Pérez',
            document: new DocumentId('CC', '12345678')
        );
    }
    
    public function test_employee_can_be_promoted_when_eligible(): void
    {
        $employee = new Employee(/* ... */);
        $newPosition = new Position(/* ... */);
        
        $employee->promote($newPosition);
        
        $this->assertEquals($newPosition, $employee->position());
    }
    
    public function test_employee_cannot_be_promoted_when_not_eligible(): void
    {
        $employee = new Employee(/* ... */);
        $newPosition = new Position(/* ... */);
        
        $this->expectException(EmployeeNotEligibleForPromotionException::class);
        
        $employee->promote($newPosition);
    }
    
    public function test_employee_can_be_inactivated(): void
    {
        $employee = new Employee(/* ... */);
        
        $employee->inactivate('admin-123');
        
        $this->assertEquals(EmployeeStatus::Inactive, $employee->status());
        $this->assertEquals('admin-123', $employee->updatedBy());
    }
    
    public function test_employee_can_be_deleted_when_eligible(): void
    {
        $employee = new Employee(/* ... */);
        $employee->inactivate();
        
        $this->assertTrue($employee->canBeDeleted());
    }
    
    public function test_employee_cannot_be_deleted_when_has_active_projects(): void
    {
        $employee = new Employee(/* ... */);
        $employee->inactivate();
        
        // Simular que tiene proyectos activos
        $this->mockHasActiveProjects($employee, true);
        
        $this->assertFalse($employee->canBeDeleted());
    }
}
```

---

## 📊 Diferencias con otros elementos

### **Entities vs Commands vs DTOs:**

| Aspecto | **Entities** | **Commands** | **DTOs** |
|---------|--------------|--------------|----------|
| **Propósito** | Representar objetos del negocio | Solicitar acciones | Transportar datos |
| **Contiene lógica** | ✅ Sí (lógica de negocio) | ❌ No | ❌ No |
| **Es mutable** | ✅ Sí (puede cambiar) | ❌ No (inmutable) | ❌ No (inmutable) |
| **Tiene identidad** | ✅ Sí (ID único) | ❌ No | ❌ No |
| **Vive en el tiempo** | ✅ Sí (persistente) | ❌ No (temporal) | ❌ No (temporal) |
| **Ejemplo** | `Employee` | `CreateEmployeeCommand` | `EmployeeDTO` |

### **Entities vs Value Objects:**

| Aspecto | **Entities** | **Value Objects** |
|---------|--------------|-------------------|
| **Identidad** | ✅ Sí (ID único) | ❌ No |
| **Mutabilidad** | ✅ Sí (puede cambiar) | ❌ No (inmutable) |
| **Comparación** | Por identidad | Por valor |
| **Ejemplo** | `Employee` | `Email`, `Phone` |

---

## 💻 Ejemplos prácticos

### **Ejemplo 1: Entity Employee completa**
```php
// app/Modules/Employees/Domain/Entities/Employee.php
final class Employee
{
    public function __construct(
        private readonly string $id,
        private string $tenantId,
        private string $firstName,
        private string $lastName,
        private DocumentId $document,
        private ?Email $email = null,
        private ?Phone $phone = null,
        private ?DateTimeImmutable $hireDate = null,
        private EmployeeStatus $status = EmployeeStatus::Active,
        private ?string $createdBy = null,
        private ?string $updatedBy = null,
        private DateTimeImmutable $createdAt = new DateTimeImmutable(),
        private DateTimeImmutable $updatedAt = new DateTimeImmutable(),
        private ?DateTimeImmutable $deletedAt = null
    ) {
        $this->validate();
    }
    
    // Métodos de negocio
    public function promote(Position $newPosition): void
    {
        if (!$this->isEligibleForPromotion()) {
            throw new EmployeeNotEligibleForPromotionException();
        }
        
        $this->position = $newPosition;
        $this->promotionDate = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }
    
    public function inactivate(?string $updatedBy = null): void
    {
        $this->status = EmployeeStatus::Inactive;
        $this->updatedBy = $updatedBy;
        $this->updatedAt = new DateTimeImmutable();
    }
    
    public function updatePersonalInfo(
        string $firstName,
        string $lastName,
        ?Email $email = null,
        ?Phone $phone = null
    ): void {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        
        if ($email) {
            $this->email = $email;
        }
        
        if ($phone) {
            $this->phone = $phone;
        }
        
        $this->updatedAt = new DateTimeImmutable();
    }
    
    public function canBeDeleted(): bool
    {
        return $this->status === EmployeeStatus::Inactive &&
               !$this->hasActiveProjects() &&
               !$this->hasPendingPayments();
    }
    
    // Métodos privados de validación
    private function validate(): void
    {
        if (empty($this->firstName) || empty($this->lastName)) {
            throw new InvalidEmployeeDataException('Name cannot be empty');
        }
        
        if (empty($this->tenantId)) {
            throw new InvalidEmployeeDataException('Tenant ID is required');
        }
    }
    
    private function isEligibleForPromotion(): bool
    {
        return $this->yearsInCurrentPosition() >= 2 && 
               $this->performanceRating() >= 4.0;
    }
    
    private function hasActiveProjects(): bool
    {
        // Lógica para verificar proyectos activos
        return false;
    }
    
    private function hasPendingPayments(): bool
    {
        // Lógica para verificar pagos pendientes
        return false;
    }
    
    // Getters
    public function id(): string { return $this->id; }
    public function tenantId(): string { return $this->tenantId; }
    public function firstName(): string { return $this->firstName; }
    public function lastName(): string { return $this->lastName; }
    public function document(): DocumentId { return $this->document; }
    public function email(): ?Email { return $this->email; }
    public function phone(): ?Phone { return $this->phone; }
    public function hireDate(): ?DateTimeImmutable { return $this->hireDate; }
    public function status(): EmployeeStatus { return $this->status; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function updatedAt(): DateTimeImmutable { return $this->updatedAt; }
    public function deletedAt(): ?DateTimeImmutable { return $this->deletedAt; }
}
```

### **Ejemplo 2: Entity Department**
```php
// app/Modules/Employees/Domain/Entities/Department.php
final class Department
{
    public function __construct(
        private readonly string $id,
        private string $name,
        private string $description,
        private ?string $managerId = null,
        private DepartmentStatus $status = DepartmentStatus::Active,
        private DateTimeImmutable $createdAt = new DateTimeImmutable(),
        private DateTimeImmutable $updatedAt = new DateTimeImmutable()
    ) {
        $this->validate();
    }
    
    public function assignManager(string $managerId): void
    {
        if ($this->managerId === $managerId) {
            throw new SameManagerException('Employee is already the manager');
        }
        
        $this->managerId = $managerId;
        $this->updatedAt = new DateTimeImmutable();
    }
    
    public function removeManager(): void
    {
        $this->managerId = null;
        $this->updatedAt = new DateTimeImmutable();
    }
    
    public function updateInfo(string $name, string $description): void
    {
        $this->name = $name;
        $this->description = $description;
        $this->updatedAt = new DateTimeImmutable();
    }
    
    public function canBeDeleted(): bool
    {
        return $this->status === DepartmentStatus::Inactive &&
               !$this->hasActiveEmployees();
    }
    
    private function validate(): void
    {
        if (empty($this->name)) {
            throw new InvalidDepartmentDataException('Name cannot be empty');
        }
    }
    
    private function hasActiveEmployees(): bool
    {
        // Lógica para verificar empleados activos
        return false;
    }
    
    // Getters
    public function id(): string { return $this->id; }
    public function name(): string { return $this->name; }
    public function description(): string { return $this->description; }
    public function managerId(): ?string { return $this->managerId; }
    public function status(): DepartmentStatus { return $this->status; }
}
```

---

## 🎯 ¿Cuándo usar Entities?

### **✅ SÍ usar Entities cuando:**
- **Necesitas** representar un objeto real del negocio
- **Requieres** lógica de negocio compleja
- **Quieres** mantener la consistencia del estado
- **Necesitas** validaciones de negocio
- **Quieres** encapsular comportamiento

### **❌ NO usar Entities cuando:**
- **Solo necesitas** transportar datos (usa DTOs)
- **Solo necesitas** solicitar acciones (usa Commands)
- **No hay lógica** de negocio (usa Value Objects)
- **Es temporal** (usa DTOs o Commands)

---

## 🎯 Resumen

### **🎯 Entities son:**
- **Objetos principales** del negocio
- **Con identidad única** (ID)
- **Mutables** (pueden cambiar)
- **Con lógica de negocio**
- **Encapsuladas** (datos protegidos)

### **🎯 Para qué sirven:**
- Representar conceptos principales del negocio
- Encapsular lógica de negocio
- Mantener la consistencia del estado
- Aplicar reglas de negocio
- Facilitar el testing y mantenimiento

### **📋 Características clave:**
- Identidad única e inmutable
- Atributos mutables
- Lógica de negocio encapsulada
- Validación en constructor
- Getters para acceso a datos

### **✅ Mejores prácticas:**
- ID inmutable
- Datos encapsulados
- Lógica de negocio en la entidad
- Validación en constructor
- Métodos expresivos

### ** Casos de uso:**
- Crear empleados
- Promover empleados
- Inactivar empleados
- Actualizar datos
- Validar reglas de negocio
- Calcular salarios
- Verificar elegibilidad

**¡Las Entities son el corazón de tu dominio, representando los conceptos más importantes de tu negocio con su lógica correspondiente!** 🚀
