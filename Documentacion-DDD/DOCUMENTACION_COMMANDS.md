# Documentación: Commands - Guía Completa

## 📋 Índice
1. [Introducción](#introducción)
2. [¿Qué son los Commands?](#qué-son-los-commands)
3. [Estructura de un Command](#estructura-de-un-command)
4. [Ciclo de Vida del Command](#ciclo-de-vida-del-command)
5. [Qué tener en cuenta para implementar Commands](#qué-tener-en-cuenta-para-implementar-commands)
6. [Casos de Uso para Commands](#casos-de-uso-para-commands)
7. [Relación con el Ciclo de Vida del Módulo](#relación-con-el-ciclo-de-vida-del-módulo)
8. [Mejores Prácticas](#mejores-prácticas)
9. [Ejemplos Completos](#ejemplos-completos)
10. [Patrones y Anti-patrones](#patrones-y-anti-patrones)
11. [Testing de Commands](#testing-de-commands)
12. [Resumen y Recomendaciones](#resumen-y-recomendaciones)

---

## 🎯 Introducción

Los **Commands** son una pieza fundamental en la arquitectura DDD (Domain-Driven Design). Representan las **intenciones** o **acciones** que los usuarios quieren ejecutar en el sistema, transportando todos los datos necesarios para realizar una operación específica.

### **¿Por qué usar Commands?**
- **Separación clara** entre datos y lógica
- **Inmutabilidad** de los datos
- **Testabilidad** mejorada
- **Mantenibilidad** del código
- **Claridad** en las intenciones del usuario

---

## 🎯 ¿Qué son los Commands?

### **Definición:**
Un **Command** es un objeto que representa una **intención** o **acción** que el usuario quiere ejecutar en el sistema. Es como un "mensaje" que contiene toda la información necesaria para realizar una operación.

### **Características principales:**
- **Inmutables**: No cambian después de ser creados
- **Específicos**: Representan una acción concreta
- **Con datos**: Contienen toda la información necesaria
- **Sin lógica**: Solo transportan datos, no ejecutan lógica

### **Analogía de la Vida Real:**
Imagina que los **Commands** son como **órdenes de trabajo** en una fábrica:

- **Orden de Producción**: "Fabricar 100 sillas de madera"
- **Orden de Mantenimiento**: "Reparar la máquina X"
- **Orden de Envío**: "Enviar pedido #12345 a la dirección Y"

Cada orden contiene **toda la información necesaria** para ejecutar la tarea, pero **no ejecuta la tarea** por sí misma.

---

## 🏗️ Estructura de un Command

### **Ejemplo básico:**
```php
// app/Modules/Employees/Application/Commands/CreateEmployeeCommand.php
final class CreateEmployeeCommand
{
    public function __construct(
        public readonly string $tenantId,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $documentType,
        public readonly string $documentNumber,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?string $hireDate,
        public readonly ?string $actorId
    ) {}
}
```

### **¿Por qué `readonly`?**
- **Inmutabilidad**: Los datos no pueden cambiar después de crear el Command
- **Thread-safe**: Múltiples hilos pueden acceder sin problemas
- **Claridad**: Es obvio que es solo para transportar datos
- **Prevención de errores**: Evita modificaciones accidentales

### **¿Por qué `final`?**
- **Prevención de herencia**: No se puede extender accidentalmente
- **Claridad**: Indica que es una clase completa
- **Optimización**: El compilador puede optimizar mejor

---

## 🔄 Ciclo de Vida del Command

### **1. Creación en el Controller:**
```php
class EmployeeController extends Controller
{
    public function store(CreateEmployeeRequest $request) {
        // 1. Crear Command con datos validados
        $command = new CreateEmployeeCommand(
            tenantId: $request->input('tenant_id'),
            firstName: $request->input('first_name'),
            lastName: $request->input('last_name'),
            documentType: $request->input('document_type'),
            documentNumber: $request->input('document_number'),
            email: $request->input('email'),
            phone: $request->input('phone'),
            hireDate: $request->input('hire_date'),
            actorId: Auth::id()
        );

        // 2. Pasar al Handler
        $handler = app(CreateEmployeeHandler::class);
        $dto = $handler->handle($command);

        // 3. Retornar respuesta
        return response()->json(['data' => $dto->toArray()], 201);
    }
}
```

### **2. Procesamiento en el Handler:**
```php
class CreateEmployeeHandler
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository,
        private EventDispatcherInterface $eventDispatcher
    ) {}

    public function handle(CreateEmployeeCommand $command): EmployeeDTO
    {
        // 1. Validar datos del Command
        $this->validateCommand($command);
        
        // 2. Crear entidad usando datos del Command
        $employee = new Employee(
            id: Str::uuid()->toString(),
            tenantId: $command->tenantId,
            firstName: $command->firstName,
            lastName: $command->lastName,
            document: new DocumentId($command->documentType, $command->documentNumber),
            email: $command->email ? new Email($command->email) : null,
            phone: $command->phone ? new Phone($command->phone) : null,
            hireDate: $command->hireDate ? new \DateTimeImmutable($command->hireDate) : null,
            status: EmployeeStatus::ACTIVE,
            createdBy: $command->actorId,
            updatedBy: $command->actorId,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable()
        );

        // 3. Guardar usando Repository
        $this->employeeRepository->save($employee);

        // 4. Disparar evento
        event(new EmployeeCreated($employee));

        // 5. Retornar DTO
        return $this->convertToDTO($employee);
    }
}
```

### **3. Destrucción:**
- El Command se destruye automáticamente al salir del scope
- No persiste en la base de datos
- Solo existe durante el procesamiento

---

## 📋 Qué tener en cuenta para implementar Commands

### **1. Naming Convention:**
```php
// ✅ BIEN - Nombres descriptivos y específicos
CreateEmployeeCommand
UpdateEmployeeCommand
DeleteEmployeeCommand
ProcessPayrollCommand
SendNotificationCommand
ActivateEmployeeCommand
DeactivateEmployeeCommand

// ❌ MAL - Nombres vagos o genéricos
EmployeeCommand
UpdateCommand
ProcessCommand
ActionCommand
```

### **2. Estructura de propiedades:**
```php
// ✅ BIEN - Propiedades específicas y necesarias
final class CreateEmployeeCommand
{
    public function __construct(
        public readonly string $tenantId,        // ← Necesario para multi-tenant
        public readonly string $firstName,       // ← Datos del empleado
        public readonly string $lastName,        // ← Datos del empleado
        public readonly string $documentType,    // ← Datos del empleado
        public readonly string $documentNumber,  // ← Datos del empleado
        public readonly ?string $email,          // ← Opcional
        public readonly ?string $phone,          // ← Opcional
        public readonly ?string $hireDate,       // ← Opcional
        public readonly ?string $actorId         // ← Para auditoría
    ) {}
}

// ❌ MAL - Propiedades innecesarias o vagas
final class CreateEmployeeCommand
{
    public function __construct(
        public readonly array $data,        // ❌ Muy vago
        public readonly string $id,         // ❌ No necesario para crear
        public readonly string $status      // ❌ Se asigna en el Handler
    ) {}
}
```

### **3. Validación de datos:**
```php
// ✅ BIEN - Validación en el Command
final class CreateEmployeeCommand
{
    public function __construct(
        public readonly string $tenantId,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $documentType,
        public readonly string $documentNumber,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?string $hireDate,
        public readonly ?string $actorId
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->tenantId)) {
            throw new InvalidArgumentException('Tenant ID is required');
        }
        
        if (empty($this->firstName)) {
            throw new InvalidArgumentException('First name is required');
        }
        
        if (empty($this->lastName)) {
            throw new InvalidArgumentException('Last name is required');
        }
        
        if ($this->email && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format');
        }
        
        if ($this->hireDate && !strtotime($this->hireDate)) {
            throw new InvalidArgumentException('Invalid hire date format');
        }
    }
}
```

### **4. Métodos auxiliares:**
```php
// ✅ BIEN - Métodos útiles
final class CreateEmployeeCommand
{
    // ... constructor ...

    public function toArray(): array
    {
        return [
            'tenant_id' => $this->tenantId,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'document_type' => $this->documentType,
            'document_number' => $this->documentNumber,
            'email' => $this->email,
            'phone' => $this->phone,
            'hire_date' => $this->hireDate,
            'actor_id' => $this->actorId,
        ];
    }

    public function hasEmail(): bool
    {
        return !empty($this->email);
    }

    public function hasPhone(): bool
    {
        return !empty($this->phone);
    }

    public function hasHireDate(): bool
    {
        return !empty($this->hireDate);
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getDocument(): string
    {
        return $this->documentType . ' ' . $this->documentNumber;
    }
}
```

---

## 🎯 Casos de Uso para Commands

### **1. Operaciones CRUD:**
```php
// Crear entidades
CreateEmployeeCommand
CreateCustomerCommand
CreateProductCommand
CreateOrderCommand

// Actualizar entidades
UpdateEmployeeCommand
UpdateCustomerCommand
UpdateProductCommand
UpdateOrderCommand

// Eliminar entidades
DeleteEmployeeCommand
DeleteCustomerCommand
DeleteProductCommand
DeleteOrderCommand
```

### **2. Operaciones de Negocio:**
```php
// Procesar nómina
ProcessPayrollCommand
CalculateSalaryCommand
GeneratePayrollReportCommand
ApprovePayrollCommand

// Gestión de inventario
UpdateStockCommand
ReserveProductCommand
ProcessOrderCommand
CancelOrderCommand

// Notificaciones
SendEmailCommand
SendSMSCommand
SendPushNotificationCommand
SendBulkEmailsCommand
```

### **3. Operaciones Complejas:**
```php
// Migración de datos
MigrateEmployeeDataCommand
SyncCustomerDataCommand
UpdateProductPricesCommand
ImportDataFromFileCommand

// Reportes
GenerateMonthlyReportCommand
ExportDataCommand
CreateDashboardCommand
GenerateFinancialReportCommand

// Aprobaciones
ApproveEmployeeCommand
RejectEmployeeCommand
ApproveOrderCommand
RejectOrderCommand
```

### **4. Operaciones Asíncronas:**
```php
// Procesamiento en background
ProcessLargeFileCommand
SendBulkEmailsCommand
GenerateComplexReportCommand
SyncExternalDataCommand

// Operaciones de mantenimiento
CleanupOldDataCommand
OptimizeDatabaseCommand
BackupDataCommand
ArchiveOldRecordsCommand
```

### **5. Operaciones de Estado:**
```php
// Cambios de estado
ActivateEmployeeCommand
DeactivateEmployeeCommand
SuspendEmployeeCommand
ReactivateEmployeeCommand

// Transiciones de estado
MoveOrderToProcessingCommand
MoveOrderToShippedCommand
MoveOrderToDeliveredCommand
CancelOrderCommand
```

---

## 🔄 Relación con el Ciclo de Vida del Módulo

### **1. Registro del Módulo:**
```php
// app/Modules/Employees/EmployeesServiceProvider.php
class EmployeesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Registrar Commands (opcional, para auto-discovery)
        $this->app->bind(CreateEmployeeCommand::class);
        $this->app->bind(UpdateEmployeeCommand::class);
        $this->app->bind(DeleteEmployeeCommand::class);
        $this->app->bind(ActivateEmployeeCommand::class);
        $this->app->bind(DeactivateEmployeeCommand::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/Infrastructure/Http/Routes/api.php');
        $this->loadMigrationsFrom(__DIR__.'/Infrastructure/Database/Migrations');
    }
}
```

### **2. Uso en Controllers:**
```php
class EmployeeController extends Controller
{
    public function store(CreateEmployeeRequest $request) {
        $command = new CreateEmployeeCommand(/* ... */);
        $handler = app(CreateEmployeeHandler::class);
        $dto = $handler->handle($command);
        return response()->json(['data' => $dto->toArray()], 201);
    }

    public function update(UpdateEmployeeRequest $request, string $id) {
        $command = new UpdateEmployeeCommand(
            id: $id,
            firstName: $request->input('first_name'),
            lastName: $request->input('last_name'),
            // ... más datos
        );
        
        $handler = app(UpdateEmployeeHandler::class);
        $dto = $handler->handle($command);
        return response()->json(['data' => $dto->toArray()]);
    }

    public function destroy(string $id) {
        $command = new DeleteEmployeeCommand($id);
        $handler = app(DeleteEmployeeHandler::class);
        $handler->handle($command);
        return response()->noContent();
    }
}
```

### **3. Uso en Commands de Consola:**
```php
// app/Console/Commands/ImportEmployeesCommand.php
class ImportEmployeesCommand extends Command
{
    protected $signature = 'employees:import {file}';
    protected $description = 'Import employees from CSV file';

    public function handle() {
        $file = $this->argument('file');
        $employees = $this->getEmployeesFromFile($file);
        
        $this->info("Importing {$employees->count()} employees...");
        
        foreach ($employees as $employeeData) {
            $command = new CreateEmployeeCommand(
                tenantId: $employeeData['tenant_id'],
                firstName: $employeeData['first_name'],
                lastName: $employeeData['last_name'],
                documentType: $employeeData['document_type'],
                documentNumber: $employeeData['document_number'],
                email: $employeeData['email'] ?? null,
                phone: $employeeData['phone'] ?? null,
                hireDate: $employeeData['hire_date'] ?? null,
                actorId: 'system'
            );
            
            $handler = app(CreateEmployeeHandler::class);
            $handler->handle($command);
            
            $this->line("Imported: {$command->getFullName()}");
        }
        
        $this->info('Import completed successfully!');
    }
}
```

### **4. Uso en Jobs:**
```php
// app/Jobs/ProcessEmployeeDataJob.php
class ProcessEmployeeDataJob implements ShouldQueue
{
    public function __construct(
        private string $employeeId,
        private array $data
    ) {}

    public function handle() {
        $command = new ProcessEmployeeDataCommand(
            employeeId: $this->employeeId,
            data: $this->data
        );
        
        $handler = app(ProcessEmployeeDataHandler::class);
        $handler->handle($command);
    }
}
```

### **5. Uso en Event Listeners:**
```php
// app/Listeners/ProcessNewEmployeeListener.php
class ProcessNewEmployeeListener
{
    public function handle(EmployeeCreated $event) {
        $command = new SendWelcomeEmailCommand(
            employeeId: $event->employee->id(),
            email: $event->employee->email()?->value()
        );
        
        $handler = app(SendWelcomeEmailHandler::class);
        $handler->handle($command);
    }
}
```

---

## 🎨 Mejores Prácticas

### **1. Un Command por acción:**
```php
// ✅ BIEN - Commands específicos
CreateEmployeeCommand
UpdateEmployeeCommand
DeleteEmployeeCommand
ActivateEmployeeCommand
DeactivateEmployeeCommand

// ❌ MAL - Command genérico
EmployeeCommand
UpdateCommand
ActionCommand
```

### **2. Commands inmutables:**
```php
// ✅ BIEN - Inmutable
final class CreateEmployeeCommand
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName
    ) {}
}

// ❌ MAL - Mutable
final class CreateEmployeeCommand
{
    public string $firstName;
    public string $lastName;
    
    public function setFirstName(string $name): void {
        $this->firstName = $name; // ❌ Puede cambiar
    }
}
```

### **3. Validación en el Command:**
```php
// ✅ BIEN - Validación básica
final class CreateEmployeeCommand
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly ?string $email
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->firstName)) {
            throw new InvalidArgumentException('First name is required');
        }
        
        if ($this->email && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format');
        }
    }
}
```

### **4. Commands pequeños y específicos:**
```php
// ✅ BIEN - Command específico
final class CreateEmployeeCommand
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email
    ) {}
}

// ❌ MAL - Command muy grande
final class EmployeeCommand
{
    public function __construct(
        public readonly string $action, // create, update, delete
        public readonly array $data,
        public readonly ?string $id
    ) {}
}
```

### **5. Naming consistente:**
```php
// ✅ BIEN - Naming consistente
CreateEmployeeCommand
UpdateEmployeeCommand
DeleteEmployeeCommand
ActivateEmployeeCommand
DeactivateEmployeeCommand

// ❌ MAL - Naming inconsistente
CreateEmployeeCommand
EmployeeUpdateCommand
DeleteEmployeeCommand
ActivateEmployeeCommand
EmployeeDeactivateCommand
```

---

## 💡 Ejemplos Completos

### **Ejemplo 1: Command Simple**
```php
// app/Modules/Employees/Application/Commands/ActivateEmployeeCommand.php
final class ActivateEmployeeCommand
{
    public function __construct(
        public readonly string $employeeId,
        public readonly ?string $actorId
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->employeeId)) {
            throw new InvalidArgumentException('Employee ID is required');
        }
    }

    public function toArray(): array
    {
        return [
            'employee_id' => $this->employeeId,
            'actor_id' => $this->actorId,
        ];
    }
}
```

### **Ejemplo 2: Command Complejo**
```php
// app/Modules/Employees/Application/Commands/ProcessPayrollCommand.php
final class ProcessPayrollCommand
{
    public function __construct(
        public readonly string $employeeId,
        public readonly string $period,
        public readonly int $hoursWorked,
        public readonly float $hourlyRate,
        public readonly array $deductions,
        public readonly string $taxBracket,
        public readonly ?string $actorId
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->employeeId)) {
            throw new InvalidArgumentException('Employee ID is required');
        }
        
        if (empty($this->period)) {
            throw new InvalidArgumentException('Period is required');
        }
        
        if ($this->hoursWorked <= 0) {
            throw new InvalidArgumentException('Hours worked must be positive');
        }
        
        if ($this->hourlyRate <= 0) {
            throw new InvalidArgumentException('Hourly rate must be positive');
        }
    }

    public function toArray(): array
    {
        return [
            'employee_id' => $this->employeeId,
            'period' => $this->period,
            'hours_worked' => $this->hoursWorked,
            'hourly_rate' => $this->hourlyRate,
            'deductions' => $this->deductions,
            'tax_bracket' => $this->taxBracket,
            'actor_id' => $this->actorId,
        ];
    }

    public function getGrossSalary(): float
    {
        return $this->hoursWorked * $this->hourlyRate;
    }

    public function getTotalDeductions(): float
    {
        return array_sum($this->deductions);
    }
}
```

### **Ejemplo 3: Command con Validaciones Complejas**
```php
// app/Modules/Employees/Application/Commands/CreateEmployeeCommand.php
final class CreateEmployeeCommand
{
    public function __construct(
        public readonly string $tenantId,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $documentType,
        public readonly string $documentNumber,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?string $hireDate,
        public readonly ?string $actorId
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->tenantId)) {
            throw new InvalidArgumentException('Tenant ID is required');
        }
        
        if (empty($this->firstName)) {
            throw new InvalidArgumentException('First name is required');
        }
        
        if (empty($this->lastName)) {
            throw new InvalidArgumentException('Last name is required');
        }
        
        if (empty($this->documentType)) {
            throw new InvalidArgumentException('Document type is required');
        }
        
        if (empty($this->documentNumber)) {
            throw new InvalidArgumentException('Document number is required');
        }
        
        if ($this->email && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format');
        }
        
        if ($this->phone && !preg_match('/^\+?[1-9]\d{1,14}$/', $this->phone)) {
            throw new InvalidArgumentException('Invalid phone format');
        }
        
        if ($this->hireDate && !strtotime($this->hireDate)) {
            throw new InvalidArgumentException('Invalid hire date format');
        }
    }

    public function toArray(): array
    {
        return [
            'tenant_id' => $this->tenantId,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'document_type' => $this->documentType,
            'document_number' => $this->documentNumber,
            'email' => $this->email,
            'phone' => $this->phone,
            'hire_date' => $this->hireDate,
            'actor_id' => $this->actorId,
        ];
    }

    public function hasEmail(): bool
    {
        return !empty($this->email);
    }

    public function hasPhone(): bool
    {
        return !empty($this->phone);
    }

    public function hasHireDate(): bool
    {
        return !empty($this->hireDate);
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getDocument(): string
    {
        return $this->documentType . ' ' . $this->documentNumber;
    }
}
```

---

## 🚫 Patrones y Anti-patrones

### **✅ Patrones Recomendados:**

#### **1. Commands específicos:**
```php
// ✅ BIEN - Un Command por acción
CreateEmployeeCommand
UpdateEmployeeCommand
DeleteEmployeeCommand
```

#### **2. Commands inmutables:**
```php
// ✅ BIEN - Inmutable con readonly
final class CreateEmployeeCommand
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName
    ) {}
}
```

#### **3. Validación en el constructor:**
```php
// ✅ BIEN - Validación en el constructor
final class CreateEmployeeCommand
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName
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

### **❌ Anti-patrones (Evitar):**

#### **1. Commands genéricos:**
```php
// ❌ MAL - Command genérico
final class EmployeeCommand
{
    public function __construct(
        public readonly string $action,
        public readonly array $data
    ) {}
}
```

#### **2. Commands mutables:**
```php
// ❌ MAL - Command mutable
final class CreateEmployeeCommand
{
    public string $firstName;
    public string $lastName;
    
    public function setFirstName(string $name): void {
        $this->firstName = $name; // ❌ Puede cambiar
    }
}
```

#### **3. Commands con lógica de negocio:**
```php
// ❌ MAL - Command con lógica
final class CreateEmployeeCommand
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName
    ) {}

    public function createEmployee(): Employee {
        // ❌ NO debe tener lógica de negocio
        return new Employee($this->firstName, $this->lastName);
    }
}
```

---

## 🧪 Testing de Commands

### **Test de validación:**
```php
// tests/Unit/Commands/CreateEmployeeCommandTest.php
class CreateEmployeeCommandTest extends TestCase
{
    public function test_creates_command_with_valid_data(): void
    {
        $command = new CreateEmployeeCommand(
            tenantId: 'tenant-1',
            firstName: 'Juan',
            lastName: 'Pérez',
            documentType: 'CC',
            documentNumber: '12345678',
            email: 'juan@email.com',
            phone: '+573001234567',
            hireDate: '2023-01-01',
            actorId: 'user-1'
        );

        $this->assertEquals('tenant-1', $command->tenantId);
        $this->assertEquals('Juan', $command->firstName);
        $this->assertEquals('Pérez', $command->lastName);
        $this->assertTrue($command->hasEmail());
        $this->assertTrue($command->hasPhone());
        $this->assertEquals('Juan Pérez', $command->getFullName());
    }

    public function test_throws_exception_with_empty_first_name(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('First name is required');

        new CreateEmployeeCommand(
            tenantId: 'tenant-1',
            firstName: '', // ❌ Vacío
            lastName: 'Pérez',
            documentType: 'CC',
            documentNumber: '12345678',
            email: null,
            phone: null,
            hireDate: null,
            actorId: null
        );
    }

    public function test_throws_exception_with_invalid_email(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format');

        new CreateEmployeeCommand(
            tenantId: 'tenant-1',
            firstName: 'Juan',
            lastName: 'Pérez',
            documentType: 'CC',
            documentNumber: '12345678',
            email: 'invalid-email', // ❌ Email inválido
            phone: null,
            hireDate: null,
            actorId: null
        );
    }
}
```

### **Test de métodos auxiliares:**
```php
public function test_to_array_returns_correct_data(): void
{
    $command = new CreateEmployeeCommand(
        tenantId: 'tenant-1',
        firstName: 'Juan',
        lastName: 'Pérez',
        documentType: 'CC',
        documentNumber: '12345678',
        email: 'juan@email.com',
        phone: '+573001234567',
        hireDate: '2023-01-01',
        actorId: 'user-1'
    );

    $expected = [
        'tenant_id' => 'tenant-1',
        'first_name' => 'Juan',
        'last_name' => 'Pérez',
        'document_type' => 'CC',
        'document_number' => '12345678',
        'email' => 'juan@email.com',
        'phone' => '+573001234567',
        'hire_date' => '2023-01-01',
        'actor_id' => 'user-1',
    ];

    $this->assertEquals($expected, $command->toArray());
}
```

---

## 📝 Resumen y Recomendaciones

### **🎯 Commands son:**
- **Objetos inmutables** que transportan datos
- **Representan intenciones** del usuario
- **Específicos** para cada acción
- **Sin lógica** de negocio

### **📋 Para implementarlos:**
- Usa `readonly` para inmutabilidad
- Usa `final` para prevenir herencia
- Valida datos básicos en el constructor
- Nombra descriptivamente
- Manténlos pequeños y específicos

### **🎯 Casos de uso:**
- Operaciones CRUD
- Operaciones de negocio
- Operaciones complejas
- Operaciones asíncronas
- Operaciones de estado

### **🔄 En el ciclo de vida:**
- Se crean en Controllers, Commands, Jobs
- Se procesan en Handlers
- Se destruyen automáticamente

### **✅ Mejores prácticas:**
- Un Command por acción
- Commands inmutables
- Validación en el constructor
- Naming consistente
- Métodos auxiliares útiles

### **❌ Evitar:**
- Commands genéricos
- Commands mutables
- Commands con lógica de negocio
- Commands muy grandes
- Naming inconsistente

**Los Commands son la base para una arquitectura limpia y mantenible, permitiendo separar claramente los datos de la lógica de negocio y facilitando el testing y mantenimiento del código.**
