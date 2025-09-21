# 📚 DOCUMENTACIÓN COMPLETA - MÓDULO DE EMPLEADOS

## 🏗️ ARQUITECTURA DEL MÓDULO

### 📁 Estructura de Directorios
```
app/Modules/Employees/
├── Application/           # Capa de Aplicación (Casos de Uso)
│   ├── Commands/         # Comandos (DTOs de entrada)
│   ├── Handlers/         # Manejadores de comandos
│   └── DTOs/            # Objetos de transferencia de datos
├── Domain/              # Capa de Dominio (Lógica de Negocio)
│   ├── Entities/        # Entidades del dominio
│   ├── ValueObjects/    # Objetos de valor
│   ├── Repositories/    # Interfaces de repositorios
│   └── Exceptions/      # Excepciones del dominio
├── Infrastructure/      # Capa de Infraestructura
│   ├── Http/           # Controladores, requests, recursos
│   ├── Database/       # Modelos, repositorios, migraciones
│   └── ...
└── EmployeesServiceProvider.php  # Proveedor de servicios
```

## 🔄 CICLO DE VIDA COMPLETO

### 1️⃣ INGRESO AL MÓDULO (Lista de Empleados)

#### Frontend (Vue.js + Inertia)
1. **Usuario navega a `/employees`**
2. **Laravel detecta la ruta web** en `routes/web.php`:
```php
Route::get('/employees', function() {
    // Obtener datos reales desde el repositorio
    $repo = app(\App\Modules\Employees\Domain\Repositories\EmployeeRepositoryInterface::class);
    $result = $repo->paginate([], 1, 15);
    
    // Transformar datos para el frontend
    $employees = [
        'data' => array_map(fn($e) => [...], $result['data']),
        'meta' => ['total' => $result['total']]
    ];
    
    return Inertia::render('Employees/Index', ['employees' => $employees]);
})->name('employees.index');
```

#### Backend - Flujo de Datos
1. **Service Provider** (`EmployeesServiceProvider`) registra dependencias:
```php
$this->app->bind(EmployeeRepositoryInterface::class, EloquentEmployeeRepository::class);
```

2. **Repositorio** (`EloquentEmployeeRepository`) ejecuta consulta:
```php
public function paginate(array $filters, int $page, int $perPage): array {
    $q = EmployeeModel::query();
    // Aplica filtros si existen
    $total = (clone $q)->count();
    $rows = $q->orderByDesc('created_at')->forPage($page, $perPage)->get();
    
    return [
        'data' => array_map(fn($m) => $this->toDomain($m), $rows->all()),
        'total' => $total,
    ];
}
```

3. **Transformación a Entidad de Dominio**:
```php
private function toDomain(EmployeeModel $m): Employee {
    return new Employee(
        id: (string)$m->id,
        tenantId: $m->tenant_id,
        firstName: $m->first_name,
        // ... otros campos con Value Objects
        document: new DocumentId($m->document_type, $m->document_number),
        email: $m->email ? new Email($m->email) : null,
        phone: $m->phone ? new Phone($m->phone) : null,
        // ...
    );
}
```

4. **Vue Component** (`Employees/Index.vue`) recibe datos como props:
```vue
<script>
export default {
  props: {
    employees: {
      type: Object,
      required: true,
      default: () => ({ data: [], meta: { total: 0 } })
    }
  },
  computed: {
    filteredEmployees() {
      return this.employees.data || [];
    }
  }
}
</script>
```

### 2️⃣ CREAR EMPLEADO

#### Frontend → Backend
1. **Usuario hace clic en "Crear Empleado"**
2. **Vue Router** navega a `/employees/create`
3. **Componente Create.vue** muestra formulario
4. **Usuario envía formulario**:
```javascript
async submitForm() {
  const response = await fetch('/api/employees', {
    method: 'POST',
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    },
    body: JSON.stringify(this.form)
  })
}
```

#### Backend - Flujo CQRS
1. **Ruta API** intercepta request:
```php
Route::post('/', [EmployeeController::class, 'store']);
```

2. **Controller** (`EmployeeController@store`):
```php
public function store(CreateEmployeeRequest $r, CreateEmployeeHandler $handler) {
    // 1. Crear comando con datos validados
    $cmd = new CreateEmployeeCommand(
        tenantId: $r->input('tenant_id'),
        firstName: $r->input('first_name'),
        lastName: $r->input('last_name'),
        documentType: $r->input('document_type'),
        documentNumber: $r->input('document_number'),
        email: $r->input('email'),
        phone: $r->input('phone'),
        hireDate: $r->input('hire_date'),
        actorId: Auth::id()?->toString() ?? null
    );
    
    // 2. Ejecutar handler
    $dto = $handler->handle($cmd);
    
    // 3. Retornar respuesta JSON
    return response()->json(['data' => $dto], 201);
}
```

3. **Validación** (`CreateEmployeeRequest`):
```php
public function rules(): array {
    return [
        'first_name' => ['required','string','max:80'],
        'document_type' => ['required','in:CC,NIT,CE,PA,TI,RC'],
        'document_number' => ['required','string','max:32'],
        'email' => ['nullable','email','max:150'],
        // ...
    ];
}
```

4. **Handler** (`CreateEmployeeHandler`):
```php
public function handle(CreateEmployeeCommand $c): EmployeeDTO {
    // 1. Verificar duplicado por documento
    $doc = new DocumentId($c->documentType, $c->documentNumber);
    if ($this->repo->findByDocument($c->tenantId, $doc)) {
        throw new \DomainException('Empleado duplicado (documento ya existe).');
    }
    
    // 2. Crear entidad de dominio
    $id = (string) \Illuminate\Support\Str::ulid();
    $employee = Employee::create(
        id: $id,
        tenantId: $c->tenantId,
        firstName: $c->firstName,
        lastName: $c->lastName,
        document: $doc,
        email: $c->email ? new Email($c->email) : null,
        phone: $c->phone ? Phone::fromString($c->phone) : null,
        hireDate: $c->hireDate ? new \DateTimeImmutable($c->hireDate) : null,
        createdBy: $c->actorId
    );
    
    // 3. Persistir en base de datos
    $this->repo->save($employee);
    
    // 4. Retornar DTO
    return EmployeeDTO::fromDomain($employee);
}
```

5. **Entidad Employee** (`Employee::create`):
```php
public static function create(...): self {
    // Validaciones de negocio
    if (trim($firstName) === '') {
        throw new \InvalidArgumentException('first_name requerido');
    }
    
    return new self(
        id: $id,
        // ... otros campos
        status: EmployeeStatus::Active,  // Estado por defecto
        createdAt: new \DateTimeImmutable(),
        updatedAt: new \DateTimeImmutable()
    );
}
```

6. **Repositorio** (`EloquentEmployeeRepository@save`):
```php
public function save(Employee $e): void {
    // Buscar modelo existente o crear nuevo
    $m = EmployeeModel::find($e->id());
    if (!$m) {
        $m = new EmployeeModel();  // HasUlids genera ID automáticamente
    }
    
    // Mapear datos de entidad a modelo
    $m->tenant_id = $e->tenantId();
    $m->first_name = $e->firstName();
    // ... otros campos
    $m->email = $e->email()?->value();  // Value Object → string
    $m->phone = $e->phone()?->value();
    
    // Persistir en BD
    $m->save();
}
```

7. **Modelo** (`EmployeeModel`) con traits:
```php
class EmployeeModel extends Model {
    use SoftDeletes, HasUlids;  // Genera ULIDs automáticamente
    
    protected $table = 'emp_employees';
    public $incrementing = false;
    protected $keyType = 'string';
}
```

### 3️⃣ EDITAR EMPLEADO

#### Flujo Similar pero con Diferencias Clave
1. **Frontend** carga datos existentes:
```javascript
async loadEmployee() {
  const response = await fetch(`/api/employees/${this.id}`);
  const data = await response.json();
  this.employee = data.data;
}
```

2. **Backend** (`EmployeeController@show`):
```php
public function show(string $id, EmployeeRepositoryInterface $repo) {
    $e = $repo->findById($id);
    abort_unless($e, 404);
    
    return response()->json(['data' => [
        'id' => $e->id(),
        'first_name' => $e->firstName(),
        // ... transformación completa
    ]]);
}
```

3. **Update Handler** (`UpdateEmployeeHandler`):
```php
public function handle(UpdateEmployeeCommand $c): EmployeeDTO {
    // 1. Buscar empleado existente
    $e = $this->repo->findById($c->id);
    if (!$e) throw new EmployeeNotFoundException('Empleado no encontrado');
    
    // 2. Actualizar entidad (método del dominio)
    $e->update(
        firstName: $c->firstName,
        lastName: $c->lastName,
        email: $c->email ? new Email($c->email) : null,
        phone: $c->phone ? Phone::fromString($c->phone) : null,
        hireDate: $c->hireDate ? new \DateTimeImmutable($c->hireDate) : null,
        updatedBy: $c->actorId
    );
    
    // 3. Persistir cambios
    $this->repo->save($e);
    
    return EmployeeDTO::fromDomain($e);
}
```

4. **Método de Dominio** (`Employee@update`):
```php
public function update(...): void {
    // Validaciones de negocio
    if (trim($firstName) === '') {
        throw new \InvalidArgumentException('first_name requerido');
    }
    
    // Actualizar propiedades
    $this->firstName = $firstName;
    $this->lastName = $lastName;
    $this->email = $email;
    $this->phone = $phone;
    $this->hireDate = $hireDate;
    $this->updatedBy = $updatedBy;
    $this->updatedAt = new \DateTimeImmutable();  // Timestamp automático
}
```

### 4️⃣ ELIMINAR EMPLEADO

#### Soft Delete Pattern
1. **Frontend** envía DELETE request
2. **Controller** (`EmployeeController@destroy`):
```php
public function destroy(string $id, DeleteEmployeeHandler $handler) {
    $handler->handle(new DeleteEmployeeCommand($id));
    return response()->noContent();  // 204 status
}
```

3. **Delete Handler**:
```php
public function handle(DeleteEmployeeCommand $c): void {
    $this->repo->delete($c->id);  // Soft delete
}
```

4. **Repositorio** implementa soft delete:
```php
public function delete(string $id): void {
    EmployeeModel::where('id', $id)->delete();  // Laravel soft delete
}
```

## 🔧 COMPONENTES TÉCNICOS DETALLADOS

### Value Objects (Objetos de Valor)

#### DocumentId
```php
final class DocumentId {
    public function __construct(
        private readonly string $type,
        private readonly string $number
    ) {
        // Validaciones inmutables
        if ($type === '' || $number === '') {
            throw new InvalidEmployeeDataException('Documento inválido');
        }
        if (strlen($number) > 32) {
            throw new InvalidEmployeeDataException('Documento demasiado largo');
        }
    }
    
    // Métodos de acceso
    public function type(): string { return strtoupper($this->type); }
    public function number(): string { return $this->number; }
}
```

#### Email
```php
final class Email {
    public function __construct(private readonly string $value) {
        // Validación con filtro PHP
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmployeeDataException('Email inválido');
        }
    }
    
    public function value(): string { return strtolower($this->value); }
    public function __toString(): string { return $this->value(); }
}
```

#### Phone
```php
final class Phone {
    public function __construct(private readonly string $value) {
        if ($value === '' || strlen($value) > 30) {
            throw new \InvalidArgumentException('Teléfono inválido');
        }
    }
    
    // Factory method para sanitización
    public static function fromString(string $value): self {
        $sanitized = preg_replace('/\s+/', '', $value);
        return new self($sanitized);
    }
}
```

### Enum EmployeeStatus
```php
enum EmployeeStatus: string {
    case Active = 'active';
    case Inactive = 'inactive';
}
```

## 🔄 PATRONES DE DISEÑO UTILIZADOS

### 1. **CQRS (Command Query Responsibility Segregation)**
- **Commands**: CreateEmployee, UpdateEmployee, DeleteEmployee
- **Queries**: Repositorio maneja consultas
- **Handlers**: Procesan comandos específicos

### 2. **Repository Pattern**
```php
interface EmployeeRepositoryInterface {
    public function findById(string $id): ?Employee;
    public function findByDocument(?string $tenantId, DocumentId $document): ?Employee;
    public function save(Employee $e): void;
    public function delete(string $id): void;
    public function paginate(array $filters, int $page, int $perPage): array;
}
```

### 3. **Value Objects Pattern**
- DocumentId, Email, Phone encapsulan validaciones
- Inmutables (readonly properties)
- Comportamiento específico del dominio

### 4. **Factory Pattern**
```php
// Employee::create() - construcción segura
// Phone::fromString() - sanitización automática
```

### 5. **DTO Pattern**
```php
final class EmployeeDTO {
    public static function fromDomain(Employee $e): self {
        return new self(
            id: $e->id(),
            tenant_id: $e->tenantId(),
            // ... mapeo completo desde entidad
        );
    }
}
```

## 🗄️ BASE DE DATOS

### Migración
```sql
CREATE TABLE emp_employees (
    id CHAR(26) PRIMARY KEY,           -- ULID
    tenant_id CHAR(26) NULL,           -- Multitenancia
    first_name VARCHAR(80) NOT NULL,
    last_name VARCHAR(80) NULL,
    document_type VARCHAR(10) NOT NULL, -- CC, CE, PA, etc.
    document_number VARCHAR(32) NOT NULL,
    email VARCHAR(150) NULL,
    phone VARCHAR(30) NULL,
    hire_date DATE NULL,
    status VARCHAR(20) DEFAULT 'active',
    created_by CHAR(26) NULL,
    updated_by CHAR(26) NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,         -- Soft Delete
    
    UNIQUE KEY unique_tenant_doc (tenant_id, document_type, document_number)
);
```

### Modelo Eloquent
```php
class EmployeeModel extends Model {
    use SoftDeletes, HasUlids;
    
    protected $table = 'emp_employees';
    public $incrementing = false;      // No auto-increment
    protected $keyType = 'string';     // ULID como string
    
    protected $fillable = [
        'id', 'tenant_id', 'first_name', 'last_name',
        'document_type', 'document_number', 'email', 'phone',
        'hire_date', 'status', 'created_by', 'updated_by'
    ];
    
    protected $casts = [
        'hire_date' => 'date',
    ];
}
```

## 🔄 DEPENDENCY INJECTION

### Service Provider
```php
final class EmployeesServiceProvider extends ServiceProvider {
    public function register(): void {
        // Registrar binding de interfaz → implementación
        $this->app->bind(
            EmployeeRepositoryInterface::class, 
            EloquentEmployeeRepository::class
        );
    }
    
    public function boot(): void {
        // Cargar rutas, migraciones, etc.
        $this->loadRoutesFrom(__DIR__.'/Infrastructure/Http/Routes/api.php');
        $this->loadMigrationsFrom(__DIR__.'/Infrastructure/Database/Migrations');
    }
}
```

### Laravel Container Resolution
Cuando el controlador necesita `CreateEmployeeHandler`:
```php
// Laravel automáticamente resuelve:
CreateEmployeeHandler(
    EmployeeRepositoryInterface $repo  // → EloquentEmployeeRepository
)
```

## 🚨 MANEJO DE ERRORES

### Excepciones del Dominio
```php
// Domain/Exceptions/InvalidEmployeeDataException.php
class InvalidEmployeeDataException extends \DomainException {}

// Domain/Exceptions/EmployeeNotFoundException.php  
class EmployeeNotFoundException extends \DomainException {}
```

### Validación en Capas
1. **Frontend**: Validación básica de formulario
2. **Request**: Validación de formato y tipos
3. **Value Objects**: Validación de reglas de negocio
4. **Entity**: Validación de estado del dominio
5. **Repository**: Validación de unicidad en BD

## 📊 FLUJO DE DATOS COMPLETO

```
[Usuario] → [Vue Component] → [Fetch API] → [Laravel Route]
    ↓
[Controller] → [Request Validation] → [Command Creation]
    ↓
[Handler] → [Domain Entity] → [Value Objects] → [Repository]
    ↓
[Eloquent Model] → [Database] → [Response] → [JSON] → [Frontend]
```

## 🎯 BENEFICIOS DE ESTA ARQUITECTURA

1. **Separación de Responsabilidades**: Cada capa tiene un propósito específico
2. **Testabilidad**: Componentes aislados y con dependencias inyectadas
3. **Mantenibilidad**: Código organizado y patrones consistentes
4. **Escalabilidad**: Fácil agregar nuevas funcionalidades
5. **Validación Robusta**: Múltiples capas de validación
6. **Inmutabilidad**: Value Objects protegen la integridad de datos
7. **Flexibilidad**: Interfaces permiten cambiar implementaciones

Esta arquitectura implementa **Domain-Driven Design (DDD)** con **Clean Architecture**, separando claramente las preocupaciones del dominio, aplicación e infraestructura.
