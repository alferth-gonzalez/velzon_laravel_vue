# 📋 Documentación: Arquitectura Monolito Modular

## 🏗️ Descripción General

Este proyecto implementa una **arquitectura de monolito modular** siguiendo principios de **Domain-Driven Design (DDD)** y **Clean Architecture**. Cada módulo es independiente y autocontenido, facilitando el mantenimiento, escalabilidad y eventual migración a microservicios.

## 🎯 Ventajas de esta Arquitectura

- **Separación clara de responsabilidades** por dominio de negocio
- **Facilita el trabajo en equipo** (cada equipo puede trabajar en un módulo)
- **Escalabilidad progresiva** (fácil migración a microservicios)
- **Mantenimiento simplificado** (cambios aislados por módulo)
- **Reutilización de código** entre módulos
- **Testing independiente** por módulo

---

## 📁 Estructura Global del Proyecto

```
corporate/
├── app/
│   ├── Http/Controllers/           # Controladores generales del core
│   ├── Models/                    # Modelos Eloquent del core
│   ├── Modules/                   # 🎯 MÓDULOS DE DOMINIO
│   │   ├── Customers/             # Módulo de Clientes
│   │   ├── Pos/                   # Módulo de Punto de Venta
│   │   └── [OtrosModulos]/        # Futuros módulos
│   └── Providers/                 # Service Providers del core
├── resources/
│   ├── js/
│   │   ├── Pages/
│   │   │   ├── Customers/         # Páginas Vue del módulo Customers
│   │   │   └── [OtrosModulos]/    # Páginas de otros módulos
│   │   └── Components/            # Componentes Vue globales
│   └── views/                     # Vistas Blade (si las hay)
├── routes/
│   ├── web.php                    # Rutas web principales
│   └── api.php                    # Rutas API principales
└── database/
    ├── migrations/                # Migraciones del core
    └── seeders/                   # Seeders del core
```

---

## 🎯 Anatomía de un Módulo

Cada módulo sigue la estructura de **Clean Architecture** con capas claramente definidas:

### 📂 Estructura Detallada del Módulo `Customers`

```
app/Modules/Customers/
├── 📋 CustomersServiceProvider.php     # Registra el módulo en Laravel
├── 📄 README.md                        # Documentación específica del módulo
│
├── 🏢 Domain/                          # CAPA DE DOMINIO (Lógica de Negocio)
│   ├── Entities/                       # Entidades del dominio
│   │   ├── Customer.php                # Entidad principal Cliente
│   │   ├── Contact.php                 # Entidad Contacto
│   │   └── Address.php                 # Entidad Dirección
│   │
│   ├── ValueObjects/                   # Objetos de Valor
│   │   ├── CustomerType.php            # Natural/Jurídica
│   │   ├── CustomerStatus.php          # Activo/Inactivo/Suspendido
│   │   ├── DocumentId.php              # CC/NIT/CE/PA/TI/RC
│   │   ├── Email.php                   # Email validado
│   │   ├── Phone.php                   # Teléfono validado
│   │   └── CountryCode.php             # Código país
│   │
│   ├── Events/                         # Eventos del dominio
│   │   ├── CustomerCreated.php         # Cliente creado
│   │   ├── CustomerUpdated.php         # Cliente actualizado
│   │   ├── CustomerMerged.php          # Clientes fusionados
│   │   └── CustomerBlacklisted.php     # Cliente en lista negra
│   │
│   ├── Services/                       # Servicios del dominio
│   │   ├── CustomerService.php         # Lógica de negocio principal
│   │   ├── DuplicationDetector.php     # Detector de duplicados
│   │   └── ValidationService.php       # Validaciones específicas
│   │
│   └── Exceptions/                     # Excepciones del dominio
│       ├── CustomerNotFoundException.php
│       ├── DuplicateCustomerException.php
│       └── InvalidCustomerDataException.php
│
├── 🛠️ Application/                      # CAPA DE APLICACIÓN (Casos de Uso)
│   ├── Commands/                       # Comandos (Write Operations)
│   │   ├── CreateCustomerCommand.php   # Crear cliente
│   │   ├── UpdateCustomerCommand.php   # Actualizar cliente
│   │   ├── MergeCustomersCommand.php   # Fusionar clientes
│   │   └── BlacklistCustomerCommand.php # Listar cliente
│   │
│   ├── Queries/                        # Consultas (Read Operations)
│   │   ├── GetCustomerByIdQuery.php    # Obtener por ID
│   │   ├── GetCustomersByFiltersQuery.php # Búsqueda con filtros
│   │   └── GetDuplicatesQuery.php      # Encontrar duplicados
│   │
│   ├── DTOs/                          # Data Transfer Objects
│   │   ├── CreateCustomerDTO.php       # DTO para creación
│   │   ├── UpdateCustomerDTO.php       # DTO para actualización
│   │   └── CustomerFilterDTO.php       # DTO para filtros
│   │
│   └── Handlers/                       # Manejadores de eventos
│       ├── SendWelcomeEmailHandler.php # Enviar email bienvenida
│       └── NotifyAccountManagerHandler.php # Notificar account manager
│
└── 🚀 Infrastructure/                   # CAPA DE INFRAESTRUCTURA (Implementaciones)
    ├── Database/
    │   ├── Migrations/                 # Migraciones específicas del módulo
    │   │   ├── 2025_09_12_000001_create_customers_table.php
    │   │   ├── 2025_09_12_000002_create_customer_contacts_table.php
    │   │   ├── 2025_09_12_000003_create_customer_addresses_table.php
    │   │   └── 2025_09_12_000004_create_customer_tax_profiles_table.php
    │   │
    │   ├── Models/                     # Modelos Eloquent
    │   │   ├── Customer.php            # Modelo principal
    │   │   ├── CustomerContact.php     # Modelo contactos
    │   │   ├── CustomerAddress.php     # Modelo direcciones
    │   │   └── CustomerTaxProfile.php  # Modelo perfil tributario
    │   │
    │   ├── Repositories/               # Implementación repositorios
    │   │   ├── EloquentCustomerRepository.php
    │   │   └── EloquentContactRepository.php
    │   │
    │   └── Seeders/                    # Datos de prueba
    │       ├── CustomerSeeder.php
    │       └── ContactSeeder.php
    │
    ├── Http/                          # Capa HTTP
    │   ├── Controllers/                # Controladores
    │   │   └── CustomerController.php  # API REST del módulo
    │   │
    │   ├── Requests/                   # Form Requests
    │   │   ├── CreateCustomerRequest.php
    │   │   ├── UpdateCustomerRequest.php
    │   │   └── CustomerFilterRequest.php
    │   │
    │   ├── Resources/                  # API Resources
    │   │   ├── CustomerResource.php
    │   │   ├── CustomerCollection.php
    │   │   └── ContactResource.php
    │   │
    │   └── Routes/                     # Rutas del módulo
    │       ├── api.php                 # Rutas API
    │       └── web.php                 # Rutas web
    │
    ├── Events/                        # Implementación eventos
    │   └── CustomerEventListener.php
    │
    └── External/                      # Integraciones externas
        ├── DIAN/                      # Integración DIAN
        │   ├── DIANClient.php
        │   └── DIANValidator.php
        └── Email/
            └── EmailService.php
```

---

## 🎯 Responsabilidades por Capa

### 🏢 **Domain Layer** (Capa de Dominio)
**Propósito**: Contiene la lógica de negocio pura, independiente de framework

#### 📋 **Entities** (Entidades)
- **Qué son**: Objetos principales del dominio con identidad única
- **Responsabilidades**:
  - Mantener estado del objeto de negocio
  - Implementar invariantes de negocio
  - Exponer comportamientos específicos del dominio
- **Ejemplo**: `Customer.php` maneja la lógica de un cliente específico

```php
// Ejemplo de método en entidad
public function canBeDeleted(): bool
{
    return !$this->hasActiveOrders() && !$this->isBlacklisted();
}
```

#### 💎 **Value Objects** (Objetos de Valor)
- **Qué son**: Objetos inmutables que representan valores específicos
- **Responsabilidades**:
  - Validar formato y reglas de negocio
  - Ser inmutables
  - Facilitar comparaciones
- **Ejemplo**: `DocumentId.php` valida y formatea documentos

```php
// Ejemplo de Value Object
public static function fromString(string $type, string $number): self
{
    if (!in_array($type, ['CC', 'NIT', 'CE', 'PA'], true)) {
        throw new InvalidDocumentTypeException($type);
    }
    
    return new self($type, self::cleanNumber($number));
}
```

#### 🎯 **Domain Services** (Servicios de Dominio)
- **Qué son**: Lógica de negocio que no pertenece a una entidad específica
- **Responsabilidades**:
  - Coordinación entre múltiples entidades
  - Algoritmos de negocio complejos
  - Validaciones que involucran múltiples objetos

#### 🚨 **Events** (Eventos)
- **Qué son**: Notificaciones de que algo importante ocurrió en el dominio
- **Responsabilidades**:
  - Permitir reacciones asíncronas
  - Mantener consistencia eventual
  - Facilitar integración entre módulos

### 🛠️ **Application Layer** (Capa de Aplicación)
**Propósito**: Orquesta casos de uso y coordina entre dominio e infraestructura

#### ⚡ **Commands** (Comandos)
- **Qué son**: Operaciones que modifican el estado (Write operations)
- **Responsabilidades**:
  - Validar permisos y autorizaciones
  - Coordinar llamadas al dominio
  - Emitir eventos
- **Patrón**: Un comando por caso de uso de escritura

#### 🔍 **Queries** (Consultas)
- **Qué son**: Operaciones de solo lectura (Read operations)
- **Responsabilidades**:
  - Obtener datos para presentación
  - Aplicar filtros y paginación
  - Optimizar consultas
- **Patrón**: Una query por caso de uso de lectura

#### 📦 **DTOs** (Data Transfer Objects)
- **Qué son**: Objetos para transferir datos entre capas
- **Responsabilidades**:
  - Agrupar datos relacionados
  - Validar entrada de datos
  - Facilitar serialización

### 🚀 **Infrastructure Layer** (Capa de Infraestructura)
**Propósito**: Implementa interfaces definidas en capas superiores

#### 🗄️ **Database**
- **Models**: Modelos Eloquent (ORM mapping)
- **Migrations**: Esquema de base de datos
- **Repositories**: Implementación de acceso a datos
- **Seeders**: Datos de prueba y configuración inicial

#### 🌐 **HTTP**
- **Controllers**: Puntos de entrada HTTP
- **Requests**: Validación de entrada HTTP
- **Resources**: Transformación de salida JSON
- **Routes**: Definición de endpoints

#### 🔌 **External**
- **APIs externas**: Clientes para servicios externos
- **Email**: Implementación de envío de emails
- **File Storage**: Manejo de archivos

---

## 📋 Flujo de Datos

### 1. 📥 **Solicitud Entrante**
```
HTTP Request → Route → Controller → Request Validation
```

### 2. 🛠️ **Procesamiento**
```
Controller → Command/Query → Domain Service → Entity → Repository
```

### 3. 📤 **Respuesta**
```
Repository → Entity → Domain Service → Command/Query → Resource → HTTP Response
```

### 4. 🔔 **Eventos (Asíncrono)**
```
Domain Event → Event Handler → External Service
```

---

## 🔧 Configuración de Módulos

### 📋 **ServiceProvider** (Registro del Módulo)

Cada módulo tiene su `ServiceProvider` que registra:

```php
<?php
// app/Modules/Customers/CustomersServiceProvider.php

class CustomersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Registrar interfaces y implementaciones
        $this->app->bind(
            CustomerRepositoryInterface::class,
            EloquentCustomerRepository::class
        );
        
        // Registrar comandos y queries
        $this->app->bind(CreateCustomerCommand::class);
        $this->app->bind(GetCustomerByIdQuery::class);
    }

    public function boot(): void
    {
        // Cargar rutas del módulo
        $this->loadRoutesFrom(__DIR__ . '/Infrastructure/Http/Routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/Infrastructure/Http/Routes/web.php');
        
        // Cargar migraciones
        $this->loadMigrationsFrom(__DIR__ . '/Infrastructure/Database/Migrations');
        
        // Registrar eventos
        Event::listen(CustomerCreated::class, SendWelcomeEmailHandler::class);
    }
}
```

### 🗃️ **Migraciones del Módulo**

Cada módulo maneja sus propias migraciones:

```php
<?php
// Ejemplo: create_customers_table.php

public function up()
{
    Schema::create('customers', function (Blueprint $table) {
        $table->id();
        $table->string('tenant_id')->nullable();
        $table->enum('type', ['natural', 'juridical']);
        $table->string('document_type');
        $table->string('document_number');
        $table->string('business_name');
        $table->string('first_name')->nullable();
        $table->string('last_name')->nullable();
        $table->string('email')->nullable();
        $table->string('phone')->nullable();
        $table->enum('status', ['active', 'inactive', 'suspended', 'blacklisted', 'prospect']);
        $table->string('segment')->nullable();
        $table->text('notes')->nullable();
        $table->timestamps();
        $table->softDeletes();
        
        // Índices
        $table->unique(['tenant_id', 'document_type', 'document_number']);
        $table->unique(['tenant_id', 'email']);
        $table->index(['tenant_id', 'status']);
        $table->index('type');
    });
}
```

---

## 🌐 Frontend por Módulos

### 📂 Estructura Frontend

```
resources/js/Pages/
├── Customers/                         # Páginas del módulo Customers
│   ├── Index.vue                      # Lista de clientes
│   ├── Show.vue                       # Ver cliente
│   ├── Create.vue                     # Crear cliente
│   ├── Edit.vue                       # Editar cliente
│   └── Components/                    # Componentes específicos
│       ├── CustomerForm.vue           # Formulario de cliente
│       ├── CustomerCard.vue           # Tarjeta de cliente
│       └── CustomerModal.vue          # Modal de cliente
│
├── Pos/                              # Páginas del módulo POS
│   ├── Index.vue
│   ├── Sale.vue
│   └── Components/
│
└── Shared/                           # Componentes compartidos
    ├── DataTable.vue
    ├── Modal.vue
    └── Form/
        ├── Input.vue
        └── Select.vue
```

### 🎨 **Componentes Vue por Módulo**

```vue
<!-- Ejemplo: resources/js/Pages/Customers/Index.vue -->
<template>
  <Layout>
    <PageHeader title="Gestión de Clientes" />
    
    <div class="card">
      <div class="card-body">
        <!-- Filtros -->
        <CustomerFilters v-model="filters" @filter="loadCustomers" />
        
        <!-- Tabla -->
        <CustomerTable 
          :customers="customers.data" 
          @edit="editCustomer"
          @delete="deleteCustomer" 
        />
        
        <!-- Paginación -->
        <Pagination :meta="customers.meta" />
      </div>
    </div>
    
    <!-- Modales -->
    <CustomerFormModal 
      v-model:show="showFormModal"
      :customer="selectedCustomer"
      @saved="loadCustomers" 
    />
  </Layout>
</template>

<script>
import { router } from '@inertiajs/vue3'
import CustomerFilters from './Components/CustomerFilters.vue'
import CustomerTable from './Components/CustomerTable.vue'
import CustomerFormModal from './Components/CustomerFormModal.vue'

export default {
  components: {
    CustomerFilters,
    CustomerTable,
    CustomerFormModal
  },
  
  props: {
    customers: Object
  },
  
  methods: {
    loadCustomers() {
      router.get('/customers', this.filters, {
        preserveState: true,
        preserveScroll: true
      })
    }
  }
}
</script>
```

---

## 🔄 Comunicación Entre Módulos

### 1. 🎯 **Eventos de Dominio**
```php
// Módulo A emite evento
event(new CustomerCreated($customer));

// Módulo B escucha evento
class UpdateSalesQuotaHandler
{
    public function handle(CustomerCreated $event)
    {
        // Actualizar cuota de ventas
    }
}
```

### 2. 🔌 **Servicios Compartidos**
```php
// Interfaz compartida
interface NotificationServiceInterface
{
    public function send(string $to, string $message): void;
}

// Implementación en Infrastructure
class EmailNotificationService implements NotificationServiceInterface
{
    // Implementación
}
```

### 3. 📡 **APIs Internas**
```php
// Desde un módulo, llamar API de otro módulo
$response = Http::get('/api/customers/' . $customerId);
```

---

## 🧪 Testing por Módulos

### 📂 Estructura de Tests

```
tests/
├── Feature/
│   └── Modules/
│       ├── Customers/
│       │   ├── CustomerCreationTest.php
│       │   ├── CustomerUpdateTest.php
│       │   └── CustomerApiTest.php
│       └── Pos/
│           └── SaleProcessTest.php
│
└── Unit/
    └── Modules/
        ├── Customers/
        │   ├── Domain/
        │   │   ├── Entities/CustomerTest.php
        │   │   ├── ValueObjects/DocumentIdTest.php
        │   │   └── Services/CustomerServiceTest.php
        │   └── Application/
        │       ├── CreateCustomerCommandTest.php
        │       └── GetCustomerByIdQueryTest.php
        └── Pos/
```

### 🧪 **Ejemplo de Test**

```php
<?php
// tests/Feature/Modules/Customers/CustomerCreationTest.php

class CustomerCreationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_create_a_natural_customer()
    {
        $customerData = [
            'type' => 'natural',
            'document_type' => 'CC',
            'document_number' => '12345678',
            'business_name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'phone' => '+57 300 123 4567',
            'status' => 'active'
        ];

        $response = $this->actingAs($this->user())
                         ->postJson('/api/customers', $customerData);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'id',
                         'type',
                         'document',
                         'business_name',
                         'email',
                         'phone'
                     ]
                 ]);

        $this->assertDatabaseHas('customers', [
            'document_type' => 'CC',
            'document_number' => '12345678',
            'email' => 'juan@example.com'
        ]);
    }
}
```

---

## 🚀 Comandos de Gestión

### 📋 **Comandos Artisan del Módulo**

```bash
# Crear un nuevo módulo
php artisan make:module ModuleName

# Ejecutar migraciones de un módulo específico
php artisan migrate --path=app/Modules/Customers/Infrastructure/Database/Migrations

# Ejecutar seeders de un módulo
php artisan db:seed --class=Customers\\Infrastructure\\Database\\Seeders\\CustomerSeeder

# Listar rutas de un módulo
php artisan route:list --name=customers

# Cache de rutas por módulo
php artisan route:cache
```

### 🔄 **Scripts de Desarrollo**

```bash
# Instalar dependencias
composer install
npm install

# Compilar assets por módulo
npm run build

# Ejecutar tests de un módulo específico
php artisan test --testsuite=Feature --filter=Customer

# Limpiar cachés
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

---

## 📈 Escalabilidad y Futuro

### 🔄 **Migración a Microservicios**

Esta arquitectura facilita la eventual migración a microservicios:

1. **Extraer módulo**: Cada módulo puede convertirse en un microservicio independiente
2. **Mantener interfaces**: Las interfaces definidas facilitan la comunicación
3. **Events → Message Queues**: Los eventos pueden convertirse en mensajes entre servicios
4. **Shared Kernel**: Los objetos compartidos pueden ser librerías

### 📊 **Monitoreo por Módulo**

```php
// Logs estructurados por módulo
Log::withContext([
    'module' => 'customers',
    'action' => 'create',
    'customer_id' => $customer->id
])->info('Customer created successfully');

// Métricas por módulo
Metrics::increment('customers.created');
Metrics::histogram('customers.creation_time', $duration);
```

### 🛡️ **Seguridad por Módulo**

```php
// Políticas específicas por módulo
Gate::define('customers.create', function ($user) {
    return $user->hasPermission('customers.create');
});

// Middleware específico
Route::middleware('customers.permissions')->group(function () {
    // Rutas del módulo
});
```

---

## 📚 Buenas Prácticas

### ✅ **DOs**

1. **Mantener módulos independientes**: Cada módulo debe poder funcionar sin depender de otros
2. **Usar eventos para comunicación**: Facilita desacoplamiento
3. **Definir interfaces claras**: Facilita testing y cambios futuros
4. **Seguir convenciones de naming**: Mantiene consistencia
5. **Documentar cada módulo**: README.md por módulo
6. **Tests exhaustivos**: Cobertura por capa

### ❌ **DON'Ts**

1. **No acceder directamente a BD de otros módulos**: Usar APIs o eventos
2. **No mezclar lógica de dominio con infraestructura**: Mantener separación de capas
3. **No crear dependencias circulares**: Entre módulos
4. **No ignorar eventos de dominio**: Implementar handlers necesarios
5. **No violar encapsulación**: Usar interfaces públicas
6. **No duplicar lógica**: Crear servicios compartidos si es necesario

---

## 🎯 Conclusión

Esta arquitectura modular proporciona:

- **Mantenibilidad**: Código organizado y fácil de mantener
- **Escalabilidad**: Fácil crecimiento horizontal
- **Testabilidad**: Testing independiente por módulo
- **Flexibilidad**: Cambios aislados sin impacto global
- **Reutilización**: Componentes reutilizables
- **Separación de responsabilidades**: Cada capa tiene un propósito claro

La implementación actual del módulo **Customers** sirve como referencia para crear nuevos módulos siguiendo los mismos patrones y convenciones establecidos.
