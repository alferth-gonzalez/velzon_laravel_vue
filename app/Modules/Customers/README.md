# 👥 Módulo Customers - Documentación

## 📋 Descripción
Gestión completa de clientes siguiendo arquitectura DDD (Domain-Driven Design).

## 🏗️ Estructura del Módulo

```
app/Modules/Customers/
├── CustomersServiceProvider.php
├── README.md
├── Domain/                    # Lógica de negocio pura
│   ├── Entities/             # Customer, Contact, Address
│   ├── ValueObjects/         # DocumentId, Email, Phone, etc.
│   ├── Events/              # CustomerCreated, CustomerUpdated
│   ├── Services/            # CustomerService, DuplicationDetector
│   └── Exceptions/          # Excepciones específicas
├── Application/             # Casos de uso
│   ├── Commands/           # CreateCustomer, UpdateCustomer
│   ├── Queries/           # GetCustomerById, GetCustomersByFilters
│   ├── DTOs/             # CreateCustomerDTO, UpdateCustomerDTO
│   └── Handlers/         # Manejadores de eventos
└── Infrastructure/        # Implementaciones técnicas
    ├── Database/         # Models, Migrations, Repositories
    ├── Http/            # Controllers, Requests, Resources
    └── External/        # Integraciones externas
```

## 🎯 Funcionalidades Principales

### ✅ Gestión de Clientes
- CRUD completo (Crear, Leer, Actualizar, Eliminar)
- Personas naturales y jurídicas
- Validación de documentos (CC, NIT, CE, PA, TI, RC)
- Estados: Active, Inactive, Suspended, Blacklisted, Prospect

### ✅ Contactos y Direcciones
- Múltiples contactos por cliente
- Múltiples direcciones (Home, Office, Billing, Shipping)
- Información de contacto adicional

### ✅ Funcionalidades Avanzadas
- Detección de duplicados
- Fusión de clientes
- Lista negra
- Segmentación de clientes
- Perfiles tributarios

## 🏢 Domain Layer

### Entidades Principales

#### Customer.php
```php
- CustomerId $id
- CustomerType $type (Natural/Jurídica)
- DocumentId $document
- string $businessName
- CustomerStatus $status
- Collection $contacts
- Collection $addresses
```

#### Contact.php
```php
- ContactId $id
- string $name
- Email $email
- Phone $phone
- string $position
```

#### Address.php
```php
- AddressId $id
- string $type
- string $line1, $line2
- string $city, $state, $country
- bool $isDefault
```

### Value Objects

#### DocumentId.php
```php
- Valida tipos: CC, NIT, CE, PA, TI, RC
- Formato específico por tipo
- Dígito verificador para NIT
```

#### CustomerType.php
```php
enum CustomerType: string {
    case NATURAL = 'natural';
    case JURIDICAL = 'juridical';
}
```

#### CustomerStatus.php
```php
enum CustomerStatus: string {
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';
    case BLACKLISTED = 'blacklisted';
    case PROSPECT = 'prospect';
}
```

## 🛠️ Application Layer

### Commands (Escritura)
- **CreateCustomerCommand**: Crear nuevo cliente
- **UpdateCustomerCommand**: Actualizar cliente existente
- **MergeCustomersCommand**: Fusionar clientes duplicados
- **BlacklistCustomerCommand**: Marcar cliente en lista negra

### Queries (Lectura)
- **GetCustomerByIdQuery**: Obtener cliente por ID
- **GetCustomersByFiltersQuery**: Búsqueda con filtros
- **GetDuplicatesQuery**: Encontrar duplicados

### DTOs
- **CreateCustomerDTO**: Datos para crear cliente
- **UpdateCustomerDTO**: Datos para actualizar
- **CustomerFilterDTO**: Filtros de búsqueda

## 🚀 Infrastructure Layer

### HTTP Controllers
```php
// CustomerController.php
GET    /api/customers           # Listar con filtros
POST   /api/customers           # Crear nuevo
GET    /api/customers/{id}      # Ver específico
PUT    /api/customers/{id}      # Actualizar
DELETE /api/customers/{id}      # Eliminar
```

### Database Models
- **Customer**: Modelo principal Eloquent
- **CustomerContact**: Contactos adicionales
- **CustomerAddress**: Direcciones del cliente
- **CustomerTaxProfile**: Perfil tributario

### Validaciones
```php
// CreateCustomerRequest.php
'type' => 'required|in:natural,juridical'
'document_type' => 'required|in:CC,NIT,CE,PA,TI,RC'
'document_number' => 'required|unique:customers'
'business_name' => 'required|max:255'
'email' => 'nullable|email|unique:customers'
```

## 🌐 Frontend (Vue.js)

### Páginas
- **Index.vue**: Lista de clientes con filtros
- **Show.vue**: Vista detalle del cliente
- **Create.vue**: Formulario de creación
- **Edit.vue**: Formulario de edición

### Componentes
- **CustomerForm.vue**: Formulario reutilizable
- **CustomerTable.vue**: Tabla de resultados
- **CustomerModal.vue**: Modales para acciones

## 🧪 Testing

### Unit Tests
```php
// CustomerTest.php
- Creación de entidades
- Validación de value objects
- Lógica de negocio

// DocumentIdTest.php
- Validación de documentos
- Formateo específico
```

### Feature Tests
```php
// CustomerApiTest.php
- CRUD via API
- Validaciones HTTP
- Autenticación/Autorización
```

## 🔧 Comandos

```bash
# Migrar módulo
php artisan migrate --path=app/Modules/Customers/Infrastructure/Database/Migrations

# Seeders
php artisan db:seed --class=Customers\\Infrastructure\\Database\\Seeders\\DatabaseSeeder

# Tests
php artisan test --filter=Customer

# Rutas
php artisan route:list --name=customers
```

## 📊 Eventos del Dominio

```php
// CustomerCreated
- Se dispara al crear cliente
- Envía email de bienvenida
- Notifica al account manager

// CustomerUpdated
- Se dispara al actualizar
- Log de cambios
- Sincronización con CRM

// CustomerMerged
- Se dispara al fusionar
- Actualiza referencias
- Limpia datos duplicados
```

## 🔮 Próximas Funcionalidades

- [ ] Gestión de documentos adjuntos
- [ ] Historial de cambios (auditoría)
- [ ] Integración con DIAN
- [ ] Scoring automático de clientes
- [ ] Geolocalización de direcciones
- [ ] Dashboard con métricas
- [ ] Reportes avanzados

---

**Desarrollado siguiendo principios DDD y Clean Architecture**