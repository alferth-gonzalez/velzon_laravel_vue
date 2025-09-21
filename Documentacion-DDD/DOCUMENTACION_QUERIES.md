# Documentación: Queries (Consultas) - Guía Completa

## 📋 Índice
1. [¿Qué son las Queries?](#qué-son-las-queries)
2. [Estructura de las Queries](#estructura-de-las-queries)
3. [Ciclo de Vida de las Queries](#ciclo-de-vida-de-las-queries)
4. [Para qué sirven las Queries](#para-qué-sirven-las-queries)
5. [Casos de Uso para Queries](#casos-de-uso-para-queries)
6. [Relación con el Ciclo de Vida del Módulo](#relación-con-el-ciclo-de-vida-del-módulo)
7. [Mejores Prácticas](#mejores-prácticas)
8. [Ejemplos Completos](#ejemplos-completos)
9. [Testing de Queries](#testing-de-queries)
10. [Resumen](#resumen)

---

## 🎯 ¿Qué son las Queries?

### **Definición:**
Las **Queries** son objetos que representan **consultas** o **peticiones de información** que el usuario quiere obtener del sistema. Son como "preguntas" que se hacen al sistema.

### **Características principales:**
- **Solo lectura**: No modifican datos
- **Específicas**: Representan una consulta concreta
- **Con parámetros**: Contienen los filtros y criterios de búsqueda
- **Sin lógica**: Solo transportan los parámetros de la consulta

### **Analogía de la Vida Real:**
Las **Queries** son como **formularios de búsqueda** en una biblioteca:

- **"Buscar libros de ciencia ficción"**
- **"Buscar libros publicados en 2023"**
- **"Buscar libros del autor Isaac Asimov"**

Cada formulario contiene **los criterios de búsqueda**, pero no ejecuta la búsqueda por sí mismo.

---

## 🏗️ Estructura de las Queries

### **Ejemplo básico:**
```php
// app/Modules/Employees/Application/Queries/GetEmployeeByIdQuery.php
final class GetEmployeeByIdQuery
{
    public function __construct(
        public readonly string $employeeId
    ) {}
}
```

### **Ejemplo más complejo:**
```php
// app/Modules/Employees/Application/Queries/ListEmployeesQuery.php
final class ListEmployeesQuery
{
    public function __construct(
        public readonly ?string $tenantId = null,
        public readonly ?string $status = null,
        public readonly ?string $search = null,
        public readonly int $page = 1,
        public readonly int $perPage = 15,
        public readonly ?string $sortBy = null,
        public readonly ?string $sortDirection = 'asc'
    ) {}
}
```

### **Estructura actual en tu proyecto:**
```php
// app/Modules/Employees/Application/Queries/ListEmployeesQuery.php
final class ListEmployeesQuery {
    public function __construct(
        public ?string $tenantId,
        public ?string $status,
        public ?string $search,
        public int $page = 1,
        public int $perPage = 15
    ) {}
}
```

---

## 🔄 Ciclo de Vida de las Queries

### **1. Creación en el Controller:**
```php
class EmployeeController extends Controller
{
    public function index(Request $request) {
        // 1. Crear Query con parámetros de búsqueda
        $query = new ListEmployeesQuery(
            tenantId: $request->input('tenant_id'),
            status: $request->input('status'),
            search: $request->input('search'),
            page: (int) $request->input('page', 1),
            perPage: (int) $request->input('per_page', 15),
            sortBy: $request->input('sort_by'),
            sortDirection: $request->input('sort_direction', 'asc')
        );

        // 2. Pasar al Handler
        $handler = app(ListEmployeesHandler::class);
        $dto = $handler->handle($query);

        // 3. Retornar respuesta
        return response()->json(['data' => $dto->toArray()]);
    }
}
```

### **2. Procesamiento en el Handler:**
```php
class ListEmployeesHandler
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository
    ) {}

    public function handle(ListEmployeesQuery $query): EmployeeListDTO
    {
        // 1. Aplicar filtros usando parámetros del Query
        $filters = [
            'tenant_id' => $query->tenantId,
            'status' => $query->status,
            'search' => $query->search,
        ];

        // 2. Buscar empleados con filtros
        $result = $this->employeeRepository->paginate(
            $filters,
            $query->page,
            $query->perPage
        );

        // 3. Crear DTO con resultados
        $dto = new EmployeeListDTO(
            employees: array_map(fn($employee) => $this->convertToDTO($employee), $result['data']),
            total: $result['total'],
            page: $query->page,
            perPage: $query->perPage
        );

        return $dto;
    }
}
```

### **3. Respuesta HTTP:**
```json
{
    "data": {
        "employees": [
            {
                "id": "123",
                "first_name": "Juan",
                "last_name": "Pérez",
                "email": "juan@email.com"
            }
        ],
        "total": 1,
        "page": 1,
        "per_page": 15
    }
}
```

---

## 🎯 Para qué sirven las Queries

### **1. Consultas de lectura:**
```php
// Obtener empleado por ID
GetEmployeeByIdQuery

// Listar empleados con filtros
ListEmployeesQuery

// Buscar empleados
SearchEmployeesQuery

// Obtener reporte
GetEmployeeReportQuery
```

### **2. Filtros y criterios de búsqueda:**
```php
// Query con múltiples filtros
final class ListEmployeesQuery
{
    public function __construct(
        public readonly ?string $tenantId = null,
        public readonly ?string $status = null,
        public readonly ?string $department = null,
        public readonly ?string $search = null,
        public readonly ?string $hireDateFrom = null,
        public readonly ?string $hireDateTo = null,
        public readonly int $page = 1,
        public readonly int $perPage = 15
    ) {}
}
```

### **3. Parámetros de paginación:**
```php
// Query con paginación
final class ListEmployeesQuery
{
    public function __construct(
        public readonly int $page = 1,
        public readonly int $perPage = 15,
        public readonly ?string $sortBy = null,
        public readonly ?string $sortDirection = 'asc'
    ) {}
}
```

---

## 📋 Casos de Uso para Queries

### **1. Consultas simples:**
```php
// Obtener empleado por ID
GetEmployeeByIdQuery

// Obtener empleado por email
GetEmployeeByEmailQuery

// Obtener empleado por documento
GetEmployeeByDocumentQuery
```

### **2. Consultas con filtros:**
```php
// Listar empleados activos
ListActiveEmployeesQuery

// Listar empleados por departamento
ListEmployeesByDepartmentQuery

// Listar empleados por rango de fechas
ListEmployeesByDateRangeQuery
```

### **3. Consultas de búsqueda:**
```php
// Buscar empleados por texto
SearchEmployeesQuery

// Buscar empleados por múltiples criterios
AdvancedSearchEmployeesQuery
```

### **4. Consultas de reportes:**
```php
// Obtener reporte de empleados
GetEmployeeReportQuery

// Obtener estadísticas
GetEmployeeStatsQuery

// Obtener resumen
GetEmployeeSummaryQuery
```

---

## 🔗 Relación con el Ciclo de Vida del Módulo

### **1. En el Módulo:**
```php
// app/Modules/Employees/Application/Queries/
├── GetEmployeeByIdQuery.php
├── ListEmployeesQuery.php
├── SearchEmployeesQuery.php
├── GetEmployeeReportQuery.php
└── GetEmployeeStatsQuery.php
```

### **2. En el Service Provider:**
```php
class EmployeesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Las Queries no necesitan registro especial
        // Se instancian directamente cuando se necesitan
    }
}
```

### **3. En los Controllers:**
```php
class EmployeeController extends Controller
{
    public function index(Request $request) {
        $query = new ListEmployeesQuery(/* ... */);
        $handler = app(ListEmployeesHandler::class);
        $dto = $handler->handle($query);
        return response()->json(['data' => $dto->toArray()]);
    }

    public function show(string $id) {
        $query = new GetEmployeeByIdQuery($id);
        $handler = app(GetEmployeeByIdHandler::class);
        $dto = $handler->handle($query);
        return response()->json(['data' => $dto->toArray()]);
    }
}
```

### **4. En los Handlers:**
```php
class GetEmployeeByIdHandler
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository
    ) {}

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

---

## ✅ Mejores Prácticas

### **1. Queries específicas:**
```php
// ✅ BIEN - Queries específicas
GetEmployeeByIdQuery
ListEmployeesQuery
SearchEmployeesQuery

// ❌ MAL - Query genérica
GenericQuery
DataQuery
```

### **2. Queries inmutables:**
```php
// ✅ BIEN - Inmutable
final class GetEmployeeByIdQuery
{
    public function __construct(
        public readonly string $employeeId
    ) {}
}

// ❌ MAL - Mutable
final class GetEmployeeByIdQuery
{
    public string $employeeId;
    
    public function setEmployeeId(string $id): void {
        $this->employeeId = $id; // ❌ Puede cambiar
    }
}
```

### **3. Parámetros opcionales:**
```php
// ✅ BIEN - Parámetros opcionales con valores por defecto
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

### **4. Validación de parámetros:**
```php
// ✅ BIEN - Validación en el constructor
final class GetEmployeeByIdQuery
{
    public function __construct(
        public readonly string $employeeId
    ) {
        if (empty($employeeId)) {
            throw new InvalidArgumentException('Employee ID is required');
        }
    }
}
```

### **5. Nombres descriptivos:**
```php
// ✅ BIEN - Nombres claros
GetEmployeeByIdQuery
ListEmployeesByDepartmentQuery
SearchEmployeesByTextQuery

// ❌ MAL - Nombres confusos
GetQuery
ListQuery
SearchQuery
```

### **6. Una Query por consulta:**
```php
// ✅ BIEN - Una Query específica
GetEmployeeByIdQuery
GetEmployeeByEmailQuery

// ❌ MAL - Query genérica
GetEmployeeQuery // ¿Por ID? ¿Por email? ¿Por qué?
```

---

## 💻 Ejemplos Completos

### **Ejemplo 1: Query Simple**
```php
// app/Modules/Employees/Application/Queries/GetEmployeeByIdQuery.php
final class GetEmployeeByIdQuery
{
    public function __construct(
        public readonly string $employeeId
    ) {
        if (empty($employeeId)) {
            throw new InvalidArgumentException('Employee ID is required');
        }
    }

    public function toArray(): array
    {
        return [
            'employee_id' => $this->employeeId,
        ];
    }
}
```

### **Ejemplo 2: Query Compleja**
```php
// app/Modules/Employees/Application/Queries/ListEmployeesQuery.php
final class ListEmployeesQuery
{
    public function __construct(
        public readonly ?string $tenantId = null,
        public readonly ?string $status = null,
        public readonly ?string $department = null,
        public readonly ?string $search = null,
        public readonly ?string $hireDateFrom = null,
        public readonly ?string $hireDateTo = null,
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
        
        if ($this->sortDirection && !in_array($this->sortDirection, ['asc', 'desc'])) {
            throw new InvalidArgumentException('Sort direction must be asc or desc');
        }
    }

    public function toArray(): array
    {
        return [
            'tenant_id' => $this->tenantId,
            'status' => $this->status,
            'department' => $this->department,
            'search' => $this->search,
            'hire_date_from' => $this->hireDateFrom,
            'hire_date_to' => $this->hireDateTo,
            'page' => $this->page,
            'per_page' => $this->perPage,
            'sort_by' => $this->sortBy,
            'sort_direction' => $this->sortDirection,
        ];
    }

    public function hasFilters(): bool
    {
        return !empty($this->tenantId) || 
               !empty($this->status) || 
               !empty($this->department) || 
               !empty($this->search);
    }
}
```

### **Ejemplo 3: Query de Búsqueda**
```php
// app/Modules/Employees/Application/Queries/SearchEmployeesQuery.php
final class SearchEmployeesQuery
{
    public function __construct(
        public readonly string $searchTerm,
        public readonly ?string $tenantId = null,
        public readonly ?string $status = null,
        public readonly int $page = 1,
        public readonly int $perPage = 15
    ) {
        if (empty($searchTerm)) {
            throw new InvalidArgumentException('Search term is required');
        }
    }

    public function toArray(): array
    {
        return [
            'search_term' => $this->searchTerm,
            'tenant_id' => $this->tenantId,
            'status' => $this->status,
            'page' => $this->page,
            'per_page' => $this->perPage,
        ];
    }
}
```

### **Ejemplo 4: Query de Reporte**
```php
// app/Modules/Employees/Application/Queries/GetEmployeeReportQuery.php
final class GetEmployeeReportQuery
{
    public function __construct(
        public readonly ?string $tenantId = null,
        public readonly ?string $department = null,
        public readonly ?string $status = null,
        public readonly ?string $dateFrom = null,
        public readonly ?string $dateTo = null,
        public readonly string $format = 'json' // json, csv, pdf
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->format && !in_array($this->format, ['json', 'csv', 'pdf'])) {
            throw new InvalidArgumentException('Format must be json, csv, or pdf');
        }
    }

    public function toArray(): array
    {
        return [
            'tenant_id' => $this->tenantId,
            'department' => $this->department,
            'status' => $this->status,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'format' => $this->format,
        ];
    }
}
```

---

## 🧪 Testing de Queries

### **Test de validación:**
```php
// tests/Unit/Queries/GetEmployeeByIdQueryTest.php
class GetEmployeeByIdQueryTest extends TestCase
{
    public function test_creates_query_with_valid_id(): void
    {
        $query = new GetEmployeeByIdQuery('123');
        
        $this->assertEquals('123', $query->employeeId);
    }

    public function test_throws_exception_with_empty_id(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Employee ID is required');

        new GetEmployeeByIdQuery('');
    }

    public function test_to_array_returns_correct_data(): void
    {
        $query = new GetEmployeeByIdQuery('123');
        
        $expected = ['employee_id' => '123'];
        $this->assertEquals($expected, $query->toArray());
    }
}
```

### **Test de Query compleja:**
```php
// tests/Unit/Queries/ListEmployeesQueryTest.php
class ListEmployeesQueryTest extends TestCase
{
    public function test_creates_query_with_default_values(): void
    {
        $query = new ListEmployeesQuery();
        
        $this->assertNull($query->tenantId);
        $this->assertNull($query->status);
        $this->assertEquals(1, $query->page);
        $this->assertEquals(15, $query->perPage);
    }

    public function test_creates_query_with_custom_values(): void
    {
        $query = new ListEmployeesQuery(
            tenantId: 'tenant-123',
            status: 'active',
            page: 2,
            perPage: 20
        );
        
        $this->assertEquals('tenant-123', $query->tenantId);
        $this->assertEquals('active', $query->status);
        $this->assertEquals(2, $query->page);
        $this->assertEquals(20, $query->perPage);
    }

    public function test_throws_exception_with_invalid_page(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Page must be greater than 0');

        new ListEmployeesQuery(page: 0);
    }

    public function test_throws_exception_with_invalid_per_page(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Per page must be between 1 and 100');

        new ListEmployeesQuery(perPage: 101);
    }

    public function test_has_filters_returns_true_when_filters_present(): void
    {
        $query = new ListEmployeesQuery(
            tenantId: 'tenant-123',
            status: 'active'
        );
        
        $this->assertTrue($query->hasFilters());
    }

    public function test_has_filters_returns_false_when_no_filters(): void
    {
        $query = new ListEmployeesQuery();
        
        $this->assertFalse($query->hasFilters());
    }
}
```

---

## 📊 Diferencias con otros elementos

### **Queries vs Commands:**
| Aspecto | Queries | Commands |
|---------|---------|----------|
| **Propósito** | Obtener información | Ejecutar acciones |
| **Modifica datos** | ❌ No | ✅ Sí |
| **Retorna** | Datos | Resultado de la acción |
| **Ejemplo** | `GetEmployeeByIdQuery` | `CreateEmployeeCommand` |

### **Queries vs DTOs:**
| Aspecto | Queries | DTOs |
|---------|---------|------|
| **Propósito** | Solicitar datos | Transportar datos |
| **Dirección** | Entrada (input) | Salida (output) |
| **Contiene** | Parámetros de búsqueda | Datos encontrados |
| **Ejemplo** | `ListEmployeesQuery` | `EmployeeDTO` |

### **Queries vs Handlers:**
| Aspecto | Queries | Handlers |
|---------|---------|----------|
| **Propósito** | Transportar parámetros | Procesar la consulta |
| **Contiene lógica** | ❌ No | ✅ Sí |
| **Ejecuta** | ❌ No | ✅ Sí |
| **Ejemplo** | `GetEmployeeByIdQuery` | `GetEmployeeByIdHandler` |

---

## 🎯 Resumen

### **🎯 Queries son:**
- **Objetos de consulta** que representan peticiones de información
- **Solo lectura**, no modifican datos
- **Específicas** para cada tipo de consulta
- **Con parámetros** para filtros y criterios

### **🎯 Para qué sirven:**
- Consultas de lectura
- Filtros y criterios de búsqueda
- Parámetros de paginación
- Consultas de reportes

### **📋 Casos de uso:**
- Obtener datos por ID
- Listar con filtros
- Buscar por criterios
- Generar reportes

### **🔄 En el ciclo de vida:**
- Se crean en Controllers
- Se procesan en Handlers
- Se usan para consultar datos
- Se destruyen automáticamente

### **✅ Mejores prácticas:**
- Queries específicas para cada consulta
- Queries inmutables
- Parámetros opcionales con valores por defecto
- Validación de parámetros
- Nombres descriptivos
- Una Query por consulta

### **🔗 Relación con otros elementos:**
- **Controllers**: Crean las Queries
- **Handlers**: Procesan las Queries
- **Repositories**: Ejecutan las consultas
- **DTOs**: Transportan los resultados

**Las Queries son esenciales para manejar consultas de lectura de forma estructurada y mantenible en la arquitectura DDD.**
