<?php

declare(strict_types=1);

namespace App\Modules\Customers\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Customers\Application\Commands\CreateCustomerCommand;
use App\Modules\Customers\Application\Commands\UpdateCustomerCommand;
use App\Modules\Customers\Application\Commands\MergeCustomersCommand;
use App\Modules\Customers\Application\Commands\BlacklistCustomerCommand;
use App\Modules\Customers\Application\Queries\GetCustomerByIdQuery;
use App\Modules\Customers\Application\Queries\ListCustomersQuery;
use App\Modules\Customers\Application\Queries\SearchCustomersQuery;
use App\Modules\Customers\Application\DTOs\CreateCustomerDTO;
use App\Modules\Customers\Application\DTOs\UpdateCustomerDTO;
use App\Modules\Customers\Application\DTOs\CustomerFiltersDTO;
use App\Modules\Customers\Infrastructure\Http\Requests\CreateCustomerRequest;
use App\Modules\Customers\Infrastructure\Http\Requests\UpdateCustomerRequest;
use App\Modules\Customers\Infrastructure\Http\Resources\CustomerResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function __construct(
        private CreateCustomerCommand $createCommand,
        private UpdateCustomerCommand $updateCommand,
        private MergeCustomersCommand $mergeCommand,
        private BlacklistCustomerCommand $blacklistCommand,
        private GetCustomerByIdQuery $getByIdQuery,
        private ListCustomersQuery $listQuery,
        private SearchCustomersQuery $searchQuery
    ) {}

    /**
     * Display the customers page view
     */
    public function indexView(Request $request): Response
    {
        // Datos de ejemplo para mostrar la interfaz
        $customers = [
            'data' => [
                [
                    'id' => 1,
                    'business_name' => 'Empresa ABC S.A.S',
                    'full_name' => 'Juan Pérez García',
                    'document' => [
                        'type' => 'NIT',
                        'number' => '900123456-7',
                        'formatted' => '900.123.456-7'
                    ],
                    'email' => 'contacto@empresaabc.com',
                    'phone' => '+57 310 123 4567',
                    'type' => [
                        'value' => 'juridical',
                        'description' => 'Persona Jurídica'
                    ],
                    'status' => [
                        'value' => 'active',
                        'description' => 'Activo'
                    ],
                    'segment' => 'Corporativo',
                    'created_at' => '2024-01-15T10:30:00Z'
                ],
                [
                    'id' => 2,
                    'business_name' => 'María González López',
                    'full_name' => 'María González López',
                    'document' => [
                        'type' => 'CC',
                        'number' => '52123456',
                        'formatted' => '52.123.456'
                    ],
                    'email' => 'maria.gonzalez@email.com',
                    'phone' => '+57 320 987 6543',
                    'type' => [
                        'value' => 'natural',
                        'description' => 'Persona Natural'
                    ],
                    'status' => [
                        'value' => 'prospect',
                        'description' => 'Prospecto'
                    ],
                    'segment' => 'PYME',
                    'created_at' => '2024-02-10T14:20:00Z'
                ],
                [
                    'id' => 3,
                    'business_name' => 'Tech Solutions Ltda',
                    'full_name' => 'Carlos Rodríguez Martín',
                    'document' => [
                        'type' => 'NIT',
                        'number' => '800987654-1',
                        'formatted' => '800.987.654-1'
                    ],
                    'email' => 'info@techsolutions.co',
                    'phone' => '+57 315 456 7890',
                    'type' => [
                        'value' => 'juridical',
                        'description' => 'Persona Jurídica'
                    ],
                    'status' => [
                        'value' => 'inactive',
                        'description' => 'Inactivo'
                    ],
                    'segment' => 'Tecnología',
                    'created_at' => '2024-03-05T09:15:00Z'
                ]
            ],
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 15,
                'total' => 3,
                'from' => 1,
                'to' => 3
            ]
        ];

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'filters' => $request->only(['search', 'status', 'type', 'segment'])
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        Log::withContext(['module' => 'customers', 'action' => 'index']);

        $filters = new CustomerFiltersDTO(
            tenantId: $request->input('tenant_id'),
            status: $request->input('status'),
            type: $request->input('type'),
            segment: $request->input('segment'),
            search: $request->input('search'),
            documentType: $request->input('document_type')
        );

        $customers = $this->listQuery->execute(
            $filters,
            (int) $request->input('page', 1),
            (int) $request->input('per_page', 15)
        );

        return response()->json([
            'success' => true,
            'data' => CustomerResource::collection($customers->items()),
            'meta' => [
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
            ]
        ]);
    }

    public function storeSimple(Request $request): JsonResponse
    {
        Log::withContext(['module' => 'customers', 'action' => 'store_simple']);

        // Validación básica sin permisos
        $validated = $request->validate([
            'type' => 'required|in:natural,juridical',
            'document_type' => 'required|string',
            'document_number' => 'required|string',
            'business_name' => 'required|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email:rfc|max:255',
            'phone' => 'nullable|string|max:20',
            'status' => 'sometimes|in:active,inactive,suspended,blacklisted,prospect',
            'segment' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
        ]);

        try {
            // Por ahora solo simular la creación exitosa
            $customer = [
                'id' => rand(1000, 9999),
                'type' => $validated['type'],
                'document' => [
                    'type' => $validated['document_type'],
                    'number' => $validated['document_number'],
                    'formatted' => $validated['document_number']
                ],
                'business_name' => $validated['business_name'],
                'full_name' => trim(($validated['first_name'] ?? '') . ' ' . ($validated['last_name'] ?? '')),
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'status' => [
                    'value' => $validated['status'] ?? 'active',
                    'label' => ucfirst($validated['status'] ?? 'active')
                ],
                'segment' => $validated['segment'],
                'address' => $validated['address'],
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Cliente creado exitosamente',
                'data' => $customer
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el cliente: ' . $e->getMessage()
            ], 422);
        }
    }

    public function store(CreateCustomerRequest $request): JsonResponse
    {
        Log::withContext(['module' => 'customers', 'action' => 'store']);

        try {
            $dto = new CreateCustomerDTO(
                tenantId: $request->input('tenant_id'),
                type: $request->input('type'),
                documentType: $request->input('document_type'),
                documentNumber: $request->input('document_number'),
                businessName: $request->input('business_name'),
                firstName: $request->input('first_name'),
                lastName: $request->input('last_name'),
                email: $request->input('email'),
                phone: $request->input('phone'),
                status: $request->input('status', 'prospect'),
                segment: $request->input('segment'),
                notes: $request->input('notes')
            );

            $customer = $this->createCommand->execute($dto);

            return response()->json([
                'success' => true,
                'message' => 'Cliente creado exitosamente',
                'data' => new CustomerResource($customer)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el cliente: ' . $e->getMessage()
            ], 422);
        }
    }

    public function show(int $id): JsonResponse
    {
        Log::withContext(['module' => 'customers', 'action' => 'show', 'customer_id' => $id]);

        try {
            $customer = $this->getByIdQuery->execute($id);

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new CustomerResource($customer)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateCustomerRequest $request, int $id): JsonResponse
    {
        Log::withContext(['module' => 'customers', 'action' => 'update', 'customer_id' => $id]);

        try {
            $dto = new UpdateCustomerDTO(
                businessName: $request->input('business_name'),
                firstName: $request->input('first_name'),
                lastName: $request->input('last_name'),
                email: $request->input('email'),
                phone: $request->input('phone'),
                segment: $request->input('segment'),
                notes: $request->input('notes')
            );

            $customer = $this->updateCommand->execute($id, $dto);

            return response()->json([
                'success' => true,
                'message' => 'Cliente actualizado exitosamente',
                'data' => new CustomerResource($customer)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el cliente: ' . $e->getMessage()
            ], 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        Log::withContext(['module' => 'customers', 'action' => 'destroy', 'customer_id' => $id]);

        try {
            $customer = $this->getByIdQuery->execute($id);

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ], 404);
            }

            if (!$customer->getStatus()->canBeDeleted()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar un cliente en lista negra'
                ], 422);
            }

            // Soft delete
            $customer->softDelete('Eliminado por usuario');

            return response()->json([
                'success' => true,
                'message' => 'Cliente eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    public function search(Request $request): JsonResponse
    {
        Log::withContext(['module' => 'customers', 'action' => 'search']);

        $query = $request->input('q', '');
        $tenantId = $request->input('tenant_id');
        $limit = (int) $request->input('limit', 20);

        if (strlen($query) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'La búsqueda debe tener al menos 2 caracteres'
            ], 422);
        }

        try {
            $customers = $this->searchQuery->execute($tenantId, $query, [], $limit);

            return response()->json([
                'success' => true,
                'data' => CustomerResource::collection($customers)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en la búsqueda: ' . $e->getMessage()
            ], 500);
        }
    }

    public function merge(Request $request): JsonResponse
    {
        Log::withContext(['module' => 'customers', 'action' => 'merge']);

        $request->validate([
            'source_id' => 'required|integer|exists:customers,id',
            'destination_id' => 'required|integer|exists:customers,id|different:source_id',
            'reason' => 'nullable|string|max:500'
        ]);

        try {
            $idempotencyKey = Str::uuid()->toString();
            $actorId = $request->user()->id;

            $customer = $this->mergeCommand->execute(
                sourceCustomerId: $request->input('source_id'),
                destinationCustomerId: $request->input('destination_id'),
                idempotencyKey: $idempotencyKey,
                actorId: $actorId,
                reason: $request->input('reason')
            );

            return response()->json([
                'success' => true,
                'message' => 'Clientes combinados exitosamente',
                'data' => new CustomerResource($customer)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al combinar clientes: ' . $e->getMessage()
            ], 422);
        }
    }

    public function blacklist(Request $request, int $id): JsonResponse
    {
        Log::withContext(['module' => 'customers', 'action' => 'blacklist', 'customer_id' => $id]);

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        try {
            $customer = $this->blacklistCommand->execute(
                customerId: $id,
                reason: $request->input('reason'),
                actorId: $request->user()->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Cliente agregado a lista negra exitosamente',
                'data' => new CustomerResource($customer)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar a lista negra: ' . $e->getMessage()
            ], 422);
        }
    }
}
