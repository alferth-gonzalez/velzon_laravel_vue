# Documentación: Repositories (Repositorios) - Guía Completa

## 📋 Índice
1. [¿Qué son los Repositories?](#qué-son-los-repositories)
2. [Características principales](#características-principales)
3. [Estructura de un Repository](#estructura-de-un-repository)
4. [¿Por qué las funciones "no hacen nada"?](#por-qué-las-funciones-no-hacen-nada)
5. [¿Dónde está la implementación real?](#dónde-está-la-implementación-real)
6. [Flujo completo de cómo funciona](#flujo-completo-de-cómo-funciona)
7. [Casos de uso típicos](#casos-de-uso-típicos)
8. [Casos de uso específicos del negocio](#casos-de-uso-específicos-del-negocio)
9. [Mejores prácticas](#mejores-prácticas)
10. [Testing de Repositories](#testing-de-repositories)
11. [Relación con otras capas](#relación-con-otras-capas)
12. [Ejemplos prácticos](#ejemplos-prácticos)
13. [Resumen](#resumen)

---

## 🎯 ¿Qué son los Repositories?

### **Definición:**
Los **Repositories** son **interfaces** que definen cómo acceder a los datos de las entidades. Son como "contratos" que especifican qué operaciones se pueden realizar con los datos, pero sin implementar cómo se hacen.

### **Características principales:**
- **Son interfaces** (no implementaciones)
- **Definen contratos** para acceso a datos
- **Son independientes** de la tecnología
- **Permiten intercambiar** implementaciones
- **Encapsulan** la lógica de acceso a datos

### **Analogía de la Vida Real:**
Los **Repositories** son como **"catálogos de biblioteca"**:

- **Definen** qué libros puedes buscar
- **Especifican** cómo buscar (por título, autor, etc.)
- **No dicen** cómo están organizados los libros
- **Puedes cambiar** la organización sin cambiar el catálogo

---

## 🧩 Características principales

### **1. Son interfaces (no implementaciones):**
```php
// ✅ BIEN - Interface del Repository
interface EmployeeRepositoryInterface
{
    public function findById(string $id): ?Employee;
    public function save(Employee $employee): void;
}

// ❌ MAL - Implementación en el Repository
class EmployeeRepositoryInterface
{
    public function findById(string $id): ?Employee
    {
        // ❌ No implementar aquí
        return EmployeeModel::find($id);
    }
}
```

**¿Por qué es importante?**
- **Separa** la definición de la implementación
- **Permite** intercambiar implementaciones
- **Facilita** el testing
- **Mantiene** la independencia de la tecnología

### **2. Retornan entidades del dominio:**
```php
interface EmployeeRepositoryInterface
{
    // ✅ BIEN - Retorna entidad del dominio
    public function findById(string $id): ?Employee;
    
    // ❌ MAL - Retorna modelo de Eloquent
    public function findById(string $id): ?EmployeeModel;
}
```

**¿Por qué es importante?**
- **Mantiene** la consistencia del dominio
- **Evita** dependencias de la infraestructura
- **Facilita** el testing
- **Permite** la evolución independiente

### **3. Son independientes de la tecnología:**
```php
interface EmployeeRepositoryInterface
{
    // ✅ BIEN - Solo define qué hacer, no cómo
    public function findById(string $id): ?Employee;
    public function save(Employee $employee): void;
    
    // ❌ MAL - Depende de Eloquent
    public function findById(string $id): ?Employee
    {
        return EmployeeModel::find($id); // ❌ Dependencia de Eloquent
    }
}
```

**¿Por qué es importante?**
- **Puede funcionar** con cualquier tecnología
- **Es portable** entre proyectos
- **Facilita** el testing
- **Permite** la evolución independiente

---

## 🏗️ Estructura de un Repository

### **Ejemplo básico:**
```php
// Interface del Repository
interface EmployeeRepositoryInterface
{
    public function findById(string $id): ?Employee;
    public function findByEmail(Email $email): ?Employee;
    public function save(Employee $employee): void;
    public function delete(string $id): void;
    public function paginate(array $filters, int $page, int $perPage): array;
}
```

### **Elementos clave:**
1. **Métodos de búsqueda** (findById, findByEmail)
2. **Métodos de persistencia** (save, delete)
3. **Métodos de consulta** (paginate, findAll)
4. **Retornan entidades** del dominio
5. **Son independientes** de la tecnología

---

## ❓ ¿Por qué las funciones "no hacen nada"?

### **El Repository Interface es solo un "contrato":**
```php
// app/Modules/Employees/Domain/Repositories/EmployeeRepositoryInterface.php
interface EmployeeRepositoryInterface
{
    public function findById(string $id): ?Employee;
    public function findByDocument(?string $tenantId, DocumentId $document): ?Employee;
    public function findByLastName(string $lastName): array;
    public function save(Employee $employee): void;
    public function delete(string $id): void;
    public function paginate(array $filters, int $page, int $perPage): array;
}
```

**Estas funciones NO hacen nada porque:**
- **Es solo una interfaz** (contrato)
- **No contiene implementación**
- **Solo define QUÉ se puede hacer, no CÓMO**

### **¿Por qué no poner la implementación directamente en la interface?**

#### **❌ MAL - Implementación en la interface:**
```php
interface EmployeeRepositoryInterface
{
    public function findById(string $id): ?Employee
    {
        // ❌ Esto viola el principio de separación
        return EmployeeModel::find($id);
    }
}
```

**Problemas:**
- **Domain depende** de Eloquent
- **No se puede intercambiar** implementaciones
- **Difícil de testear**
- **Violación** de principios DDD

#### **✅ BIEN - Interface separada:**
```php
// Domain - Solo define el contrato
interface EmployeeRepositoryInterface
{
    public function findById(string $id): ?Employee;
}

// Infrastructure - Implementa el contrato
class EloquentEmployeeRepository implements EmployeeRepositoryInterface
{
    public function findById(string $id): ?Employee
    {
        return EmployeeModel::find($id);
    }
}
```

**Ventajas:**
- **Domain es independiente** de la tecnología
- **Se puede intercambiar** implementaciones
- **Fácil de testear**
- **Cumple** principios DDD

---

## 🔗 ¿Dónde está la implementación real?

### **1. La implementación está en Infrastructure:**
```php
// app/Modules/Employees/Infrastructure/Database/Repositories/EloquentEmployeeRepository.php
class EloquentEmployeeRepository implements EmployeeRepositoryInterface
{
    public function findById(string $id): ?Employee
    {
        // ✅ AQUÍ SÍ HAY IMPLEMENTACIÓN REAL
        $model = EmployeeModel::find($id);
        return $model ? $this->toDomain($model) : null;
    }
    
    public function save(Employee $employee): void
    {
        // ✅ AQUÍ SÍ HAY IMPLEMENTACIÓN REAL
        $model = $this->toModel($employee);
        $model->save();
    }
    
    private function toDomain(EmployeeModel $model): Employee
    {
        // Convierte modelo de Eloquent a entidad de dominio
        return new Employee(
            id: $model->id,
            firstName: $model->first_name,
            lastName: $model->last_name,
            email: new Email($model->email)
        );
    }
    
    private function toModel(Employee $employee): EmployeeModel
    {
        // Convierte entidad de dominio a modelo de Eloquent
        $model = new EmployeeModel();
        $model->id = $employee->id();
        $model->first_name = $employee->firstName();
        $model->last_name = $employee->lastName();
        $model->email = $employee->email()->value();
        return $model;
    }
}
```

### **2. El Service Provider conecta ambos:**
```php
// app/Modules/Employees/EmployeesServiceProvider.php
class EmployeesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ✅ AQUÍ SE CONECTA LA INTERFACE CON LA IMPLEMENTACIÓN
        $this->app->bind(
            EmployeeRepositoryInterface::class,
            EloquentEmployeeRepository::class
        );
    }
}
```

---

## 🔄 Flujo completo de cómo funciona

### **1. Application usa la Interface:**
```php
// app/Modules/Employees/Application/Handlers/CreateEmployeeHandler.php
class CreateEmployeeHandler
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository // ✅ Usa la interface
    ) {}
    
    public function handle(CreateEmployeeCommand $command): EmployeeDTO
    {
        $employee = new Employee(/* ... */);
        
        // ✅ Llama a la interface, pero Laravel inyecta la implementación real
        $this->employeeRepository->save($employee);
        
        return $this->convertToDTO($employee);
    }
}
```

### **2. Laravel inyecta la implementación real:**
```php
// Cuando Laravel crea CreateEmployeeHandler, automáticamente:
$handler = new CreateEmployeeHandler(
    new EloquentEmployeeRepository() // ✅ Inyecta la implementación real
);
```

### **3. La implementación real ejecuta la lógica:**
```php
// EloquentEmployeeRepository::save() se ejecuta realmente
public function save(Employee $employee): void
{
    $model = $this->toModel($employee);
    $model->save(); // ✅ Esto SÍ se ejecuta en la base de datos
}
```

### **4. Ejemplo práctico completo:**

#### **Escenario: "Crear un empleado"**

**1. Usuario hace petición:**
```http
POST /api/employees
{
    "first_name": "Juan",
    "last_name": "Pérez",
    "email": "juan@email.com"
}
```

**2. Controller crea Command:**
```php
class EmployeeController extends Controller
{
    public function store(Request $request)
    {
        $command = new CreateEmployeeCommand(/* ... */);
        $handler = app(CreateEmployeeHandler::class); // Laravel resuelve las dependencias
        $dto = $handler->handle($command);
        return response()->json(['data' => $dto->toArray()]);
    }
}
```

**3. Laravel inyecta dependencias:**
```php
// Laravel automáticamente hace esto:
$handler = new CreateEmployeeHandler(
    new EloquentEmployeeRepository() // ✅ Inyecta la implementación real
);
```

**4. Handler usa la interface:**
```php
class CreateEmployeeHandler
{
    public function handle(CreateEmployeeCommand $command): EmployeeDTO
    {
        $employee = new Employee(/* ... */);
        
        // ✅ Esta llamada va a EloquentEmployeeRepository::save()
        $this->employeeRepository->save($employee);
        
        return $this->convertToDTO($employee);
    }
}
```

**5. Implementación real ejecuta:**
```php
class EloquentEmployeeRepository implements EmployeeRepositoryInterface
{
    public function save(Employee $employee): void
    {
        // ✅ ESTO SÍ SE EJECUTA EN LA BASE DE DATOS
        $model = $this->toModel($employee);
        $model->save();
    }
}
```

---

## 📋 Casos de uso típicos

### **1. Búsqueda por ID:**
```php
interface EmployeeRepositoryInterface
{
    public function findById(string $id): ?Employee;
}
```

**Uso:**
```php
class GetEmployeeByIdHandler
{
    public function handle(GetEmployeeByIdQuery $query): EmployeeDTO
    {
        $employee = $this->employeeRepository->findById($query->employeeId);
        
        if (!$employee) {
            throw new EmployeeNotFoundException();
        }
        
        return $this->convertToDTO($employee);
    }
}
```

### **2. Búsqueda por email:**
```php
interface EmployeeRepositoryInterface
{
    public function findByEmail(Email $email): ?Employee;
}
```

**Uso:**
```php
class GetEmployeeByEmailHandler
{
    public function handle(GetEmployeeByEmailQuery $query): EmployeeDTO
    {
        $employee = $this->employeeRepository->findByEmail($query->email);
        
        if (!$employee) {
            throw new EmployeeNotFoundException();
        }
        
        return $this->convertToDTO($employee);
    }
}
```

### **3. Listado con filtros:**
```php
interface EmployeeRepositoryInterface
{
    public function paginate(array $filters, int $page, int $perPage): array;
}
```

**Uso:**
```php
class ListEmployeesHandler
{
    public function handle(ListEmployeesQuery $query): EmployeeListDTO
    {
        $filters = [
            'tenant_id' => $query->tenantId,
            'status' => $query->status,
            'search' => $query->search,
        ];
        
        $result = $this->employeeRepository->paginate(
            $filters,
            $query->page,
            $query->perPage
        );
        
        return $this->convertToDTO($result);
    }
}
```

### **4. Persistencia:**
```php
interface EmployeeRepositoryInterface
{
    public function save(Employee $employee): void;
    public function delete(string $id): void;
}
```

**Uso:**
```php
class CreateEmployeeHandler
{
    public function handle(CreateEmployeeCommand $command): EmployeeDTO
    {
        $employee = new Employee(/* ... */);
        
        $this->employeeRepository->save($employee);
        
        return $this->convertToDTO($employee);
    }
}
```

---

## 🎯 Casos de uso específicos del negocio

### **1. Búsqueda por múltiples criterios:**
```php
interface EmployeeRepositoryInterface
{
    public function findByDocument(?string $tenantId, DocumentId $document): ?Employee;
    public function findByLastName(string $lastName): array;
    public function findByDepartment(string $departmentId): array;
    public function findByStatus(EmployeeStatus $status): array;
}
```

### **2. Consultas complejas:**
```php
interface EmployeeRepositoryInterface
{
    public function findEligibleForPromotion(): array;
    public function findWithActiveProjects(): array;
    public function findWithPendingPayments(): array;
    public function findByHireDateRange(DateTimeImmutable $from, DateTimeImmutable $to): array;
}
```

### **3. Operaciones de agregación:**
```php
interface EmployeeRepositoryInterface
{
    public function countByStatus(EmployeeStatus $status): int;
    public function countByDepartment(string $departmentId): int;
    public function getAverageSalaryByDepartment(string $departmentId): float;
    public function getTotalEmployees(): int;
}
```

---

## ✅ Mejores prácticas

### **1. Nombres descriptivos:**
```php
// ✅ BIEN - Nombres claros
interface EmployeeRepositoryInterface
{
    public function findById(string $id): ?Employee;
    public function findByEmail(Email $email): ?Employee;
    public function findByDocument(DocumentId $document): ?Employee;
}

// ❌ MAL - Nombres confusos
interface EmployeeRepositoryInterface
{
    public function get(string $id): ?Employee;
    public function find(string $email): ?Employee;
    public function search(string $document): ?Employee;
}
```

### **2. Retornar entidades del dominio:**
```php
// ✅ BIEN - Retorna entidad del dominio
interface EmployeeRepositoryInterface
{
    public function findById(string $id): ?Employee;
    public function findAll(): array; // array de Employee
}

// ❌ MAL - Retorna modelo de Eloquent
interface EmployeeRepositoryInterface
{
    public function findById(string $id): ?EmployeeModel;
    public function findAll(): Collection; // Collection de EmployeeModel
}
```

### **3. Usar Value Objects en parámetros:**
```php
// ✅ BIEN - Usa Value Objects
interface EmployeeRepositoryInterface
{
    public function findByEmail(Email $email): ?Employee;
    public function findByDocument(DocumentId $document): ?Employee;
}

// ❌ MAL - Usa strings primitivos
interface EmployeeRepositoryInterface
{
    public function findByEmail(string $email): ?Employee;
    public function findByDocument(string $documentType, string $documentNumber): ?Employee;
}
```

### **4. Métodos específicos:**
```php
// ✅ BIEN - Métodos específicos
interface EmployeeRepositoryInterface
{
    public function findById(string $id): ?Employee;
    public function findByEmail(Email $email): ?Employee;
    public function findByDocument(DocumentId $document): ?Employee;
}

// ❌ MAL - Método genérico
interface EmployeeRepositoryInterface
{
    public function find(string $field, string $value): ?Employee;
}
```

### **5. Separar definición de implementación:**
```php
// ✅ BIEN - Interface en Domain
// app/Modules/Employees/Domain/Repositories/EmployeeRepositoryInterface.php
interface EmployeeRepositoryInterface
{
    public function findById(string $id): ?Employee;
}

// ✅ BIEN - Implementación en Infrastructure
// app/Modules/Employees/Infrastructure/Database/Repositories/EloquentEmployeeRepository.php
class EloquentEmployeeRepository implements EmployeeRepositoryInterface
{
    public function findById(string $id): ?Employee
    {
        // Implementación real
    }
}
```

---

## 🧪 Testing de Repositories

### **Test de interface:**
```php
// tests/Unit/Domain/Repositories/EmployeeRepositoryInterfaceTest.php
abstract class EmployeeRepositoryInterfaceTest extends TestCase
{
    abstract protected function createRepository(): EmployeeRepositoryInterface;
    
    public function test_finds_employee_by_id(): void
    {
        $repository = $this->createRepository();
        $employee = $this->createEmployee();
        
        $repository->save($employee);
        
        $found = $repository->findById($employee->id());
        
        $this->assertNotNull($found);
        $this->assertEquals($employee->id(), $found->id());
    }
    
    public function test_returns_null_when_employee_not_found(): void
    {
        $repository = $this->createRepository();
        
        $found = $repository->findById('non-existent-id');
        
        $this->assertNull($found);
    }
    
    public function test_saves_employee(): void
    {
        $repository = $this->createRepository();
        $employee = $this->createEmployee();
        
        $repository->save($employee);
        
        $found = $repository->findById($employee->id());
        $this->assertNotNull($found);
    }
    
    public function test_deletes_employee(): void
    {
        $repository = $this->createRepository();
        $employee = $this->createEmployee();
        
        $repository->save($employee);
        $repository->delete($employee->id());
        
        $found = $repository->findById($employee->id());
        $this->assertNull($found);
    }
    
    private function createEmployee(): Employee
    {
        return new Employee(
            id: '123',
            tenantId: 'tenant-123',
            firstName: 'Juan',
            lastName: 'Pérez',
            document: new DocumentId('CC', '12345678'),
            email: new Email('juan@email.com')
        );
    }
}
```

### **Test de implementación:**
```php
// tests/Unit/Infrastructure/Repositories/EloquentEmployeeRepositoryTest.php
class EloquentEmployeeRepositoryTest extends EmployeeRepositoryInterfaceTest
{
    protected function createRepository(): EmployeeRepositoryInterface
    {
        return new EloquentEmployeeRepository();
    }
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
    }
}
```

### **Test con mock:**
```php
// tests/Unit/Application/Handlers/CreateEmployeeHandlerTest.php
class CreateEmployeeHandlerTest extends TestCase
{
    public function test_creates_employee_successfully(): void
    {
        $mockRepository = $this->createMock(EmployeeRepositoryInterface::class);
        $mockRepository->expects($this->once())
                      ->method('save')
                      ->with($this->isInstanceOf(Employee::class));
        
        $handler = new CreateEmployeeHandler($mockRepository);
        $command = new CreateEmployeeCommand(/* ... */);
        
        $dto = $handler->handle($command);
        
        $this->assertInstanceOf(EmployeeDTO::class, $dto);
    }
}
```

---

## 🔗 Relación con otras capas

### **1. Con Application:**
```php
// Application usa el Repository Interface
class CreateEmployeeHandler
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository
    ) {}
    
    public function handle(CreateEmployeeCommand $command): EmployeeDTO
    {
        $employee = new Employee(/* ... */);
        
        // Usa el Repository Interface
        $this->employeeRepository->save($employee);
        
        return $this->convertToDTO($employee);
    }
}
```

### **2. Con Infrastructure:**
```php
// Infrastructure implementa el Repository Interface
class EloquentEmployeeRepository implements EmployeeRepositoryInterface
{
    public function findById(string $id): ?Employee
    {
        $model = EmployeeModel::find($id);
        return $model ? $this->toDomain($model) : null;
    }
    
    public function save(Employee $employee): void
    {
        $model = $this->toModel($employee);
        $model->save();
    }
}
```

### **3. Con Service Provider:**
```php
// Service Provider registra la implementación
class EmployeesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            EmployeeRepositoryInterface::class,
            EloquentEmployeeRepository::class
        );
    }
}
```

---

## 💻 Ejemplos prácticos

### **Ejemplo 1: Repository Interface completo**
```php
// app/Modules/Employees/Domain/Repositories/EmployeeRepositoryInterface.php
interface EmployeeRepositoryInterface
{
    // Búsquedas por ID
    public function findById(string $id): ?Employee;
    
    // Búsquedas por atributos únicos
    public function findByEmail(Email $email): ?Employee;
    public function findByDocument(?string $tenantId, DocumentId $document): ?Employee;
    
    // Búsquedas por criterios
    public function findByLastName(string $lastName): array;
    public function findByDepartment(string $departmentId): array;
    public function findByStatus(EmployeeStatus $status): array;
    public function findByHireDateRange(DateTimeImmutable $from, DateTimeImmutable $to): array;
    
    // Búsquedas complejas
    public function findEligibleForPromotion(): array;
    public function findWithActiveProjects(): array;
    public function findWithPendingPayments(): array;
    
    // Listados con filtros
    public function paginate(array $filters, int $page, int $perPage): array;
    public function findAll(): array;
    
    // Persistencia
    public function save(Employee $employee): void;
    public function delete(string $id): void;
    
    // Operaciones de agregación
    public function countByStatus(EmployeeStatus $status): int;
    public function countByDepartment(string $departmentId): int;
    public function getAverageSalaryByDepartment(string $departmentId): float;
    public function getTotalEmployees(): int;
}
```

### **Ejemplo 2: Implementación con Eloquent**
```php
// app/Modules/Employees/Infrastructure/Database/Repositories/EloquentEmployeeRepository.php
class EloquentEmployeeRepository implements EmployeeRepositoryInterface
{
    public function findById(string $id): ?Employee
    {
        $model = EmployeeModel::find($id);
        return $model ? $this->toDomain($model) : null;
    }
    
    public function findByEmail(Email $email): ?Employee
    {
        $model = EmployeeModel::where('email', $email->value())->first();
        return $model ? $this->toDomain($model) : null;
    }
    
    public function findByDocument(?string $tenantId, DocumentId $document): ?Employee
    {
        $query = EmployeeModel::where('document_type', $document->type())
                             ->where('document_number', $document->number());
        
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }
        
        $model = $query->first();
        return $model ? $this->toDomain($model) : null;
    }
    
    public function findByLastName(string $lastName): array
    {
        $models = EmployeeModel::where('last_name', 'like', "%$lastName%")
                              ->whereNull('deleted_at')
                              ->get();
        
        return $models->map(fn($model) => $this->toDomain($model))->toArray();
    }
    
    public function paginate(array $filters, int $page, int $perPage): array
    {
        $query = EmployeeModel::query();
        
        if (!empty($filters['tenant_id'])) {
            $query->where('tenant_id', $filters['tenant_id']);
        }
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                  ->orWhere('last_name', 'like', "%$search%");
            });
        }
        
        $total = $query->count();
        $models = $query->orderByDesc('created_at')
                       ->forPage($page, $perPage)
                       ->get();
        
        return [
            'data' => $models->map(fn($model) => $this->toDomain($model))->toArray(),
            'total' => $total,
        ];
    }
    
    public function save(Employee $employee): void
    {
        $model = $this->toModel($employee);
        $model->save();
    }
    
    public function delete(string $id): void
    {
        EmployeeModel::where('id', $id)->delete();
    }
    
    private function toDomain(EmployeeModel $model): Employee
    {
        return new Employee(
            id: $model->id,
            tenantId: $model->tenant_id,
            firstName: $model->first_name,
            lastName: $model->last_name,
            document: new DocumentId($model->document_type, $model->document_number),
            email: $model->email ? new Email($model->email) : null,
            phone: $model->phone ? new Phone($model->phone) : null,
            hireDate: $model->hire_date ? new DateTimeImmutable($model->hire_date) : null,
            status: EmployeeStatus::from($model->status),
            createdBy: $model->created_by,
            updatedBy: $model->updated_by,
            createdAt: new DateTimeImmutable($model->created_at),
            updatedAt: new DateTimeImmutable($model->updated_at),
            deletedAt: $model->deleted_at ? new DateTimeImmutable($model->deleted_at) : null
        );
    }
    
    private function toModel(Employee $employee): EmployeeModel
    {
        $model = new EmployeeModel();
        $model->id = $employee->id();
        $model->tenant_id = $employee->tenantId();
        $model->first_name = $employee->firstName();
        $model->last_name = $employee->lastName();
        $model->document_type = $employee->document()->type();
        $model->document_number = $employee->document()->number();
        $model->email = $employee->email()?->value();
        $model->phone = $employee->phone()?->value();
        $model->hire_date = $employee->hireDate()?->format('Y-m-d');
        $model->status = $employee->status()->value;
        $model->created_by = $employee->createdBy();
        $model->updated_by = $employee->updatedBy();
        $model->created_at = $employee->createdAt();
        $model->updated_at = $employee->updatedAt();
        $model->deleted_at = $employee->deletedAt();
        return $model;
    }
}
```

### **Ejemplo 3: Implementación con Query Builder**
```php
// app/Modules/Employees/Infrastructure/Database/Repositories/QueryBuilderEmployeeRepository.php
class QueryBuilderEmployeeRepository implements EmployeeRepositoryInterface
{
    public function findById(string $id): ?Employee
    {
        $data = DB::table('employees')->where('id', $id)->first();
        return $data ? $this->toDomain($data) : null;
    }
    
    public function findByEmail(Email $email): ?Employee
    {
        $data = DB::table('employees')
                  ->where('email', $email->value())
                  ->first();
        return $data ? $this->toDomain($data) : null;
    }
    
    public function paginate(array $filters, int $page, int $perPage): array
    {
        $query = DB::table('employees');
        
        if (!empty($filters['tenant_id'])) {
            $query->where('tenant_id', $filters['tenant_id']);
        }
        
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                  ->orWhere('last_name', 'like', "%$search%");
            });
        }
        
        $total = $query->count();
        $data = $query->orderByDesc('created_at')
                     ->forPage($page, $perPage)
                     ->get();
        
        return [
            'data' => $data->map(fn($row) => $this->toDomain($row))->toArray(),
            'total' => $total,
        ];
    }
    
    public function save(Employee $employee): void
    {
        $data = $this->toArray($employee);
        
        if (DB::table('employees')->where('id', $employee->id())->exists()) {
            DB::table('employees')->where('id', $employee->id())->update($data);
        } else {
            DB::table('employees')->insert($data);
        }
    }
    
    public function delete(string $id): void
    {
        DB::table('employees')->where('id', $id)->delete();
    }
    
    private function toDomain($data): Employee
    {
        return new Employee(
            id: $data->id,
            tenantId: $data->tenant_id,
            firstName: $data->first_name,
            lastName: $data->last_name,
            document: new DocumentId($data->document_type, $data->document_number),
            email: $data->email ? new Email($data->email) : null,
            phone: $data->phone ? new Phone($data->phone) : null,
            hireDate: $data->hire_date ? new DateTimeImmutable($data->hire_date) : null,
            status: EmployeeStatus::from($data->status),
            createdBy: $data->created_by,
            updatedBy: $data->updated_by,
            createdAt: new DateTimeImmutable($data->created_at),
            updatedAt: new DateTimeImmutable($data->updated_at),
            deletedAt: $data->deleted_at ? new DateTimeImmutable($data->deleted_at) : null
        );
    }
    
    private function toArray(Employee $employee): array
    {
        return [
            'id' => $employee->id(),
            'tenant_id' => $employee->tenantId(),
            'first_name' => $employee->firstName(),
            'last_name' => $employee->lastName(),
            'document_type' => $employee->document()->type(),
            'document_number' => $employee->document()->number(),
            'email' => $employee->email()?->value(),
            'phone' => $employee->phone()?->value(),
            'hire_date' => $employee->hireDate()?->format('Y-m-d'),
            'status' => $employee->status()->value,
            'created_by' => $employee->createdBy(),
            'updated_by' => $employee->updatedBy(),
            'created_at' => $employee->createdAt(),
            'updated_at' => $employee->updatedAt(),
            'deleted_at' => $employee->deletedAt(),
        ];
    }
}
```

---

## 🎯 ¿Cuándo usar Repositories?

### **✅ SÍ usar Repositories cuando:**
- **Necesitas** acceder a datos de entidades
- **Quieres** separar la lógica de acceso a datos
- **Necesitas** intercambiar implementaciones
- **Quieres** facilitar el testing
- **Necesitas** mantener la independencia de la tecnología

### **❌ NO usar Repositories cuando:**
- **Solo necesitas** operaciones simples de CRUD
- **No hay lógica** de negocio compleja
- **Es un prototipo** simple
- **No necesitas** intercambiar implementaciones

---

## 🎯 Resumen

### **🎯 Repositories son:**
- **Interfaces** que definen acceso a datos
- **Contratos** para operaciones de datos
- **Independientes** de la tecnología
- **Permiten intercambiar** implementaciones
- **Encapsulan** la lógica de acceso a datos

### **🎯 Para qué sirven:**
- Definir cómo acceder a los datos
- Separar la lógica de acceso a datos
- Facilitar el testing
- Permitir la evolución independiente
- Mantener la consistencia del dominio

### **📋 Características clave:**
- Son interfaces, no implementaciones
- Retornan entidades del dominio
- Son independientes de la tecnología
- Tienen métodos específicos
- Usan Value Objects en parámetros

### **✅ Mejores prácticas:**
- Nombres descriptivos
- Retornar entidades del dominio
- Usar Value Objects en parámetros
- Métodos específicos
- Separar definición de implementación

### **🔄 Flujo completo:**
1. **Application** usa la interface
2. **Laravel** inyecta la implementación real
3. **Infrastructure** ejecuta la lógica real
4. **Base de datos** se actualiza

**¡Los Repositories son esenciales para mantener una arquitectura limpia y permitir la evolución independiente de tu aplicación!** 🚀
