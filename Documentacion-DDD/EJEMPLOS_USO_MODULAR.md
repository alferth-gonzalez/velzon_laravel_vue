# 💡 Ejemplos Prácticos - Arquitectura Modular

## 🎯 Cómo Crear un Nuevo Módulo

### 1. 📁 Estructura Base
```bash
# Crear estructura de carpetas
mkdir -p app/Modules/Products/{Domain,Application,Infrastructure}
mkdir -p app/Modules/Products/Domain/{Entities,ValueObjects,Events,Services,Exceptions}
mkdir -p app/Modules/Products/Application/{Commands,Queries,DTOs,Handlers}
mkdir -p app/Modules/Products/Infrastructure/{Database,Http,External}
mkdir -p app/Modules/Products/Infrastructure/Database/{Models,Migrations,Repositories,Seeders}
mkdir -p app/Modules/Products/Infrastructure/Http/{Controllers,Requests,Resources,Routes}
```

### 2. 🔧 ServiceProvider del Módulo
```php
<?php
// app/Modules/Products/ProductsServiceProvider.php

namespace App\Modules\Products;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

class ProductsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Registrar bindings
        $this->app->bind(
            ProductRepositoryInterface::class,
            EloquentProductRepository::class
        );
        
        // Registrar comandos
        $this->app->bind(CreateProductCommand::class);
        $this->app->bind(UpdateProductCommand::class);
    }

    public function boot(): void
    {
        // Cargar rutas
        $this->loadRoutesFrom(__DIR__ . '/Infrastructure/Http/Routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/Infrastructure/Http/Routes/web.php');
        
        // Cargar migraciones
        $this->loadMigrationsFrom(__DIR__ . '/Infrastructure/Database/Migrations');
        
        // Registrar eventos
        Event::listen(ProductCreated::class, SendInventoryNotificationHandler::class);
    }
}
```

### 3. 📋 Registrar en config/app.php
```php
// config/app.php
'providers' => [
    // ... otros providers
    App\Modules\Products\ProductsServiceProvider::class,
    App\Modules\Customers\CustomersServiceProvider::class,
],
```

## 🏢 Ejemplos de Domain Layer

### 1. 📦 Entidad Product
```php
<?php
// app/Modules/Products/Domain/Entities/Product.php

namespace App\Modules\Products\Domain\Entities;

use App\Modules\Products\Domain\ValueObjects\ProductCode;
use App\Modules\Products\Domain\ValueObjects\Price;
use App\Modules\Products\Domain\ValueObjects\ProductStatus;

class Product
{
    public function __construct(
        private ProductId $id,
        private ProductCode $code,
        private string $name,
        private string $description,
        private Price $price,
        private int $stock,
        private ProductStatus $status,
        private ?\DateTimeImmutable $createdAt = null
    ) {
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
    }

    public static function create(
        ProductCode $code,
        string $name,
        string $description,
        Price $price,
        int $initialStock = 0
    ): self {
        $product = new self(
            id: ProductId::generate(),
            code: $code,
            name: $name,
            description: $description,
            price: $price,
            stock: $initialStock,
            status: ProductStatus::ACTIVE
        );

        // Disparar evento
        event(new ProductCreated($product));

        return $product;
    }

    public function updatePrice(Price $newPrice): void
    {
        $oldPrice = $this->price;
        $this->price = $newPrice;

        event(new ProductPriceUpdated($this, $oldPrice, $newPrice));
    }

    public function addStock(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new InvalidStockQuantityException($quantity);
        }

        $this->stock += $quantity;
        event(new StockAdded($this, $quantity));
    }

    public function removeStock(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new InvalidStockQuantityException($quantity);
        }

        if ($this->stock < $quantity) {
            throw new InsufficientStockException($this, $quantity);
        }

        $this->stock -= $quantity;
        event(new StockRemoved($this, $quantity));

        if ($this->stock <= 0) {
            event(new ProductOutOfStock($this));
        }
    }

    public function isAvailable(): bool
    {
        return $this->status->isActive() && $this->stock > 0;
    }

    // Getters
    public function getId(): ProductId { return $this->id; }
    public function getCode(): ProductCode { return $this->code; }
    public function getName(): string { return $this->name; }
    public function getPrice(): Price { return $this->price; }
    public function getStock(): int { return $this->stock; }
    public function getStatus(): ProductStatus { return $this->status; }
}
```

### 2. 💰 Value Object Price
```php
<?php
// app/Modules/Products/Domain/ValueObjects/Price.php

namespace App\Modules\Products\Domain\ValueObjects;

class Price
{
    private function __construct(
        private float $amount,
        private string $currency = 'COP'
    ) {
        if ($amount < 0) {
            throw new InvalidPriceException("Price cannot be negative: {$amount}");
        }
    }

    public static function fromAmount(float $amount, string $currency = 'COP'): self
    {
        return new self($amount, strtoupper($currency));
    }

    public static function free(): self
    {
        return new self(0.0);
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getFormattedAmount(): string
    {
        return number_format($this->amount, 2, ',', '.');
    }

    public function add(Price $other): self
    {
        $this->ensureSameCurrency($other);
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function multiply(float $factor): self
    {
        return new self($this->amount * $factor, $this->currency);
    }

    public function isGreaterThan(Price $other): bool
    {
        $this->ensureSameCurrency($other);
        return $this->amount > $other->amount;
    }

    public function equals(Price $other): bool
    {
        return $this->amount === $other->amount && $this->currency === $other->currency;
    }

    private function ensureSameCurrency(Price $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new CurrencyMismatchException(
                "Cannot operate with different currencies: {$this->currency} vs {$other->currency}"
            );
        }
    }
}
```

## 🛠️ Ejemplos de Application Layer

### 1. ⚡ Command - Crear Producto
```php
<?php
// app/Modules/Products/Application/Commands/CreateProductCommand.php

namespace App\Modules\Products\Application\Commands;

use App\Modules\Products\Application\DTOs\CreateProductDTO;
use App\Modules\Products\Domain\Entities\Product;
use App\Modules\Products\Domain\ValueObjects\ProductCode;
use App\Modules\Products\Domain\ValueObjects\Price;
use App\Modules\Products\Infrastructure\Database\Repositories\ProductRepositoryInterface;

class CreateProductCommand
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function execute(CreateProductDTO $dto): Product
    {
        // 1. Validar que el código no exista
        if ($this->productRepository->findByCode($dto->code)) {
            throw new DuplicateProductCodeException($dto->code);
        }

        // 2. Crear value objects
        $code = ProductCode::fromString($dto->code);
        $price = Price::fromAmount($dto->price, $dto->currency);

        // 3. Crear entidad
        $product = Product::create(
            code: $code,
            name: $dto->name,
            description: $dto->description,
            price: $price,
            initialStock: $dto->initialStock
        );

        // 4. Persistir
        $this->productRepository->save($product);

        return $product;
    }
}
```

### 2. 🔍 Query - Buscar Productos
```php
<?php
// app/Modules/Products/Application/Queries/SearchProductsQuery.php

namespace App\Modules\Products\Application\Queries;

use App\Modules\Products\Application\DTOs\ProductSearchDTO;
use App\Modules\Products\Infrastructure\Database\Repositories\ProductRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchProductsQuery
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function execute(ProductSearchDTO $searchDto): LengthAwarePaginator
    {
        return $this->productRepository->searchWithFilters(
            search: $searchDto->search,
            category: $searchDto->category,
            minPrice: $searchDto->minPrice,
            maxPrice: $searchDto->maxPrice,
            inStock: $searchDto->inStock,
            status: $searchDto->status,
            page: $searchDto->page,
            perPage: $searchDto->perPage
        );
    }
}
```

### 3. 📦 DTO - Crear Producto
```php
<?php
// app/Modules/Products/Application/DTOs/CreateProductDTO.php

namespace App\Modules\Products\Application\DTOs;

class CreateProductDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly string $description,
        public readonly float $price,
        public readonly string $currency,
        public readonly int $initialStock,
        public readonly ?string $category,
        public readonly int $createdBy
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            code: $data['code'],
            name: $data['name'],
            description: $data['description'] ?? '',
            price: (float) $data['price'],
            currency: $data['currency'] ?? 'COP',
            initialStock: (int) ($data['initial_stock'] ?? 0),
            category: $data['category'] ?? null,
            createdBy: (int) $data['created_by']
        );
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'currency' => $this->currency,
            'initial_stock' => $this->initialStock,
            'category' => $this->category,
            'created_by' => $this->createdBy,
        ];
    }
}
```

## 🚀 Ejemplos de Infrastructure Layer

### 1. 🎮 Controller
```php
<?php
// app/Modules/Products/Infrastructure/Http/Controllers/ProductController.php

namespace App\Modules\Products\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Products\Application\Commands\CreateProductCommand;
use App\Modules\Products\Application\Queries\SearchProductsQuery;
use App\Modules\Products\Application\DTOs\CreateProductDTO;
use App\Modules\Products\Application\DTOs\ProductSearchDTO;
use App\Modules\Products\Infrastructure\Http\Requests\CreateProductRequest;
use App\Modules\Products\Infrastructure\Http\Requests\ProductSearchRequest;
use App\Modules\Products\Infrastructure\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __construct(
        private CreateProductCommand $createCommand,
        private SearchProductsQuery $searchQuery
    ) {}

    public function index(ProductSearchRequest $request): JsonResponse
    {
        $searchDto = ProductSearchDTO::fromRequest($request);
        $products = $this->searchQuery->execute($searchDto);

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products->items()),
            'meta' => [
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'last_page' => $products->lastPage(),
            ]
        ]);
    }

    public function store(CreateProductRequest $request): JsonResponse
    {
        try {
            $dto = CreateProductDTO::fromArray(
                array_merge($request->validated(), ['created_by' => $request->user()->id])
            );
            
            $product = $this->createCommand->execute($dto);

            return response()->json([
                'success' => true,
                'message' => 'Producto creado exitosamente',
                'data' => new ProductResource($product)
            ], 201);

        } catch (DuplicateProductCodeException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe un producto con ese código',
                'error' => $e->getMessage()
            ], 422);
        }
    }
}
```

### 2. 📝 Form Request
```php
<?php
// app/Modules/Products/Infrastructure/Http/Requests/CreateProductRequest.php

namespace App\Modules\Products\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('products.create');
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Z0-9\-]+$/',
                Rule::unique('products', 'code')
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'in:COP,USD,EUR'],
            'initial_stock' => ['sometimes', 'integer', 'min:0'],
            'category' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'El código del producto es obligatorio.',
            'code.unique' => 'Ya existe un producto con este código.',
            'code.regex' => 'El código solo puede contener letras mayúsculas, números y guiones.',
            'name.required' => 'El nombre del producto es obligatorio.',
            'price.required' => 'El precio es obligatorio.',
            'price.numeric' => 'El precio debe ser un número.',
            'price.min' => 'El precio no puede ser negativo.',
        ];
    }
}
```

### 3. 🔧 Resource
```php
<?php
// app/Modules/Products/Infrastructure/Http/Resources/ProductResource.php

namespace App\Modules\Products\Infrastructure\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'price' => [
                'amount' => $this->price,
                'currency' => $this->currency,
                'formatted' => $this->getFormattedPrice()
            ],
            'stock' => [
                'quantity' => $this->stock,
                'available' => $this->stock > 0,
                'status' => $this->getStockStatus()
            ],
            'status' => [
                'value' => $this->status,
                'label' => $this->getStatusLabel(),
                'color' => $this->getStatusColor()
            ],
            'category' => $this->category,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Campos condicionales
            'inventory' => $this->whenLoaded('inventory'),
            'category_info' => $this->whenLoaded('categoryInfo'),
        ];
    }

    private function getFormattedPrice(): string
    {
        return number_format($this->price, 2, ',', '.') . ' ' . $this->currency;
    }

    private function getStockStatus(): string
    {
        if ($this->stock <= 0) return 'out_of_stock';
        if ($this->stock <= 10) return 'low_stock';
        return 'in_stock';
    }

    private function getStatusLabel(): string
    {
        return match($this->status) {
            'active' => 'Activo',
            'inactive' => 'Inactivo',
            'discontinued' => 'Descontinuado',
            default => 'Desconocido'
        };
    }

    private function getStatusColor(): string
    {
        return match($this->status) {
            'active' => 'success',
            'inactive' => 'warning',
            'discontinued' => 'danger',
            default => 'secondary'
        };
    }
}
```

## 🌐 Comunicación Entre Módulos

### 1. 📡 Eventos de Dominio
```php
<?php
// Módulo Products emite evento
namespace App\Modules\Products\Domain\Events;

class ProductOutOfStock
{
    public function __construct(
        public readonly Product $product,
        public readonly \DateTimeImmutable $occurredAt
    ) {}
}

// Módulo Notifications escucha evento
namespace App\Modules\Notifications\Application\Handlers;

class NotifyProductOutOfStockHandler
{
    public function handle(ProductOutOfStock $event): void
    {
        // Enviar notificación al equipo de inventario
        $this->notificationService->send([
            'to' => 'inventory@empresa.com',
            'subject' => 'Producto sin stock',
            'message' => "El producto {$event->product->getName()} se ha quedado sin stock."
        ]);
    }
}

// Registro en ServiceProvider
Event::listen(ProductOutOfStock::class, NotifyProductOutOfStockHandler::class);
```

### 2. 🔌 Servicio Compartido
```php
<?php
// Interfaz compartida
namespace App\Shared\Services;

interface NotificationServiceInterface
{
    public function send(array $notification): void;
    public function sendBulk(array $notifications): void;
}

// Implementación
namespace App\Shared\Services;

class EmailNotificationService implements NotificationServiceInterface
{
    public function send(array $notification): void
    {
        Mail::to($notification['to'])
            ->send(new GenericNotification(
                $notification['subject'],
                $notification['message']
            ));
    }
}

// Registro en AppServiceProvider
$this->app->bind(
    NotificationServiceInterface::class,
    EmailNotificationService::class
);
```

### 3. 🌍 API Interna
```php
<?php
// Desde módulo Orders llamar a módulo Products
namespace App\Modules\Orders\Application\Services;

class OrderService
{
    public function createOrder(CreateOrderDTO $dto): Order
    {
        // Verificar disponibilidad de productos
        $response = Http::get('/api/products/availability', [
            'product_ids' => $dto->productIds
        ]);

        if (!$response->successful()) {
            throw new ProductAvailabilityException();
        }

        $availability = $response->json('data');
        
        // Continuar con creación de orden...
    }
}
```

## 🧪 Testing Ejemplos

### 1. 🔬 Unit Test - Value Object
```php
<?php
// tests/Unit/Modules/Products/Domain/ValueObjects/PriceTest.php

use App\Modules\Products\Domain\ValueObjects\Price;
use App\Modules\Products\Domain\Exceptions\InvalidPriceException;

class PriceTest extends TestCase
{
    /** @test */
    public function can_create_valid_price()
    {
        $price = Price::fromAmount(100.50, 'COP');
        
        $this->assertEquals(100.50, $price->getAmount());
        $this->assertEquals('COP', $price->getCurrency());
        $this->assertEquals('100,50', $price->getFormattedAmount());
    }

    /** @test */
    public function cannot_create_negative_price()
    {
        $this->expectException(InvalidPriceException::class);
        
        Price::fromAmount(-10.0);
    }

    /** @test */
    public function can_add_prices_same_currency()
    {
        $price1 = Price::fromAmount(100.0, 'COP');
        $price2 = Price::fromAmount(50.0, 'COP');
        
        $total = $price1->add($price2);
        
        $this->assertEquals(150.0, $total->getAmount());
        $this->assertEquals('COP', $total->getCurrency());
    }

    /** @test */
    public function cannot_add_prices_different_currency()
    {
        $price1 = Price::fromAmount(100.0, 'COP');
        $price2 = Price::fromAmount(50.0, 'USD');
        
        $this->expectException(CurrencyMismatchException::class);
        
        $price1->add($price2);
    }
}
```

### 2. 🏗️ Feature Test - API
```php
<?php
// tests/Feature/Modules/Products/ProductCreationTest.php

use App\Modules\Products\Infrastructure\Database\Models\Product;

class ProductCreationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_create_product_via_api()
    {
        $productData = [
            'code' => 'PROD-001',
            'name' => 'Producto de Prueba',
            'description' => 'Descripción del producto',
            'price' => 29.99,
            'currency' => 'COP',
            'initial_stock' => 100,
            'category' => 'Electronics'
        ];

        $response = $this->actingAs($this->createUser())
                         ->postJson('/api/products', $productData);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'id', 'code', 'name', 'price', 'stock'
                     ]
                 ]);

        $this->assertDatabaseHas('products', [
            'code' => 'PROD-001',
            'name' => 'Producto de Prueba',
            'price' => 29.99
        ]);
    }

    /** @test */
    public function cannot_create_product_with_duplicate_code()
    {
        Product::factory()->create(['code' => 'PROD-001']);

        $productData = [
            'code' => 'PROD-001',
            'name' => 'Otro Producto',
            'price' => 50.0
        ];

        $response = $this->actingAs($this->createUser())
                         ->postJson('/api/products', $productData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['code']);
    }
}
```

## 🚀 Comandos Útiles

```bash
# Crear nuevo módulo completo
php artisan make:module Orders

# Migrar módulo específico
php artisan migrate --path=app/Modules/Products/Infrastructure/Database/Migrations

# Rollback módulo específico
php artisan migrate:rollback --path=app/Modules/Products/Infrastructure/Database/Migrations

# Seeders por módulo
php artisan db:seed --class=Products\\Infrastructure\\Database\\Seeders\\ProductSeeder

# Tests por módulo
php artisan test tests/Feature/Modules/Products/
php artisan test --filter=Product

# Rutas por módulo
php artisan route:list --name=products

# Cache management
php artisan route:cache
php artisan config:cache
php artisan event:cache
```

## 📈 Métricas y Monitoreo

```php
<?php
// Logging estructurado por módulo
Log::withContext([
    'module' => 'products',
    'action' => 'create',
    'product_id' => $product->getId(),
    'user_id' => $userId
])->info('Product created successfully');

// Métricas por módulo
Metrics::increment('products.created', ['category' => $product->getCategory()]);
Metrics::histogram('products.creation_time', $duration, ['module' => 'products']);
Metrics::gauge('products.total_stock', $totalStock);
```

---

**Esta guía proporciona ejemplos prácticos para implementar la arquitectura modular en tu proyecto Laravel con Vue.js**
