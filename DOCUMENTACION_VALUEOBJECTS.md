# Documentación: ValueObjects (Objetos de Valor) - Guía Completa

## 📋 Índice
1. [¿Qué son los ValueObjects?](#qué-son-los-valueobjects)
2. [Características principales](#características-principales)
3. [¿Por qué parecen "inútiles"?](#por-qué-parecen-inútiles)
4. [Valor agregado real](#valor-agregado-real)
5. [Casos de uso típicos](#casos-de-uso-típicos)
6. [Casos de uso específicos del negocio](#casos-de-uso-específicos-del-negocio)
7. [Ciclo de vida en peticiones HTTP](#ciclo-de-vida-en-peticiones-http)
8. [Mejores prácticas](#mejores-prácticas)
9. [Testing de ValueObjects](#testing-de-valueobjects)
10. [Relación con otras capas](#relación-con-otras-capas)
11. [Ejemplos prácticos](#ejemplos-prácticos)
12. [Resumen](#resumen)

---

## 🎯 ¿Qué son los ValueObjects?

### **Definición:**
Los **ValueObjects** son objetos que representan **conceptos del negocio** que no tienen identidad única. Se identifican por su **valor**, no por su identidad.

### **Características principales:**
- **No tienen identidad** única (ID)
- **Son inmutables** (no cambian)
- **Se comparan** por valor, no por referencia
- **Representan conceptos** del negocio
- **Son reutilizables** y seguros

### **Analogía de la Vida Real:**
Los **ValueObjects** son como **"conceptos"** en la vida real:

- **$100 USD** - Es el mismo valor sin importar el billete
- **"juan@email.com"** - Es el mismo email sin importar cómo se escriba
- **"123-45-6789"** - Es el mismo número de teléfono
- **"Activo"** - Es el mismo estado sin importar cuándo se use

---

## 🧩 Características principales

### **1. No tienen identidad única:**
```php
// ✅ BIEN - ValueObject sin ID
class Email
{
    public function __construct(private string $email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException('Invalid email format');
        }
    }
}

// ❌ MAL - ValueObject con ID
class Email
{
    private string $id; // ❌ No debe tener ID
    private string $email;
}
```

**¿Por qué es importante?**
- **Se identifican** por su valor
- **Son reutilizables** en diferentes contextos
- **No necesitan** persistencia individual
- **Facilitan** la comparación

### **2. Son inmutables:**
```php
// ✅ BIEN - Inmutable
class Email
{
    public function __construct(private readonly string $email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException('Invalid email format');
        }
    }
    
    public function value(): string
    {
        return $this->email; // Solo lectura
    }
}

// ❌ MAL - Mutable
class Email
{
    private string $email;
    
    public function setEmail(string $email): void
    {
        $this->email = $email; // ❌ Puede cambiar
    }
}
```

**¿Por qué es importante?**
- **Previenen** cambios accidentales
- **Son seguros** para compartir
- **Facilitan** el testing
- **Mantienen** la consistencia

### **3. Se comparan por valor:**
```php
class Email
{
    public function equals(Email $other): bool
    {
        return $this->email === $other->email; // Compara por valor
    }
}

// Uso
$email1 = new Email('juan@email.com');
$email2 = new Email('juan@email.com');

$email1->equals($email2); // ✅ true - Mismo valor
```

**¿Por qué es importante?**
- **Permiten** comparaciones lógicas
- **Facilitan** la validación
- **Mantienen** la consistencia
- **Son predecibles**

---

## ❓ ¿Por qué parecen "inútiles"?

### **Lo que ves:**
```php
// Parece que solo "envuelve" un string
class Email
{
    public function __construct(private readonly string $email) {}
    public function value(): string { return $this->email; }
}

// ¿Para qué esto?
$email = new Email('juan@email.com');
echo $email->value(); // 'juan@email.com' - ¡Igual que el string original!
```

### **Lo que NO ves:**
```php
// El valor real está en lo que NO ves
class Email
{
    public function __construct(private readonly string $email)
    {
        // ✅ VALIDACIÓN AUTOMÁTICA
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException('Invalid email format');
        }
    }
    
    public function value(): string { return $this->email; }
    
    // ✅ COMPORTAMIENTO ESPECÍFICO
    public function domain(): string
    {
        return substr($this->email, strpos($this->email, '@') + 1);
    }
    
    public function localPart(): string
    {
        return substr($this->email, 0, strpos($this->email, '@'));
    }
    
    public function equals(Email $other): bool
    {
        return strtolower($this->email) === strtolower($other->email);
    }
}
```

---

## 🚀 Valor agregado real

### **1. Validación automática:**
```php
// ❌ SIN ValueObject - Validación manual en cada lugar
class EmployeeController
{
    public function store(Request $request)
    {
        $email = $request->input('email');
        
        // ❌ Validación manual repetida en cada lugar
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['error' => 'Invalid email'], 400);
        }
        
        // ❌ Más validaciones manuales
        if (empty($email)) {
            return response()->json(['error' => 'Email required'], 400);
        }
        
        // ❌ Validación de duplicados
        if ($this->emailExists($email)) {
            return response()->json(['error' => 'Email exists'], 400);
        }
    }
}

// ✅ CON ValueObject - Validación automática
class EmployeeController
{
    public function store(Request $request)
    {
        try {
            $email = new Email($request->input('email')); // ✅ Validación automática
            // Si llega aquí, el email es válido
        } catch (InvalidEmailException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
```

### **2. Comportamiento específico del concepto:**
```php
// ❌ SIN ValueObject - Lógica dispersa
class EmployeeService
{
    public function sendWelcomeEmail(Employee $employee)
    {
        $email = $employee->email;
        
        // ❌ Lógica de email dispersa por todo el código
        $domain = substr($email, strpos($email, '@') + 1);
        $localPart = substr($email, 0, strpos($email, '@'));
        
        if ($domain === 'gmail.com') {
            // Lógica específica para Gmail
        }
        
        // ❌ Más lógica de email en otros lugares
        $normalizedEmail = strtolower($email);
        // ...
    }
}

// ✅ CON ValueObject - Comportamiento encapsulado
class EmployeeService
{
    public function sendWelcomeEmail(Employee $employee)
    {
        $email = $employee->email();
        
        // ✅ Comportamiento específico del concepto
        if ($email->domain() === 'gmail.com') {
            // Lógica específica para Gmail
        }
        
        $normalizedEmail = $email->value(); // Ya está normalizado
    }
}
```

### **3. Comparación segura:**
```php
// ❌ SIN ValueObject - Comparación manual
class EmployeeService
{
    public function findDuplicateEmails()
    {
        $emails = $this->getAllEmails();
        
        foreach ($emails as $email1) {
            foreach ($emails as $email2) {
                // ❌ Comparación manual propensa a errores
                if (strtolower(trim($email1)) === strtolower(trim($email2))) {
                    // Encontrado duplicado
                }
            }
        }
    }
}

// ✅ CON ValueObject - Comparación segura
class EmployeeService
{
    public function findDuplicateEmails()
    {
        $emails = $this->getAllEmails();
        
        foreach ($emails as $email1) {
            foreach ($emails as $email2) {
                // ✅ Comparación segura y consistente
                if ($email1->equals($email2)) {
                    // Encontrado duplicado
                }
            }
        }
    }
}
```

### **4. Prevención de errores:**
```php
// ❌ SIN ValueObject - Errores comunes
class EmployeeService
{
    public function updateEmployee($employeeId, $newEmail)
    {
        // ❌ Fácil cometer errores
        $employee->email = $newEmail; // ¿Es válido? ¿Está normalizado?
        
        // ❌ Fácil confundir parámetros
        $this->sendEmail($employeeId, $newEmail); // ¿Cuál es cuál?
    }
}

// ✅ CON ValueObject - Prevención de errores
class EmployeeService
{
    public function updateEmployee($employeeId, Email $newEmail)
    {
        // ✅ Imposible asignar email inválido
        $employee->updateEmail($newEmail); // Solo acepta Email válido
        
        // ✅ Imposible confundir parámetros
        $this->sendEmail($employeeId, $newEmail); // Tipos claros
    }
}
```

---

## 📋 Casos de uso típicos

### **1. Conceptos de identificación:**
```php
class DocumentId
{
    public function __construct(
        private readonly string $type,
        private readonly string $number
    ) {
        $this->validate();
    }
    
    public function type(): string
    {
        return $this->type;
    }
    
    public function number(): string
    {
        return $this->number;
    }
    
    public function equals(DocumentId $other): bool
    {
        return $this->type === $other->type && 
               $this->number === $other->number;
    }
    
    public function toString(): string
    {
        return "{$this->type}-{$this->number}";
    }
    
    private function validate(): void
    {
        if (empty($this->type) || empty($this->number)) {
            throw new InvalidDocumentIdException('Type and number are required');
        }
    }
}
```

### **2. Conceptos de comunicación:**
```php
class Email
{
    public function __construct(private readonly string $email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException('Invalid email format');
        }
    }
    
    public function value(): string
    {
        return $this->email;
    }
    
    public function domain(): string
    {
        return substr($this->email, strpos($this->email, '@') + 1);
    }
    
    public function localPart(): string
    {
        return substr($this->email, 0, strpos($this->email, '@'));
    }
    
    public function isGmail(): bool
    {
        return $this->domain() === 'gmail.com';
    }
    
    public function isCompany(): bool
    {
        return $this->domain() === 'empresa.com';
    }
    
    public function equals(Email $other): bool
    {
        return strtolower($this->email) === strtolower($other->email);
    }
}
```

### **3. Conceptos de contacto:**
```php
class Phone
{
    public function __construct(private readonly string $phone)
    {
        $this->validate();
    }
    
    public function value(): string
    {
        return $this->phone;
    }
    
    public function formatted(): string
    {
        return preg_replace('/(\d{3})(\d{3})(\d{4})/', '($1) $2-$3', $this->phone);
    }
    
    public function equals(Phone $other): bool
    {
        return $this->normalize($this->phone) === $this->normalize($other->phone);
    }
    
    private function validate(): void
    {
        if (!preg_match('/^\d{10}$/', $this->phone)) {
            throw new InvalidPhoneException('Phone must be 10 digits');
        }
    }
    
    private function normalize(string $phone): string
    {
        return preg_replace('/[^0-9]/', '', $phone);
    }
}
```

### **4. Conceptos de estado:**
```php
enum EmployeeStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Suspended = 'suspended';
    case Terminated = 'terminated';
    
    public function isActive(): bool
    {
        return $this === self::Active;
    }
    
    public function canWork(): bool
    {
        return $this === self::Active;
    }
    
    public function displayName(): string
    {
        return match($this) {
            self::Active => 'Activo',
            self::Inactive => 'Inactivo',
            self::Suspended => 'Suspendido',
            self::Terminated => 'Terminado',
        };
    }
}
```

---

## 🎯 Casos de uso específicos del negocio

### **1. Conceptos monetarios:**
```php
class Money
{
    public function __construct(
        private readonly float $amount,
        private readonly string $currency
    ) {
        $this->validate();
    }
    
    public function amount(): float
    {
        return $this->amount;
    }
    
    public function currency(): string
    {
        return $this->currency;
    }
    
    public function add(Money $other): Money
    {
        if ($this->currency !== $other->currency) {
            throw new CurrencyMismatchException('Cannot add different currencies');
        }
        
        return new Money($this->amount + $other->amount, $this->currency);
    }
    
    public function subtract(Money $other): Money
    {
        if ($this->currency !== $other->currency) {
            throw new CurrencyMismatchException('Cannot subtract different currencies');
        }
        
        return new Money($this->amount - $other->amount, $this->currency);
    }
    
    public function multiply(float $factor): Money
    {
        return new Money($this->amount * $factor, $this->currency);
    }
    
    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount && 
               $this->currency === $other->currency;
    }
    
    public function formatted(): string
    {
        return number_format($this->amount, 2) . ' ' . $this->currency;
    }
    
    private function validate(): void
    {
        if ($this->amount < 0) {
            throw new InvalidMoneyException('Amount cannot be negative');
        }
        
        if (empty($this->currency)) {
            throw new InvalidMoneyException('Currency is required');
        }
    }
}
```

### **2. Conceptos de dirección:**
```php
class Address
{
    public function __construct(
        private readonly string $street,
        private readonly string $city,
        private readonly string $state,
        private readonly string $zipCode,
        private readonly string $country
    ) {
        $this->validate();
    }
    
    public function street(): string
    {
        return $this->street;
    }
    
    public function city(): string
    {
        return $this->city;
    }
    
    public function fullAddress(): string
    {
        return "{$this->street}, {$this->city}, {$this->state} {$this->zipCode}, {$this->country}";
    }
    
    public function equals(Address $other): bool
    {
        return $this->street === $other->street &&
               $this->city === $other->city &&
               $this->state === $other->state &&
               $this->zipCode === $other->zipCode &&
               $this->country === $other->country;
    }
    
    private function validate(): void
    {
        if (empty($this->street) || empty($this->city)) {
            throw new InvalidAddressException('Street and city are required');
        }
    }
}
```

### **3. Conceptos de fecha:**
```php
class DateRange
{
    public function __construct(
        private readonly DateTimeImmutable $startDate,
        private readonly DateTimeImmutable $endDate
    ) {
        $this->validate();
    }
    
    public function startDate(): DateTimeImmutable
    {
        return $this->startDate;
    }
    
    public function endDate(): DateTimeImmutable
    {
        return $this->endDate;
    }
    
    public function duration(): int
    {
        return $this->startDate->diff($this->endDate)->days;
    }
    
    public function contains(DateTimeImmutable $date): bool
    {
        return $date >= $this->startDate && $date <= $this->endDate;
    }
    
    public function overlaps(DateRange $other): bool
    {
        return $this->startDate <= $other->endDate && 
               $this->endDate >= $other->startDate;
    }
    
    private function validate(): void
    {
        if ($this->startDate > $this->endDate) {
            throw new InvalidDateRangeException('Start date cannot be after end date');
        }
    }
}
```

---

## 🔄 Ciclo de vida en peticiones HTTP

### **Escenario: "Crear un empleado con email y teléfono"**

#### **1. Usuario hace petición HTTP:**
```http
POST /api/employees
Content-Type: application/json
Authorization: Bearer token123

{
    "first_name": "Juan",
    "last_name": "Pérez",
    "email": "juan.perez@empresa.com",
    "phone": "1234567890",
    "document_type": "CC",
    "document_number": "12345678"
}
```

#### **2. Controller recibe la petición:**
```php
// app/Modules/Employees/Infrastructure/Http/Controllers/EmployeeController.php
class EmployeeController extends Controller
{
    public function store(Request $request)
    {
        // 1. Crear Command con datos de la petición
        $command = new CreateEmployeeCommand(
            firstName: $request->input('first_name'),
            lastName: $request->input('last_name'),
            email: $request->input('email'),           // ✅ String primitivo
            phone: $request->input('phone'),           // ✅ String primitivo
            documentType: $request->input('document_type'),
            documentNumber: $request->input('document_number')
        );
        
        // 2. Pasar Command al Handler
        $handler = app(CreateEmployeeHandler::class);
        $dto = $handler->handle($command);
        
        // 3. Retornar respuesta HTTP
        return response()->json(['data' => $dto->toArray()]);
    }
}
```

#### **3. Handler procesa el Command:**
```php
// app/Modules/Employees/Application/Handlers/CreateEmployeeHandler.php
class CreateEmployeeHandler
{
    public function __construct(
        private EmployeeService $employeeService
    ) {}
    
    public function handle(CreateEmployeeCommand $command): EmployeeDTO
    {
        // 1. Crear ValueObjects desde strings primitivos
        $email = new Email($command->email);                    // ✅ String → Email ValueObject
        $phone = new Phone($command->phone);                    // ✅ String → Phone ValueObject
        $document = new DocumentId($command->documentType, $command->documentNumber); // ✅ String → DocumentId ValueObject
        
        // 2. Crear Entity con ValueObjects
        $employee = new Employee(
            id: Str::uuid(),
            firstName: $command->firstName,
            lastName: $command->lastName,
            email: $email,                                      // ✅ Email ValueObject
            phone: $phone,                                      // ✅ Phone ValueObject
            document: $document                                 // ✅ DocumentId ValueObject
        );
        
        // 3. Usar Service del Domain
        $this->employeeService->validateEmployee($employee);
        
        // 4. Persistir Entity
        $this->employeeRepository->save($employee);
        
        // 5. Crear DTO de respuesta
        return new EmployeeDTO(
            id: $employee->id(),
            firstName: $employee->firstName(),
            lastName: $employee->lastName(),
            email: $employee->email()->value(),                 // ✅ Email ValueObject → String
            phone: $employee->phone()->value(),                 // ✅ Phone ValueObject → String
            document: $employee->document()->toString()         // ✅ DocumentId ValueObject → String
        );
    }
}
```

#### **4. Transformaciones de datos durante el flujo:**

**1. HTTP Request → Command:**
```php
// String primitivo → String primitivo
"juan.perez@empresa.com" → $command->email
```

**2. Command → ValueObjects:**
```php
// String primitivo → ValueObject
$command->email → new Email($command->email)
```

**3. ValueObjects → Entity:**
```php
// ValueObjects → Entity properties
new Email($command->email) → $employee->email
```

**4. Entity → Repository:**
```php
// ValueObjects → String primitivos para persistencia
$employee->email()->value() → $model->email
```

**5. Repository → Entity:**
```php
// String primitivos → ValueObjects
$model->email → new Email($model->email)
```

**6. Entity → DTO:**
```php
// ValueObjects → String primitivos para respuesta
$employee->email()->value() → $dto->email
```

#### **5. Flujo visual del proceso:**
```
1. HTTP Request (String) 
   ↓
2. Controller (String)
   ↓
3. Command (String)
   ↓
4. Handler → ValueObjects (Email, Phone, DocumentId)
   ↓
5. Entity (ValueObjects)
   ↓
6. Service (ValueObjects)
   ↓
7. Repository (ValueObjects → String para DB)
   ↓
8. Database (String)
   ↓
9. Repository (String → ValueObjects)
   ↓
10. Entity (ValueObjects)
    ↓
11. DTO (ValueObjects → String)
    ↓
12. HTTP Response (String)
```

---

## ✅ Mejores prácticas

### **1. Inmutabilidad:**
```php
// ✅ BIEN - Inmutable
class Email
{
    public function __construct(private readonly string $email)
    {
        $this->validate();
    }
    
    public function value(): string
    {
        return $this->email; // Solo lectura
    }
}

// ❌ MAL - Mutable
class Email
{
    private string $email;
    
    public function setEmail(string $email): void
    {
        $this->email = $email; // ❌ Puede cambiar
    }
}
```

### **2. Validación en constructor:**
```php
// ✅ BIEN - Validación en constructor
class Email
{
    public function __construct(private readonly string $email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException('Invalid email format');
        }
    }
}

// ❌ MAL - Sin validación
class Email
{
    public function __construct(private readonly string $email)
    {
        // ❌ No valida el formato
    }
}
```

### **3. Método equals:**
```php
// ✅ BIEN - Método equals
class Email
{
    public function equals(Email $other): bool
    {
        return strtolower($this->email) === strtolower($other->email);
    }
}

// ❌ MAL - Sin método equals
class Email
{
    // ❌ No se puede comparar
}
```

### **4. Métodos de utilidad:**
```php
// ✅ BIEN - Métodos útiles
class Email
{
    public function domain(): string
    {
        return substr($this->email, strpos($this->email, '@') + 1);
    }
    
    public function localPart(): string
    {
        return substr($this->email, 0, strpos($this->email, '@'));
    }
    
    public function isGmail(): bool
    {
        return $this->domain() === 'gmail.com';
    }
}

// ❌ MAL - Solo getter básico
class Email
{
    public function value(): string
    {
        return $this->email; // ❌ Solo retorna el valor
    }
}
```

### **5. Nombres descriptivos:**
```php
// ✅ BIEN - Nombres descriptivos
class Email
class Phone
class DocumentId
class Money

// ❌ MAL - Nombres genéricos
class StringValue
class Data
class Info
```

---

## 🧪 Testing de ValueObjects

### **Test básico:**
```php
// tests/Unit/Domain/ValueObjects/EmailTest.php
class EmailTest extends TestCase
{
    public function test_creates_email_with_valid_format(): void
    {
        $email = new Email('juan@email.com');
        
        $this->assertEquals('juan@email.com', $email->value());
    }
    
    public function test_throws_exception_with_invalid_format(): void
    {
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage('Invalid email format');
        
        new Email('invalid-email');
    }
    
    public function test_equals_returns_true_for_same_email(): void
    {
        $email1 = new Email('juan@email.com');
        $email2 = new Email('juan@email.com');
        
        $this->assertTrue($email1->equals($email2));
    }
    
    public function test_equals_returns_false_for_different_email(): void
    {
        $email1 = new Email('juan@email.com');
        $email2 = new Email('maria@email.com');
        
        $this->assertFalse($email1->equals($email2));
    }
    
    public function test_domain_returns_correct_domain(): void
    {
        $email = new Email('juan@email.com');
        
        $this->assertEquals('email.com', $email->domain());
    }
    
    public function test_local_part_returns_correct_local_part(): void
    {
        $email = new Email('juan@email.com');
        
        $this->assertEquals('juan', $email->localPart());
    }
    
    public function test_is_gmail_returns_true_for_gmail(): void
    {
        $email = new Email('juan@gmail.com');
        
        $this->assertTrue($email->isGmail());
    }
    
    public function test_is_gmail_returns_false_for_non_gmail(): void
    {
        $email = new Email('juan@empresa.com');
        
        $this->assertFalse($email->isGmail());
    }
}
```

---

## 🔗 Relación con otras capas

### **1. Con Entities:**
```php
// Entities usan ValueObjects
class Employee
{
    public function __construct(
        private string $id,
        private string $firstName,
        private string $lastName,
        private Email $email,           // ✅ ValueObject
        private Phone $phone,           // ✅ ValueObject
        private DocumentId $document    // ✅ ValueObject
    ) {}
}
```

### **2. Con Services:**
```php
// Services usan ValueObjects
class EmployeeService
{
    public function validateEmployee(Employee $employee): void
    {
        if ($this->emailExists($employee->email())) {
            throw new EmployeeAlreadyExistsException();
        }
    }
    
    private function emailExists(Email $email): bool
    {
        return $this->employeeRepository->findByEmail($email) !== null;
    }
}
```

### **3. Con Repositories:**
```php
// Repositories usan ValueObjects
interface EmployeeRepositoryInterface
{
    public function findByEmail(Email $email): ?Employee;
    public function findByDocument(DocumentId $document): ?Employee;
}
```

---

## 💻 Ejemplos prácticos

### **Ejemplo 1: ValueObject Email completo**
```php
// app/Modules/Employees/Domain/ValueObjects/Email.php
final class Email
{
    public function __construct(private readonly string $email)
    {
        $this->validate();
    }
    
    public function value(): string
    {
        return $this->email;
    }
    
    public function domain(): string
    {
        return substr($this->email, strpos($this->email, '@') + 1);
    }
    
    public function localPart(): string
    {
        return substr($this->email, 0, strpos($this->email, '@'));
    }
    
    public function isGmail(): bool
    {
        return $this->domain() === 'gmail.com';
    }
    
    public function isCompany(): bool
    {
        return $this->domain() === 'empresa.com';
    }
    
    public function equals(Email $other): bool
    {
        return strtolower($this->email) === strtolower($other->email);
    }
    
    public function __toString(): string
    {
        return $this->email;
    }
    
    private function validate(): void
    {
        if (empty($this->email)) {
            throw new InvalidEmailException('Email cannot be empty');
        }
        
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException('Invalid email format');
        }
    }
}
```

### **Ejemplo 2: ValueObject Money completo**
```php
// app/Modules/Employees/Domain/ValueObjects/Money.php
final class Money
{
    public function __construct(
        private readonly float $amount,
        private readonly string $currency
    ) {
        $this->validate();
    }
    
    public function amount(): float
    {
        return $this->amount;
    }
    
    public function currency(): string
    {
        return $this->currency;
    }
    
    public function add(Money $other): Money
    {
        if ($this->currency !== $other->currency) {
            throw new CurrencyMismatchException('Cannot add different currencies');
        }
        
        return new Money($this->amount + $other->amount, $this->currency);
    }
    
    public function subtract(Money $other): Money
    {
        if ($this->currency !== $other->currency) {
            throw new CurrencyMismatchException('Cannot subtract different currencies');
        }
        
        return new Money($this->amount - $other->amount, $this->currency);
    }
    
    public function multiply(float $factor): Money
    {
        return new Money($this->amount * $factor, $this->currency);
    }
    
    public function divide(float $divisor): Money
    {
        if ($divisor === 0) {
            throw new DivisionByZeroException('Cannot divide by zero');
        }
        
        return new Money($this->amount / $divisor, $this->currency);
    }
    
    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount && 
               $this->currency === $other->currency;
    }
    
    public function formatted(): string
    {
        return number_format($this->amount, 2) . ' ' . $this->currency;
    }
    
    public function isZero(): bool
    {
        return $this->amount === 0.0;
    }
    
    public function isPositive(): bool
    {
        return $this->amount > 0;
    }
    
    public function isNegative(): bool
    {
        return $this->amount < 0;
    }
    
    private function validate(): void
    {
        if ($this->amount < 0) {
            throw new InvalidMoneyException('Amount cannot be negative');
        }
        
        if (empty($this->currency)) {
            throw new InvalidMoneyException('Currency is required');
        }
        
        if (!in_array($this->currency, ['USD', 'EUR', 'COP'])) {
            throw new InvalidMoneyException('Unsupported currency');
        }
    }
}
```

---

## 🎯 ¿Cuándo usar ValueObjects?

### **✅ SÍ usar ValueObjects cuando:**
- **Representas** conceptos del negocio
- **No necesitas** identidad única
- **Quieres** validación automática
- **Necesitas** comparación por valor
- **Quieres** inmutabilidad
- **Tienes** lógica específica del concepto

### **❌ NO usar ValueObjects cuando:**
- **Necesitas** identidad única (usa Entity)
- **Solo necesitas** tipos primitivos simples
- **No hay lógica** de validación
- **Es un prototipo** simple
- **No hay comportamiento** específico

---

## 🎯 Resumen

### **🎯 ValueObjects son:**
- **Objetos** que representan conceptos del negocio
- **Sin identidad** única
- **Inmutables** (no cambian)
- **Se comparan** por valor
- **Reutilizables** y seguros

### **🎯 Para qué sirven:**
- Representar conceptos del negocio
- Validar datos automáticamente
- Facilitar la comparación
- Mantener la inmutabilidad
- Mejorar la expresividad del código
- Prevenir errores comunes

### **📋 Características clave:**
- Sin identidad única
- Inmutables
- Se comparan por valor
- Validación en constructor
- Métodos de utilidad específicos

### **✅ Mejores prácticas:**
- Inmutabilidad
- Validación en constructor
- Método equals
- Métodos de utilidad
- Nombres descriptivos

### **🚀 Valor agregado real:**
- **Validación automática** - Una vez, en un lugar
- **Comportamiento específico** - Lógica del concepto encapsulada
- **Comparación segura** - Maneja casos edge automáticamente
- **Prevención de errores** - Imposible usar datos inválidos
- **Expresividad** - Código más claro y legible
- **Mantenibilidad** - Cambios en un solo lugar

### **🔄 En el ciclo de vida de peticiones HTTP:**
1. **Entrada**: Strings primitivos del HTTP
2. **Transformación**: Strings → ValueObjects
3. **Procesamiento**: ValueObjects en toda la lógica de negocio
4. **Persistencia**: ValueObjects → Strings para base de datos
5. **Recuperación**: Strings → ValueObjects desde base de datos
6. **Salida**: ValueObjects → Strings para HTTP response

**¡Los ValueObjects son el puente entre los datos primitivos del HTTP y los conceptos ricos del dominio, aportando validación automática, comportamiento específico y prevención de errores!** 🚀
