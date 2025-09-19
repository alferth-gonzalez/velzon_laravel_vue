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
