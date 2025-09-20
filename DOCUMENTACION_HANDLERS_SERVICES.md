# Documentación: Handlers vs Services - Diferencias y Cuándo Usar Cada Uno

## 📋 Índice
1. [Introducción](#introducción)
2. [¿Qué son los Services?](#qué-son-los-services)
3. [¿Qué son los Handlers?](#qué-son-los-handlers)
4. [Diferencias Clave](#diferencias-clave)
5. [Cuándo usar cada uno](#cuándo-usar-cada-uno)
6. [Ejemplos de la Vida Real](#ejemplos-de-la-vida-real)
7. [Patrones y Mejores Prácticas](#patrones-y-mejores-prácticas)
8. [Casos de Uso Comunes](#casos-de-uso-comunes)
9. [Resumen y Recomendaciones](#resumen-y-recomendaciones)

---

## 🎯 Introducción

Una de las confusiones más comunes en la arquitectura DDD es entender cuándo usar **Services** y cuándo usar **Handlers**. Ambos manejan lógica de negocio, pero tienen propósitos y responsabilidades diferentes.

### **El problema común:**
- **Services** (Domain) → Lógica de negocio
- **Handlers** (Application) → Lógica de negocio
- **¿Cuál usar?** 🤔

**La respuesta:** Depende del **propósito** y **contexto** de la lógica.

---

## 🏢 ¿Qué son los Services?

### **Definición:**
Los **Services** son clases que contienen **lógica de negocio pura** del dominio. Son independientes de frameworks y se enfocan en las reglas de negocio específicas.

### **Características:**
- **Ubicación**: `Domain/Services/`
- **Propósito**: Lógica de negocio pura
- **Independientes**: No dependen de frameworks
- **Reutilizables**: Se pueden usar en múltiples contextos
- **Sin efectos secundarios**: No manejan HTTP, base de datos, etc.

### **Analogía de la Vida Real:**
Imagina que los **Services** son como **especialistas** en un hospital:

- **Cardiólogo**: Sabe todo sobre el corazón
- **Neurólogo**: Sabe todo sobre el cerebro
- **Traumatólogo**: Sabe todo sobre huesos

Cada uno es un **experto** en su área específica, pero no coordina operaciones completas.

---

## ⚙️ ¿Qué son los Handlers?

### **Definición:**
Los **Handlers** son clases que **orquestan** y **coordinan** la lógica de negocio. Unen diferentes Services y manejan el flujo completo de una operación.

### **Características:**
- **Ubicación**: `Application/Handlers/`
- **Propósito**: Orquestar y coordinar
- **Dependientes**: Pueden depender de frameworks
- **Específicos**: Un Handler por Command
- **Con efectos secundarios**: Manejan base de datos, eventos, etc.

### **Analogía de la Vida Real:**
Imagina que los **Handlers** son como **coordinadores de cirugía** en un hospital:

- **Coordinan** a múltiples especialistas
- **Manejan** el flujo completo de la operación
- **Se aseguran** de que todo esté en orden
- **Gestionan** los recursos necesarios

---

## 🔍 Diferencias Clave

| Aspecto | Services (Domain) | Handlers (Application) |
|---------|-------------------|------------------------|
| **Ubicación** | `Domain/Services/` | `Application/Handlers/` |
| **Propósito** | Lógica de negocio pura | Orquestar y coordinar |
| **Dependencias** | Independientes | Pueden depender de frameworks |
| **Reutilización** | Altamente reutilizables | Específicos por Command |
| **Efectos secundarios** | No tienen | Sí tienen (DB, eventos, etc.) |
| **Complejidad** | Simples y enfocados | Complejos y coordinadores |
| **Testing** | Fácil (sin dependencias) | Más complejo (con dependencias) |

---

## 🎯 Cuándo usar cada uno

### **✅ Services (Domain) - Usar cuando:**

#### **1. Lógica de negocio pura:**
```php
// ✅ BIEN - Service con lógica pura
class EmployeeService
{
    public function calculateYearsOfService(Employee $employee): int
    {
        $hireDate = $employee->hireDate();
        $now = new \DateTimeImmutable();
        return $now->diff($hireDate)->y;
    }
    
    public function isEligibleForPromotion(Employee $employee): bool
    {
        $yearsOfService = $this->calculateYearsOfService($employee);
        $hasGoodPerformance = $employee->hasGoodPerformance();
        
        return $yearsOfService >= 2 && $hasGoodPerformance;
    }
}
```

#### **2. Reglas de negocio complejas:**
```php
// ✅ BIEN - Service con reglas complejas
class PayrollService
{
    public function calculateGrossSalary(Employee $employee, int $hoursWorked): float
    {
        $hourlyRate = $employee->hourlyRate();
        
        if ($hoursWorked <= 40) {
            return $hoursWorked * $hourlyRate;
        }
        
        $regularHours = 40;
        $overtimeHours = $hoursWorked - 40;
        $overtimeRate = $hourlyRate * 1.5;
        
        return ($regularHours * $hourlyRate) + ($overtimeHours * $overtimeRate);
    }
    
    public function calculateTaxes(float $grossSalary, string $taxBracket): float
    {
        // Lógica compleja de cálculo de impuestos
        if ($taxBracket === 'low') {
            return $grossSalary * 0.10;
        } elseif ($taxBracket === 'medium') {
            return $grossSalary * 0.20;
        } else {
            return $grossSalary * 0.30;
        }
    }
}
```

#### **3. Validaciones de negocio:**
```php
// ✅ BIEN - Service con validaciones
class EmailService
{
    public function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    public function isCorporateEmail(string $email): bool
    {
        $corporateDomains = ['@empresa.com', '@company.com'];
        
        foreach ($corporateDomains as $domain) {
            if (str_ends_with($email, $domain)) {
                return true;
            }
        }
        
        return false;
    }
}
```

### **✅ Handlers (Application) - Usar cuando:**

#### **1. Orquestar múltiples Services:**
```php
// ✅ BIEN - Handler que orquesta Services
class ProcessPayrollHandler
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository,
        private PayrollService $payrollService, // ← Service del dominio
        private TaxService $taxService, // ← Service del dominio
        private DeductionService $deductionService, // ← Service del dominio
        private PayrollRepositoryInterface $payrollRepository
    ) {}

    public function handle(ProcessPayrollCommand $command): PayrollDTO
    {
        // 1. Obtener empleado
        $employee = $this->employeeRepository->findById($command->employeeId);
        
        // 2. Calcular salario bruto usando Service
        $grossSalary = $this->payrollService->calculateGrossSalary($employee, $command->hoursWorked);
        
        // 3. Calcular impuestos usando Service
        $taxes = $this->taxService->calculateTaxes($grossSalary, $employee->taxBracket());
        
        // 4. Calcular deducciones usando Service
        $deductions = $this->deductionService->calculateDeductions($employee, $grossSalary);
        
        // 5. Calcular salario neto
        $netSalary = $grossSalary - $taxes - $deductions;
        
        // 6. Crear nómina
        $payroll = new Payroll(
            employeeId: $employee->id(),
            grossSalary: $grossSalary,
            taxes: $taxes,
            deductions: $deductions,
            netSalary: $netSalary,
            period: $command->period
        );
        
        // 7. Guardar en base de datos
        $this->payrollRepository->save($payroll);
        
        // 8. Disparar evento
        event(new PayrollProcessed($payroll));
        
        // 9. Retornar DTO
        return $this->convertToDTO($payroll);
    }
}
```

#### **2. Manejar efectos secundarios:**
```php
// ✅ BIEN - Handler con efectos secundarios
class CreateEmployeeHandler
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository,
        private EmployeeService $employeeService, // ← Service del dominio
        private EventDispatcherInterface $eventDispatcher,
        private NotificationService $notificationService
    ) {}

    public function handle(CreateEmployeeCommand $command): EmployeeDTO
    {
        // 1. Validar datos usando Service
        if (!$this->employeeService->isValidEmployeeData($command)) {
            throw new InvalidEmployeeDataException();
        }
        
        // 2. Crear empleado
        $employee = $this->createEmployee($command);
        
        // 3. Aplicar lógica de negocio del Service
        if ($this->employeeService->isEligibleForPromotion($employee)) {
            $employee->markAsEligibleForPromotion();
        }
        
        // 4. Guardar en base de datos (efecto secundario)
        $this->employeeRepository->save($employee);
        
        // 5. Disparar evento (efecto secundario)
        $this->eventDispatcher->dispatch(new EmployeeCreated($employee));
        
        // 6. Enviar notificación (efecto secundario)
        $this->notificationService->sendWelcomeEmail($employee);
        
        // 7. Retornar DTO
        return $this->convertToDTO($employee);
    }
}
```

---

## 🏥 Ejemplos de la Vida Real

### **Ejemplo 1: Hospital**

#### **Services (Especialistas):**
```php
// Cardiólogo - Especialista en corazón
class CardiologyService
{
    public function diagnoseHeartCondition(Patient $patient): string
    {
        // Lógica pura de diagnóstico cardíaco
        if ($patient->hasChestPain() && $patient->hasShortnessOfBreath()) {
            return 'possible_heart_attack';
        }
        
        return 'normal';
    }
    
    public function calculateHeartRate(Patient $patient): int
    {
        // Lógica pura de cálculo de frecuencia cardíaca
        return $patient->getPulseRate();
    }
}

// Neurólogo - Especialista en cerebro
class NeurologyService
{
    public function diagnoseNeurologicalCondition(Patient $patient): string
    {
        // Lógica pura de diagnóstico neurológico
        if ($patient->hasHeadache() && $patient->hasVisionProblems()) {
            return 'possible_migraine';
        }
        
        return 'normal';
    }
}
```

#### **Handler (Coordinador de Cirugía):**
```php
// Coordinador de Cirugía - Orquesta a los especialistas
class HeartSurgeryHandler
{
    public function __construct(
        private CardiologyService $cardiologyService,
        private AnesthesiaService $anesthesiaService,
        private SurgeryRepositoryInterface $surgeryRepository,
        private EventDispatcherInterface $eventDispatcher
    ) {}

    public function handle(PerformHeartSurgeryCommand $command): SurgeryDTO
    {
        // 1. Obtener paciente
        $patient = $this->getPatient($command->patientId);
        
        // 2. Diagnosticar usando Service
        $diagnosis = $this->cardiologyService->diagnoseHeartCondition($patient);
        
        // 3. Preparar anestesia usando Service
        $anesthesia = $this->anesthesiaService->prepareAnesthesia($patient);
        
        // 4. Realizar cirugía
        $surgery = $this->performSurgery($patient, $diagnosis);
        
        // 5. Guardar en base de datos
        $this->surgeryRepository->save($surgery);
        
        // 6. Disparar evento
        $this->eventDispatcher->dispatch(new SurgeryCompleted($surgery));
        
        // 7. Retornar resultado
        return $this->convertToDTO($surgery);
    }
}
```

### **Ejemplo 2: Restaurante**

#### **Services (Chefs Especialistas):**
```php
// Chef de Pastelería - Especialista en postres
class PastryService
{
    public function createCake(string $type, array $ingredients): Cake
    {
        // Lógica pura de creación de pasteles
        $recipe = $this->getRecipe($type);
        $cake = new Cake($recipe, $ingredients);
        
        return $cake;
    }
    
    public function calculateBakingTime(Cake $cake): int
    {
        // Lógica pura de cálculo de tiempo de horneado
        $baseTime = 30; // minutos base
        $sizeMultiplier = $cake->getSize() * 10;
        
        return $baseTime + $sizeMultiplier;
    }
}

// Chef de Cocina - Especialista en platos principales
class KitchenService
{
    public function prepareMainCourse(string $dish, array $ingredients): MainCourse
    {
        // Lógica pura de preparación de platos principales
        $recipe = $this->getRecipe($dish);
        $mainCourse = new MainCourse($recipe, $ingredients);
        
        return $mainCourse;
    }
}
```

#### **Handler (Maître D'):**
```php
// Maître D' - Coordina todo el servicio
class DinnerServiceHandler
{
    public function __construct(
        private KitchenService $kitchenService,
        private PastryService $pastryService,
        private OrderRepositoryInterface $orderRepository,
        private NotificationService $notificationService
    ) {}

    public function handle(ProcessDinnerOrderCommand $command): OrderDTO
    {
        // 1. Obtener orden
        $order = $this->getOrder($command->orderId);
        
        // 2. Preparar plato principal usando Service
        $mainCourse = $this->kitchenService->prepareMainCourse(
            $order->getMainDish(),
            $order->getIngredients()
        );
        
        // 3. Preparar postre usando Service
        $dessert = $this->pastryService->createCake(
            $order->getDessert(),
            $order->getDessertIngredients()
        );
        
        // 4. Crear orden completa
        $completeOrder = new CompleteOrder($mainCourse, $dessert);
        
        // 5. Guardar en base de datos
        $this->orderRepository->save($completeOrder);
        
        // 6. Notificar al cliente
        $this->notificationService->notifyOrderReady($completeOrder);
        
        // 7. Retornar resultado
        return $this->convertToDTO($completeOrder);
    }
}
```

---

## 🎨 Patrones y Mejores Prácticas

### **✅ Patrones Recomendados:**

#### **1. Service para lógica pura:**
```php
// ✅ BIEN - Service con lógica pura
class EmployeeService
{
    public function calculateYearsOfService(Employee $employee): int
    {
        // Solo lógica de negocio, sin dependencias externas
        $hireDate = $employee->hireDate();
        $now = new \DateTimeImmutable();
        return $now->diff($hireDate)->y;
    }
}
```

#### **2. Handler para orquestación:**
```php
// ✅ BIEN - Handler que orquesta
class UpdateEmployeeHandler
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository,
        private EmployeeService $employeeService // ← Usa el Service
    ) {}

    public function handle(UpdateEmployeeCommand $command): EmployeeDTO
    {
        // 1. Obtener empleado
        $employee = $this->employeeRepository->findById($command->employeeId);
        
        // 2. Actualizar datos
        $employee->update(/* ... */);
        
        // 3. Aplicar lógica del Service
        if ($this->employeeService->isEligibleForPromotion($employee)) {
            $employee->markAsEligibleForPromotion();
        }
        
        // 4. Guardar
        $this->employeeRepository->save($employee);
        
        // 5. Retornar DTO
        return $this->convertToDTO($employee);
    }
}
```

### **❌ Anti-patrones (Evitar):**

#### **1. Service con dependencias externas:**
```php
// ❌ MAL - Service con dependencias externas
class EmployeeService
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository // ❌ NO en Service
    ) {}

    public function createEmployee(array $data): Employee
    {
        // ❌ Service no debe manejar base de datos
        $employee = new Employee(/* ... */);
        $this->employeeRepository->save($employee);
        return $employee;
    }
}
```

#### **2. Handler sin orquestación:**
```php
// ❌ MAL - Handler que solo hace una cosa simple
class SimpleCalculationHandler
{
    public function handle(CalculateCommand $command): float
    {
        // ❌ Esto debería ser un Service
        return $command->number1 + $command->number2;
    }
}
```

---

## 🔧 Casos de Uso Comunes

### **1. CRUD Operations:**

#### **Service (Lógica pura):**
```php
class EmployeeService
{
    public function validateEmployeeData(array $data): bool
    {
        // Validaciones de negocio puras
        return !empty($data['first_name']) && 
               !empty($data['last_name']) && 
               $this->isValidEmail($data['email']);
    }
    
    public function calculateEmployeeAge(Employee $employee): int
    {
        // Cálculo puro
        $birthDate = $employee->birthDate();
        $now = new \DateTimeImmutable();
        return $now->diff($birthDate)->y;
    }
}
```

#### **Handler (Orquestación):**
```php
class CreateEmployeeHandler
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository,
        private EmployeeService $employeeService
    ) {}

    public function handle(CreateEmployeeCommand $command): EmployeeDTO
    {
        // 1. Validar usando Service
        if (!$this->employeeService->validateEmployeeData($command->toArray())) {
            throw new InvalidEmployeeDataException();
        }
        
        // 2. Crear empleado
        $employee = new Employee(/* ... */);
        
        // 3. Guardar
        $this->employeeRepository->save($employee);
        
        // 4. Retornar DTO
        return $this->convertToDTO($employee);
    }
}
```

### **2. Operaciones Complejas:**

#### **Services (Lógica especializada):**
```php
class PayrollService
{
    public function calculateGrossSalary(Employee $employee, int $hours): float
    {
        // Lógica pura de cálculo de salario
        return $employee->hourlyRate() * $hours;
    }
}

class TaxService
{
    public function calculateTaxes(float $salary, string $bracket): float
    {
        // Lógica pura de cálculo de impuestos
        return $salary * $this->getTaxRate($bracket);
    }
}
```

#### **Handler (Coordinación):**
```php
class ProcessPayrollHandler
{
    public function __construct(
        private PayrollService $payrollService,
        private TaxService $taxService,
        private PayrollRepositoryInterface $payrollRepository
    ) {}

    public function handle(ProcessPayrollCommand $command): PayrollDTO
    {
        // 1. Calcular salario bruto
        $grossSalary = $this->payrollService->calculateGrossSalary(
            $command->employee, 
            $command->hours
        );
        
        // 2. Calcular impuestos
        $taxes = $this->taxService->calculateTaxes(
            $grossSalary, 
            $command->taxBracket
        );
        
        // 3. Crear nómina
        $payroll = new Payroll($grossSalary, $taxes);
        
        // 4. Guardar
        $this->payrollRepository->save($payroll);
        
        // 5. Retornar DTO
        return $this->convertToDTO($payroll);
    }
}
```

---

## 📝 Resumen y Recomendaciones

### **🎯 Regla Práctica:**

#### **¿Tienes dudas? Pregúntate:**

1. **¿Es lógica de negocio pura?** → **Service**
2. **¿Necesitas orquestar algo?** → **Handler**
3. **¿Es muy simple?** → **Service**
4. **¿Es complejo y necesita coordinación?** → **Handler**

### **✅ Services - Usar cuando:**
- Lógica de negocio pura
- Cálculos y validaciones
- Reglas de negocio complejas
- Lógica reutilizable
- Sin dependencias externas

### **✅ Handlers - Usar cuando:**
- Orquestar múltiples Services
- Manejar efectos secundarios
- Coordinar operaciones complejas
- Un Handler por Command
- Con dependencias externas

### **❌ NO uses Services cuando:**
- Necesitas acceso a base de datos
- Necesitas manejar HTTP
- Necesitas disparar eventos
- Necesitas orquestar múltiples servicios

### **❌ NO uses Handlers cuando:**
- Solo necesitas lógica de negocio pura
- La lógica es muy simple
- No necesitas orquestar nada

### **💡 Consejo Final:**
**Los Services contienen la lógica de negocio pura, los Handlers la orquestan y coordinan.**

Esta separación hace que tu código sea más limpio, testeable y mantenible, siguiendo los principios de DDD y Clean Architecture.
