# Documentación: Carpeta Domain - Guía Completa

## 📋 Índice
1. [¿Qué es la carpeta Domain?](#qué-es-la-carpeta-domain)
2. [Propósito y Función](#propósito-y-función)
3. [Estructura de la carpeta Domain](#estructura-de-la-carpeta-domain)
4. [Elementos principales](#elementos-principales)
5. [Relación con otras capas](#relación-con-otras-capas)
6. [Principios fundamentales](#principios-fundamentales)
7. [Ventajas de esta arquitectura](#ventajas-de-esta-arquitectura)
8. [Casos de uso típicos](#casos-de-uso-típicos)
9. [Mejores prácticas](#mejores-prácticas)
10. [Ejemplos prácticos](#ejemplos-prácticos)
11. [Resumen](#resumen)

---

## 🎯 ¿Qué es la carpeta Domain?

### **Definición:**
La carpeta **Domain** es el **corazón** de la arquitectura DDD (Domain-Driven Design). Es como el **"cerebro"** de tu aplicación que contiene todo el conocimiento y las reglas de negocio.

### **Características principales:**
- **Contiene** la lógica de negocio más importante
- **Es independiente** de frameworks y tecnologías
- **Representa** el conocimiento del dominio
- **Es reutilizable** y mantenible
- **No depende** de la interfaz de usuario
- **Es el núcleo** de tu aplicación

### **Analogía de la Vida Real:**
La carpeta **Domain** es como el **"manual de operaciones"** de una empresa:

- **Contiene** todas las reglas y procedimientos
- **Define** cómo funciona el negocio
- **Es independiente** de la tecnología usada
- **Puede ser consultado** por cualquier departamento
- **Es la fuente de verdad** del negocio

---

## 🎯 Propósito y Función

### **1. Centralizar la lógica de negocio:**
```php
// Toda la lógica de negocio está en Domain
class Employee
{
    public function promote(Position $newPosition): void
    {
        if (!$this->isEligibleForPromotion()) {
            throw new EmployeeNotEligibleForPromotionException();
        }
        
        $this->position = $newPosition;
        $this->promotionDate = new DateTimeImmutable();
    }
    
    private function isEligibleForPromotion(): bool
    {
        // Lógica de negocio específica
        return $this->yearsInCurrentPosition() >= 2 && 
               $this->performanceRating() >= 4.0;
    }
}
```

### **2. Mantener la consistencia del dominio:**
```php
// Las reglas se aplican consistentemente
class Email
{
    public function __construct(string $email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException('Invalid email format');
        }
        $this->email = $email;
    }
}
```

### **3. Facilitar el testing y mantenimiento:**
```php
// Fácil de probar independientemente
class EmployeeService
{
    public function calculateSalary(Employee $employee): Money
    {
        // Lógica de negocio que se puede probar fácilmente
        $baseSalary = $employee->position()->baseSalary();
        $bonus = $this->calculateBonus($employee);
        $deductions = $this->calculateDeductions($employee);
        
        return $baseSalary->add($bonus)->subtract($deductions);
    }
}
```

### **4. Permitir la evolución independiente:**
```php
// Domain puede evolucionar sin afectar otras capas
class Employee
{
    // Se pueden agregar nuevos métodos sin afectar Application o Infrastructure
    public function transferToDepartment(Department $newDepartment): void
    {
        // Nueva funcionalidad que no afecta otras capas
    }
}
```

---

## 🏗️ Estructura de la carpeta Domain

### **Estructura típica:**
```
app/Modules/Employees/Domain/
├── Entities/           # Entidades principales
│   ├── Employee.php
│   ├── Department.php
│   ├── Position.php
│   └── Salary.php
├── ValueObjects/       # Objetos de valor
│   ├── DocumentId.php
│   ├── Email.php
│   ├── Phone.php
│   ├── EmployeeStatus.php
│   └── Money.php
├── Services/           # Servicios de dominio
│   ├── EmployeeService.php
│   ├── SalaryCalculationService.php
│   ├── EmployeeValidationService.php
│   └── EmployeeNotificationService.php
├── Repositories/       # Interfaces de repositorios
│   ├── EmployeeRepositoryInterface.php
│   ├── DepartmentRepositoryInterface.php
│   └── PositionRepositoryInterface.php
├── Events/            # Eventos de dominio
│   ├── EmployeeCreatedEvent.php
│   ├── EmployeeUpdatedEvent.php
│   ├── EmployeeDeletedEvent.php
│   └── EmployeePromotedEvent.php
├── Exceptions/        # Excepciones específicas
│   ├── EmployeeNotFoundException.php
│   ├── InvalidEmployeeDataException.php
│   ├── EmployeeAlreadyExistsException.php
│   └── EmployeeNotEligibleForPromotionException.php
└── Specifications/    # Especificaciones de negocio
    ├── EmployeeEligibleForPromotionSpec.php
    ├── EmployeeCanBeDeletedSpec.php
    ├── EmployeeSalaryWithinRangeSpec.php
    └── EmployeeHasValidDocumentsSpec.php
```

### **Estructura en tu proyecto actual:**
```
app/Modules/Employees/Domain/
├── Entities/
│   └── Employee.php
├── ValueObjects/
│   ├── DocumentId.php
│   ├── Email.php
│   ├── Phone.php
│   └── EmployeeStatus.php
├── Services/
│   └── EmployeeService.php
└── Repositories/
    └── EmployeeRepositoryInterface.php
```

---

## 🧩 Elementos principales

### **1. Entities (Entidades):**
```php
// Representan objetos principales del negocio
class Employee
{
    public function __construct(
        private string $id,
        private string $firstName,
        private string $lastName,
        private Email $email,
        private EmployeeStatus $status
    ) {}
    
    public function promote(Position $newPosition): void
    {
        // Lógica de negocio específica
    }
}
```

**Características:**
- **Representan** objetos principales del negocio
- **Tienen identidad** única (ID)
- **Pueden cambiar** a lo largo del tiempo
- **Contienen** lógica de negocio
- **Ejemplo:** `Employee`, `Department`, `Position`

### **2. ValueObjects (Objetos de valor):**
```php
// Representan conceptos del negocio
class Email
{
    public function __construct(private string $email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException('Invalid email format');
        }
    }
    
    public function value(): string
    {
        return $this->email;
    }
}
```

**Características:**
- **Representan** conceptos del negocio
- **No tienen identidad** única
- **Son inmutables** (no cambian)
- **Se comparan** por valor, no por referencia
- **Ejemplo:** `Email`, `Phone`, `DocumentId`

### **3. Services (Servicios de dominio):**
```php
// Contienen lógica de negocio compleja
class EmployeeService
{
    public function calculateSalary(Employee $employee): Money
    {
        // Lógica de negocio que no pertenece a una entidad específica
        $baseSalary = $employee->position()->baseSalary();
        $bonus = $this->calculateBonus($employee);
        $deductions = $this->calculateDeductions($employee);
        
        return $baseSalary->add($bonus)->subtract($deductions);
    }
}
```

**Características:**
- **Contienen** lógica de negocio compleja
- **No pertenecen** a una entidad específica
- **Coordinan** entre múltiples entidades
- **Implementan** reglas de negocio
- **Ejemplo:** `EmployeeService`, `SalaryCalculationService`

### **4. Repositories (Interfaces):**
```php
// Definen cómo acceder a los datos
interface EmployeeRepositoryInterface
{
    public function findById(string $id): ?Employee;
    public function findByEmail(Email $email): ?Employee;
    public function save(Employee $employee): void;
    public function delete(string $id): void;
}
```

**Características:**
- **Definen** cómo acceder a los datos
- **Son contratos** para la persistencia
- **No implementan** la lógica de acceso
- **Permiten** intercambiar implementaciones
- **Ejemplo:** `EmployeeRepositoryInterface`

### **5. Events (Eventos):**
```php
// Representan algo que ocurrió en el dominio
class EmployeeCreatedEvent
{
    public function __construct(
        public readonly Employee $employee,
        public readonly DateTimeImmutable $occurredAt
    ) {}
}
```

**Características:**
- **Representan** algo que ocurrió en el dominio
- **Son inmutables** (no cambian)
- **Se disparan** cuando algo importante pasa
- **Permiten** desacoplar componentes
- **Ejemplo:** `EmployeeCreatedEvent`, `EmployeeUpdatedEvent`

### **6. Exceptions (Excepciones):**
```php
// Representan errores específicos del dominio
class EmployeeNotFoundException extends Exception
{
    public function __construct(string $employeeId)
    {
        parent::__construct("Employee with ID {$employeeId} not found");
    }
}
```

**Características:**
- **Representan** errores específicos del dominio
- **Son descriptivas** y claras
- **Permiten** manejo específico de errores
- **Facilitan** el debugging
- **Ejemplo:** `EmployeeNotFoundException`

### **7. Specifications (Especificaciones):**
```php
// Encapsulan reglas de negocio complejas
class EmployeeEligibleForPromotionSpec
{
    public function isSatisfiedBy(Employee $employee): bool
    {
        return $employee->yearsInCurrentPosition() >= 2 && 
               $employee->performanceRating() >= 4.0 &&
               $employee->hasNoDisciplinaryActions();
    }
}
```

**Características:**
- **Encapsulan** reglas de negocio complejas
- **Son reutilizables** y combinables
- **Permiten** validaciones complejas
- **Facilitan** el testing
- **Ejemplo:** `EmployeeEligibleForPromotionSpec`

---

## 🔗 Relación con otras capas

### **1. Con Application:**
```php
// Application usa Domain
class CreateEmployeeHandler
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository, // Interface del Domain
        private EmployeeService $employeeService                 // Service del Domain
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

### **2. Con Infrastructure:**
```php
// Infrastructure implementa interfaces del Domain
class EloquentEmployeeRepository implements EmployeeRepositoryInterface
{
    public function findById(string $id): ?Employee
    {
        // Implementación específica con Eloquent
        $model = EmployeeModel::find($id);
        return $model ? $this->toDomain($model) : null;
    }
}
```

### **3. Con Controllers:**
```php
// Controllers no acceden directamente al Domain
// Solo a través de Application
class EmployeeController extends Controller
{
    public function store(Request $request)
    {
        // No accede directamente a Domain
        // Solo a través de Application
        $handler = app(CreateEmployeeHandler::class);
        $dto = $handler->handle($command);
    }
}
```

---

## 🎯 Principios fundamentales

### **1. Independencia de la tecnología:**
```php
// Domain no depende de Laravel, Eloquent, etc.
class Employee
{
    // Usa solo PHP puro, no dependencias externas
    public function __construct(
        private string $id,
        private string $firstName,
        private string $lastName
    ) {}
}
```

**Beneficios:**
- **No depende** de Laravel, Eloquent, etc.
- **Puede funcionar** con cualquier framework
- **Es portable** entre proyectos
- **Facilita** el testing

### **2. Lógica de negocio centralizada:**
```php
// Toda la lógica está en Domain
class Employee
{
    public function promote(Position $newPosition): void
    {
        // Lógica de negocio centralizada
        if (!$this->isEligibleForPromotion()) {
            throw new EmployeeNotEligibleForPromotionException();
        }
        
        $this->position = $newPosition;
        $this->promotionDate = new DateTimeImmutable();
    }
}
```

**Beneficios:**
- **Toda la lógica** está en Domain
- **No se duplica** en otras capas
- **Es consistente** en toda la aplicación
- **Es fácil de mantener**

### **3. Expresividad del dominio:**
```php
// El código refleja el lenguaje del negocio
class Employee
{
    public function isEligibleForPromotion(): bool
    {
        // El método tiene un nombre que refleja el negocio
        return $this->yearsInCurrentPosition() >= 2 && 
               $this->performanceRating() >= 4.0;
    }
}
```

**Beneficios:**
- **El código** refleja el lenguaje del negocio
- **Es fácil de entender** para los usuarios
- **Facilita** la comunicación con stakeholders
- **Reduce** la brecha entre código y negocio

### **4. Encapsulación:**
```php
// Los datos están protegidos
class Employee
{
    private string $id;
    private string $firstName;
    private string $lastName;
    
    public function id(): string
    {
        return $this->id; // Solo lectura
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
```

**Beneficios:**
- **Los datos** están protegidos
- **Solo se accede** a través de métodos
- **Se valida** en el momento correcto
- **Se mantiene** la integridad

---

## 🚀 Ventajas de esta arquitectura

### **1. Mantenibilidad:**
```php
// Cambios aislados en cada elemento
class Employee
{
    // Se puede modificar la lógica de promoción sin afectar otras capas
    public function promote(Position $newPosition): void
    {
        // Nueva lógica de promoción
    }
}
```

**Beneficios:**
- **Cambios aislados** en cada elemento
- **Fácil modificación** de reglas de negocio
- **Reutilización** de componentes
- **Escalabilidad** del código

### **2. Testabilidad:**
```php
// Fácil de probar cada elemento por separado
class EmployeeTest extends TestCase
{
    public function test_employee_can_be_promoted_when_eligible(): void
    {
        $employee = new Employee(/* ... */);
        $newPosition = new Position(/* ... */);
        
        $employee->promote($newPosition);
        
        $this->assertEquals($newPosition, $employee->position());
    }
}
```

**Beneficios:**
- **Fácil de probar** cada elemento por separado
- **Tests unitarios** simples y rápidos
- **Tests de integración** claros
- **Cobertura** completa del código

### **3. Flexibilidad:**
```php
// Intercambio de implementaciones
interface EmployeeRepositoryInterface
{
    public function findById(string $id): ?Employee;
}

// Se puede cambiar de Eloquent a Query Builder sin afectar Domain
class EloquentEmployeeRepository implements EmployeeRepositoryInterface { /* ... */ }
class QueryBuilderEmployeeRepository implements EmployeeRepositoryInterface { /* ... */ }
```

**Beneficios:**
- **Intercambio** de implementaciones
- **Múltiples interfaces** de usuario
- **Diferentes fuentes** de datos
- **Evolución** independiente

### **4. Claridad:**
```php
// Código expresivo y claro
class Employee
{
    public function isEligibleForPromotion(): bool
    {
        // El método es claro y expresivo
        return $this->yearsInCurrentPosition() >= 2 && 
               $this->performanceRating() >= 4.0;
    }
}
```

**Beneficios:**
- **Código expresivo** y claro
- **Fácil de entender** para nuevos desarrolladores
- **Documentación** viva en el código
- **Comunicación** efectiva con stakeholders

---

## 📋 Casos de uso típicos

### **1. Validación de datos:**
```php
// En ValueObjects
class Email
{
    public function __construct(string $email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException('Invalid email format');
        }
        $this->email = $email;
    }
}
```

### **2. Lógica de negocio:**
```php
// En Entities
class Employee
{
    public function promote(Position $newPosition): void
    {
        if (!$this->isEligibleForPromotion()) {
            throw new EmployeeNotEligibleForPromotionException();
        }
        
        $this->position = $newPosition;
        $this->promotionDate = new DateTimeImmutable();
    }
}
```

### **3. Reglas complejas:**
```php
// En Services
class SalaryCalculationService
{
    public function calculateSalary(Employee $employee): Money
    {
        $baseSalary = $employee->position()->baseSalary();
        $bonus = $this->calculateBonus($employee);
        $deductions = $this->calculateDeductions($employee);
        
        return $baseSalary->add($bonus)->subtract($deductions);
    }
}
```

### **4. Especificaciones:**
```php
// En Specifications
class EmployeeEligibleForPromotionSpec
{
    public function isSatisfiedBy(Employee $employee): bool
    {
        return $employee->yearsInCurrentPosition() >= 2 && 
               $employee->performanceRating() >= 4.0 &&
               $employee->hasNoDisciplinaryActions();
    }
}
```

---

## ✅ Mejores prácticas

### **1. Independencia de la tecnología:**
```php
// ✅ BIEN - Solo PHP puro
class Employee
{
    public function __construct(
        private string $id,
        private string $firstName,
        private string $lastName
    ) {}
}

// ❌ MAL - Dependencia de Laravel
class Employee
{
    public function __construct(
        private string $id,
        private string $firstName,
        private string $lastName
    ) {
        // ❌ No usar helpers de Laravel en Domain
        $this->id = Str::uuid();
    }
}
```

### **2. Lógica de negocio centralizada:**
```php
// ✅ BIEN - Lógica en Domain
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

// ❌ MAL - Lógica en Controller
class EmployeeController
{
    public function promote(Request $request)
    {
        // ❌ No poner lógica de negocio en Controller
        if ($employee->yearsInCurrentPosition() >= 2) {
            $employee->promote($newPosition);
        }
    }
}
```

### **3. Expresividad del dominio:**
```php
// ✅ BIEN - Nombres expresivos
class Employee
{
    public function isEligibleForPromotion(): bool
    {
        return $this->yearsInCurrentPosition() >= 2 && 
               $this->performanceRating() >= 4.0;
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

### **4. Encapsulación adecuada:**
```php
// ✅ BIEN - Datos protegidos
class Employee
{
    private string $id;
    private string $firstName;
    
    public function id(): string
    {
        return $this->id;
    }
    
    public function changeName(string $firstName): void
    {
        if (empty($firstName)) {
            throw new InvalidEmployeeDataException('Name cannot be empty');
        }
        $this->firstName = $firstName;
    }
}

// ❌ MAL - Datos expuestos
class Employee
{
    public string $id;
    public string $firstName;
}
```

---

## 💻 Ejemplos prácticos

### **Ejemplo 1: Entity completa**
```php
// app/Modules/Employees/Domain/Entities/Employee.php
final class Employee
{
    public function __construct(
        private string $id,
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
    ) {}
    
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
    
    private function isEligibleForPromotion(): bool
    {
        return $this->yearsInCurrentPosition() >= 2 && 
               $this->performanceRating() >= 4.0;
    }
    
    // Getters
    public function id(): string { return $this->id; }
    public function firstName(): string { return $this->firstName; }
    public function lastName(): string { return $this->lastName; }
    public function email(): ?Email { return $this->email; }
    public function status(): EmployeeStatus { return $this->status; }
}
```

### **Ejemplo 2: ValueObject completo**
```php
// app/Modules/Employees/Domain/ValueObjects/Email.php
final class Email
{
    public function __construct(private string $email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException('Invalid email format: ' . $email);
        }
    }
    
    public function value(): string
    {
        return $this->email;
    }
    
    public function domain(): string
    {
        return substr($this->email, strpos($this->email, '@') + 1);
    }
    
    public function equals(Email $other): bool
    {
        return $this->email === $other->email;
    }
    
    public function __toString(): string
    {
        return $this->email;
    }
}
```

### **Ejemplo 3: Service completo**
```php
// app/Modules/Employees/Domain/Services/EmployeeService.php
class EmployeeService
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository
    ) {}
    
    public function validateEmployee(Employee $employee): void
    {
        if (empty($employee->firstName()) || empty($employee->lastName())) {
            throw new InvalidEmployeeDataException('Name cannot be empty');
        }
        
        if ($employee->email() && $this->emailExists($employee->email())) {
            throw new EmployeeAlreadyExistsException('Email already exists');
        }
    }
    
    public function calculateSalary(Employee $employee): Money
    {
        $baseSalary = $employee->position()->baseSalary();
        $bonus = $this->calculateBonus($employee);
        $deductions = $this->calculateDeductions($employee);
        
        return $baseSalary->add($bonus)->subtract($deductions);
    }
    
    private function emailExists(Email $email): bool
    {
        return $this->employeeRepository->findByEmail($email) !== null;
    }
    
    private function calculateBonus(Employee $employee): Money
    {
        // Lógica de cálculo de bonificación
        return new Money(0, 'USD');
    }
    
    private function calculateDeductions(Employee $employee): Money
    {
        // Lógica de cálculo de deducciones
        return new Money(0, 'USD');
    }
}
```

### **Ejemplo 4: Specification completa**
```php
// app/Modules/Employees/Domain/Specifications/EmployeeEligibleForPromotionSpec.php
final class EmployeeEligibleForPromotionSpec
{
    public function isSatisfiedBy(Employee $employee): bool
    {
        return $employee->yearsInCurrentPosition() >= 2 && 
               $employee->performanceRating() >= 4.0 &&
               $employee->hasNoDisciplinaryActions() &&
               $employee->hasCompletedRequiredTraining();
    }
    
    public function and(Specification $other): Specification
    {
        return new AndSpecification($this, $other);
    }
    
    public function or(Specification $other): Specification
    {
        return new OrSpecification($this, $other);
    }
    
    public function not(): Specification
    {
        return new NotSpecification($this);
    }
}
```

---

## 🎯 Resumen

### **🎯 Domain es:**
- **El corazón** de la arquitectura DDD
- **Contiene** toda la lógica de negocio
- **Es independiente** de la tecnología
- **Representa** el conocimiento del dominio

### **🎯 Para qué sirve:**
- Centralizar la lógica de negocio
- Mantener la consistencia del dominio
- Facilitar el testing y mantenimiento
- Permitir la evolución independiente

### **📋 Elementos principales:**
- **Entities**: Objetos principales del negocio
- **ValueObjects**: Conceptos inmutables
- **Services**: Lógica de negocio compleja
- **Repositories**: Interfaces de acceso a datos
- **Events**: Sucesos importantes
- **Exceptions**: Errores específicos
- **Specifications**: Reglas de negocio complejas

### **🔄 En el ciclo de vida:**
- Es usado por Application
- Es implementado por Infrastructure
- No es accedido directamente por Controllers
- Contiene toda la lógica de negocio

### **✅ Mejores prácticas:**
- Independencia de la tecnología
- Lógica de negocio centralizada
- Expresividad del dominio
- Encapsulación adecuada

### **🔗 Relación con otras capas:**
- **Application**: Usa Domain
- **Infrastructure**: Implementa Domain
- **Controllers**: No accede directamente a Domain

**La carpeta Domain es la base sólida de tu aplicación, conteniendo todo el conocimiento y las reglas de negocio de forma organizada y mantenible.**
