# 📋 Documentación: Integración entre Dominios en DDD

## 🎯 Índice
1. [Introducción](#introducción)
2. [El Problema: Comunicación entre Dominios](#el-problema-comunicación-entre-dominios)
3. [La Solución: Patrones de Integración](#la-solución-patrones-de-integración)
4. [Ejemplo Práctico: Nómina ↔ Empleados](#ejemplo-práctico-nómina--empleados)
5. [Flujo Completo de Comunicación](#flujo-completo-de-comunicación)
6. [Migración a Microservicios](#migración-a-microservicios)
7. [Mejores Prácticas](#mejores-prácticas)
8. [Casos de Uso Comunes](#casos-de-uso-comunes)
9. [Resumen y Conclusiones](#resumen-y-conclusiones)

---

## 🎯 Introducción

En una arquitectura de **Domain-Driven Design (DDD)** con **monolito modular**, cada dominio debe mantenerse **aislado** y **independiente**. Sin embargo, en aplicaciones reales, los dominios necesitan **comunicarse** entre sí para cumplir con los casos de uso del negocio.

Esta documentación explica **cómo implementar correctamente** la comunicación entre dominios manteniendo el aislamiento, usando como ejemplo la integración entre los módulos de **Nómina** y **Empleados**.

### **¿Por qué es importante este tema?**

- **Mantiene la integridad** de cada dominio
- **Facilita la evolución** independiente de cada módulo
- **Permite la migración** futura a microservicios
- **Mejora la testabilidad** y mantenibilidad del código
- **Reduce el acoplamiento** entre módulos

---

## 🚨 El Problema: Comunicación entre Dominios

### **El Dilema Fundamental**

En DDD, cada dominio debe ser:
- **Autónomo**: Contiene toda su lógica de negocio
- **Aislado**: No depende directamente de otros dominios
- **Cohesivo**: Todas sus partes están relacionadas

Pero en la práctica, los dominios necesitan información de otros dominios:

```
Módulo Nómina necesita:
├── Lista de empleados activos
├── Fechas de contratación
├── Salarios base
├── Información de vacaciones
└── Datos de ausentismos
```

### **❌ Soluciones Incorrectas**

#### **1. Acceso Directo (Violación del Aislamiento)**
```php
// ❌ MAL - Nómina accede directamente a Empleados
class PayrollService
{
    public function calculatePayroll()
    {
        // Violación: Acceso directo al dominio de Empleados
        $employees = Employee::where('status', 'active')->get();
        // ...
    }
}
```

**Problemas:**
- **Acoplamiento fuerte** entre dominios
- **Violación del principio** de aislamiento
- **Difícil de testear** independientemente
- **Imposible migrar** a microservicios

#### **2. Compartir Entidades (Violación de la Autonomía)**
```php
// ❌ MAL - Compartir entidades entre dominios
class PayrollService
{
    public function calculatePayroll(Employee $employee) // ← Entidad de otro dominio
    {
        // ...
    }
}
```

**Problemas:**
- **Dependencia directa** de la estructura de otro dominio
- **Cambios en Empleados** rompen Nómina
- **Violación de la encapsulación**

---

## ✅ La Solución: Patrones de Integración

### **1. Anti-Corruption Layer (Capa Anti-Corrupción)**

#### **¿Qué es?**
Una **capa de traducción** que convierte la información de un dominio al formato que entiende otro dominio.

#### **¿Por qué se llama "Anti-Corruption"?**
- **Previene la "corrupción"** del dominio consumidor
- **Mantiene la pureza** de cada dominio
- **Evita que** un dominio se "contamine" con la lógica de otro

#### **¿Cómo funciona?**
```
Dominio A (Empleados) → Anti-Corruption Layer → Dominio B (Nómina)
     ↓                        ↓                        ↓
  Entidades              DTOs/Interfaces          Lógica de Negocio
  Repositorios           Servicios de              Cálculos
  Eventos                Integración              Resultados
```

### **2. Event-Driven Communication (Comunicación por Eventos)**

#### **¿Qué es?**
Los dominios se comunican a través de **eventos** que representan algo importante que ocurrió.

#### **¿Cómo funciona?**
```
Empleados crea empleado → Emite evento → Nómina escucha evento → Procesa información
```

### **3. Application Services (Servicios de Aplicación)**

#### **¿Qué es?**
Servicios que **orquestan** la comunicación entre dominios sin violar el aislamiento.

#### **¿Cómo funciona?**
```
Controller → Application Service → Domain Service A → Domain Service B
```

---

## 🏗️ Ejemplo Práctico: Nómina ↔ Empleados

### **Caso de Uso:**
Calcular bono por antigüedad para empleados con más de 5 años en la empresa.

### **Requisitos:**
- Nómina necesita información de Empleados
- Mantener el aislamiento entre dominios
- Permitir evolución independiente
- Facilitar testing y mantenimiento

---

## 🔧 Implementación Paso a Paso

### **Paso 1: Definir la Interfaz de Integración**

#### **¿Qué es?**
Un **"contrato"** que define qué información puede solicitar Nómina a Empleados.

#### **¿Por qué es necesario?**
- **Define claramente** qué datos necesita Nómina
- **Oculta la complejidad** del dominio de Empleados
- **Permite cambiar** la implementación sin afectar Nómina
- **Facilita el testing** (puedes crear mocks)

#### **Implementación:**
```php
<?php
// app/Modules/Payroll/Domain/Integration/Services/EmployeeInformationServiceInterface.php
namespace App\Modules\Payroll\Domain\Integration\Services;

use App\Modules\Payroll\Domain\Integration\DTOs\EmployeeData;

interface EmployeeInformationServiceInterface
{
    /**
     * Obtiene un empleado por ID desde el dominio de Empleados
     * 
     * @param string $employeeId ID del empleado
     * @return EmployeeData|null Datos del empleado o null si no existe
     */
    public function getEmployeeById(string $employeeId): ?EmployeeData;
    
    /**
     * Obtiene empleados con mínimo de años de antigüedad
     * 
     * @param int $minimumYears Años mínimos de antigüedad
     * @param string|null $tenantId ID del tenant (opcional)
     * @return array Lista de empleados elegibles
     */
    public function getEmployeesWithMinimumYears(int $minimumYears, ?string $tenantId = null): array;
    
    /**
     * Verifica si un empleado está activo
     * 
     * @param string $employeeId ID del empleado
     * @return bool True si está activo, false en caso contrario
     */
    public function isEmployeeActive(string $employeeId): bool;
    
    /**
     * Obtiene el salario base de un empleado
     * 
     * @param string $employeeId ID del empleado
     * @return Money|null Salario base o null si no está disponible
     */
    public function getEmployeeBaseSalary(string $employeeId): ?Money;
}
```

**Piénsalo como:** Un menú de restaurante. Nómina ve el menú (interfaz) pero no sabe cómo se cocina la comida (implementación).

### **Paso 2: Crear DTOs para Transferencia de Datos**

#### **¿Qué es?**
Un **"sobre"** que contiene solo la información que Nómina necesita, sin exponer la estructura interna de Empleados.

#### **¿Por qué es necesario?**
- **Protege** la estructura interna de Empleados
- **Solo expone** los datos que Nómina necesita
- **Evita acoplamiento** - si Empleados cambia su estructura, Nómina no se ve afectada
- **Facilita la serialización** (convertir a JSON)

#### **Implementación:**
```php
<?php
// app/Modules/Payroll/Domain/Integration/DTOs/EmployeeData.php
namespace App\Modules\Payroll\Domain\Integration\DTOs;

use App\Modules\Payroll\Domain\ValueObjects\Money;

class EmployeeData
{
    public function __construct(
        public readonly string $id,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly ?string $email,
        public readonly string $status,
        public readonly ?\DateTimeImmutable $hireDate,
        public readonly ?Money $baseSalary,
        public readonly ?int $yearsOfService = null
    ) {}

    /**
     * Verifica si el empleado está activo
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Obtiene el nombre completo del empleado
     */
    public function getFullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }

    /**
     * Verifica si el empleado tiene los años mínimos requeridos
     */
    public function hasMinimumYears(int $minimumYears): bool
    {
        return $this->yearsOfService !== null && $this->yearsOfService >= $minimumYears;
    }
}
```

**Piénsalo como:** Un formulario que llenas en el banco. Solo pones la información necesaria, no toda tu vida personal.

### **Paso 3: Implementar el Anti-Corruption Layer**

#### **¿Qué es?**
Es la **"capa de traducción"** que convierte la información de Empleados al formato que entiende Nómina.

#### **¿Por qué se llama "Anti-Corruption"?**
- **Previene la "corrupción"** del dominio de Nómina
- **Mantiene la pureza** de cada dominio
- **Evita que** Nómina se "contamine" con la lógica de Empleados

#### **Implementación:**
```php
<?php
// app/Modules/Payroll/Infrastructure/Integration/EmployeeInformationService.php
namespace App\Modules\Payroll\Infrastructure\Integration;

use App\Modules\Employees\Domain\Repositories\EmployeeRepositoryInterface;
use App\Modules\Payroll\Domain\Integration\Services\EmployeeInformationServiceInterface;
use App\Modules\Payroll\Domain\Integration\DTOs\EmployeeData;
use App\Modules\Payroll\Domain\ValueObjects\Money;
use Carbon\Carbon;

class EmployeeInformationService implements EmployeeInformationServiceInterface
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository
    ) {}

    /**
     * Obtiene un empleado específico desde el dominio de Empleados
     */
    public function getEmployeeById(string $employeeId): ?EmployeeData
    {
        // 1. PEDIR información al dominio de Empleados
        $employee = $this->employeeRepository->findById($employeeId);
        
        if (!$employee) {
            return null;
        }

        // 2. TRANSFORMAR la información al formato de Nómina
        return $this->transformToEmployeeData($employee);
    }

    /**
     * Obtiene empleados con mínimo de años de antigüedad
     */
    public function getEmployeesWithMinimumYears(int $minimumYears, ?string $tenantId = null): array
    {
        // 1. Obtener empleados activos desde el dominio de Empleados
        $employees = $this->employeeRepository->findActiveEmployees($tenantId);
        
        $eligibleEmployees = [];
        $cutoffDate = Carbon::now()->subYears($minimumYears);

        foreach ($employees as $employee) {
            // 2. Verificar antigüedad usando lógica del dominio de Empleados
            if ($this->isEmployeeEligibleForSeniority($employee, $cutoffDate)) {
                $yearsOfService = $this->calculateYearsOfService($employee->hireDate());
                
                $eligibleEmployees[] = new EmployeeData(
                    id: $employee->id(),
                    firstName: $employee->firstName(),
                    lastName: $employee->lastName(),
                    email: $employee->email()?->value(),
                    status: $employee->status()->value,
                    hireDate: $employee->hireDate(),
                    baseSalary: $this->getEmployeeBaseSalary($employee->id()),
                    yearsOfService: $yearsOfService
                );
            }
        }

        return $eligibleEmployees;
    }

    /**
     * Verifica si un empleado está activo
     */
    public function isEmployeeActive(string $employeeId): bool
    {
        $employee = $this->employeeRepository->findById($employeeId);
        return $employee && $employee->status()->value === 'active';
    }

    /**
     * Obtiene el salario base de un empleado
     */
    public function getEmployeeBaseSalary(string $employeeId): ?Money
    {
        // Aquí podrías obtener el salario desde:
        // 1. Un módulo de Salarios
        // 2. Configuración del empleado
        // 3. API externa
        // Por simplicidad, asumimos un salario base
        return new Money(3000000, 'COP');
    }

    /**
     * Transforma una entidad de Empleados a DTO de Nómina
     */
    private function transformToEmployeeData($employee): EmployeeData
    {
        return new EmployeeData(
            id: $employee->id(),
            firstName: $employee->firstName(),
            lastName: $employee->lastName(),
            email: $employee->email()?->value(),
            status: $employee->status()->value,
            hireDate: $employee->hireDate(),
            baseSalary: $this->getEmployeeBaseSalary($employee->id())
        );
    }

    /**
     * Verifica si un empleado es elegible para bono por antigüedad
     */
    private function isEmployeeEligibleForSeniority($employee, \DateTimeImmutable $cutoffDate): bool
    {
        return $employee->hireDate() && $employee->hireDate() <= $cutoffDate;
    }

    /**
     * Calcula años de servicio
     */
    private function calculateYearsOfService(\DateTimeImmutable $hireDate): int
    {
        $now = new \DateTimeImmutable();
        $interval = $now->diff($hireDate);
        return $interval->y;
    }
}
```

**Piénsalo como:** Un traductor que convierte el español (Empleados) al inglés (Nómina), manteniendo el significado pero usando el idioma correcto.

### **Paso 4: Implementar el Servicio de Dominio de Nómina**

#### **¿Qué es?**
Es donde **Nómina usa** la información de Empleados para hacer sus cálculos.

#### **¿Por qué es importante?**
- **Contiene la lógica de negocio** de Nómina
- **No sabe** cómo se obtiene la información de Empleados
- **Solo se preocupa** por sus propios cálculos

#### **Implementación:**
```php
<?php
// app/Modules/Payroll/Domain/Core/Services/SeniorityBonusService.php
namespace App\Modules\Payroll\Domain\Core\Services;

use App\Modules\Payroll\Domain\Integration\Services\EmployeeInformationServiceInterface;
use App\Modules\Payroll\Domain\Integration\DTOs\EmployeeData;

class SeniorityBonusService
{
    public function __construct(
        private EmployeeInformationServiceInterface $employeeService
    ) {}

    /**
     * Calcula bono por antigüedad para empleados elegibles
     */
    public function calculateSeniorityBonusForEligibleEmployees(
        int $minimumYears,
        float $bonusPercentage,
        ?string $tenantId = null
    ): array {
        // 1. SOLICITAR información al dominio de Empleados
        $eligibleEmployees = $this->employeeService->getEmployeesWithMinimumYears(
            $minimumYears,
            $tenantId
        );

        $employeesWithBonus = [];

        foreach ($eligibleEmployees as $employeeData) {
            // 2. APLICAR lógica de negocio de Nómina
            $bonusCalculation = $this->calculateBonusForEmployee($employeeData, $bonusPercentage);
            
            $employeesWithBonus[] = new SeniorityBonusEmployeeDTO(
                employeeId: $employeeData->id,
                firstName: $employeeData->firstName,
                lastName: $employeeData->lastName,
                hireDate: $employeeData->hireDate,
                yearsOfService: $employeeData->yearsOfService,
                baseSalary: $employeeData->baseSalary,
                bonusAmount: $bonusCalculation->bonusAmount,
                totalWithBonus: $bonusCalculation->totalWithBonus
            );
        }

        return $employeesWithBonus;
    }

    /**
     * Calcula bono para un empleado específico
     */
    private function calculateBonusForEmployee(EmployeeData $employee, float $bonusPercentage): SeniorityBonusCalculation
    {
        // Validar que el empleado sea elegible
        if (!$employee->isActive() || !$employee->baseSalary) {
            throw new EmployeeNotEligibleForBonusException(
                "Employee {$employee->id} is not eligible for bonus"
            );
        }

        // Calcular monto del bono
        $bonusAmount = new Money(
            $employee->baseSalary->getAmount() * ($bonusPercentage / 100),
            $employee->baseSalary->getCurrency()
        );

        // Calcular total con bono
        $totalWithBonus = $employee->baseSalary->add($bonusAmount);

        return new SeniorityBonusCalculation(
            yearsOfService: $employee->yearsOfService,
            bonusAmount: $bonusAmount,
            totalWithBonus: $totalWithBonus
        );
    }
}
```

**Piénsalo como:** Un chef que pide ingredientes al almacén. No le importa cómo se consiguieron los ingredientes, solo los usa para cocinar.

### **Paso 5: Registrar Dependencias en ServiceProvider**

#### **¿Qué es?**
Es donde **Laravel conecta** la interfaz con la implementación.

#### **¿Por qué es necesario?**
- **Laravel necesita saber** qué clase usar cuando se pide la interfaz
- **Permite cambiar** la implementación sin tocar el código
- **Facilita el testing** (puedes inyectar mocks)

#### **Implementación:**
```php
<?php
// app/Modules/Payroll/PayrollServiceProvider.php
namespace App\Modules\Payroll;

use Illuminate\Support\ServiceProvider;
use App\Modules\Payroll\Domain\Integration\Services\EmployeeInformationServiceInterface;
use App\Modules\Payroll\Infrastructure\Integration\EmployeeInformationService;

class PayrollServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Registrar el servicio de integración
        $this->app->bind(
            EmployeeInformationServiceInterface::class,
            EmployeeInformationService::class
        );

        // Registrar otros servicios del módulo
        $this->app->bind(SeniorityBonusService::class);
        $this->app->bind(GetSeniorityBonusEmployeesQuery::class);
        $this->app->bind(GetSeniorityBonusEmployeesHandler::class);
    }

    public function boot(): void
    {
        // Cargar rutas del módulo
        $this->loadRoutesFrom(__DIR__.'/Infrastructure/Http/Routes/api.php');
        
        // Cargar migraciones del módulo
        $this->loadMigrationsFrom(__DIR__.'/Infrastructure/Database/Migrations');
        
        // Registrar event listeners si es necesario
        // Event::listen(EmployeeCreated::class, EmployeeCreatedHandler::class);
    }
}
```

**Piénsalo como:** Un directorio telefónico. Cuando alguien busca "EmployeeInformationService", Laravel sabe que debe usar "EmployeeInformationService".

---

## 🔄 Flujo Completo de Comunicación

### **Paso a Paso del Flujo:**

```
1. HTTP Request → Controller
   ↓
2. Controller → Query Handler
   ↓
3. Query Handler → Domain Service (Nómina)
   ↓
4. Domain Service → Integration Service
   ↓
5. Integration Service → Employee Repository (Empleados)
   ↓
6. Employee Repository → Database
   ↓
7. Database → Employee Repository
   ↓
8. Employee Repository → Integration Service
   ↓
9. Integration Service → Transform to DTO
   ↓
10. DTO → Domain Service (Nómina)
    ↓
11. Domain Service → Apply Business Logic
    ↓
12. Business Logic → Query Handler
    ↓
13. Query Handler → Controller
    ↓
14. Controller → HTTP Response
```

### **Ejemplo Práctico del Flujo:**

```php
<?php
// 1. HTTP Request
GET /api/payroll/employees/seniority-bonus

// 2. Controller
class PayrollController extends Controller
{
    public function getSeniorityBonusEmployees(Request $request)
    {
        // 3. Crear Query
        $query = new GetSeniorityBonusEmployeesQuery(
            minimumYears: 5,
            bonusPercentage: 10.0,
            tenantId: auth()->user()->tenant_id
        );

        // 4. Ejecutar Query
        $employees = $this->queryBus->dispatch($query);

        // 5. Transformar a Resource
        return new SeniorityBonusEmployeeCollection($employees);
    }
}

// 6. Query Handler
class GetSeniorityBonusEmployeesHandler
{
    public function handle(GetSeniorityBonusEmployeesQuery $query): array
    {
        // 7. Llamar al servicio de dominio
        return $this->seniorityService->calculateSeniorityBonusForEligibleEmployees(
            minimumYears: $query->minimumYears,
            bonusPercentage: $query->bonusPercentage,
            tenantId: $query->tenantId
        );
    }
}

// 8. Domain Service
class SeniorityBonusService
{
    public function calculateSeniorityBonusForEligibleEmployees(int $minimumYears): array
    {
        // 9. SOLICITAR información al dominio de Empleados
        $eligibleEmployees = $this->employeeService->getEmployeesWithMinimumYears($minimumYears);
        
        // 10. APLICAR lógica de negocio de Nómina
        foreach ($eligibleEmployees as $employee) {
            $bonus = $this->calculateBonus($employee);
            // ...
        }
    }
}
```

---

## 🚀 Migración a Microservicios

### **¿Por qué es posible esta migración?**

La arquitectura DDD que implementamos está **diseñada específicamente** para facilitar esta separación. Cada dominio ya está **aislado** y **desacoplado**.

### **Estado Actual vs Estado Futuro**

#### **ANTES (Monolito Modular):**
```
Laravel App
├── Módulo Empleados (Laravel)
├── Módulo Nómina (Laravel)
└── Módulo Clientes (Laravel)
```

#### **DESPUÉS (Microservicios):**
```
Servicio Empleados (Laravel)     Servicio Nómina (Laravel/Node.js/Python)
├── API REST                    ├── API REST
├── Base de Datos               ├── Base de Datos
└── Eventos                     └── Eventos
```

### **¿Qué Cambia en la Comunicación?**

#### **ANTES (Comunicación Interna):**
```php
// Nómina llama directamente al repositorio de Empleados
$employee = $this->employeeRepository->findById($employeeId);
```

#### **DESPUÉS (Comunicación Externa):**
```php
// Nómina llama a la API de Empleados
$response = $this->httpClient->get("http://empleados-api.com/api/employees/{$employeeId}");
$employee = $this->transformResponseToEmployeeData($response);
```

### **Estrategia de Migración**

#### **Fase 1: Preparación**
1. **Extraer la base de datos** del módulo de Nómina
2. **Identificar dependencias** entre dominios
3. **Mapear todas las comunicaciones** existentes

#### **Fase 2: Crear el Microservicio**
1. **Nueva estructura** del proyecto de Nómina
2. **Cambiar comunicación interna** por externa
3. **Implementar patrones** de microservicios

#### **Fase 3: Implementar Patrones de Microservicios**
1. **Circuit Breaker** para resilencia
2. **Event-Driven Communication** para desacoplamiento
3. **API Gateway** para enrutamiento
4. **Saga Pattern** para transacciones distribuidas

### **Ejemplo de Implementación para Microservicios**

```php
<?php
// payroll-service/src/Infrastructure/External/EmployeeApiClient.php
class EmployeeApiClient implements EmployeeInformationServiceInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $employeeServiceUrl
    ) {}

    public function getEmployeeById(string $employeeId): ?EmployeeData
    {
        try {
            // Llamada HTTP a la API de Empleados
            $response = $this->httpClient->get(
                "{$this->employeeServiceUrl}/api/employees/{$employeeId}",
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->getAuthToken(),
                        'Content-Type' => 'application/json'
                    ]
                ]
            );

            if ($response->getStatusCode() === 404) {
                return null;
            }

            $employeeData = json_decode($response->getBody(), true);
            return $this->transformResponseToEmployeeData($employeeData);

        } catch (Exception $e) {
            // Manejar errores de red, timeouts, etc.
            throw new EmployeeServiceUnavailableException(
                "Could not fetch employee data: " . $e->getMessage()
            );
        }
    }
}
```

### **Ventajas de la Migración**

#### **✅ Beneficios:**
1. **Escalabilidad Independiente**: Nómina puede escalar sin afectar Empleados
2. **Tecnologías Diferentes**: Cada servicio puede usar la tecnología más adecuada
3. **Equipos Independientes**: Diferentes equipos pueden trabajar en cada servicio
4. **Despliegue Independiente**: Cambios en Nómina no afectan Empleados
5. **Resilencia**: Si un servicio falla, los otros siguen funcionando

#### **⚠️ Desafíos:**
1. **Complejidad de Red**: Latencia, timeouts, fallos de red
2. **Consistencia de Datos**: Transacciones distribuidas son más complejas
3. **Monitoreo**: Necesitas herramientas para monitorear múltiples servicios
4. **Debugging**: Más difícil rastrear problemas entre servicios

---

## ✅ Mejores Prácticas

### **1. Principios de Diseño**

#### **✅ DOs (Hacer)**
- **Usar interfaces** para definir contratos entre dominios
- **Crear DTOs** para transferir datos sin acoplamiento
- **Implementar Anti-Corruption Layers** para traducir entre dominios
- **Usar eventos** para comunicación asíncrona
- **Mantener dominios independientes** y cohesivos
- **Documentar las interfaces** de integración

#### **❌ DON'Ts (No hacer)**
- **No acceder directamente** a entidades de otros dominios
- **No compartir entidades** entre dominios
- **No crear dependencias circulares** entre módulos
- **No mezclar lógica de dominio** con infraestructura
- **No ignorar el manejo de errores** en la comunicación
- **No violar la encapsulación** de los dominios

### **2. Patrones de Comunicación**

#### **Síncrona (Request-Response)**
```php
// Para casos donde necesitas respuesta inmediata
$employee = $this->employeeService->getEmployeeById($employeeId);
```

#### **Asíncrona (Event-Driven)**
```php
// Para casos donde no necesitas respuesta inmediata
event(new EmployeeCreated($employee));
```

### **3. Manejo de Errores**

```php
<?php
// Ejemplo de manejo de errores en integración
class EmployeeInformationService
{
    public function getEmployeeById(string $employeeId): ?EmployeeData
    {
        try {
            $employee = $this->employeeRepository->findById($employeeId);
            return $this->transformToEmployeeData($employee);
        } catch (EmployeeNotFoundException $e) {
            // Empleado no encontrado - comportamiento esperado
            return null;
        } catch (Exception $e) {
            // Error inesperado - registrar y re-lanzar
            Log::error("Error fetching employee {$employeeId}: " . $e->getMessage());
            throw new EmployeeServiceUnavailableException(
                "Could not fetch employee data: " . $e->getMessage()
            );
        }
    }
}
```

### **4. Testing de Integración**

```php
<?php
// tests/Unit/Modules/Payroll/Domain/Core/Services/SeniorityBonusServiceTest.php
class SeniorityBonusServiceTest extends TestCase
{
    /** @test */
    public function can_calculate_bonus_for_eligible_employees()
    {
        // Mock del servicio de integración
        $employeeService = Mockery::mock(EmployeeInformationServiceInterface::class);
        
        $employeeService->shouldReceive('getEmployeesWithMinimumYears')
            ->with(5, null)
            ->andReturn([
                new EmployeeData(
                    id: 'emp-001',
                    firstName: 'Juan',
                    lastName: 'Pérez',
                    email: 'juan@example.com',
                    status: 'active',
                    hireDate: new \DateTimeImmutable('2018-01-01'),
                    baseSalary: new Money(3000000, 'COP'),
                    yearsOfService: 6
                )
            ]);

        $service = new SeniorityBonusService($employeeService);
        
        $result = $service->calculateSeniorityBonusForEligibleEmployees(5, 10.0);
        
        $this->assertCount(1, $result);
        $this->assertEquals(300000, $result[0]->bonusAmount->getAmount());
    }
}
```

---

## 📋 Casos de Uso Comunes

### **1. Consulta de Información**
```php
// Nómina necesita información de empleados para cálculos
$employees = $this->employeeService->getActiveEmployees();
```

### **2. Validación de Reglas de Negocio**
```php
// Nómina necesita validar si un empleado puede recibir bono
$isEligible = $this->employeeService->isEmployeeEligibleForBonus($employeeId);
```

### **3. Procesamiento de Eventos**
```php
// Cuando se crea un empleado, Nómina debe registrarlo
Event::listen(EmployeeCreated::class, function ($event) {
    $this->payrollService->registerEmployeeForPayroll($event->employee);
});
```

### **4. Sincronización de Datos**
```php
// Nómina necesita sincronizar datos de empleados periódicamente
$employees = $this->employeeService->getEmployeesUpdatedSince($lastSync);
```

---

## 🎯 Resumen y Conclusiones

### **¿Por qué es importante la integración entre dominios?**

1. **Mantiene la integridad** de cada dominio
2. **Facilita la evolución** independiente de cada módulo
3. **Permite la migración** futura a microservicios
4. **Mejora la testabilidad** y mantenibilidad del código
5. **Reduce el acoplamiento** entre módulos

### **¿Cómo implementar correctamente la integración?**

1. **Definir interfaces** claras entre dominios
2. **Crear DTOs** para transferir datos sin acoplamiento
3. **Implementar Anti-Corruption Layers** para traducir entre dominios
4. **Usar eventos** para comunicación asíncrona
5. **Mantener dominios independientes** y cohesivos

### **¿Cuáles son los beneficios de esta arquitectura?**

1. **Separación de responsabilidades**: Cada dominio hace lo suyo
2. **Bajo acoplamiento**: Los dominios no dependen directamente entre sí
3. **Alta cohesión**: Cada dominio está enfocado en su propósito
4. **Facilidad de testing**: Puedes probar cada parte independientemente
5. **Escalabilidad**: Fácil migrar a microservicios en el futuro
6. **Mantenibilidad**: Cambios en un dominio no afectan al otro

### **¿Cómo evoluciona esta arquitectura?**

La arquitectura DDD con integración entre dominios está **diseñada para evolucionar**. Comienza como un **monolito modular** y puede migrar gradualmente a **microservicios** sin romper la funcionalidad existente.

**La clave está en que diseñamos la comunicación entre dominios pensando en el futuro**, no solo en el presente.

---

## 📚 Referencias y Recursos Adicionales

- [Domain-Driven Design Reference](https://www.domainlanguage.com/ddd/reference/)
- [Microservices Patterns](https://microservices.io/patterns/index.html)
- [Laravel Service Providers](https://laravel.com/docs/providers)
- [Clean Architecture](https://blog.cleancoder.com/uncle-bob/2012/08/13/the-clean-architecture.html)

---

**Esta documentación proporciona una guía completa para implementar correctamente la integración entre dominios en una arquitectura DDD, manteniendo el aislamiento y facilitando la evolución futura del sistema.**
