# Documentación: Commands vs DTOs - Diferencias y Cuándo Usar Cada Uno

## 📋 Índice
1. [Introducción](#introducción)
2. [¿Qué son los Commands?](#qué-son-los-commands)
3. [¿Qué son los DTOs?](#qué-son-los-dtos)
4. [Diferencias Clave](#diferencias-clave)
5. [Flujo Completo: Command → DTO](#flujo-completo-command--dto)
6. [Casos de Uso Específicos](#casos-de-uso-específicos)
7. [Ejemplos Prácticos](#ejemplos-prácticos)
8. [Cuándo usar cada uno](#cuándo-usar-cada-uno)
9. [Mejores Prácticas](#mejores-prácticas)
10. [Resumen y Recomendaciones](#resumen-y-recomendaciones)

---

## 🎯 Introducción

Una de las confusiones más comunes en la arquitectura DDD es entender la diferencia entre **Commands** y **DTOs**. Ambos transportan datos, pero tienen propósitos y momentos de uso completamente diferentes.

### **El problema común:**
- **Commands** → Transportan datos
- **DTOs** → Transportan datos
- **¿Cuál usar?** 🤔

**La respuesta:** Depende del **momento** y **dirección** del flujo de datos.

---

## 🎯 ¿Qué son los Commands?

### **Definición:**
Un **Command** es un objeto que representa una **intención** o **acción** que el usuario quiere ejecutar en el sistema. Transporta los **datos de entrada** necesarios para realizar una operación.

### **Características:**
- **Datos de entrada**: Lo que el usuario quiere hacer
- **Antes del procesamiento**: Se usan antes de ejecutar la acción
- **Del usuario al sistema**: Van del frontend al backend
- **Inmutables**: No cambian después de ser creados

### **Analogía de la Vida Real:**
Los **Commands** son como **órdenes de trabajo** en una fábrica:

- **Orden de Producción**: "Fabricar 100 sillas de madera"
- **Orden de Mantenimiento**: "Reparar la máquina X"
- **Orden de Envío**: "Enviar pedido #12345 a la dirección Y"

Cada orden contiene **toda la información necesaria** para ejecutar la tarea, pero **no ejecuta la tarea** por sí misma.

---

## 🎯 ¿Qué son los DTOs?

### **Definición:**
Un **DTO** (Data Transfer Object) es un objeto que se usa para **transferir datos** entre diferentes capas de la aplicación. Transporta los **datos de salida** después de procesar una acción.

### **Características:**
- **Datos de salida**: El resultado del procesamiento
- **Después del procesamiento**: Se usan después de ejecutar la acción
- **Del sistema al usuario**: Van del backend al frontend
- **Serializables**: Se pueden convertir a JSON/XML

### **Analogía de la Vida Real:**
Los **DTOs** son como **reportes de trabajo** en una fábrica:

- **Reporte de Producción**: "Se fabricaron 100 sillas exitosamente"
- **Reporte de Mantenimiento**: "Máquina X reparada, tiempo: 2 horas"
- **Reporte de Envío**: "Pedido #12345 enviado, tracking: ABC123"

Cada reporte contiene **el resultado** del trabajo realizado.

---

## 🔍 Diferencias Clave

| Aspecto | Commands | DTOs |
|---------|----------|------|
| **Propósito** | Datos de entrada | Datos de salida |
| **Cuándo se usan** | Antes de procesar | Después de procesar |
| **De dónde vienen** | Usuario/Frontend | Handler/Service |
| **A dónde van** | Handler | Usuario/Frontend |
| **Contienen** | Lo que el usuario quiere | Lo que el sistema devuelve |
| **Ejemplo** | "Crear empleado Juan" | "Empleado Juan creado con ID 123" |
| **Momento** | Al inicio del flujo | Al final del flujo |
| **Dirección** | Usuario → Sistema | Sistema → Usuario |

---

## 🔄 Flujo Completo: Command → DTO

### **1. Usuario envía petición HTTP:**
```json
POST /api/employees
{
    "first_name": "Juan",
    "last_name": "Pérez",
    "email": "juan@email.com"
}
```

### **2. Controller crea Command:**
```php
class EmployeeController {
    public function store(CreateEmployeeRequest $request) {
        // Command = Datos de ENTRADA
        $command = new CreateEmployeeCommand(
            firstName: $request->input('first_name'),  // ← Del usuario
            lastName: $request->input('last_name'),    // ← Del usuario
            email: $request->input('email')            // ← Del usuario
        );
        
        // Pasar al Handler
        $handler = app(CreateEmployeeHandler::class);
        $dto = $handler->handle($command);
        
        // Retornar DTO como respuesta
        return response()->json(['data' => $dto->toArray()], 201);
    }
}
```

### **3. Handler procesa Command y crea DTO:**
```php
class CreateEmployeeHandler {
    public function handle(CreateEmployeeCommand $command): EmployeeDTO {
        // 1. Procesar Command (datos de entrada)
        $employee = new Employee(
            id: Str::uuid()->toString(),           // ← Generado por el sistema
            firstName: $command->firstName,        // ← Del Command
            lastName: $command->lastName,          // ← Del Command
            email: new Email($command->email),     // ← Del Command
            status: EmployeeStatus::ACTIVE,        // ← Asignado por el sistema
            createdAt: new \DateTimeImmutable(),   // ← Generado por el sistema
            updatedAt: new \DateTimeImmutable()    // ← Generado por el sistema
        );
        
        // 2. Guardar en base de datos
        $this->employeeRepository->save($employee);
        
        // 3. Crear DTO (datos de salida)
        $dto = new EmployeeDTO(
            id: $employee->id(),                   // ← Generado por el sistema
            firstName: $employee->firstName(),     // ← Del Command
            lastName: $employee->lastName(),       // ← Del Command
            email: $employee->email()?->value(),   // ← Del Command
            status: $employee->status()->value,    // ← Asignado por el sistema
            createdAt: $employee->createdAt()->format('Y-m-d H:i:s'), // ← Generado por el sistema
            updatedAt: $employee->updatedAt()->format('Y-m-d H:i:s')  // ← Generado por el sistema
        );
        
        return $dto;
    }
}
```

### **4. Respuesta HTTP:**
```json
{
    "data": {
        "id": "123e4567-e89b-12d3-a456-426614174000",
        "first_name": "Juan",
        "last_name": "Pérez",
        "email": "juan@email.com",
        "status": "active",
        "created_at": "2023-01-01 10:00:00",
        "updated_at": "2023-01-01 10:00:00"
    }
}
```

---

## 📋 Casos de Uso Específicos

### **Commands - Para acciones del usuario:**
```php
// Usuario quiere crear empleado
CreateEmployeeCommand

// Usuario quiere actualizar empleado
UpdateEmployeeCommand

// Usuario quiere eliminar empleado
DeleteEmployeeCommand

// Usuario quiere procesar nómina
ProcessPayrollCommand

// Usuario quiere generar reporte
GenerateReportCommand
```

### **DTOs - Para respuestas del sistema:**
```php
// Sistema devuelve empleado creado
EmployeeDTO

// Sistema devuelve lista de empleados
EmployeeListDTO

// Sistema devuelve reporte generado
EmployeeReportDTO

// Sistema devuelve resumen
EmployeeSummaryDTO

// Sistema devuelve resultado de búsqueda
EmployeeSearchDTO
```

---

## 💡 Ejemplos Prácticos

### **Ejemplo 1: Crear Empleado**

#### **Command (Entrada):**
```php
// app/Modules/Employees/Application/Commands/CreateEmployeeCommand.php
final class CreateEmployeeCommand
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly ?string $phone
    ) {}
}
```

#### **DTO (Salida):**
```php
// app/Modules/Employees/Application/DTOs/EmployeeDTO.php
final class EmployeeDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly ?string $phone,
        public readonly string $status,
        public readonly string $createdAt,
        public readonly string $updatedAt
    ) {}
}
```

#### **Flujo completo:**
```php
// 1. Usuario envía datos
POST /api/employees
{
    "first_name": "Juan",
    "last_name": "Pérez",
    "email": "juan@email.com"
}

// 2. Controller crea Command
$command = new CreateEmployeeCommand(
    firstName: "Juan",
    lastName: "Pérez",
    email: "juan@email.com"
);

// 3. Handler procesa y crea DTO
$dto = new EmployeeDTO(
    id: "123",
    firstName: "Juan",
    lastName: "Pérez",
    email: "juan@email.com",
    status: "active",
    createdAt: "2023-01-01 10:00:00",
    updatedAt: "2023-01-01 10:00:00"
);

// 4. Usuario recibe respuesta
{
    "data": {
        "id": "123",
        "first_name": "Juan",
        "last_name": "Pérez",
        "email": "juan@email.com",
        "status": "active",
        "created_at": "2023-01-01 10:00:00",
        "updated_at": "2023-01-01 10:00:00"
    }
}
```

### **Ejemplo 2: Procesar Nómina**

#### **Command (Entrada):**
```php
// app/Modules/Payroll/Application/Commands/ProcessPayrollCommand.php
final class ProcessPayrollCommand
{
    public function __construct(
        public readonly string $employeeId,
        public readonly string $period,
        public readonly int $hoursWorked,
        public readonly float $hourlyRate
    ) {}
}
```

#### **DTO (Salida):**
```php
// app/Modules/Payroll/Application/DTOs/PayrollDTO.php
final class PayrollDTO
{
    public function __construct(
        public readonly string $payrollId,
        public readonly string $employeeId,
        public readonly string $period,
        public readonly float $grossSalary,
        public readonly float $taxes,
        public readonly float $deductions,
        public readonly float $netSalary,
        public readonly string $status,
        public readonly string $processedAt
    ) {}
}
```

#### **Flujo completo:**
```php
// 1. Usuario envía datos
POST /api/payroll/process
{
    "employee_id": "123",
    "period": "2023-01",
    "hours_worked": 40,
    "hourly_rate": 25.50
}

// 2. Controller crea Command
$command = new ProcessPayrollCommand(
    employeeId: "123",
    period: "2023-01",
    hoursWorked: 40,
    hourlyRate: 25.50
);

// 3. Handler procesa y crea DTO
$dto = new PayrollDTO(
    payrollId: "PAY-456",
    employeeId: "123",
    period: "2023-01",
    grossSalary: 1020.00,
    taxes: 204.00,
    deductions: 100.00,
    netSalary: 716.00,
    status: "processed",
    processedAt: "2023-01-01 10:00:00"
);

// 4. Usuario recibe respuesta
{
    "data": {
        "payroll_id": "PAY-456",
        "employee_id": "123",
        "period": "2023-01",
        "gross_salary": 1020.00,
        "taxes": 204.00,
        "deductions": 100.00,
        "net_salary": 716.00,
        "status": "processed",
        "processed_at": "2023-01-01 10:00:00"
    }
}
```

---

## 🎯 Cuándo usar cada uno

### **✅ Commands - Usar cuando:**
- El usuario quiere **hacer algo**
- Necesitas **datos de entrada**
- Antes de **procesar** la acción
- Del **frontend al backend**

### **✅ DTOs - Usar cuando:**
- El sistema **devuelve algo**
- Necesitas **datos de salida**
- Después de **procesar** la acción
- Del **backend al frontend**

### **❌ NO uses Commands cuando:**
- Necesitas devolver datos al usuario
- Después de procesar una acción
- Para respuestas de API

### **❌ NO uses DTOs cuando:**
- Necesitas datos de entrada del usuario
- Antes de procesar una acción
- Para comandos del usuario

---

## 🎨 Mejores Prácticas

### **1. Naming consistente:**
```php
// ✅ BIEN - Naming consistente
CreateEmployeeCommand → EmployeeDTO
UpdateEmployeeCommand → EmployeeDTO
DeleteEmployeeCommand → void (no DTO)
ProcessPayrollCommand → PayrollDTO

// ❌ MAL - Naming inconsistente
CreateEmployeeCommand → EmployeeData
UpdateEmployeeCommand → EmployeeResponse
DeleteEmployeeCommand → EmployeeDTO
```

### **2. Commands específicos:**
```php
// ✅ BIEN - Commands específicos
CreateEmployeeCommand
UpdateEmployeeCommand
DeleteEmployeeCommand

// ❌ MAL - Command genérico
EmployeeCommand
```

### **3. DTOs específicos:**
```php
// ✅ BIEN - DTOs específicos
EmployeeDTO
EmployeeListDTO
EmployeeReportDTO

// ❌ MAL - DTO genérico
GenericDTO
```

### **4. Flujo claro:**
```php
// ✅ BIEN - Flujo claro
Controller → Command → Handler → DTO → Controller → Response

// ❌ MAL - Flujo confuso
Controller → DTO → Handler → Command → Controller → Response
```

---

## 📊 Resumen Visual

```
┌─────────────────────────────────────────────────────────────────┐
│                        FLUJO DE DATOS                          │
└─────────────────────────────────────────────────────────────────┘

Usuario → Controller → Command → Handler → DTO → Controller → Usuario
   ↓         ↓          ↓         ↓       ↓        ↓         ↓
 Frontend   HTTP    Entrada   Proceso  Salida   HTTP    Frontend
```

### **Commands (Entrada):**
- **Momento**: Al inicio
- **Dirección**: Usuario → Sistema
- **Contenido**: Lo que el usuario quiere
- **Ejemplo**: "Crear empleado Juan"

### **DTOs (Salida):**
- **Momento**: Al final
- **Dirección**: Sistema → Usuario
- **Contenido**: Lo que el sistema devuelve
- **Ejemplo**: "Empleado Juan creado con ID 123"

---

## 📝 Resumen y Recomendaciones

### **🎯 Commands:**
- **¿Qué?** Datos de entrada del usuario
- **¿Cuándo?** Antes de procesar
- **¿Dónde?** Del usuario al sistema
- **¿Para qué?** Ejecutar acciones

### **🎯 DTOs:**
- **¿Qué?** Datos de salida del sistema
- **¿Cuándo?** Después de procesar
- **¿Dónde?** Del sistema al usuario
- **¿Para qué?** Mostrar resultados

### **💡 Regla Práctica:**
- **¿El usuario quiere hacer algo?** → **Command**
- **¿El sistema devuelve algo?** → **DTO**

### **✅ Mejores prácticas:**
- Commands específicos para cada acción
- DTOs específicos para cada respuesta
- Naming consistente
- Flujo claro y lógico

**Commands y DTOs trabajan juntos para crear un flujo de datos claro y mantenible en la arquitectura DDD.**
