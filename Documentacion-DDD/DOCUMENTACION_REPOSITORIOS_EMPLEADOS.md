# Documentación: Repositorios del Módulo de Empleados

## 📋 Índice
1. [Introducción](#introducción)
2. [Arquitectura de Repositorios](#arquitectura-de-repositorios)
3. [Archivos Principales](#archivos-principales)
4. [Flujo de Funcionamiento](#flujo-de-funcionamiento)
5. [Patrones y Mejores Prácticas](#patrones-y-mejores-prácticas)
6. [Ejemplos Prácticos](#ejemplos-prácticos)
7. [Ventajas y Beneficios](#ventajas-y-beneficios)

---

## 🎯 Introducción

El patrón Repository en el módulo de empleados centraliza toda la lógica de acceso a datos, proporcionando una interfaz limpia y reutilizable para operaciones de base de datos. Esta arquitectura separa la lógica de negocio de la persistencia de datos.

### ¿Por qué usar Repositorios?
- **Reutilización**: Evita duplicar código de consultas
- **Mantenibilidad**: Cambios centralizados en un solo lugar
- **Testabilidad**: Fácil creación de mocks para pruebas
- **Flexibilidad**: Cambio de implementación sin afectar el resto del código

---

## 🏗️ Arquitectura de Repositorios

```
┌─────────────────────────────────────┐
│        EmployeesServiceProvider     │
│  (Registra dependencias en IoC)    │
└─────────────┬───────────────────────┘
              │
              │ bind()
              ▼
┌─────────────────────────────────────┐
│    EmployeeRepositoryInterface      │
│        (Contrato/Abstracción)       │
└─────────────┬───────────────────────┘
              │
              │ implements
              ▼
┌─────────────────────────────────────┐
│   EloquentEmployeeRepository        │
│     (Implementación Concreta)       │
└─────────────────────────────────────┘
```

---

## 📁 Archivos Principales

### 1. **EmployeeRepositoryInterface** 
**Ubicación**: `app/Modules/Employees/Domain/Repositories/EmployeeRepositoryInterface.php`

**¿Qué es?**
- Es un **contrato** que define qué métodos debe tener cualquier repositorio de empleados
- Es una **interfaz** que especifica las operaciones disponibles
- Establece la **abstracción** entre la lógica de dominio y la persistencia

**Métodos definidos:**
```php
interface EmployeeRepositoryInterface
{
    public function findById(string $id): ?Employee;
    public function findByDocument(?string $tenantId, DocumentId $document): ?Employee;
    public function save(Employee $e): void;
    public function delete(string $id): void;
    public function paginate(array $filters, int $page, int $perPage): array;
}
```

**Responsabilidades:**
- ✅ Definir el contrato que deben cumplir las implementaciones
- ✅ Garantizar consistencia en la API del repositorio
- ✅ Permitir intercambiabilidad de implementaciones

---

### 2. **EloquentEmployeeRepository**
**Ubicación**: `app/Modules/Employees/Infrastructure/Database/Repositories/EloquentEmployeeRepository.php`

**¿Qué es?**
- Es la **implementación concreta** de `EmployeeRepositoryInterface`
- Utiliza **Eloquent ORM** para interactuar con la base de datos
- Convierte entre objetos de dominio (`Employee`) y modelos de Eloquent

**Funcionalidades principales:**
```php
final class EloquentEmployeeRepository implements EmployeeRepositoryInterface
{
    // Búsquedas
    public function findById(string $id): ?Employee
    public function findByDocument(?string $tenantId, DocumentId $document): ?Employee
    
    // Operaciones CRUD
    public function save(Employee $e): void
    public function delete(string $id): void
    
    // Consultas complejas
    public function paginate(array $filters, int $page, int $perPage): array
    
    // Conversión de datos
    private function toDomain(EmployeeModel $m): Employee
}
```

**Responsabilidades:**
- ✅ Implementar todas las operaciones definidas en la interfaz
- ✅ Manejar la conversión entre modelos Eloquent y entidades de dominio
- ✅ Aplicar filtros y búsquedas complejas
- ✅ Gestionar la persistencia de datos

---

### 3. **EmployeesServiceProvider**
**Ubicación**: `app/Modules/Employees/EmployeesServiceProvider.php`

**¿Qué es?**
- Es el **punto de entrada** del módulo en Laravel
- Registra las dependencias en el contenedor de servicios (IoC)
- Carga recursos del módulo (rutas, migraciones, vistas)

**Funcionalidades:**
```php
final class EmployeesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Vincula la interfaz con su implementación
        $this->app->bind(EmployeeRepositoryInterface::class, EloquentEmployeeRepository::class);
    }

    public function boot(): void
    {
        // Carga recursos del módulo
        $this->loadRoutesFrom(__DIR__.'/Infrastructure/Http/Routes/api.php');
        $this->loadMigrationsFrom(__DIR__.'/Infrastructure/Database/Migrations');
    }
}
```

**Responsabilidades:**
- ✅ Registrar dependencias en el contenedor IoC
- ✅ Cargar rutas, migraciones y vistas del módulo
- ✅ Configurar el módulo en el ciclo de vida de Laravel

---

## 🔄 Flujo de Funcionamiento

### 1. **Registro del Módulo**
```php
// En config/app.php
'providers' => [
    App\Modules\Employees\EmployeesServiceProvider::class,
]
```

### 2. **Inicialización**
- Laravel ejecuta `register()` y `boot()` del `EmployeesServiceProvider`
- Se registra la vinculación: `EmployeeRepositoryInterface` → `EloquentEmployeeRepository`

### 3. **Resolución de Dependencias**
```php
// En un controlador
public function __construct(
    private EmployeeRepositoryInterface $employeeRepository
) {}

// Laravel resuelve automáticamente la dependencia
// Devuelve una instancia de EloquentEmployeeRepository
```

### 4. **Uso en la Aplicación**
```php
public function getEmployee(string $id): ?Employee {
    return $this->employeeRepository->findById($id);
}
```

---

## 🎨 Patrones y Mejores Prácticas

### ✅ **Lo que SÍ debes hacer:**

#### **1. Mantener el Repository "Tonto" (Solo datos)**
```php
// ✅ BIEN - Solo maneja datos
public function findById(string $id): ?Employee {
    $data = DB::table('employees')->where('id', $id)->first();
    return $data ? $this->toDomain($data) : null;
}
```

#### **2. Siempre devolver objetos de dominio**
```php
// ✅ BIEN - Convierte a Employee
public function findById(string $id): ?Employee {
    $data = DB::table('employees')->where('id', $id)->first();
    return $data ? $this->toDomain($data) : null;
}
```

#### **3. Manejar conversiones consistentemente**
```php
private function toDomain($data): Employee {
    return new Employee(
        id: (string)$data->id,
        tenantId: $data->tenant_id,
        firstName: $data->first_name,
        lastName: $data->last_name,
        document: new DocumentId($data->document_type, $data->document_number),
        email: $data->email ? new Email($data->email) : null,
        phone: $data->phone ? new Phone($data->phone) : null,
        hireDate: $data->hire_date ? new \DateTimeImmutable($data->hire_date) : null,
        status: EmployeeStatus::from($data->status),
        // ... más campos
    );
}
```

#### **4. Traer datos completos de la tabla**
```php
// ✅ BIEN - Trae todos los campos necesarios
public function findById(string $id): ?Employee {
    $data = DB::table('employees')
        ->select('*')  // ← Todos los campos
        ->where('id', $id)
        ->first();
        
    return $data ? $this->toDomain($data) : null;
}

// ❌ MAL - Faltan campos necesarios
public function findById(string $id): ?Employee {
    $data = DB::table('employees')
        ->select('id', 'first_name', 'last_name')  // ❌ Faltan campos
        ->where('id', $id)
        ->first();
        
    return $data ? $this->toDomain($data) : null;  // ❌ Error: faltan campos
}
```

#### **5. Usar map para múltiples objetos**
```php
// ✅ BIEN - Para múltiples empleados usa array_map
public function paginate(array $filters, int $page, int $perPage): array {
    $rows = $q->forPage($page, $perPage)->get();
    
    return [
        'data' => array_map(fn($row) => $this->toDomain($row), $rows->all()),
        'total' => $total,
    ];
}

public function findByTenant(string $tenantId): array {
    $rows = DB::table('employees')
        ->where('tenant_id', $tenantId)
        ->whereNull('deleted_at')
        ->get();
    
    // Convertir cada fila a Employee
    return array_map(fn($row) => $this->toDomain($row), $rows->all());
}

// ❌ MAL - toDomain no funciona con arrays
public function paginate(array $filters, int $page, int $perPage): array {
    $rows = $q->forPage($page, $perPage)->get();
    
    return [
        'data' => $this->toDomain($rows), // ❌ Error: toDomain espera un objeto, no un array
        'total' => $total,
    ];
}
```

### ❌ **Lo que NO debes hacer:**

#### **1. No pongas lógica de negocio en el Repository**
```php
// ❌ MAL - Lógica de negocio aquí
public function findById(string $id): ?Employee {
    $employee = DB::table('employees')->where('id', $id)->first();
    
    if ($employee && $employee->status === 'inactive') {
        throw new InactiveEmployeeException(); // ❌ NO aquí
    }
    
    return $employee ? $this->toDomain($employee) : null;
}
```

#### **2. No devuelvas datos raw**
```php
// ❌ MAL - Devuelve datos raw
public function findById(string $id): ?object {
    return DB::table('employees')->where('id', $id)->first();
}
```

#### **3. No pongas estas responsabilidades en el Repository:**
- ❌ Validaciones de negocio
- ❌ Envío de emails
- ❌ Cálculos complejos
- ❌ Lógica de autorización
- ❌ Transformaciones de presentación

---

## 🔄 Entendiendo `toDomain()`

### **¿Qué es `toDomain()`?**

`toDomain()` es el método que **convierte** los datos simples de la base de datos en un objeto `Employee` con todas sus reglas de negocio.

### **Diferencia entre datos "raw" y objetos de dominio:**

#### **Datos "Raw" (de la base de datos):**
```php
// Esto es lo que devuelve la base de datos:
$data = DB::table('employees')->where('id', $id)->first();

// $data es un objeto genérico con propiedades simples:
// $data->id = "123"
// $data->first_name = "Juan"
// $data->last_name = "Pérez"
// $data->email = "juan@email.com"
// $data->status = "active"
```

#### **Objeto de Dominio (Employee):**
```php
// Esto es lo que devuelve toDomain():
$employee = new Employee(
    id: "123",
    firstName: "Juan",
    lastName: "Pérez",
    email: new Email("juan@email.com"),  // ← Value Object
    status: EmployeeStatus::ACTIVE,       // ← Enum
    document: new DocumentId("CC", "12345678")  // ← Value Object
);
```

### **¿Por qué es importante esta conversión?**

#### **Sin conversión (malo):**
```php
// En el controlador tendrías que hacer:
public function show($id) {
    $data = $this->employeeRepository->findById($id);
    
    // ❌ Acceso inconsistente a los datos
    $nombre = $data->first_name;  // snake_case
    $email = $data->email;        // Sin validación
    $status = $data->status;      // String simple
    
    return response()->json([
        'nombre' => $nombre,
        'email' => $email,
        'status' => $status
    ]);
}
```

#### **Con conversión (bueno):**
```php
// En el controlador:
public function show($id) {
    $employee = $this->employeeRepository->findById($id);
    
    // ✅ Acceso consistente y con validaciones
    $nombre = $employee->firstName();     // camelCase
    $email = $employee->email()?->value(); // Con validación
    $status = $employee->status()->value;  // Enum validado
    
    return response()->json([
        'nombre' => $nombre,
        'email' => $email,
        'status' => $status
    ]);
}
```

### **Reglas importantes para `toDomain()`:**

1. **Solo funciona con un objeto a la vez** - No con arrays
2. **Necesita todos los campos** - La consulta debe traer todos los datos
3. **Siempre devuelve un objeto Employee** - Con todas sus reglas de negocio
4. **Para múltiples objetos** - Usa `array_map()` o `collect()->map()`

---

## 💡 Ejemplos Prácticos

### **Uso en Controladores**

```php
class EmployeeController extends Controller
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository
    ) {}

    // Listar empleados con filtros
    public function index(FilterEmployeesRequest $request) {
        $filters = $request->validated();
        $page = (int)($filters['page'] ?? 1);
        $perPage = (int)($filters['per_page'] ?? 15);

        $result = $this->employeeRepository->paginate($filters, $page, $perPage);
        
        return response()->json([
            'data' => array_map(fn($e) => [
                'id' => $e->id(),
                'first_name' => $e->firstName(),
                'last_name' => $e->lastName(),
                'email' => $e->email()?->value(),
                'status' => $e->status()->value,
            ], $result['data']),
            'meta' => ['total' => $result['total']]
        ]);
    }

    // Buscar empleado por ID
    public function show(string $id) {
        $employee = $this->employeeRepository->findById($id);
        
        if (!$employee) {
            abort(404, 'Empleado no encontrado');
        }
        
        return response()->json(['data' => $employee]);
    }
}
```

### **Uso en Otros Servicios**

```php
class ReportService
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository
    ) {}

    public function generateEmployeeReport(string $tenantId) {
        // Reutiliza la lógica del repository
        $employees = $this->employeeRepository->findByTenant($tenantId);
        
        // Generar reporte...
        return $this->formatReport($employees);
    }
}
```

### **Uso en Comandos de Consola**

```php
class SendEmployeeEmailsCommand extends Command
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository
    ) {
        parent::__construct();
    }

    public function handle() {
        // Reutiliza la lógica del repository
        $employees = $this->employeeRepository->findByStatus('active');
        
        foreach ($employees as $employee) {
            // Enviar email...
        }
    }
}
```

---

## 🚀 Ventajas y Beneficios

### **1. Reutilización de Código**
```php
// Sin Repository (malo) - Repites código
public function buscarEmpleado($id) {
    $empleado = DB::table('employees')->where('id', $id)->first();
    // Conversión manual...
}

public function buscarEmpleadoPorDocumento($documento) {
    $empleado = DB::table('employees')->where('document_number', $documento)->first();
    // Misma conversión repetida...
}

// Con Repository (bueno) - Reutilizas
public function buscarEmpleado($id) {
    return $this->employeeRepository->findById($id);
}

public function buscarEmpleadoPorDocumento($documento) {
    return $this->employeeRepository->findByDocument(null, new DocumentId('CC', $documento));
}
```

### **2. Fácil Cambio de Implementación**
```php
// Cambiar de Eloquent a Query Builder solo requiere cambiar una línea:
$this->app->bind(EmployeeRepositoryInterface::class, QueryBuilderEmployeeRepository::class);

// El resto de la aplicación sigue funcionando igual
```

### **3. Testabilidad Mejorada**
```php
// En las pruebas puedes usar un repository falso
class FakeEmployeeRepository implements EmployeeRepositoryInterface {
    private $employees = [];
    
    public function findById(string $id): ?Employee {
        return $this->employees[$id] ?? null;
    }
    
    public function save(Employee $e): void {
        $this->employees[$e->id()] = $e;
    }
}
```

### **4. Separación de Responsabilidades**
- **Controller**: Maneja HTTP, validación, respuestas
- **Repository**: Maneja datos, consultas, conversiones
- **Service**: Maneja lógica de negocio compleja

### **5. Mantenibilidad**
- Cambios de consultas en un solo lugar
- Fácil agregar nuevos métodos
- Código más organizado y limpio

---

## 🏗️ Organización para Módulos Grandes

### **¿Qué hacer cuando el módulo es muy grande?**

Para módulos grandes (como nómina, contabilidad, etc.) que abarcan muchos procesos y requieren muchas consultas, **NO** debes tener todas las consultas en un solo archivo Repository.

### **Opción 1: Repositories Especializados (Recomendado)**

```
app/Modules/Payroll/
├── Domain/
│   └── Repositories/
│       ├── PayrollRepositoryInterface.php
│       ├── EmployeePayrollRepositoryInterface.php
│       ├── DeductionRepositoryInterface.php
│       ├── PaymentRepositoryInterface.php
│       └── ReportRepositoryInterface.php
└── Infrastructure/
    └── Database/
        └── Repositories/
            ├── EloquentPayrollRepository.php
            ├── EloquentEmployeePayrollRepository.php
            ├── EloquentDeductionRepository.php
            ├── EloquentPaymentRepository.php
            └── EloquentReportRepository.php
```

### **Criterios para Organizar Repositories:**

#### **Por Entidad de Negocio:**
```
PayrollRepository          → Nóminas
EmployeePayrollRepository  → Empleados en nómina
DeductionRepository        → Deducciones
PaymentRepository          → Pagos
TaxRepository             → Impuestos
```

#### **Por Funcionalidad:**
```
PayrollRepository     → CRUD básico
CalculationRepository → Cálculos y fórmulas
ReportRepository      → Reportes y consultas complejas
ExportRepository      → Exportación de datos
```

### **Ejemplo Práctico - Módulo de Nómina:**

#### **1. Repository de Nómina Principal:**
```php
// PayrollRepositoryInterface.php
interface PayrollRepositoryInterface
{
    // CRUD básico
    public function findById(string $id): ?Payroll;
    public function save(Payroll $payroll): void;
    public function delete(string $id): void;
    
    // Búsquedas
    public function findByPeriod(string $period): array;
    public function findByEmployee(string $employeeId): array;
    public function findByStatus(string $status): array;
    
    // Consultas específicas
    public function findPendingPayrolls(): array;
    public function findApprovedPayrolls(string $period): array;
}
```

#### **2. Repository de Deducciones:**
```php
// DeductionRepositoryInterface.php
interface DeductionRepositoryInterface
{
    public function findByPayroll(string $payrollId): array;
    public function findByType(string $type): array;
    public function calculateTotalDeductions(string $payrollId): float;
    public function save(Deduction $deduction): void;
    public function delete(string $id): void;
}
```

#### **3. Repository de Reportes:**
```php
// ReportRepositoryInterface.php
interface ReportRepositoryInterface
{
    public function getPayrollSummary(string $period): array;
    public function getEmployeeEarnings(string $employeeId, string $period): array;
    public function getTaxReport(string $period): array;
    public function getDeductionReport(string $period): array;
    public function getPayrollHistory(string $employeeId): array;
}
```

### **Uso en Services:**

```php
class PayrollService
{
    public function __construct(
        private PayrollRepositoryInterface $payrollRepository,
        private DeductionRepositoryInterface $deductionRepository,
        private PaymentRepositoryInterface $paymentRepository
    ) {}

    public function createPayroll(array $data): Payroll {
        $payroll = new Payroll(/* ... */);
        $this->payrollRepository->save($payroll);
        
        // Crear deducciones
        foreach ($data['deductions'] as $deductionData) {
            $deduction = new Deduction(/* ... */);
            $this->deductionRepository->save($deduction);
        }
        
        return $payroll;
    }
}
```

### **Service Provider para Múltiples Repositories:**

```php
class PayrollServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PayrollRepositoryInterface::class, EloquentPayrollRepository::class);
        $this->app->bind(DeductionRepositoryInterface::class, EloquentDeductionRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, EloquentPaymentRepository::class);
        $this->app->bind(ReportRepositoryInterface::class, EloquentReportRepository::class);
    }
}
```

### **Recomendaciones para Módulos Grandes:**

1. **Tamaño del Repository**: Máximo 15-20 métodos por Repository
2. **Naming Convention**: Usa nombres descriptivos por entidad o funcionalidad
3. **División**: Si un Repository tiene más de 20 métodos, divídelo
4. **Consistencia**: Mantén el mismo patrón en todos los Repositories
5. **Documentación**: Documenta cada Repository con su propósito

---

## 🔧 Implementaciones Alternativas

### **Query Builder (Más eficiente)**
```php
class QueryBuilderEmployeeRepository implements EmployeeRepositoryInterface {
    public function findById(string $id): ?Employee {
        $data = DB::table('employees')->where('id', $id)->first();
        return $data ? $this->toDomain($data) : null;
    }
}
```

### **Raw SQL (Máximo control)**
```php
class RawSqlEmployeeRepository implements EmployeeRepositoryInterface {
    public function findById(string $id): ?Employee {
        $data = DB::selectOne(
            'SELECT * FROM employees WHERE id = ? AND deleted_at IS NULL',
            [$id]
        );
        return $data ? $this->toDomain($data) : null;
    }
}
```

---

## 📝 Resumen

El patrón Repository en el módulo de empleados proporciona:

1. **Centralización** de la lógica de acceso a datos
2. **Reutilización** de consultas complejas
3. **Flexibilidad** para cambiar implementaciones
4. **Testabilidad** mejorada
5. **Mantenibilidad** del código
6. **Separación clara** de responsabilidades

**Los tres archivos trabajan juntos:**
- **Interface**: Define el contrato
- **Repository**: Implementa las operaciones
- **ServiceProvider**: Conecta todo en Laravel

Esta arquitectura hace que el código sea más limpio, mantenible y escalable.
