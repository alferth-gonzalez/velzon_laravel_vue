# Documentación: Services de Domain - Guía Completa

## 📋 Índice
1. [¿Qué son los Services de Domain?](#qué-son-los-services-de-domain)
2. [Características principales](#características-principales)
3. [Casos de uso típicos](#casos-de-uso-típicos)
4. [Casos de uso específicos del negocio](#casos-de-uso-específicos-del-negocio)
5. [Flujo completo de una petición HTTP](#flujo-completo-de-una-petición-http)
6. [Eventos de Dominio](#eventos-de-dominio)
7. [Mejores prácticas](#mejores-prácticas)
8. [Testing de Services](#testing-de-services)
9. [Relación con otras capas](#relación-con-otras-capas)
10. [Ejemplos prácticos](#ejemplos-prácticos)
11. [Resumen](#resumen)

---

## 🎯 ¿Qué son los Services de Domain?

### **Definición:**
Los **Services de Domain** son clases que contienen **lógica de negocio compleja** que no pertenece a una entidad específica. Son como "especialistas" que coordinan entre múltiples entidades para resolver problemas complejos del negocio.

### **Características principales:**
- **Contienen lógica de negocio** compleja
- **No pertenecen** a una entidad específica
- **Coordinan** entre múltiples entidades
- **Implementan reglas** de negocio
- **Son independientes** de la tecnología

### **Analogía de la Vida Real:**
Los **Services de Domain** son como **"especialistas"** en una empresa:

- **Contador** - Calcula salarios, maneja nómina
- **Recursos Humanos** - Coordina promociones, evaluaciones
- **Gerente de Proyectos** - Asigna tareas, coordina equipos
- **Auditor** - Verifica cumplimiento de reglas

---

## 🧩 Características principales

### **1. Lógica de negocio compleja:**
```php
class EmployeeService
{
    public function calculateSalary(Employee $employee): Money
    {
        // ✅ LÓGICA COMPLEJA que no pertenece a una entidad específica
        $baseSalary = $employee->position()->baseSalary();
        $bonus = $this->calculateBonus($employee);
        $deductions = $this->calculateDeductions($employee);
        $overtime = $this->calculateOvertime($employee);
        
        return $baseSalary
            ->add($bonus)
            ->add($overtime)
            ->subtract($deductions);
    }
}
```

**¿Por qué es importante?**
- **Encapsula** reglas de negocio complejas
- **Coordina** entre múltiples entidades
- **Mantiene** la consistencia
- **Facilita** el testing

### **2. No pertenece a una entidad específica:**
```php
class EmployeeService
{
    public function assignProject(Employee $employee, Project $project): void
    {
        // ✅ LÓGICA que involucra múltiples entidades
        if (!$this->isEmployeeAvailable($employee)) {
            throw new EmployeeNotAvailableException();
        }
        
        if (!$this->isProjectSuitable($employee, $project)) {
            throw new ProjectNotSuitableException();
        }
        
        $employee->assignToProject($project);
        $project->assignEmployee($employee);
        
        $this->notifyProjectAssignment($employee, $project);
    }
}
```

**¿Por qué es importante?**
- **Coordina** entre múltiples entidades
- **Mantiene** la consistencia del dominio
- **Evita** duplicación de lógica
- **Facilita** el mantenimiento

### **3. Implementan reglas de negocio:**
```php
class EmployeeService
{
    public function validateEmployee(Employee $employee): void
    {
        // ✅ REGLAS DE NEGOCIO específicas
        if ($employee->age() < 18) {
            throw new EmployeeTooYoungException();
        }
        
        if ($this->emailExists($employee->email())) {
            throw new EmployeeEmailAlreadyExistsException();
        }
        
        if (!$this->isDocumentValid($employee->document())) {
            throw new InvalidDocumentException();
        }
    }
}
```

**¿Por qué es importante?**
- **Centraliza** las reglas de negocio
- **Mantiene** la consistencia
- **Facilita** el testing
- **Permite** la evolución

---

## 📋 Casos de uso típicos

### **1. Cálculos complejos:**
```php
class EmployeeService
{
    public function calculateSalary(Employee $employee): Money
    {
        $baseSalary = $employee->position()->baseSalary();
        $bonus = $this->calculateBonus($employee);
        $deductions = $this->calculateDeductions($employee);
        
        return $baseSalary->add($bonus)->subtract($deductions);
    }
    
    private function calculateBonus(Employee $employee): Money
    {
        // Lógica compleja para calcular bonificación
        $performance = $employee->performanceRating();
        $years = $employee->yearsInCompany();
        
        if ($performance >= 4.5 && $years >= 5) {
            return $baseSalary->multiply(0.15);
        }
        
        if ($performance >= 4.0) {
            return $baseSalary->multiply(0.10);
        }
        
        return new Money(0, 'USD');
    }
}
```

### **2. Validaciones complejas:**
```php
class EmployeeService
{
    public function validateEmployee(Employee $employee): void
    {
        if (empty($employee->firstName()) || empty($employee->lastName())) {
            throw new InvalidEmployeeDataException('Name cannot be empty');
        }
        
        if ($employee->email() && $this->emailExists($employee->email())) {
            throw new EmployeeAlreadyExistsException('Email already exists');
        }
        
        if (!$this->isDocumentValid($employee->document())) {
            throw new InvalidDocumentException('Invalid document format');
        }
    }
    
    private function emailExists(Email $email): bool
    {
        return $this->employeeRepository->findByEmail($email) !== null;
    }
}
```

### **3. Coordinación entre entidades:**
```php
class EmployeeService
{
    public function promoteEmployee(Employee $employee, Position $newPosition): void
    {
        // ✅ COORDINA entre múltiples entidades
        if (!$employee->isEligibleForPromotion()) {
            throw new EmployeeNotEligibleForPromotionException();
        }
        
        if (!$this->isPositionAvailable($newPosition)) {
            throw new PositionNotAvailableException();
        }
        
        $oldPosition = $employee->position();
        $employee->promote($newPosition);
        $newPosition->assignEmployee($employee);
        $oldPosition->removeEmployee($employee);
        
        $this->notifyPromotion($employee, $oldPosition, $newPosition);
    }
}
```

### **4. Operaciones de negocio:**
```php
class EmployeeService
{
    public function inactivateGonzalezEmployees(): array
    {
        // ✅ OPERACIÓN DE NEGOCIO específica
        $employees = $this->employeeRepository->findByLastName('González');
        
        $results = [
            'inactivated' => 0,
            'errors' => [],
            'total_found' => count($employees)
        ];
        
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
                $results['errors'][] = "Error con empleado {$employee->id()}: " . $e->getMessage();
            }
        }
        
        return $results;
    }
}
```

---

## 🎯 Casos de uso específicos del negocio

### **1. Gestión de nómina:**
```php
class EmployeeService
{
    public function processPayroll(array $employees): PayrollResult
    {
        $totalAmount = new Money(0, 'USD');
        $processed = 0;
        $errors = [];
        
        foreach ($employees as $employee) {
            try {
                $salary = $this->calculateSalary($employee);
                $this->createPayrollEntry($employee, $salary);
                $totalAmount = $totalAmount->add($salary);
                $processed++;
            } catch (\Exception $e) {
                $errors[] = "Error processing employee {$employee->id()}: " . $e->getMessage();
            }
        }
        
        return new PayrollResult($totalAmount, $processed, $errors);
    }
}
```

### **2. Evaluación de desempeño:**
```php
class EmployeeService
{
    public function evaluatePerformance(Employee $employee, PerformanceEvaluation $evaluation): void
    {
        if (!$this->isEvaluationPeriod()) {
            throw new EvaluationNotInPeriodException();
        }
        
        if (!$this->canEvaluateEmployee($employee)) {
            throw new EmployeeCannotBeEvaluatedException();
        }
        
        $employee->setPerformanceRating($evaluation->rating());
        $this->createPerformanceRecord($employee, $evaluation);
        
        if ($evaluation->rating() >= 4.5) {
            $this->notifyHighPerformance($employee);
        }
    }
}
```

### **3. Gestión de proyectos:**
```php
class EmployeeService
{
    public function assignToProject(Employee $employee, Project $project): void
    {
        if (!$this->isEmployeeAvailable($employee)) {
            throw new EmployeeNotAvailableException();
        }
        
        if (!$this->hasRequiredSkills($employee, $project)) {
            throw new InsufficientSkillsException();
        }
        
        if ($this->wouldExceedWorkload($employee, $project)) {
            throw new WorkloadExceededException();
        }
        
        $employee->assignToProject($project);
        $project->assignEmployee($employee);
        $this->notifyProjectAssignment($employee, $project);
    }
}
```

---

## 🔄 Flujo completo de una petición HTTP

### **Escenario: "Inactivar empleados con apellido González"**

#### **1. Usuario hace petición HTTP:**
```http
POST /api/employees/inactivate-gonzalez
Content-Type: application/json
Authorization: Bearer token123

{
    "updated_by": "admin-123"
}
```

#### **2. Controller recibe la petición:**
```php
// app/Modules/Employees/Infrastructure/Http/Controllers/EmployeeController.php
class EmployeeController extends Controller
{
    public function inactivateGonzalezEmployees(Request $request)
    {
        // 1. Crear Command con datos de la petición
        $command = new InactivateGonzalezEmployeesCommand(
            updatedBy: $request->input('updated_by')
        );
        
        // 2. Pasar Command al Handler
        $handler = app(InactivateGonzalezEmployeesHandler::class);
        $dto = $handler->handle($command);
        
        // 3. Retornar respuesta HTTP
        return response()->json([
            'message' => "Se inactivaron {$dto->inactivated} empleados González de {$dto->totalFound} encontrados",
            'data' => $dto->toArray()
        ]);
    }
}
```

#### **3. Handler procesa el Command:**
```php
// app/Modules/Employees/Application/Handlers/InactivateGonzalezEmployeesHandler.php
class InactivateGonzalezEmployeesHandler
{
    public function __construct(
        private EmployeeService $employeeService // ✅ Usa Service del Domain
    ) {}
    
    public function handle(InactivateGonzalezEmployeesCommand $command): InactivateGonzalezEmployeesDTO
    {
        // 1. Usar Service del Domain para la lógica de negocio
        $results = $this->employeeService->inactivateGonzalezEmployees($command->updatedBy);
        
        // 2. Crear DTO de respuesta
        return new InactivateGonzalezEmployeesDTO(
            inactivated: $results['inactivated'],
            errors: $results['errors'],
            totalFound: $results['total_found']
        );
    }
}
```

#### **4. Service del Domain ejecuta la lógica:**
```php
// app/Modules/Employees/Domain/Services/EmployeeService.php
class EmployeeService
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository
    ) {}
    
    public function inactivateGonzalezEmployees(?string $updatedBy = null): array
    {
        // 1. Buscar empleados con apellido González
        $employees = $this->employeeRepository->findByLastName('González');
        
        $results = [
            'inactivated' => 0,
            'errors' => [],
            'total_found' => count($employees)
        ];
        
        // 2. Procesar cada empleado
        foreach ($employees as $employee) {
            try {
                // 3. Validar si ya está inactivo
                if ($employee->status()->value === 'inactive') {
                    $results['errors'][] = "Empleado {$employee->id()} ya está inactivo";
                    continue;
                }
                
                // 4. Inactivar empleado (lógica de la Entity)
                $employee->inactivate($updatedBy);
                
                // 5. Persistir cambios
                $this->employeeRepository->save($employee);
                
                $results['inactivated']++;
                
            } catch (\Exception $e) {
                $results['errors'][] = "Error con empleado {$employee->id()}: " . $e->getMessage();
            }
        }
        
        return $results;
    }
}
```

#### **5. Entity aplica la lógica de negocio:**
```php
// app/Modules/Employees/Domain/Entities/Employee.php
class Employee
{
    public function inactivate(?string $updatedBy = null): void
    {
        // ✅ LÓGICA DE NEGOCIO EN LA ENTITY
        $this->status = EmployeeStatus::Inactive;
        $this->updatedBy = $updatedBy;
        $this->updatedAt = new DateTimeImmutable();
        
        // ✅ DISPARAR EVENTO DE DOMINIO
        $this->addDomainEvent(new EmployeeInactivatedEvent($this));
    }
}
```

#### **6. Respuesta HTTP al usuario:**
```json
{
    "message": "Se inactivaron 3 empleados González de 5 encontrados",
    "data": {
        "inactivated": 3,
        "errors": [
            "Empleado 456 ya está inactivo",
            "Error con empleado 789: No se pudo actualizar"
        ],
        "total_found": 5
    }
}
```

---

## 📢 Eventos de Dominio

### **¿Qué son los Eventos de Dominio?**
Los **Eventos de Dominio** son objetos que representan **algo importante que ocurrió** en el dominio. Son como "notificaciones" que se disparan cuando algo significativo pasa en tu aplicación.

### **¿Qué hace `$this->addDomainEvent()`?**
```php
class Employee
{
    private array $domainEvents = [];
    
    public function inactivate(?string $updatedBy = null): void
    {
        $this->status = EmployeeStatus::Inactive;
        $this->updatedBy = $updatedBy;
        $this->updatedAt = new DateTimeImmutable();
        
        // ✅ REGISTRA el evento para ser disparado después
        $this->addDomainEvent(new EmployeeInactivatedEvent($this));
    }
    
    private function addDomainEvent(DomainEvent $event): void
    {
        $this->domainEvents[] = $event;
    }
    
    public function getDomainEvents(): array
    {
        return $this->domainEvents;
    }
    
    public function clearDomainEvents(): void
    {
        $this->domainEvents = [];
    }
}
```

### **¿Para qué sirven los Eventos de Dominio?**
```php
// Cuando un empleado se inactiva, otros módulos necesitan saberlo
class EmployeeInactivatedEvent
{
    public function __construct(
        public readonly Employee $employee,
        public readonly DateTimeImmutable $occurredAt = new DateTimeImmutable()
    ) {}
}
```

**Módulos que pueden escuchar este evento:**
- **Módulo de Nómina** - Dejar de pagar salario
- **Módulo de Acceso** - Desactivar credenciales
- **Módulo de Proyectos** - Remover de proyectos activos
- **Módulo de Notificaciones** - Enviar email de confirmación

### **Ejemplo de escuchadores:**
```php
// Módulo de Nómina
class EmployeeInactivatedListener
{
    public function handle(EmployeeInactivatedEvent $event): void
    {
        // Dejar de procesar nómina para este empleado
        $this->payrollService->stopPayroll($event->employee->id());
    }
}

// Módulo de Acceso
class DeactivateCredentialsListener
{
    public function handle(EmployeeInactivatedEvent $event): void
    {
        // Desactivar credenciales de acceso
        $this->accessService->deactivateCredentials($event->employee->id());
    }
}
```

---

## ✅ Mejores prácticas

### **1. Lógica específica del dominio:**
```php
// ✅ BIEN - Lógica específica del dominio
class EmployeeService
{
    public function calculateSalary(Employee $employee): Money
    {
        // Lógica de negocio específica
    }
}

// ❌ MAL - Lógica genérica
class EmployeeService
{
    public function processData($data): mixed
    {
        // Lógica genérica que no es específica del dominio
    }
}
```

### **2. Métodos específicos:**
```php
// ✅ BIEN - Métodos específicos
class EmployeeService
{
    public function calculateSalary(Employee $employee): Money
    public function validateEmployee(Employee $employee): void
    public function promoteEmployee(Employee $employee, Position $position): void
}

// ❌ MAL - Método genérico
class EmployeeService
{
    public function process(Employee $employee, string $action): mixed
    {
        // Método genérico que hace muchas cosas
    }
}
```

### **3. Usar entidades del dominio:**
```php
// ✅ BIEN - Usa entidades del dominio
class EmployeeService
{
    public function calculateSalary(Employee $employee): Money
    {
        $baseSalary = $employee->position()->baseSalary();
        // ...
    }
}

// ❌ MAL - Usa modelos de Eloquent
class EmployeeService
{
    public function calculateSalary(EmployeeModel $employee): Money
    {
        $baseSalary = $employee->position->base_salary;
        // ...
    }
}
```

### **4. Encapsular lógica compleja:**
```php
// ✅ BIEN - Lógica encapsulada
class EmployeeService
{
    public function calculateSalary(Employee $employee): Money
    {
        $baseSalary = $employee->position()->baseSalary();
        $bonus = $this->calculateBonus($employee);
        $deductions = $this->calculateDeductions($employee);
        
        return $baseSalary->add($bonus)->subtract($deductions);
    }
    
    private function calculateBonus(Employee $employee): Money
    {
        // Lógica compleja encapsulada
    }
}

// ❌ MAL - Lógica expuesta
class EmployeeService
{
    public function calculateSalary(Employee $employee): Money
    {
        // Toda la lógica compleja aquí
        $baseSalary = $employee->position()->baseSalary();
        $performance = $employee->performanceRating();
        $years = $employee->yearsInCompany();
        // ... 50 líneas más de lógica
    }
}
```

### **5. Separar responsabilidades:**
```php
// ✅ BIEN - Responsabilidades separadas
class EmployeeService
{
    public function calculateSalary(Employee $employee): Money
    {
        // Solo cálculo de salario
    }
    
    public function validateEmployee(Employee $employee): void
    {
        // Solo validación
    }
}

// ❌ MAL - Múltiples responsabilidades
class EmployeeService
{
    public function processEmployee(Employee $employee): void
    {
        // Validación, cálculo, notificación, etc.
    }
}
```

---

## 🧪 Testing de Services

### **Test básico:**
```php
// tests/Unit/Domain/Services/EmployeeServiceTest.php
class EmployeeServiceTest extends TestCase
{
    public function test_calculates_salary_correctly(): void
    {
        $employee = $this->createEmployee();
        $service = new EmployeeService(/* ... */);
        
        $salary = $service->calculateSalary($employee);
        
        $this->assertInstanceOf(Money::class, $salary);
        $this->assertGreaterThan(0, $salary->amount());
    }
    
    public function test_validates_employee_successfully(): void
    {
        $employee = $this->createValidEmployee();
        $service = new EmployeeService(/* ... */);
        
        $service->validateEmployee($employee);
        
        $this->assertTrue(true); // No exception thrown
    }
    
    public function test_throws_exception_for_invalid_employee(): void
    {
        $employee = $this->createInvalidEmployee();
        $service = new EmployeeService(/* ... */);
        
        $this->expectException(InvalidEmployeeDataException::class);
        
        $service->validateEmployee($employee);
    }
    
    public function test_promotes_employee_successfully(): void
    {
        $employee = $this->createEligibleEmployee();
        $newPosition = $this->createAvailablePosition();
        $service = new EmployeeService(/* ... */);
        
        $service->promoteEmployee($employee, $newPosition);
        
        $this->assertEquals($newPosition, $employee->position());
    }
    
    public function test_inactivates_gonzalez_employees(): void
    {
        $employees = $this->createGonzalezEmployees();
        $mockRepository = $this->createMock(EmployeeRepositoryInterface::class);
        $mockRepository->expects($this->once())
                      ->method('findByLastName')
                      ->with('González')
                      ->willReturn($employees);
        
        $service = new EmployeeService($mockRepository);
        
        $results = $service->inactivateGonzalezEmployees();
        
        $this->assertEquals(3, $results['inactivated']);
        $this->assertEquals(3, $results['total_found']);
    }
    
    private function createEmployee(): Employee
    {
        return new Employee(/* ... */);
    }
}
```

---

## 🔗 Relación con otras capas

### **1. Con Application:**
```php
// Application usa el Service del Domain
class CreateEmployeeHandler
{
    public function __construct(
        private EmployeeService $employeeService
    ) {}
    
    public function handle(CreateEmployeeCommand $command): EmployeeDTO
    {
        $employee = new Employee(/* ... */);
        
        // Usa el Service del Domain
        $this->employeeService->validateEmployee($employee);
        
        $this->employeeRepository->save($employee);
        
        return $this->convertToDTO($employee);
    }
}
```

### **2. Con Domain:**
```php
// Service usa entidades y repositorios del Domain
class EmployeeService
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository,
        private PositionRepositoryInterface $positionRepository
    ) {}
    
    public function promoteEmployee(Employee $employee, Position $newPosition): void
    {
        // Usa entidades del Domain
        $employee->promote($newPosition);
        
        // Usa repositorios del Domain
        $this->employeeRepository->save($employee);
    }
}
```

### **3. Con Infrastructure:**
```php
// Service no depende directamente de Infrastructure
// Solo usa interfaces del Domain
class EmployeeService
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository // Interface del Domain
    ) {}
}
```

---

## 💻 Ejemplos prácticos

### **Ejemplo 1: Service EmployeeService completo**
```php
// app/Modules/Employees/Domain/Services/EmployeeService.php
class EmployeeService
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository,
        private PositionRepositoryInterface $positionRepository,
        private ProjectRepositoryInterface $projectRepository
    ) {}
    
    public function calculateSalary(Employee $employee): Money
    {
        $baseSalary = $employee->position()->baseSalary();
        $bonus = $this->calculateBonus($employee);
        $deductions = $this->calculateDeductions($employee);
        $overtime = $this->calculateOvertime($employee);
        
        return $baseSalary
            ->add($bonus)
            ->add($overtime)
            ->subtract($deductions);
    }
    
    public function validateEmployee(Employee $employee): void
    {
        if (empty($employee->firstName()) || empty($employee->lastName())) {
            throw new InvalidEmployeeDataException('Name cannot be empty');
        }
        
        if ($employee->email() && $this->emailExists($employee->email())) {
            throw new EmployeeAlreadyExistsException('Email already exists');
        }
        
        if (!$this->isDocumentValid($employee->document())) {
            throw new InvalidDocumentException('Invalid document format');
        }
    }
    
    public function promoteEmployee(Employee $employee, Position $newPosition): void
    {
        if (!$employee->isEligibleForPromotion()) {
            throw new EmployeeNotEligibleForPromotionException();
        }
        
        if (!$this->isPositionAvailable($newPosition)) {
            throw new PositionNotAvailableException();
        }
        
        $oldPosition = $employee->position();
        $employee->promote($newPosition);
        $newPosition->assignEmployee($employee);
        $oldPosition->removeEmployee($employee);
        
        $this->notifyPromotion($employee, $oldPosition, $newPosition);
    }
    
    public function assignToProject(Employee $employee, Project $project): void
    {
        if (!$this->isEmployeeAvailable($employee)) {
            throw new EmployeeNotAvailableException();
        }
        
        if (!$this->hasRequiredSkills($employee, $project)) {
            throw new InsufficientSkillsException();
        }
        
        if ($this->wouldExceedWorkload($employee, $project)) {
            throw new WorkloadExceededException();
        }
        
        $employee->assignToProject($project);
        $project->assignEmployee($employee);
        $this->notifyProjectAssignment($employee, $project);
    }
    
    public function inactivateGonzalezEmployees(): array
    {
        $employees = $this->employeeRepository->findByLastName('González');
        
        $results = [
            'inactivated' => 0,
            'errors' => [],
            'total_found' => count($employees)
        ];
        
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
                $results['errors'][] = "Error con empleado {$employee->id()}: " . $e->getMessage();
            }
        }
        
        return $results;
    }
    
    // Métodos privados
    private function calculateBonus(Employee $employee): Money
    {
        $performance = $employee->performanceRating();
        $years = $employee->yearsInCompany();
        $baseSalary = $employee->position()->baseSalary();
        
        if ($performance >= 4.5 && $years >= 5) {
            return $baseSalary->multiply(0.15);
        }
        
        if ($performance >= 4.0) {
            return $baseSalary->multiply(0.10);
        }
        
        return new Money(0, 'USD');
    }
    
    private function calculateDeductions(Employee $employee): Money
    {
        $baseSalary = $employee->position()->baseSalary();
        $taxes = $baseSalary->multiply(0.12);
        $socialSecurity = $baseSalary->multiply(0.08);
        
        return $taxes->add($socialSecurity);
    }
    
    private function calculateOvertime(Employee $employee): Money
    {
        $overtimeHours = $employee->overtimeHours();
        $hourlyRate = $employee->position()->hourlyRate();
        
        return $hourlyRate->multiply($overtimeHours)->multiply(1.5);
    }
    
    private function emailExists(Email $email): bool
    {
        return $this->employeeRepository->findByEmail($email) !== null;
    }
    
    private function isDocumentValid(DocumentId $document): bool
    {
        // Lógica de validación de documento
        return true;
    }
    
    private function isPositionAvailable(Position $position): bool
    {
        return $position->isAvailable();
    }
    
    private function isEmployeeAvailable(Employee $employee): bool
    {
        return $employee->status()->value === 'active' && 
               !$employee->hasActiveProjects();
    }
    
    private function hasRequiredSkills(Employee $employee, Project $project): bool
    {
        $employeeSkills = $employee->skills();
        $requiredSkills = $project->requiredSkills();
        
        return !empty(array_intersect($employeeSkills, $requiredSkills));
    }
    
    private function wouldExceedWorkload(Employee $employee, Project $project): bool
    {
        $currentWorkload = $employee->currentWorkload();
        $projectWorkload = $project->estimatedWorkload();
        $maxWorkload = $employee->maxWorkload();
        
        return ($currentWorkload + $projectWorkload) > $maxWorkload;
    }
    
    private function notifyPromotion(Employee $employee, Position $oldPosition, Position $newPosition): void
    {
        // Lógica de notificación
    }
    
    private function notifyProjectAssignment(Employee $employee, Project $project): void
    {
        // Lógica de notificación
    }
}
```

### **Ejemplo 2: Service de Nómina**
```php
// app/Modules/Payroll/Domain/Services/PayrollService.php
class PayrollService
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository,
        private PayrollRepositoryInterface $payrollRepository
    ) {}
    
    public function processMonthlyPayroll(): PayrollResult
    {
        $employees = $this->employeeRepository->findActiveEmployees();
        $totalAmount = new Money(0, 'USD');
        $processed = 0;
        $errors = [];
        
        foreach ($employees as $employee) {
            try {
                $salary = $this->calculateEmployeeSalary($employee);
                $this->createPayrollEntry($employee, $salary);
                $totalAmount = $totalAmount->add($salary);
                $processed++;
            } catch (\Exception $e) {
                $errors[] = "Error processing employee {$employee->id()}: " . $e->getMessage();
            }
        }
        
        return new PayrollResult($totalAmount, $processed, $errors);
    }
    
    private function calculateEmployeeSalary(Employee $employee): Money
    {
        $baseSalary = $employee->position()->baseSalary();
        $bonus = $this->calculateBonus($employee);
        $deductions = $this->calculateDeductions($employee);
        
        return $baseSalary->add($bonus)->subtract($deductions);
    }
}
```

---

## 🎯 ¿Cuándo usar Services de Domain?

### **✅ SÍ usar Services cuando:**
- **Necesitas** lógica de negocio compleja
- **La lógica** no pertenece a una entidad específica
- **Necesitas** coordinar entre múltiples entidades
- **Quieres** encapsular reglas de negocio
- **Necesitas** operaciones de negocio específicas

### **❌ NO usar Services cuando:**
- **La lógica** pertenece a una entidad específica
- **Solo necesitas** operaciones simples de CRUD
- **No hay lógica** de negocio compleja
- **Es un prototipo** simple

---

## 🎯 Resumen

### **🎯 Services de Domain son:**
- **Clases** que contienen lógica de negocio compleja
- **No pertenecen** a una entidad específica
- **Coordinan** entre múltiples entidades
- **Implementan** reglas de negocio
- **Son independientes** de la tecnología

### **🎯 Para qué sirven:**
- Encapsular lógica de negocio compleja
- Coordinar entre múltiples entidades
- Implementar reglas de negocio
- Facilitar el testing y mantenimiento
- Mantener la consistencia del dominio

### **📋 Características clave:**
- Lógica de negocio compleja
- No pertenece a una entidad específica
- Coordina entre múltiples entidades
- Implementa reglas de negocio
- Es independiente de la tecnología

### **✅ Mejores prácticas:**
- Lógica específica del dominio
- Métodos específicos
- Usar entidades del dominio
- Encapsular lógica compleja
- Separar responsabilidades

### **🔄 En el flujo de petición HTTP:**
1. **Controller** recibe petición
2. **Handler** procesa Command
3. **Service** ejecuta lógica de negocio
4. **Entity** aplica reglas de negocio
5. **Repository** persiste datos
6. **Controller** retorna respuesta

**¡Los Services de Domain son esenciales para manejar la lógica de negocio compleja que no pertenece a una entidad específica!** 🚀
